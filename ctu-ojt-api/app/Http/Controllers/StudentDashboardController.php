<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Models\StudentProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentDashboardController extends Controller
{
    /**
     * Display the student dashboard
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Get student profile
        $studentProfile = $user->studentProfile;
        
        if (!$studentProfile) {
            return redirect()->route('profile.create')
                ->with('info', 'Please complete your student profile first to access the dashboard.');
        }

        // Get today's log
        $today = now()->toDateString();
        $todayLog = Log::where('student_profile_id', $studentProfile->id)
            ->where('log_date', $today)
            ->first();

        // Get recent logs (last 7 days)
        $recentLogs = Log::where('student_profile_id', $studentProfile->id)
            ->where('log_date', '>=', now()->subDays(7)->toDateString())
            ->orderBy('log_date', 'desc')
            ->get();

        // Calculate weekly hours
        $weeklyHours = $recentLogs->sum('hours_rendered');

        // Get log statistics
        $totalLogs = $studentProfile->logs()->count();
        $pendingLogs = $studentProfile->logs()->where('status', 'pending')->count();
        $approvedLogs = $studentProfile->logs()->where('status', 'approved')->count();

        return view('student.dashboard', compact(
            'studentProfile',
            'todayLog',
            'recentLogs',
            'weeklyHours',
            'totalLogs',
            'pendingLogs',
            'approvedLogs'
        ));
    }
}
