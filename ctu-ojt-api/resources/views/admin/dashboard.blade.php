<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - CTU OJT Tracking</title>
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
                    <i class="fas fa-shield-alt text-blue-600 text-xl mr-3"></i>
                    <h1 class="text-xl font-semibold text-gray-900">Admin Panel</h1>
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

    <!-- Sidebar and Main Content -->
    <div class="flex">
        <!-- Sidebar -->
        <div class="w-64 bg-white shadow-md min-h-screen">
            <div class="p-4">
                <ul class="space-y-2">
                    <li>
                        <a href="/admin/dashboard" class="flex items-center px-4 py-2 text-gray-700 bg-blue-50 rounded-lg">
                            <i class="fas fa-tachometer-alt mr-3"></i> Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="/admin/users" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
                            <i class="fas fa-users mr-3"></i> Users
                        </a>
                    </li>
                    <li>
                        <a href="/admin/students" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
                            <i class="fas fa-graduation-cap mr-3"></i> Students
                        </a>
                    </li>
                    <li>
                        <a href="/admin/logs" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
                            <i class="fas fa-clock mr-3"></i> Time Logs
                        </a>
                    </li>
                    <li>
                        <a href="/admin/analytics" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
                            <i class="fas fa-chart-bar mr-3"></i> Analytics
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 p-6">
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-blue-100 rounded-full">
                            <i class="fas fa-users text-blue-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-sm font-medium text-gray-500">Total Users</h3>
                            <p class="text-2xl font-semibold text-gray-900" id="total-users">-</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-green-100 rounded-full">
                            <i class="fas fa-graduation-cap text-green-600 text-xl"></i>
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
                            <h3 class="text-sm font-medium text-gray-500">Pending Logs</h3>
                            <p class="text-2xl font-semibold text-gray-900" id="pending-logs">-</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-purple-100 rounded-full">
                            <i class="fas fa-clock text-purple-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-sm font-medium text-gray-500">Total Hours</h3>
                            <p class="text-2xl font-semibold text-gray-900" id="total-hours">-</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">User Distribution</h3>
                    <canvas id="userDistributionChart"></canvas>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">OJT Status Overview</h3>
                    <canvas id="ojtStatusChart"></canvas>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">System Overview</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="text-center">
                            <div class="text-3xl font-bold text-blue-600" id="total-students">-</div>
                            <div class="text-sm text-gray-600">Total Students</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-green-600" id="total-supervisors">-</div>
                            <div class="text-sm text-gray-600">Total Supervisors</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-purple-600" id="completed-ojt">-</div>
                            <div class="text-sm text-gray-600">Completed OJT</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Check authentication
        const token = localStorage.getItem('admin_token');
        const user = JSON.parse(localStorage.getItem('admin_user') || '{}');
        
        if (!token) {
            window.location.href = '/admin/login';
            exit;
        }

        // Set user name
        document.getElementById('user-name').textContent = user.name || 'Admin';

        // Logout function
        function logout() {
            localStorage.removeItem('admin_token');
            localStorage.removeItem('admin_user');
            window.location.href = '/admin/login';
        }

        // Load dashboard data
        async function loadDashboardData() {
            try {
                const response = await fetch('/api/admin/dashboard', {
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json'
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    updateDashboardStats(data.data);
                    createCharts(data.data);
                } else if (response.status === 401) {
                    logout();
                }
            } catch (error) {
                console.error('Error loading dashboard:', error);
            }
        }

        function updateDashboardStats(stats) {
            document.getElementById('total-users').textContent = stats.total_users || 0;
            document.getElementById('active-students').textContent = stats.active_students || 0;
            document.getElementById('pending-logs').textContent = stats.pending_logs || 0;
            document.getElementById('total-hours').textContent = (stats.total_hours_rendered || 0).toFixed(1);
            document.getElementById('total-students').textContent = stats.total_students || 0;
            document.getElementById('total-supervisors').textContent = stats.total_supervisors || 0;
            document.getElementById('completed-ojt').textContent = stats.completed_ojt || 0;
        }

        function createCharts(stats) {
            // User Distribution Chart
            const userCtx = document.getElementById('userDistributionChart').getContext('2d');
            new Chart(userCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Students', 'Supervisors', 'Admins'],
                    datasets: [{
                        data: [stats.total_students, stats.total_supervisors, stats.total_admins],
                        backgroundColor: ['#3B82F6', '#10B981', '#8B5CF6']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });

            // OJT Status Chart
            const statusCtx = document.getElementById('ojtStatusChart').getContext('2d');
            new Chart(statusCtx, {
                type: 'bar',
                data: {
                    labels: ['Active', 'Completed', 'Not Started'],
                    datasets: [{
                        label: 'Students',
                        data: [stats.active_students, stats.completed_ojt, stats.total_users - stats.active_students - stats.completed_ojt],
                        backgroundColor: ['#10B981', '#3B82F6', '#6B7280']
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
