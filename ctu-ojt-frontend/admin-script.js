// Admin Dashboard JavaScript
class AdminDashboard {
    constructor() {
        this.apiBase = 'http://localhost:8000/api';
        this.token = localStorage.getItem('admin_token');
        this.currentUser = null;
        this.currentPage = 'dashboard';
        this.charts = {};
        
        this.init();
    }

    init() {
        this.setupEventListeners();
        
        if (this.token) {
            this.verifyToken();
        } else {
            this.showLogin();
        }
    }

    setupEventListeners() {
        // Login form
        document.getElementById('loginForm').addEventListener('submit', (e) => {
            e.preventDefault();
            this.login();
        });

        // Logout button
        document.getElementById('logoutBtn').addEventListener('click', () => {
            this.logout();
        });

        // Navigation
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const page = link.dataset.page;
                this.navigateToPage(page);
            });
        });

        // User form
        document.getElementById('userForm').addEventListener('submit', (e) => {
            e.preventDefault();
            this.saveUser();
        });

        // Filters
        document.getElementById('roleFilter')?.addEventListener('change', () => this.loadUsers());
        document.getElementById('userSearch')?.addEventListener('input', () => this.loadUsers());
        document.getElementById('statusFilter')?.addEventListener('change', () => this.loadStudentProfiles());
        document.getElementById('studentSearch')?.addEventListener('input', () => this.loadStudentProfiles());
        document.getElementById('logStatusFilter')?.addEventListener('change', () => this.loadSystemLogs());
        document.getElementById('dateFrom')?.addEventListener('change', () => this.loadSystemLogs());
        document.getElementById('dateTo')?.addEventListener('change', () => this.loadSystemLogs());
    }

    async apiRequest(endpoint, options = {}) {
        const url = `${this.apiBase}${endpoint}`;
        const headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            ...options.headers
        };

        if (this.token) {
            headers['Authorization'] = `Bearer ${this.token}`;
        }

        try {
            const response = await fetch(url, {
                ...options,
                headers
            });

            const data = await response.json();

            if (!response.ok) {
                if (response.status === 401) {
                    this.logout();
                    throw new Error('Session expired. Please login again.');
                }
                throw new Error(data.message || 'Request failed');
            }

            return data;
        } catch (error) {
            console.error('API Request Error:', error);
            throw error;
        }
    }

    showLogin() {
        document.getElementById('login-section').style.display = 'flex';
        document.getElementById('admin-dashboard').style.display = 'none';
    }

    showDashboard() {
        document.getElementById('login-section').style.display = 'none';
        document.getElementById('admin-dashboard').style.display = 'flex';
    }

    async login() {
        const email = document.getElementById('loginEmail').value;
        const password = document.getElementById('loginPassword').value;

        try {
            this.showLoading(true);
            
            const response = await this.apiRequest('/auth/login', {
                method: 'POST',
                body: JSON.stringify({ email, password })
            });

            if (response.data.user.role !== 'admin') {
                throw new Error('Access denied. Admin role required.');
            }

            this.token = response.data.token;
            this.currentUser = response.data.user;
            localStorage.setItem('admin_token', this.token);
            
            document.getElementById('adminName').textContent = this.currentUser.name;
            
            this.showDashboard();
            this.navigateToPage('dashboard');
            
            this.showNotification('Login successful', 'success');
        } catch (error) {
            this.showNotification(error.message, 'error');
        } finally {
            this.showLoading(false);
        }
    }

    async verifyToken() {
        try {
            const response = await this.apiRequest('/user');
            
            if (response.data.role !== 'admin') {
                throw new Error('Access denied. Admin role required.');
            }

            this.currentUser = response.data;
            document.getElementById('adminName').textContent = this.currentUser.name;
            
            this.showDashboard();
            this.navigateToPage('dashboard');
        } catch (error) {
            this.logout();
        }
    }

    logout() {
        this.token = null;
        this.currentUser = null;
        localStorage.removeItem('admin_token');
        
        // Clear charts
        Object.values(this.charts).forEach(chart => {
            if (chart) chart.destroy();
        });
        this.charts = {};
        
        this.showLogin();
        this.showNotification('Logged out successfully', 'info');
    }

    navigateToPage(page) {
        // Update navigation
        document.querySelectorAll('.nav-link').forEach(link => {
            link.classList.remove('active');
        });
        document.querySelector(`[data-page="${page}"]`).classList.add('active');

        // Update page content
        document.querySelectorAll('.page-content').forEach(content => {
            content.classList.remove('active');
        });
        document.getElementById(`${page}-page`).classList.add('active');

        // Update page title
        const titles = {
            dashboard: 'Dashboard',
            users: 'User Management',
            students: 'Student Profiles',
            logs: 'System Logs',
            analytics: 'Analytics & Reports'
        };
        document.getElementById('pageTitle').textContent = titles[page];

        this.currentPage = page;

        // Load page-specific data
        this.loadPageData(page);
    }

    async loadPageData(page) {
        switch (page) {
            case 'dashboard':
                await this.loadDashboardData();
                break;
            case 'users':
                await this.loadUsers();
                break;
            case 'students':
                await this.loadStudentProfiles();
                break;
            case 'logs':
                await this.loadSystemLogs();
                break;
            case 'analytics':
                await this.loadAnalytics();
                break;
        }
    }

    async loadDashboardData() {
        try {
            this.showLoading(true);
            
            const response = await this.apiRequest('/admin/dashboard');
            const stats = response.data;

            // Update statistics cards
            document.getElementById('totalUsers').textContent = stats.total_users;
            document.getElementById('totalStudents').textContent = stats.total_students;
            document.getElementById('totalSupervisors').textContent = stats.total_supervisors;
            document.getElementById('activeStudents').textContent = stats.active_students;
            document.getElementById('completedOJT').textContent = stats.completed_ojt;
            document.getElementById('pendingLogs').textContent = stats.pending_logs;

            // Load charts
            await this.loadDashboardCharts();
        } catch (error) {
            this.showNotification(error.message, 'error');
        } finally {
            this.showLoading(false);
        }
    }

    async loadDashboardCharts() {
        try {
            const analyticsResponse = await this.apiRequest('/admin/analytics');
            const analytics = analyticsResponse.data;

            // Completion Status Chart
            this.createChart('completionChart', 'doughnut', {
                labels: ['Not Started', 'Active', 'Completed', 'Suspended'],
                datasets: [{
                    data: [
                        analytics.completion_stats.not_started,
                        analytics.completion_stats.active,
                        analytics.completion_stats.completed,
                        analytics.completion_stats.suspended
                    ],
                    backgroundColor: ['#6b7280', '#3b82f6', '#10b981', '#ef4444']
                }]
            });

            // Monthly Registrations Chart
            this.createChart('registrationChart', 'line', {
                labels: analytics.monthly_registrations.map(item => item.month),
                datasets: [{
                    label: 'New Students',
                    data: analytics.monthly_registrations.map(item => item.count),
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4
                }]
            });
        } catch (error) {
            console.error('Error loading dashboard charts:', error);
        }
    }

    createChart(canvasId, type, data) {
        const canvas = document.getElementById(canvasId);
        if (!canvas) return;

        if (this.charts[canvasId]) {
            this.charts[canvasId].destroy();
        }

        this.charts[canvasId] = new Chart(canvas, {
            type,
            data,
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: type === 'doughnut' ? 'bottom' : 'top'
                    }
                },
                scales: type === 'line' ? {
                    y: {
                        beginAtZero: true
                    }
                } : {}
            }
        });
    }

    async loadUsers(page = 1) {
        try {
            this.showLoading(true);
            
            const role = document.getElementById('roleFilter').value;
            const search = document.getElementById('userSearch').value;
            
            const params = new URLSearchParams({
                page,
                per_page: 15,
                ...(role !== 'all' && { role }),
                ...(search && { search })
            });

            const response = await this.apiRequest(`/admin/users?${params}`);
            const users = response.data.data;
            const pagination = response.data;

            this.renderUsersTable(users);
            this.renderPagination('usersPagination', pagination, () => this.loadUsers);
        } catch (error) {
            this.showNotification(error.message, 'error');
        } finally {
            this.showLoading(false);
        }
    }

    renderUsersTable(users) {
        const tbody = document.getElementById('usersTableBody');
        
        if (users.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center">No users found</td></tr>';
            return;
        }

        tbody.innerHTML = users.map(user => `
            <tr>
                <td>${user.id}</td>
                <td>${user.name}</td>
                <td>${user.email}</td>
                <td><span class="status-badge ${user.role}">${user.role}</span></td>
                <td><span class="status-badge ${user.is_active ? 'active' : 'inactive'}">${user.is_active ? 'Active' : 'Inactive'}</span></td>
                <td>${new Date(user.created_at).toLocaleDateString()}</td>
                <td>
                    <button class="btn btn-sm btn-secondary" onclick="admin.editUser(${user.id})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-warning" onclick="admin.toggleUserStatus(${user.id})">
                        <i class="fas fa-toggle-${user.is_active ? 'on' : 'off'}"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="admin.deleteUser(${user.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `).join('');
    }

    async loadStudentProfiles(page = 1) {
        try {
            this.showLoading(true);
            
            const status = document.getElementById('statusFilter').value;
            const search = document.getElementById('studentSearch').value;
            
            const params = new URLSearchParams({
                page,
                per_page: 15,
                ...(status !== 'all' && { status }),
                ...(search && { search })
            });

            const response = await this.apiRequest(`/admin/student-profiles?${params}`);
            const profiles = response.data.data;
            const pagination = response.data;

            this.renderStudentsTable(profiles);
            this.renderPagination('studentsPagination', pagination, () => this.loadStudentProfiles);
        } catch (error) {
            this.showNotification(error.message, 'error');
        } finally {
            this.showLoading(false);
        }
    }

    renderStudentsTable(profiles) {
        const tbody = document.getElementById('studentsTableBody');
        
        if (profiles.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center">No student profiles found</td></tr>';
            return;
        }

        tbody.innerHTML = profiles.map(profile => {
            const completion = profile.required_hours > 0 
                ? Math.round((profile.rendered_hours / profile.required_hours) * 100)
                : 0;
            
            return `
                <tr>
                    <td>${profile.student_id_number || '-'}</td>
                    <td>${profile.user?.name || '-'}</td>
                    <td>${profile.company_name || '-'}</td>
                    <td>${profile.course || '-'}</td>
                    <td><span class="status-badge ${profile.status}">${profile.status.replace('_', ' ')}</span></td>
                    <td>${profile.rendered_hours || 0} / ${profile.required_hours || 0}</td>
                    <td>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: ${completion}%"></div>
                            <span>${completion}%</span>
                        </div>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-secondary" onclick="admin.viewStudentDetails(${profile.id})">
                            <i class="fas fa-eye"></i>
                        </button>
                    </td>
                </tr>
            `;
        }).join('');
    }

    async loadSystemLogs(page = 1) {
        try {
            this.showLoading(true);
            
            const status = document.getElementById('logStatusFilter').value;
            const dateFrom = document.getElementById('dateFrom').value;
            const dateTo = document.getElementById('dateTo').value;
            
            const params = new URLSearchParams({
                page,
                per_page: 20,
                ...(status !== 'all' && { status }),
                ...(dateFrom && { date_from: dateFrom }),
                ...(dateTo && { date_to: dateTo })
            });

            const response = await this.apiRequest(`/admin/system-logs?${params}`);
            const logs = response.data.data;
            const pagination = response.data;

            this.renderLogsTable(logs);
            this.renderPagination('logsPagination', pagination, () => this.loadSystemLogs);
        } catch (error) {
            this.showNotification(error.message, 'error');
        } finally {
            this.showLoading(false);
        }
    }

    renderLogsTable(logs) {
        const tbody = document.getElementById('logsTableBody');
        
        if (logs.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center">No logs found</td></tr>';
            return;
        }

        tbody.innerHTML = logs.map(log => `
            <tr>
                <td>${new Date(log.log_date).toLocaleDateString()}</td>
                <td>${log.user?.name || '-'}</td>
                <td>${log.time_in || '-'}</td>
                <td>${log.time_out || '-'}</td>
                <td>${log.hours_rendered || '-'}</td>
                <td><span class="status-badge ${log.status}">${log.status}</span></td>
                <td>${log.notes || '-'}</td>
            </tr>
        `).join('');
    }

    async loadAnalytics() {
        try {
            this.showLoading(true);
            
            const response = await this.apiRequest('/admin/analytics');
            const analytics = response.data;

            // Company Distribution Chart
            this.createChart('companyChart', 'bar', {
                labels: analytics.company_distribution.map(item => item.company_name),
                datasets: [{
                    label: 'Number of Students',
                    data: analytics.company_distribution.map(item => item.student_count),
                    backgroundColor: '#3b82f6'
                }]
            });

            // Course Distribution Chart
            this.createChart('courseChart', 'pie', {
                labels: analytics.course_distribution.map(item => item.course),
                datasets: [{
                    data: analytics.course_distribution.map(item => item.student_count),
                    backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#14b8a6']
                }]
            });

            // Status Overview Chart
            this.createChart('statusChart', 'doughnut', {
                labels: ['Not Started', 'Active', 'Completed', 'Suspended'],
                datasets: [{
                    data: [
                        analytics.completion_stats.not_started,
                        analytics.completion_stats.active,
                        analytics.completion_stats.completed,
                        analytics.completion_stats.suspended
                    ],
                    backgroundColor: ['#6b7280', '#3b82f6', '#10b981', '#ef4444']
                }]
            });

            // Progress Chart
            this.createChart('progressChart', 'bar', {
                labels: analytics.monthly_registrations.map(item => item.month),
                datasets: [{
                    label: 'Monthly Registrations',
                    data: analytics.monthly_registrations.map(item => item.count),
                    backgroundColor: '#10b981'
                }]
            });
        } catch (error) {
            this.showNotification(error.message, 'error');
        } finally {
            this.showLoading(false);
        }
    }

    renderPagination(containerId, pagination, loadFunction) {
        const container = document.getElementById(containerId);
        if (!container) return;

        const { current_page, last_page, total } = pagination;

        let html = `
            <button ${current_page === 1 ? 'disabled' : ''} onclick="${loadFunction.name}(1)">
                <i class="fas fa-angle-double-left"></i>
            </button>
            <button ${current_page === 1 ? 'disabled' : ''} onclick="${loadFunction.name}(${current_page - 1})">
                <i class="fas fa-angle-left"></i>
            </button>
        `;

        for (let i = Math.max(1, current_page - 2); i <= Math.min(last_page, current_page + 2); i++) {
            html += `
                <button class="${i === current_page ? 'active' : ''}" onclick="${loadFunction.name}(${i})">
                    ${i}
                </button>
            `;
        }

        html += `
            <button ${current_page === last_page ? 'disabled' : ''} onclick="${loadFunction.name}(${current_page + 1})">
                <i class="fas fa-angle-right"></i>
            </button>
            <button ${current_page === last_page ? 'disabled' : ''} onclick="${loadFunction.name}(${last_page})">
                <i class="fas fa-angle-double-right"></i>
            </button>
        `;

        container.innerHTML = html;
    }

    // User Management Functions
    showCreateUserModal() {
        document.getElementById('userModalTitle').textContent = 'Add New User';
        document.getElementById('userForm').reset();
        document.getElementById('userModal').style.display = 'flex';
        document.getElementById('userForm').dataset.userId = '';
    }

    async editUser(userId) {
        try {
            const response = await this.apiRequest(`/admin/users`);
            const user = response.data.data.find(u => u.id === userId);
            
            if (!user) {
                throw new Error('User not found');
            }

            document.getElementById('userModalTitle').textContent = 'Edit User';
            document.getElementById('userName').value = user.name;
            document.getElementById('userEmail').value = user.email;
            document.getElementById('userRole').value = user.role;
            document.getElementById('userActive').checked = user.is_active;
            document.getElementById('userPassword').value = '';
            document.getElementById('userPasswordConfirm').value = '';
            document.getElementById('userPassword').required = false;
            document.getElementById('userPasswordConfirm').required = false;
            
            document.getElementById('userModal').style.display = 'flex';
            document.getElementById('userForm').dataset.userId = userId;
        } catch (error) {
            this.showNotification(error.message, 'error');
        }
    }

    async saveUser() {
        const userId = document.getElementById('userForm').dataset.userId;
        const isEdit = userId !== '';
        
        const formData = {
            name: document.getElementById('userName').value,
            email: document.getElementById('userEmail').value,
            role: document.getElementById('userRole').value,
            is_active: document.getElementById('userActive').checked
        };

        if (!isEdit || document.getElementById('userPassword').value) {
            formData.password = document.getElementById('userPassword').value;
            formData.password_confirmation = document.getElementById('userPasswordConfirm').value;
        }

        try {
            this.showLoading(true);
            
            const endpoint = isEdit ? `/admin/users/${userId}` : '/admin/users';
            const method = isEdit ? 'PUT' : 'POST';
            
            await this.apiRequest(endpoint, {
                method,
                body: JSON.stringify(formData)
            });

            this.closeUserModal();
            this.loadUsers();
            this.showNotification(`User ${isEdit ? 'updated' : 'created'} successfully`, 'success');
        } catch (error) {
            this.showNotification(error.message, 'error');
        } finally {
            this.showLoading(false);
        }
    }

    async toggleUserStatus(userId) {
        try {
            await this.apiRequest(`/admin/users/${userId}/toggle-status`, {
                method: 'PATCH'
            });
            
            this.loadUsers();
            this.showNotification('User status updated successfully', 'success');
        } catch (error) {
            this.showNotification(error.message, 'error');
        }
    }

    async deleteUser(userId) {
        if (!confirm('Are you sure you want to delete this user?')) return;

        try {
            await this.apiRequest(`/admin/users/${userId}`, {
                method: 'DELETE'
            });
            
            this.loadUsers();
            this.showNotification('User deleted successfully', 'success');
        } catch (error) {
            this.showNotification(error.message, 'error');
        }
    }

    closeUserModal() {
        document.getElementById('userModal').style.display = 'none';
        document.getElementById('userForm').reset();
        document.getElementById('userPassword').required = true;
        document.getElementById('userPasswordConfirm').required = true;
    }

    viewStudentDetails(profileId) {
        // Implementation for viewing detailed student information
        this.showNotification('Student details view coming soon', 'info');
    }

    showLoading(show) {
        document.getElementById('loadingOverlay').style.display = show ? 'flex' : 'none';
    }

    showNotification(message, type = 'info') {
        const notification = document.getElementById('notification');
        notification.textContent = message;
        notification.className = `notification ${type} show`;
        
        setTimeout(() => {
            notification.classList.remove('show');
        }, 5000);
    }

    refreshData() {
        this.loadPageData(this.currentPage);
        this.showNotification('Data refreshed', 'success');
    }
}

// Global functions for inline event handlers
window.admin = new AdminDashboard();

function showCreateUserModal() {
    window.admin.showCreateUserModal();
}

function closeUserModal() {
    window.admin.closeUserModal();
}

function refreshData() {
    window.admin.refreshData();
}
