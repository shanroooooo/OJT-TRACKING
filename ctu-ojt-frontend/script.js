// API Configuration
const API_BASE_URL = 'http://127.0.0.1:8000/api';

// Global State
let currentUser = null;
let authToken = null;
let currentProfile = null;

// Initialize App
document.addEventListener('DOMContentLoaded', function() {
    checkAuth();
    setupEventListeners();
    updateCurrentTime();
    setInterval(updateCurrentTime, 1000);
});

// Check if user is authenticated
function checkAuth() {
    const token = localStorage.getItem('authToken');
    const user = localStorage.getItem('currentUser');
    
    if (token && user) {
        authToken = token;
        currentUser = JSON.parse(user);
        showDashboard();
        loadDashboard();
    } else {
        showAuthSection();
    }
}

// Setup Event Listeners
function setupEventListeners() {
    // Login Form
    document.getElementById('loginForm').addEventListener('submit', handleLogin);
    
    // Register Form
    document.getElementById('registerForm').addEventListener('submit', handleRegister);
    
    // Profile Form
    document.getElementById('profileForm').addEventListener('submit', handleProfileUpdate);
}

// Authentication Functions
async function handleLogin(e) {
    e.preventDefault();
    
    const email = document.getElementById('loginEmail').value;
    const password = document.getElementById('loginPassword').value;
    
    try {
        showLoading();
        const response = await fetch(`${API_BASE_URL}/auth/login`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ email, password })
        });
        
        const data = await response.json();
        
        if (response.ok) {
            authToken = data.token;
            currentUser = data.user;
            
            localStorage.setItem('authToken', authToken);
            localStorage.setItem('currentUser', JSON.stringify(currentUser));
            
            showToast('Login successful!', 'success');
            showDashboard();
            loadDashboard();
        } else {
            showToast(data.message || 'Login failed', 'error');
        }
    } catch (error) {
        showToast('Network error. Please try again.', 'error');
    } finally {
        hideLoading();
    }
}

async function handleRegister(e) {
    e.preventDefault();
    
    const name = document.getElementById('registerName').value;
    const email = document.getElementById('registerEmail').value;
    const password = document.getElementById('registerPassword').value;
    const passwordConfirm = document.getElementById('registerPasswordConfirm').value;
    
    if (password !== passwordConfirm) {
        showToast('Passwords do not match', 'error');
        return;
    }
    
    try {
        showLoading();
        const response = await fetch(`${API_BASE_URL}/auth/register`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ 
                name, 
                email, 
                password, 
                password_confirmation: passwordConfirm,
                role: 'student'
            })
        });
        
        const data = await response.json();
        
        if (response.ok) {
            authToken = data.token;
            currentUser = data.user;
            
            localStorage.setItem('authToken', authToken);
            localStorage.setItem('currentUser', JSON.stringify(currentUser));
            
            showToast('Registration successful!', 'success');
            showDashboard();
            loadDashboard();
        } else {
            showToast(data.message || 'Registration failed', 'error');
        }
    } catch (error) {
        showToast('Network error. Please try again.', 'error');
    } finally {
        hideLoading();
    }
}

function logout() {
    localStorage.removeItem('authToken');
    localStorage.removeItem('currentUser');
    authToken = null;
    currentUser = null;
    currentProfile = null;
    
    showToast('Logged out successfully', 'info');
    showAuthSection();
}

// UI Navigation Functions
function showAuthSection() {
    document.getElementById('auth-section').style.display = 'flex';
    document.getElementById('dashboard-section').style.display = 'none';
}

function showDashboard() {
    document.getElementById('auth-section').style.display = 'none';
    document.getElementById('dashboard-section').style.display = 'flex';
    
    // Update user name
    document.getElementById('userName').textContent = currentUser.name;
}

function showDashboard() {
    document.getElementById('auth-section').style.display = 'none';
    document.getElementById('dashboard-section').style.display = 'flex';
    
    // Hide all views
    document.querySelectorAll('.content-view').forEach(view => {
        view.style.display = 'none';
    });
    
    // Show dashboard view
    document.getElementById('dashboard-view').style.display = 'block';
    
    // Update navigation
    updateNavActive('dashboard');
    
    // Update user name
    document.getElementById('userName').textContent = currentUser.name;
}

function showProfile() {
    document.querySelectorAll('.content-view').forEach(view => {
        view.style.display = 'none';
    });
    
    document.getElementById('profile-view').style.display = 'block';
    updateNavActive('profile');
    loadProfile();
}

