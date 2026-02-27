<?php

namespace App\Http\Controllers;

use App\Models\StudentProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StudentProfileController extends Controller
{
    /**
     * POST /api/student/profile
     * Create student profile
     */
    public function store(Request $request): JsonResponse
    {
        $user = Auth::user();

        // Check if user already has a profile
        if ($user->studentProfile) {
            return response()->json([
                'message' => 'You already have a student profile.',
                'profile' => $user->studentProfile,
            ], 400);
        }

        $validated = $request->validate([
            'student_id_number' => [
                'required',
                'string',
                'max:50',
                'unique:student_profiles,student_id_number'
            ],
            'course' => ['required', 'string', 'max:100'],
            'major' => ['nullable', 'string', 'max:100'],
            'year_level' => ['required', 'integer', 'min:1', 'max:5'],
            'section' => ['nullable', 'string', 'max:20'],
            'company_name' => ['nullable', 'string', 'max:200'],
            'company_address' => ['nullable', 'string', 'max:500'],
            'supervisor_name' => ['nullable', 'string', 'max:100'],
            'supervisor_contact' => ['nullable', 'string', 'max:50'],
            'required_hours' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'ojt_start_date' => ['nullable', 'date', 'after_or_equal:today'],
            'ojt_end_date' => [
                'nullable',
                'date',
                'after:ojt_start_date'
            ],
        ]);

        $profile = StudentProfile::create([
            'user_id' => $user->id,
            'student_id_number' => $validated['student_id_number'],
            'course' => $validated['course'],
            'major' => $validated['major'] ?? null,
            'year_level' => $validated['year_level'],
            'section' => $validated['section'] ?? null,
            'company_name' => $validated['company_name'] ?? null,
            'company_address' => $validated['company_address'] ?? null,
            'supervisor_name' => $validated['supervisor_name'] ?? null,
            'supervisor_contact' => $validated['supervisor_contact'] ?? null,
            'required_hours' => $validated['required_hours'] ?? 486,
            'ojt_start_date' => $validated['ojt_start_date'] ?? null,
            'ojt_end_date' => $validated['ojt_end_date'] ?? null,
            'status' => $validated['ojt_start_date'] ? 'ongoing' : 'pending',
        ]);

        return response()->json([
            'message' => 'Student profile created successfully.',
            'profile' => $profile,
        ], 201);
    }

    /**
     * GET /api/student/profile
     * Get current student's profile
     */
    public function show(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        $profile = $user->studentProfile;
        
        if (!$profile) {
            return response()->json([
                'message' => 'Student profile not found.',
            ], 404);
        }

        return response()->json([
            'profile' => $profile,
            'progress' => [
                'required_hours' => $profile->required_hours,
                'rendered_hours' => $profile->rendered_hours,
                'remaining_hours' => $profile->remaining_hours,
                'completion_percentage' => $profile->completion_percentage,
            ],
        ]);
    }

    /**
     * PUT/PATCH /api/student/profile
     * Update student profile
     */
    public function update(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        $profile = $user->studentProfile;
        
        if (!$profile) {
            return response()->json([
                'message' => 'Student profile not found.',
            ], 404);
        }

        $validated = $request->validate([
            'student_id_number' => [
                'sometimes',
                'string',
                'max:50',
                Rule::unique('student_profiles', 'student_id_number')->ignore($profile->id)
            ],
            'course' => ['sometimes', 'string', 'max:100'],
            'major' => ['nullable', 'string', 'max:100'],
            'year_level' => ['sometimes', 'integer', 'min:1', 'max:5'],
            'section' => ['nullable', 'string', 'max:20'],
            'company_name' => ['nullable', 'string', 'max:200'],
            'company_address' => ['nullable', 'string', 'max:500'],
            'supervisor_name' => ['nullable', 'string', 'max:100'],
            'supervisor_contact' => ['nullable', 'string', 'max:50'],
            'required_hours' => ['sometimes', 'integer', 'min:1', 'max:1000'],
            'ojt_start_date' => ['nullable', 'date'],
            'ojt_end_date' => [
                'nullable',
                'date',
                'after:ojt_start_date'
            ],
        ]);

        $profile->update($validated);

        // Update status based on dates
        if ($profile->ojt_start_date && $profile->ojt_end_date) {
            $today = now()->toDateString();
            if ($today >= $profile->ojt_start_date && $today <= $profile->ojt_end_date) {
                $profile->status = 'ongoing';
            } elseif ($today > $profile->ojt_end_date) {
                $profile->status = $profile->completion_percentage >= 100 ? 'completed' : 'ongoing';
            }
            $profile->save();
        }

        return response()->json([
            'message' => 'Student profile updated successfully.',
            'profile' => $profile->fresh(),
        ]);
    }

    /**
     * DELETE /api/student/profile
     * Delete student profile (soft delete via cascade)
     */
    public function destroy(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        $profile = $user->studentProfile;
        
        if (!$profile) {
            return response()->json([
                'message' => 'Student profile not found.',
            ], 404);
        }

        // Check if there are approved logs
        $approvedLogsCount = $profile->logs()->where('status', 'approved')->count();
        if ($approvedLogsCount > 0) {
            return response()->json([
                'message' => 'Cannot delete profile with approved time logs.',
                'approved_logs_count' => $approvedLogsCount,
            ], 400);
        }

        $profile->delete();

        return response()->json([
            'message' => 'Student profile deleted successfully.',
        ]);
    }

    /**
     * GET /api/student/profile/summary
     * Get student's OJT progress summary
     */
    public function summary(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        $profile = $user->studentProfile;
        
        if (!$profile) {
            return response()->json([
                'message' => 'Student profile not found.',
            ], 404);
        }

        // Get log statistics
        $totalLogs = $profile->logs()->count();
        $pendingLogs = $profile->logs()->where('status', 'pending')->count();
        $approvedLogs = $profile->logs()->where('status', 'approved')->count();
        $rejectedLogs = $profile->logs()->where('status', 'rejected')->count();

        // Get recent logs
        $recentLogs = $profile->logs()
            ->with(['reviewer:id,name'])
            ->orderBy('log_date', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'profile' => [
                'id' => $profile->id,
                'student_id_number' => $profile->student_id_number,
                'course' => $profile->course,
                'major' => $profile->major,
                'year_level' => $profile->year_level,
                'section' => $profile->section,
                'company_name' => $profile->company_name,
                'status' => $profile->status,
                'ojt_start_date' => $profile->ojt_start_date,
                'ojt_end_date' => $profile->ojt_end_date,
            ],
            'progress' => [
                'required_hours' => $profile->required_hours,
                'rendered_hours' => $profile->rendered_hours,
                'remaining_hours' => $profile->remaining_hours,
                'completion_percentage' => $profile->completion_percentage,
            ],
            'log_statistics' => [
                'total_logs' => $totalLogs,
                'pending_logs' => $pendingLogs,
                'approved_logs' => $approvedLogs,
                'rejected_logs' => $rejectedLogs,
            ],
            'recent_logs' => $recentLogs,
        ]);
    }
}
