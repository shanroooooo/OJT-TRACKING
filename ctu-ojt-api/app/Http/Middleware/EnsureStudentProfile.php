<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStudentProfile
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if ($user->role !== 'student') {
            abort(403, 'Access denied. Student role required.');
        }

        if (!$user->studentProfile) {
            return redirect()->route('profile.create')
                ->with('info', 'Please complete your student profile first to access the dashboard.');
        }

        return $next($request);
    }
}
