<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\StudentProfile;
use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SupervisorController extends Controller
{
    /**
     * Get supervisor dashboard
     */
    public function dashboard(): JsonResponse
    {
        $user = auth()->user();
        
        // Get students assigned to this supervisor
        $assignedStudents = StudentProfile::where('supervisor_contact', $user->email)
                                        ->orWhere('supervisor_name', 'like', '%' . $user->name . '%')
                                        ->with(['user', 'logs' => function($query) {
                                            $query->orderBy('log_date', 'desc')->limit(5);
                                        }])
                                        ->get();

        $stats = [
            'total_students' => $assignedStudents->count(),
            'active_students' => $assignedStudents->where('status', 'active')->count(),
            'completed_students' => $assignedStudents->where('status', 'completed')->count(),
            'pending_logs' => Log::whereIn('student_profile_id', $assignedStudents->pluck('id'))
                                ->where('status', 'pending')
                                ->count(),
            'total_hours_supervised' => $assignedStudents->sum('rendered_hours'),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => $stats,
                'recent_activities' => Log::whereIn('student_profile_id', $assignedStudents->pluck('id'))
                                         ->with(['studentProfile.user'])
                                         ->orderBy('created_at', 'desc')
                                         ->limit(10)
                                         ->get()
            ]
        ]);
    }

    /**
     * Get assigned students
     */
    public function students(Request $request): JsonResponse
    {
        $user = auth()->user();
        
        $query = StudentProfile::where('supervisor_contact', $user->email)
                              ->orWhere('supervisor_name', 'like', '%' . $user->name . '%')
                              ->with(['user', 'logs' => function($query) {
                                  $query->orderBy('log_date', 'desc');
                              }]);

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('student_id_number', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($subQuery) use ($search) {
                      $subQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $students = $query->orderBy('created_at', 'desc')
                         ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $students
        ]);
    }

    /**
     * Get student details with full log history
     */
    public function studentDetails(StudentProfile $studentProfile): JsonResponse
    {
        // Verify supervisor has access to this student
        $user = auth()->user();
        $hasAccess = $studentProfile->supervisor_contact === $user->email ||
                    strpos($studentProfile->supervisor_name, $user->name) !== false;

        if (!$hasAccess) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to student profile'
            ], 403);
        }

        $studentProfile->load(['user', 'logs' => function($query) {
            $query->orderBy('log_date', 'desc');
        }]);

        return response()->json([
            'success' => true,
            'data' => $studentProfile
        ]);
    }

    /**
     * Get time logs for assigned students
     */
    public function timeLogs(Request $request): JsonResponse
    {
        $user = auth()->user();
        
        $assignedStudentIds = StudentProfile::where('supervisor_contact', $user->email)
                                          ->orWhere('supervisor_name', 'like', '%' . $user->name . '%')
                                          ->pluck('id');

        $query = Log::whereIn('student_profile_id', $assignedStudentIds)
                   ->with(['studentProfile.user', 'user']);

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->has('date_from')) {
            $query->whereDate('log_date', '>=', $request->date_from);
        }
        if ($request->has('date_to')) {
            $query->whereDate('log_date', '<=', $request->date_to);
        }

        // Filter by student
        if ($request->has('student_id')) {
            $query->where('student_profile_id', $request->student_id);
        }

        $logs = $query->orderBy('log_date', 'desc')
                     ->orderBy('created_at', 'desc')
                     ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $logs
        ]);
    }

    /**
     * Review and approve/reject time log
     */
    public function reviewTimeLog(Request $request, Log $log): JsonResponse
    {
        // Verify supervisor has access to this log
        $user = auth()->user();
        $studentProfile = $log->studentProfile;
        
        $hasAccess = $studentProfile->supervisor_contact === $user->email ||
                    strpos($studentProfile->supervisor_name, $user->name) !== false;

        if (!$hasAccess) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to this time log'
            ], 403);
        }

        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
            'review_notes' => 'nullable|string|max:1000',
            'adjusted_hours' => 'nullable|numeric|min:0|max:24',
        ]);

        $log->status = $validated['status'];
        $log->reviewed_by = $user->id;
        $log->reviewed_at = now();
        $log->review_notes = $validated['review_notes'] ?? null;

        // Adjust hours if specified
        if (isset($validated['adjusted_hours'])) {
            $oldHours = $log->hours_rendered;
            $log->hours_rendered = $validated['adjusted_hours'];
            
            // Update student's total rendered hours
            $studentProfile->rendered_hours += ($validated['adjusted_hours'] - $oldHours);
            $studentProfile->save();
        }

        $log->save();

        return response()->json([
            'success' => true,
            'message' => "Time log {$validated['status']} successfully",
            'data' => $log->load(['studentProfile.user', 'reviewer'])
        ]);
    }

    /**
     * Bulk review multiple time logs
     */
    public function bulkReviewLogs(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'log_ids' => 'required|array',
            'log_ids.*' => 'exists:logs,id',
            'status' => 'required|in:approved,rejected',
            'review_notes' => 'nullable|string|max:1000',
        ]);

        $user = auth()->user();
        $updatedCount = 0;
        $errors = [];

        foreach ($validated['log_ids'] as $logId) {
            $log = Log::find($logId);
            $studentProfile = $log->studentProfile;
            
            $hasAccess = $studentProfile->supervisor_contact === $user->email ||
                        strpos($studentProfile->supervisor_name, $user->name) !== false;

            if (!$hasAccess) {
                $errors[] = "Log ID {$logId}: Unauthorized access";
                continue;
            }

            $log->status = $validated['status'];
            $log->reviewed_by = $user->id;
            $log->reviewed_at = now();
            $log->review_notes = $validated['review_notes'] ?? null;
            $log->save();

            $updatedCount++;
        }

        return response()->json([
            'success' => true,
            'message' => "Successfully reviewed {$updatedCount} time logs",
            'data' => [
                'updated_count' => $updatedCount,
                'errors' => $errors
            ]
        ]);
    }

    /**
     * Get student progress summary
     */
    public function studentProgress(StudentProfile $studentProfile): JsonResponse
    {
        // Verify supervisor has access
        $user = auth()->user();
        $hasAccess = $studentProfile->supervisor_contact === $user->email ||
                    strpos($studentProfile->supervisor_name, $user->name) !== false;

        if (!$hasAccess) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to student progress'
            ], 403);
        }

        $studentProfile->load(['user', 'logs' => function($query) {
            $query->orderBy('log_date', 'desc');
        }]);

        // Calculate progress metrics
        $totalDays = $studentProfile->logs()->count();
        $approvedDays = $studentProfile->logs()->where('status', 'approved')->count();
        $pendingDays = $studentProfile->logs()->where('status', 'pending')->count();
        $rejectedDays = $studentProfile->logs()->where('status', 'rejected')->count();

        // Weekly progress
        $weeklyProgress = $studentProfile->logs()
            ->selectRaw('WEEK(log_date) as week, YEAR(log_date) as year, SUM(hours_rendered) as total_hours, COUNT(*) as days_worked')
            ->groupBy('week', 'year')
            ->orderBy('year', 'desc')
            ->orderBy('week', 'desc')
            ->limit(8)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'student' => $studentProfile,
                'progress_stats' => [
                    'total_days' => $totalDays,
                    'approved_days' => $approvedDays,
                    'pending_days' => $pendingDays,
                    'rejected_days' => $rejectedDays,
                    'completion_percentage' => $studentProfile->completion_percentage,
                    'remaining_hours' => $studentProfile->remaining_hours,
                ],
                'weekly_progress' => $weeklyProgress
            ]
        ]);
    }

    /**
     * Export student reports
     */
    public function exportStudentReport(Request $request, StudentProfile $studentProfile): JsonResponse
    {
        // Verify supervisor has access
        $user = auth()->user();
        $hasAccess = $studentProfile->supervisor_contact === $user->email ||
                    strpos($studentProfile->supervisor_name, $user->name) !== false;

        if (!$hasAccess) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to student report'
            ], 403);
        }

        $validated = $request->validate([
            'format' => 'required|in:json,csv',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
        ]);

        $query = $studentProfile->logs()->with(['user']);

        if ($validated['date_from'] ?? null) {
            $query->whereDate('log_date', '>=', $validated['date_from']);
        }
        if ($validated['date_to'] ?? null) {
            $query->whereDate('log_date', '<=', $validated['date_to']);
        }

        $logs = $query->orderBy('log_date', 'desc')->get();

        if ($validated['format'] === 'csv') {
            // CSV export logic would go here
            return response()->json([
                'success' => true,
                'message' => 'CSV export feature coming soon',
                'data' => $logs
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'student' => $studentProfile->load('user'),
                'logs' => $logs,
                'summary' => [
                    'total_hours' => $logs->sum('hours_rendered'),
                    'total_days' => $logs->count(),
                    'approved_hours' => $logs->where('status', 'approved')->sum('hours_rendered'),
                    'pending_hours' => $logs->where('status', 'pending')->sum('hours_rendered'),
                ]
            ]
        ]);
    }
}
