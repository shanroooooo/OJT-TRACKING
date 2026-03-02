<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\StudentProfile;
use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    /**
     * Get dashboard statistics
     */
    public function dashboard(): JsonResponse
    {
        $stats = [
            'total_users' => User::count(),
            'total_students' => User::where('role', 'student')->count(),
            'total_supervisors' => User::where('role', 'supervisor')->count(),
            'total_admins' => User::where('role', 'admin')->count(),
            'active_students' => StudentProfile::where('status', 'active')->count(),
            'completed_ojt' => StudentProfile::where('status', 'completed')->count(),
            'total_hours_rendered' => StudentProfile::sum('rendered_hours'),
            'pending_logs' => Log::where('status', 'pending')->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Get all users with pagination
     */
    public function users(Request $request): JsonResponse
    {
        $query = User::query();

        // Filter by role
        if ($request->has('role') && $request->role !== 'all') {
            $query->where('role', $request->role);
        }

        // Search by name or email
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->with('studentProfile')
                      ->orderBy('created_at', 'desc')
                      ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    /**
     * Create a new user
     */
    public function createUser(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => ['required', Rule::in(['student', 'supervisor', 'admin'])],
            'is_active' => 'boolean',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'data' => $user
        ], 201);
    }

    /**
     * Update user details
     */
    public function updateUser(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => ['sometimes', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'sometimes|string|min:8|confirmed',
            'role' => ['sometimes', Rule::in(['student', 'supervisor', 'admin'])],
            'is_active' => 'boolean',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'data' => $user
        ]);
    }

    /**
     * Delete a user
     */
    public function deleteUser(User $user): JsonResponse
    {
        // Prevent deletion of the last admin
        if ($user->role === 'admin') {
            $adminCount = User::where('role', 'admin')->count();
            if ($adminCount <= 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete the last admin user'
                ], 422);
            }
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully'
        ]);
    }

    /**
     * Get all student profiles
     */
    public function studentProfiles(Request $request): JsonResponse
    {
        $query = StudentProfile::with(['user', 'logs']);

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Search by student info
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

        $profiles = $query->orderBy('created_at', 'desc')
                          ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $profiles
        ]);
    }

    /**
     * Get system logs and activities
     */
    public function systemLogs(Request $request): JsonResponse
    {
        $query = Log::with(['user', 'studentProfile']);

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

        $logs = $query->orderBy('log_date', 'desc')
                     ->orderBy('created_at', 'desc')
                     ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $logs
        ]);
    }

    /**
     * Get OJT statistics and analytics
     */
    public function analytics(): JsonResponse
    {
        // Monthly registration trends
        $monthlyRegistrations = User::where('role', 'student')
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count')
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get();

        // Company distribution
        $companyDistribution = StudentProfile::selectRaw('company_name, COUNT(*) as student_count')
            ->groupBy('company_name')
            ->orderBy('student_count', 'desc')
            ->limit(10)
            ->get();

        // Course distribution
        $courseDistribution = StudentProfile::selectRaw('course, COUNT(*) as student_count')
            ->groupBy('course')
            ->orderBy('student_count', 'desc')
            ->get();

        // OJT completion rates
        $completionStats = [
            'not_started' => StudentProfile::where('status', 'not_started')->count(),
            'active' => StudentProfile::where('status', 'active')->count(),
            'completed' => StudentProfile::where('status', 'completed')->count(),
            'suspended' => StudentProfile::where('status', 'suspended')->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'monthly_registrations' => $monthlyRegistrations,
                'company_distribution' => $companyDistribution,
                'course_distribution' => $courseDistribution,
                'completion_stats' => $completionStats,
            ]
        ]);
    }

    /**
     * Toggle user active status
     */
    public function toggleUserStatus(User $user): JsonResponse
    {
        $user->is_active = !$user->is_active;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => "User status updated to " . ($user->is_active ? 'active' : 'inactive'),
            'data' => $user
        ]);
    }
}