function showTimeLog() {
    document.querySelectorAll('.content-view').forEach(view => {
        view.style.display = 'none';
    });
    
    document.getElementById('timelog-view').style.display = 'block';
    updateNavActive('timelog');
    loadTodayLog();
}

function showLogs() {
    document.querySelectorAll('.content-view').forEach(view => {
        view.style.display = 'none';
    });
    
    document.getElementById('logs-view').style.display = 'block';
    updateNavActive('logs');
    loadLogs();
}

function updateNavActive(section) {
    document.querySelectorAll('.nav-link').forEach(link => {
        link.classList.remove('active');
    });
    
    const activeLinks = {
        'dashboard': 0,
        'profile': 1,
        'timelog': 2,
        'logs': 3
    };
    
    if (activeLinks[section] !== undefined) {
        document.querySelectorAll('.nav-link')[activeLinks[section]].classList.add('active');
    }
}

// Auth Form Switching
function showRegister() {
    document.getElementById('login-form').style.display = 'none';
    document.getElementById('register-form').style.display = 'block';
}

function showLogin() {
    document.getElementById('login-form').style.display = 'block';
    document.getElementById('register-form').style.display = 'none';
}

// Dashboard Functions
async function loadDashboard() {
    try {
        const response = await apiRequest('/student/profile/summary');
        
        if (response) {
            updateDashboardCards(response);
            loadTodayLog();
        }
    } catch (error) {
        console.error('Failed to load dashboard:', error);
    }
}

function updateDashboardCards(data) {
    document.getElementById('renderedHours').textContent = data.progress.rendered_hours.toFixed(2);
    document.getElementById('remainingHours').textContent = data.progress.remaining_hours.toFixed(2);
    document.getElementById('completionPercentage').textContent = data.progress.completion_percentage.toFixed(1) + '%';
    document.getElementById('ojtStatus').textContent = data.profile.status.charAt(0).toUpperCase() + data.profile.status.slice(1);
}

async function loadTodayLog() {
    try {
        const response = await apiRequest('/student/today');
        
        if (response) {
            updateTodayLogUI(response);
        }
    } catch (error) {
        console.error('Failed to load today log:', error);
    }
}

function updateTodayLogUI(data) {
    const content = document.getElementById('todayLogContent');
    
    if (data.log) {
        const log = data.log;
        content.innerHTML = `
            <div class="log-item">
                <div class="log-header">
                    <span class="log-date">${formatDate(log.log_date)}</span>
                    <span class="log-status ${log.status}">${log.status}</span>
                </div>
                <div class="log-activities">${log.activities_done}</div>
                ${log.remarks ? `<div class="log-time">Remarks: ${log.remarks}</div>` : ''}
                <div class="log-time">
                    Time In: ${log.time_in || 'Not logged in'}
                    ${log.time_out ? `| Time Out: ${log.time_out} | Hours: ${log.hours_rendered}` : ''}
                </div>
            </div>
        `;
        
        // Update time logging buttons
        document.getElementById('timeInBtn').disabled = !data.can_time_in;
        document.getElementById('timeOutBtn').disabled = !data.can_time_out;
        
        if (log.activities_done) {
            document.getElementById('activitiesDone').value = log.activities_done;
        }
        if (log.remarks) {
            document.getElementById('remarks').value = log.remarks;
        }
    } else {
        content.innerHTML = '<p>No time log for today. Click "Time In" to start logging.</p>';
        document.getElementById('timeInBtn').disabled = false;
        document.getElementById('timeOutBtn').disabled = true;
    }
}

// Profile Functions
async function loadProfile() {
    try {
        const response = await apiRequest('/student/profile');
        
        if (response) {
            currentProfile = response.profile;
            displayProfile(response);
        }
    } catch (error) {
        console.error('Failed to load profile:', error);
    }
}

