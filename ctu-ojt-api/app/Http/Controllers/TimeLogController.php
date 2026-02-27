<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Models\StudentProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class TimeLogController extends Controller
{
    /**
     * POST /api/student/time-in
     * Log time in for today
     */
    public function timeIn(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        // Get or create student profile
        $studentProfile = $user->studentProfile;
        if (!$studentProfile) {
            return response()->json([
                'message' => 'Student profile not found. Please complete your profile first.',
            ], 404);
        }

        $validated = $request->validate([
            'activities_done' => ['required', 'string', 'max:1000'],
            'remarks' => ['nullable', 'string', 'max:500'],
        ]);

        $today = now()->toDateString();
        
        // Check if already logged in today
        $existingLog = Log::where('student_profile_id', $studentProfile->id)
            ->where('log_date', $today)
            ->first();

        if ($existingLog && $existingLog->time_in) {
            return response()->json([
                'message' => 'You have already logged in today.',
                'log' => $existingLog,
            ], 400);
        }

        // Create or update today's log
        $log = Log::updateOrCreate(
            [
                'student_profile_id' => $studentProfile->id,
                'log_date' => $today,
            ],
            [
                'user_id' => $user->id,
                'time_in' => now()->toTimeString(),
                'activities_done' => $validated['activities_done'],
                'remarks' => $validated['remarks'] ?? null,
                'status' => 'pending',
            ]
        );

        return response()->json([
            'message' => 'Time in recorded successfully.',
            'log' => $log,
        ], 201);
    }

    /**
     * PATCH /api/student/time-out
     * Log time out for today
     */
    public function timeOut(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        $studentProfile = $user->studentProfile;
        if (!$studentProfile) {
            return response()->json([
                'message' => 'Student profile not found.',
            ], 404);
        }

        $today = now()->toDateString();
        
        // Find today's log
        $log = Log::where('student_profile_id', $studentProfile->id)
            ->where('log_date', $today)
            ->first();

        if (!$log || !$log->time_in) {
            return response()->json([
                'message' => 'No time in record found for today. Please log in first.',
            ], 400);
        }

        if ($log->time_out) {
            return response()->json([
                'message' => 'You have already logged out today.',
                'log' => $log,
            ], 400);
        }

        // Calculate hours rendered
        $timeIn = \Carbon\Carbon::parse($log->time_in);
        $timeOut = now();
        $minutesRendered = $timeIn->diffInMinutes($timeOut);
        $hoursRendered = round($minutesRendered / 60, 2);

        // Update the log
        $log->update([
            'time_out' => $timeOut->toTimeString(),
            'hours_rendered' => $hoursRendered,
            'minutes_rendered' => $minutesRendered,
        ]);

        // Update student profile rendered hours
        $studentProfile->increment('rendered_hours', $hoursRendered);

        return response()->json([
            'message' => 'Time out recorded successfully.',
            'log' => $log->fresh(),
            'student_profile' => $studentProfile->fresh(),
        ]);
    }

    /**
     * GET /api/student/today
     * Get today's time log
     */
    public function today(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        $studentProfile = $user->studentProfile;
        if (!$studentProfile) {
            return response()->json([
                'message' => 'Student profile not found.',
            ], 404);
        }

        $today = now()->toDateString();
        
        $log = Log::where('student_profile_id', $studentProfile->id)
            ->where('log_date', $today)
            ->first();

        return response()->json([
            'log' => $log,
            'can_time_in' => !$log || !$log->time_in,
            'can_time_out' => $log && $log->time_in && !$log->time_out,
        ]);
    }

    /**
     * GET /api/student/logs
     * Get student's time logs with pagination and filtering
     */
    public function myLogs(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        $studentProfile = $user->studentProfile;
        if (!$studentProfile) {
            return response()->json([
                'message' => 'Student profile not found.',
            ], 404);
        }

        $validated = $request->validate([
            'status' => ['nullable', 'in:pending,approved,rejected'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $query = Log::where('student_profile_id', $studentProfile->id)
            ->orderBy('log_date', 'desc');

        // Apply filters
        if (!empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        if (!empty($validated['date_from'])) {
            $query->where('log_date', '>=', $validated['date_from']);
        }

        if (!empty($validated['date_to'])) {
            $query->where('log_date', '<=', $validated['date_to']);
        }

        $perPage = $validated['per_page'] ?? 15;
        $logs = $query->paginate($perPage);

        return response()->json([
            'logs' => $logs,
            'summary' => [
                'total_logs' => $logs->total(),
                'total_hours' => $studentProfile->rendered_hours,
                'required_hours' => $studentProfile->required_hours,
                'remaining_hours' => $studentProfile->remaining_hours,
                'completion_percentage' => $studentProfile->completion_percentage,
            ],
        ]);
    }
}