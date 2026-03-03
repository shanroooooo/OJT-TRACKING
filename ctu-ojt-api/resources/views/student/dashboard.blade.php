@extends('layouts.auth')

@section('title', 'Student Dashboard - CTU OJT Tracking')

@section('content')

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Welcome Header with Real-time Clock -->
        <div class="bg-gradient-to-r from-blue-500 to-purple-600 rounded-xl shadow-xl p-6 mb-8 text-white">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-3xl font-bold mb-2">Welcome back, {{ Auth::user()->name }}!</h2>
                    <p class="text-blue-100">{{ $studentProfile->course }}{{ $studentProfile->major ? ' - ' . $studentProfile->major : '' }}</p>
                    <p class="text-sm text-blue-200 mt-1">Student ID: {{ $studentProfile->student_id_number }}</p>
                </div>
                <div class="text-center clock-glow bg-white/10 backdrop-blur rounded-lg p-4">
                    <div id="clock" class="text-4xl font-bold mb-2"></div>
                    <div id="date" class="text-sm"></div>
                </div>
            </div>
        </div>

        <!-- Quick Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Rendered Hours -->
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Rendered Hours</p>
                        <p class="text-2xl font-bold text-gray-800">{{ number_format($studentProfile->rendered_hours, 1) }}</p>
                    </div>
                    <div class="bg-green-100 rounded-full p-3">
                        <i class="fas fa-clock text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Remaining Hours -->
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-orange-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Remaining Hours</p>
                        <p class="text-2xl font-bold text-gray-800">{{ number_format($studentProfile->remaining_hours, 1) }}</p>
                    </div>
                    <div class="bg-orange-100 rounded-full p-3">
                        <i class="fas fa-hourglass-half text-orange-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Completion Percentage -->
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Completion</p>
                        <p class="text-2xl font-bold text-gray-800">{{ number_format($studentProfile->completion_percentage, 1) }}%</p>
                    </div>
                    <div class="relative">
                        <svg class="w-12 h-12 progress-ring">
                            <circle cx="24" cy="24" r="20" stroke="#e5e7eb" stroke-width="4" fill="none"></circle>
                            <circle cx="24" cy="24" r="20" stroke="#3b82f6" stroke-width="4" fill="none"
                                stroke-dasharray="{{ $studentProfile->completion_percentage * 1.256 }} 125.6"
                                stroke-linecap="round"></circle>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Weekly Hours -->
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-purple-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">This Week</p>
                        <p class="text-2xl font-bold text-gray-800">{{ number_format($weeklyHours, 1) }}h</p>
                    </div>
                    <div class="bg-purple-100 rounded-full p-3">
                        <i class="fas fa-calendar-week text-purple-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Today's Time Log Status -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <div class="lg:col-span-2 bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-calendar-day mr-2 text-blue-500"></i>
                    Today's Time Log Status
                </h3>
                
                @if($todayLog)
                    <div class="space-y-3">
                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                            <span class="font-medium">Date:</span>
                            <span>{{ $todayLog->log_date->format('F d, Y') }}</span>
                        </div>
                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                            <span class="font-medium">Time In:</span>
                            <span class="text-green-600 font-semibold">
                                @if($todayLog->time_in)
                                    {{ $todayLog->time_in }}
                                    <i class="fas fa-check-circle ml-1"></i>
                                @else
                                    <span class="text-gray-400">Not yet logged in</span>
                                @endif
                            </span>
                        </div>
                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                            <span class="font-medium">Time Out:</span>
                            <span class="text-red-600 font-semibold">
                                @if($todayLog->time_out)
                                    {{ $todayLog->time_out }}
                                    <i class="fas fa-check-circle ml-1"></i>
                                @else
                                    <span class="text-gray-400">Not yet logged out</span>
                                @endif
                            </span>
                        </div>
                        @if($todayLog->hours_rendered)
                        <div class="flex justify-between items-center p-3 bg-blue-50 rounded">
                            <span class="font-medium">Hours Rendered:</span>
                            <span class="text-blue-600 font-bold">{{ number_format($todayLog->hours_rendered, 2) }} hours</span>
                        </div>
                        @endif
                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                            <span class="font-medium">Status:</span>
                            <span class="px-3 py-1 rounded-full text-xs font-semibold
                                @if($todayLog->status == 'approved') bg-green-100 text-green-800
                                @elseif($todayLog->status == 'rejected') bg-red-100 text-red-800
                                @else bg-yellow-100 text-yellow-800
                                @endif">
                                {{ ucfirst($todayLog->status) }}
                            </span>
                        </div>
                    </div>
                @else
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-clock text-4xl mb-3"></i>
                        <p>No time log for today yet</p>
                        <p class="text-sm">Start by logging your time in</p>
                    </div>
                @endif

                <!-- Action Buttons -->
                <div class="mt-6 flex gap-3">
                    @if(!$todayLog || !$todayLog->time_in)
                        <button onclick="timeIn()" class="flex-1 bg-green-500 hover:bg-green-600 text-white font-semibold py-3 px-4 rounded-lg transition flex items-center justify-center">
                            <i class="fas fa-sign-in-alt mr-2"></i>
                            Time In
                        </button>
                    @endif

                    @if($todayLog && $todayLog->time_in && !$todayLog->time_out)
                        <button onclick="timeOut()" class="flex-1 bg-red-500 hover:bg-red-600 text-white font-semibold py-3 px-4 rounded-lg transition flex items-center justify-center">
                            <i class="fas fa-sign-out-alt mr-2"></i>
                            Time Out
                        </button>
                    @endif

                    <button onclick="viewLogs()" class="flex-1 bg-blue-500 hover:bg-blue-600 text-white font-semibold py-3 px-4 rounded-lg transition flex items-center justify-center">
                        <i class="fas fa-history mr-2"></i>
                        View Logs
                    </button>
                </div>
            </div>

            <!-- Log Statistics -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-chart-bar mr-2 text-purple-500"></i>
                    Log Statistics
                </h3>
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Total Logs</span>
                        <span class="font-bold text-lg">{{ $totalLogs }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Pending</span>
                        <span class="font-bold text-lg text-yellow-600">{{ $pendingLogs }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Approved</span>
                        <span class="font-bold text-lg text-green-600">{{ $approvedLogs }}</span>
                    </div>
                    <div class="pt-4 border-t">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Required Hours</span>
                            <span class="font-bold">{{ $studentProfile->required_hours }}h</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-history mr-2 text-indigo-500"></i>
                Recent Activity (Last 7 Days)
            </h3>
            @if($recentLogs->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time In</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time Out</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hours</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($recentLogs as $log)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $log->log_date->format('M d, Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $log->time_in ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $log->time_out ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $log->hours_rendered ? number_format($log->hours_rendered, 2) . 'h' : '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                            @if($log->status == 'approved') bg-green-100 text-green-800
                                            @elseif($log->status == 'rejected') bg-red-100 text-red-800
                                            @else bg-yellow-100 text-yellow-800
                                            @endif">
                                            {{ ucfirst($log->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-inbox text-4xl mb-3"></i>
                    <p>No recent activity</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Time In Modal -->
    <div id="timeInModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Time In</h3>
                <form id="timeInForm">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Activities for today</label>
                        <textarea name="activities_done" rows="3" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Describe your activities for today..."></textarea>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Remarks (optional)</label>
                        <textarea name="remarks" rows="2"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Any additional remarks..."></textarea>
                    </div>
                    <div class="flex gap-3">
                        <button type="submit" class="flex-1 bg-green-500 hover:bg-green-600 text-white font-semibold py-2 px-4 rounded transition">
                            <i class="fas fa-check mr-2"></i>Confirm Time In
                        </button>
                        <button type="button" onclick="closeModal('timeInModal')" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold py-2 px-4 rounded transition">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Success/Error Messages -->
    <div id="messageContainer" class="fixed top-4 right-4 z-50"></div>
@endsection

@push('scripts')
<script>
    // Real-time Clock
    function updateClock() {
        const now = new Date();
        const timeString = now.toLocaleTimeString('en-US', { 
            hour: '2-digit', 
            minute: '2-digit', 
            second: '2-digit' 
        });
        const dateString = now.toLocaleDateString('en-US', { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        });
        
        document.getElementById('clock').textContent = timeString;
        document.getElementById('date').textContent = dateString;
    }

    setInterval(updateClock, 1000);
    updateClock();

    // Time In Function
    function timeIn() {
        document.getElementById('timeInModal').classList.remove('hidden');
    }

    // Time Out Function
    async function timeOut() {
        if (confirm('Are you sure you want to time out?')) {
            try {
                const response = await fetch('/api/student/time-out', {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer ' + getAuthToken()
                    }
                });

                const data = await response.json();
                
                if (response.ok) {
                    showMessage('Time out recorded successfully!', 'success');
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    showMessage(data.message || 'Failed to time out', 'error');
                }
            } catch (error) {
                showMessage('Network error. Please try again.', 'error');
            }
        }
    }

    // View Logs Function
    function viewLogs() {
        window.location.href = '/student/logs';
    }

    // Close Modal
    function closeModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
    }

    // Show Message
    function showMessage(message, type) {
        const container = document.getElementById('messageContainer');
        const alertClass = type === 'success' ? 'bg-green-500' : 'bg-red-500';
        
        const messageDiv = document.createElement('div');
        messageDiv.className = `${alertClass} text-white px-6 py-4 rounded-lg shadow-lg mb-4 transform transition-all duration-300 translate-x-full`;
        messageDiv.innerHTML = `
            <div class="flex items-center">
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} mr-3"></i>
                <span>${message}</span>
            </div>
        `;
        
        container.appendChild(messageDiv);
        
        setTimeout(() => {
            messageDiv.classList.remove('translate-x-full');
        }, 100);
        
        setTimeout(() => {
            messageDiv.classList.add('translate-x-full');
            setTimeout(() => container.removeChild(messageDiv), 300);
        }, 3000);
    }

    // Get Auth Token (you'll need to implement this based on your auth system)
    function getAuthToken() {
        // This should return the authenticated user's token
        // For now, returning a placeholder - implement based on your auth system
        return localStorage.getItem('auth_token') || sessionStorage.getItem('auth_token');
    }

    // Time In Form Submit
    document.getElementById('timeInForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const data = {
            activities_done: formData.get('activities_done'),
            remarks: formData.get('remarks')
        };

        try {
            const response = await fetch('/api/student/time-in', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + getAuthToken()
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();
            
            if (response.ok) {
                closeModal('timeInModal');
                showMessage('Time in recorded successfully!', 'success');
                setTimeout(() => window.location.reload(), 1500);
            } else {
                showMessage(result.message || 'Failed to time in', 'error');
            }
        } catch (error) {
            showMessage('Network error. Please try again.', 'error');
        }
    });

    // Close modals when clicking outside
    window.onclick = function(event) {
        if (event.target.classList.contains('bg-opacity-50')) {
            event.target.classList.add('hidden');
        }
    }
</script>
@endpush

@push('styles')
<style>
    @keyframes pulse-glow {
        0%, 100% { box-shadow: 0 0 20px rgba(34, 197, 94, 0.5); }
        50% { box-shadow: 0 0 30px rgba(34, 197, 94, 0.8); }
    }
    .clock-glow {
        animation: pulse-glow 2s infinite;
    }
    .progress-ring {
        transform: rotate(-90deg);
    }
</style>
@endpush