function displayProfile(data) {
    const content = document.getElementById('profileContent');
    const profile = data.profile;
    const progress = data.progress;
    
    content.innerHTML = `
        <div class="profile-info">
            <div class="form-row">
                <div class="form-group">
                    <label>Student ID Number</label>
                    <p>${profile.student_id_number}</p>
                </div>
                <div class="form-group">
                    <label>Course</label>
                    <p>${profile.course}</p>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Major</label>
                    <p>${profile.major || 'N/A'}</p>
                </div>
                <div class="form-group">
                    <label>Year Level</label>
                    <p>${getYearLevelText(profile.year_level)}</p>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Section</label>
                    <p>${profile.section || 'N/A'}</p>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <p>${profile.status}</p>
                </div>
            </div>
            <div class="form-group">
                <label>Company Name</label>
                <p>${profile.company_name || 'N/A'}</p>
            </div>
            <div class="form-group">
                <label>Company Address</label>
                <p>${profile.company_address || 'N/A'}</p>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Supervisor Name</label>
                    <p>${profile.supervisor_name || 'N/A'}</p>
                </div>
                <div class="form-group">
                    <label>Supervisor Contact</label>
                    <p>${profile.supervisor_contact || 'N/A'}</p>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>OJT Period</label>
                    <p>${formatDate(profile.ojt_start_date)} - ${formatDate(profile.ojt_end_date)}</p>
                </div>
                <div class="form-group">
                    <label>Progress</label>
                    <p>${progress.rendered_hours} / ${progress.required_hours} hours (${progress.completion_percentage.toFixed(1)}%)</p>
                </div>
            </div>
        </div>
    `;
}

function editProfile() {
    document.getElementById('profileContent').style.display = 'none';
    document.getElementById('editProfileForm').style.display = 'block';
    
    // Populate form with current data
    if (currentProfile) {
        document.getElementById('studentIdNumber').value = currentProfile.student_id_number || '';
        document.getElementById('course').value = currentProfile.course || '';
        document.getElementById('major').value = currentProfile.major || '';
        document.getElementById('yearLevel').value = currentProfile.year_level || '';
        document.getElementById('section').value = currentProfile.section || '';
        document.getElementById('requiredHours').value = currentProfile.required_hours || '';
        document.getElementById('companyName').value = currentProfile.company_name || '';
        document.getElementById('companyAddress').value = currentProfile.company_address || '';
        document.getElementById('supervisorName').value = currentProfile.supervisor_name || '';
        document.getElementById('supervisorContact').value = currentProfile.supervisor_contact || '';
        document.getElementById('ojtStartDate').value = currentProfile.ojt_start_date || '';
        document.getElementById('ojtEndDate').value = currentProfile.ojt_end_date || '';
    }
}

function cancelEditProfile() {
    document.getElementById('profileContent').style.display = 'block';
    document.getElementById('editProfileForm').style.display = 'none';
}

async function handleProfileUpdate(e) {
    e.preventDefault();
    
    const profileData = {
        student_id_number: document.getElementById('studentIdNumber').value,
        course: document.getElementById('course').value,
        major: document.getElementById('major').value,
        year_level: parseInt(document.getElementById('yearLevel').value),
        section: document.getElementById('section').value,
        required_hours: parseInt(document.getElementById('requiredHours').value),
        company_name: document.getElementById('companyName').value,
        company_address: document.getElementById('companyAddress').value,
        supervisor_name: document.getElementById('supervisorName').value,
        supervisor_contact: document.getElementById('supervisorContact').value,
        ojt_start_date: document.getElementById('ojtStartDate').value,
        ojt_end_date: document.getElementById('ojtEndDate').value
    };
    
    try {
        showLoading();
        const response = await apiRequest('/student/profile', 'PUT', profileData);
        
        if (response) {
            showToast('Profile updated successfully!', 'success');
            currentProfile = response.profile;
            cancelEditProfile();
            displayProfile({ profile: response.profile, progress: response.progress });
        }
    } catch (error) {
        showToast('Failed to update profile', 'error');
    } finally {
        hideLoading();
    }
}

// Time Logging Functions
async function timeIn() {
    const activitiesDone = document.getElementById('activitiesDone').value;
    const remarks = document.getElementById('remarks').value;
    
    if (!activitiesDone.trim()) {
        showToast('Please describe your activities', 'error');
        return;
    }
    
    try {
        showLoading();
        const response = await apiRequest('/student/time-in', 'POST', {
            activities_done: activitiesDone,
            remarks: remarks
        });
        
        if (response) {
            showToast('Time in recorded successfully!', 'success');
            loadTodayLog();
            loadDashboard();
        }
    } catch (error) {
        showToast('Failed to record time in', 'error');
    } finally {
        hideLoading();
    }
}

