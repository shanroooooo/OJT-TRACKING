<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supervisor Dashboard - CTU OJT Tracking</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <i class="fas fa-user-tie text-green-600 text-xl mr-3"></i>
                    <h1 class="text-xl font-semibold text-gray-900">Supervisor Panel</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-600" id="user-name">Loading...</span>
                    <button onclick="logout()" class="text-red-600 hover:text-red-800">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <div class="flex">
        <!-- Sidebar -->
        <div class="w-64 bg-white shadow-md min-h-screen">
            <div class="p-4">
                <ul class="space-y-2">
                    <li>
                        <a href="/supervisor/dashboard" class="flex items-center px-4 py-2 text-gray-700 bg-green-50 rounded-lg">
                            <i class="fas fa-tachometer-alt mr-3"></i> Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="/supervisor/students" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
                            <i class="fas fa-graduation-cap mr-3"></i> My Students
                        </a>
                    </li>
                    <li>
                        <a href="/supervisor/time-logs" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
                            <i class="fas fa-clock mr-3"></i> Time Logs
                        </a>
                    </li>
                    <li>
                        <a href="/supervisor/reports" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
                            <i class="fas fa-file-alt mr-3"></i> Reports
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 p-6">
            <!-- Welcome Section -->
            <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg shadow-lg p-6 mb-8 text-white">
                <h2 class="text-2xl font-bold mb-2">Welcome back, <span id="supervisor-name">Supervisor</span>!</h2>
                <p class="text-green-100">Monitor and manage your assigned students' OJT progress</p>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-blue-100 rounded-full">
                            <i class="fas fa-users text-blue-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-sm font-medium text-gray-500">Total Students</h3>
                            <p class="text-2xl font-semibold text-gray-900" id="total-students">-</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-green-100 rounded-full">
                            <i class="fas fa-play-circle text-green-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-sm font-medium text-gray-500">Active Students</h3>
                            <p class="text-2xl font-semibold text-gray-900" id="active-students">-</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-yellow-100 rounded-full">
                            <i class="fas fa-hourglass-half text-yellow-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-sm font-medium text-gray-500">Pending Reviews</h3>
                            <p class="text-2xl font-semibold text-gray-900" id="pending-logs">-</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-purple-100 rounded-full">
                            <i class="fas fa-trophy text-purple-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-sm font-medium text-gray-500">Completed</h3>
                            <p class="text-2xl font-semibold text-gray-900" id="completed-students">-</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Student Progress Overview</h3>
                    <canvas id="progressChart"></canvas>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Weekly Hours Trend</h3>
                    <canvas id="hoursChart"></canvas>
                </div>
            </div>

            <!-- Recent Activities -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Recent Activities</h3>
                </div>
                <div class="p-6">
                    <div id="recent-activities" class="space-y-4">
                        <div class="text-center text-gray-500">
                            <i class="fas fa-spinner fa-spin mr-2"></i> Loading activities...
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="mt-8 bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <button onclick="window.location.href='/supervisor/time-logs'" 
                            class="bg-blue-600 text-white px-4 py-3 rounded-lg hover:bg-blue-700 text-center">
                        <i class="fas fa-clock mr-2"></i> Review Time Logs
                    </button>
                    <button onclick="window.location.href='/supervisor/students'" 
                            class="bg-green-600 text-white px-4 py-3 rounded-lg hover:bg-green-700 text-center">
                        <i class="fas fa-users mr-2"></i> View Students
                    </button>
                    <button onclick="window.location.href='/supervisor/reports'" 
                            class="bg-purple-600 text-white px-4 py-3 rounded-lg hover:bg-purple-700 text-center">
                        <i class="fas fa-download mr-2"></i> Generate Reports
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Check authentication
        const token = localStorage.getItem('admin_token');
        const user = JSON.parse(localStorage.getItem('admin_user') || '{}');
        
        if (!token || user.role !== 'supervisor') {
            window.location.href = '/admin/login';
            exit;
        }

        document.getElementById('user-name').textContent = user.name || 'Supervisor';
        document.getElementById('supervisor-name').textContent = user.name || 'Supervisor';

        function logout() {
            localStorage.removeItem('admin_token');
            localStorage.removeItem('admin_user');
            window.location.href = '/admin/login';
        }

        // Load dashboard data
        async function loadDashboardData() {
            try {
                const response = await fetch('/api/supervisor/dashboard', {
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json'
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    updateDashboardStats(data.data.stats);
                    displayRecentActivities(data.data.recent_activities);
                    createCharts(data.data);
                } else if (response.status === 401) {
                    logout();
                }
            } catch (error) {
                console.error('Error loading dashboard:', error);
            }
        }

        function updateDashboardStats(stats) {
            document.getElementById('total-students').textContent = stats.total_students || 0;
            document.getElementById('active-students').textContent = stats.active_students || 0;
            document.getElementById('pending-logs').textContent = stats.pending_logs || 0;
            document.getElementById('completed-students').textContent = stats.completed_students || 0;
        }

        function displayRecentActivities(activities) {
            const container = document.getElementById('recent-activities');
            
            if (activities.length === 0) {
                container.innerHTML = '<div class="text-center text-gray-500">No recent activities</div>';
                return;
            }

            container.innerHTML = activities.map(activity => `
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div class="flex items-center">
                        <div class="p-2 bg-blue-100 rounded-full mr-3">
                            <i class="fas fa-clock text-blue-600 text-sm"></i>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-900">
                                ${activity.studentProfile?.user?.name || 'Unknown Student'}
                            </div>
                            <div class="text-xs text-gray-500">
                                ${activity.activities_done || 'No activity'} - ${activity.hours_rendered || 0} hours
                            </div>
                        </div>
                    </div>
                    <div class="text-xs text-gray-500">
                        ${new Date(activity.log_date).toLocaleDateString()}
                    </div>
                </div>
            `).join('');
        }

        function createCharts(data) {
            // Progress Chart
            const progressCtx = document.getElementById('progressChart').getContext('2d');
            new Chart(progressCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Active', 'Completed', 'Not Started'],
                    datasets: [{
                        data: [
                            data.stats.active_students,
                            data.stats.completed_students,
                            data.stats.total_students - data.stats.active_students - data.stats.completed_students
                        ],
                        backgroundColor: ['#10B981', '#3B82F6', '#6B7280']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });

            // Hours Chart (sample data)
            const hoursCtx = document.getElementById('hoursChart').getContext('2d');
            new Chart(hoursCtx, {
                type: 'line',
                data: {
                    labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
                    datasets: [{
                        label: 'Total Hours',
                        data: [120, 150, 180, 200],
                        borderColor: '#3B82F6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        // Load data on page load
        loadDashboardData();
    </script>
</body>
</html>