async function timeOut() {
    try {
        showLoading();
        const response = await apiRequest('/student/time-out', 'PATCH');
        
        if (response) {
            showToast('Time out recorded successfully!', 'success');
            loadTodayLog();
            loadDashboard();
        }
    } catch (error) {
        showToast('Failed to record time out', 'error');
    } finally {
        hideLoading();
    }
}

// Logs History Functions
async function loadLogs(page = 1) {
    try {
        const status = document.getElementById('statusFilter').value;
        const dateFrom = document.getElementById('dateFrom').value;
        const dateTo = document.getElementById('dateTo').value;
        
        const params = new URLSearchParams({
            page: page,
            per_page: 10
        });
        
        if (status) params.append('status', status);
        if (dateFrom) params.append('date_from', dateFrom);
        if (dateTo) params.append('date_to', dateTo);
        
        const response = await apiRequest(`/student/logs?${params}`);
        
        if (response) {
            displayLogs(response.logs);
            displayPagination(response.logs);
        }
    } catch (error) {
        console.error('Failed to load logs:', error);
    }
}

function displayLogs(logsData) {
    const content = document.getElementById('logsContent');
    
    if (logsData.data.length === 0) {
        content.innerHTML = '<p>No logs found.</p>';
        return;
    }
    
    content.innerHTML = logsData.data.map(log => `
        <div class="log-item">
            <div class="log-header">
                <span class="log-date">${formatDate(log.log_date)}</span>
                <span class="log-status ${log.status}">${log.status}</span>
            </div>
            <div class="log-activities">${log.activities_done}</div>
            ${log.remarks ? `<div class="log-time">Remarks: ${log.remarks}</div>` : ''}
            <div class="log-time">
                Time In: ${log.time_in}
                ${log.time_out ? `| Time Out: ${log.time_out} | Hours: ${log.hours_rendered}` : ''}
            </div>
        </div>
    `).join('');
}

function displayPagination(logsData) {
    const pagination = document.getElementById('pagination');
    
    if (logsData.last_page <= 1) {
        pagination.innerHTML = '';
        return;
    }
    
    let paginationHTML = '';
    
    // Previous button
    if (logsData.current_page > 1) {
        paginationHTML += `<button onclick="loadLogs(${logsData.current_page - 1})">Previous</button>`;
    }
    
    // Page numbers
    for (let i = 1; i <= logsData.last_page; i++) {
        const activeClass = i === logsData.current_page ? 'active' : '';
        paginationHTML += `<button class="${activeClass}" onclick="loadLogs(${i})">${i}</button>`;
    }
    
    // Next button
    if (logsData.current_page < logsData.last_page) {
        paginationHTML += `<button onclick="loadLogs(${logsData.current_page + 1})">Next</button>`;
    }
    
    pagination.innerHTML = paginationHTML;
}

// Utility Functions
async function apiRequest(endpoint, method = 'GET', data = null) {
    const config = {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${authToken}`
        }
    };
    
    if (data) {
        config.body = JSON.stringify(data);
    }
    
    const response = await fetch(`${API_BASE_URL}${endpoint}`, config);
    
    if (response.status === 401) {
        logout();
        return null;
    }
    
    if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.message || 'Request failed');
    }
    
    return await response.json();
}

function showLoading() {
    document.getElementById('loadingOverlay').style.display = 'flex';
}

function hideLoading() {
    document.getElementById('loadingOverlay').style.display = 'none';
}

function showToast(message, type = 'info') {
    const container = document.getElementById('toastContainer');
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    
    const icons = {
        success: 'fas fa-check-circle',
        error: 'fas fa-exclamation-circle',
        warning: 'fas fa-exclamation-triangle',
        info: 'fas fa-info-circle'
    };
    
    toast.innerHTML = `
        <i class="${icons[type]}"></i>
        <span>${message}</span>
    `;
    
    container.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 5000);
}

function updateCurrentTime() {
    const now = new Date();
    const timeString = now.toLocaleTimeString();
    const dateString = now.toLocaleDateString();
    
    const currentTimeElement = document.getElementById('currentTime');
    if (currentTimeElement) {
        currentTimeElement.innerHTML = `
            <div>${dateString}</div>
            <div>${timeString}</div>
        `;
    }
}

function formatDate(dateString) {
    if (!dateString) return 'N/A';
    
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

function getYearLevelText(year) {
    const levels = {
        1: '1st Year',
        2: '2nd Year',
        3: '3rd Year',
        4: '4th Year',
        5: '5th Year'
    };
    return levels[year] || year;
}
