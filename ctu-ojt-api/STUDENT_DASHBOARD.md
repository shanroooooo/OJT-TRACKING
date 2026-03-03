# Student Dashboard - CTU OJT Tracking System

## Overview

The Student Dashboard provides a comprehensive view of your OJT (On-the-Job Training) progress with real-time updates and intuitive interface.

## Features

### 🎯 Key Features
- **Real-time Clock Display** - Live clock with date display
- **Progress Overview** - Visual representation of your OJT completion
- **Time Logging** - Easy time in/out functionality
- **Activity Tracking** - Monitor daily and weekly activities
- **Statistics Dashboard** - Comprehensive log statistics

### 📊 Dashboard Components

#### 1. Welcome Header
- Personalized greeting with student information
- Real-time clock with animated glow effect
- Course and student ID display

#### 2. Quick Stats Grid
- **Rendered Hours**: Total hours completed
- **Remaining Hours**: Hours left to complete
- **Completion Percentage**: Visual progress ring
- **Weekly Hours**: Hours rendered this week

#### 3. Today's Time Log Status
- Current day's log information
- Time in/out status with visual indicators
- Quick action buttons for time logging
- Log status (pending/approved/rejected)

#### 4. Log Statistics
- Total logs count
- Pending, approved, and rejected logs
- Required hours information

#### 5. Recent Activity
- Last 7 days of activity
- Detailed log information with status badges
- Sortable by date and status

## How to Use

### 1. Access the Dashboard
1. Register for an account at `/register`
2. Login at `/login`
3. Create your student profile at `/profile/create`
4. Access dashboard at `/dashboard`

### 2. Time Logging
- **Time In**: Click "Time In" button and describe your activities
- **Time Out**: Click "Time Out" button when done for the day
- **View Logs**: Click "View Logs" to see detailed history

### 3. Profile Management
- Complete your profile with academic and company information
- Set your OJT schedule and required hours
- Update information as needed

## API Integration

The dashboard integrates with the following API endpoints:

### Authentication
- `POST /api/auth/login` - User login
- `POST /api/auth/register` - User registration
- `POST /api/auth/logout` - User logout

### Student Profile
- `GET /api/student/profile` - Get student profile
- `POST /api/student/profile` - Create student profile
- `PUT /api/student/profile` - Update student profile

### Time Logging
- `POST /api/student/time-in` - Log time in
- `PATCH /api/student/time-out` - Log time out
- `GET /api/student/today` - Get today's log
- `GET /api/student/logs` - Get all logs

## Technical Implementation

### Frontend Technologies
- **Tailwind CSS** - Modern utility-first CSS framework
- **Font Awesome** - Icon library
- **Vanilla JavaScript** - No framework dependencies
- **Blade Templates** - Laravel templating engine

### Backend Integration
- **Laravel 12.0** - PHP framework
- **Laravel Sanctum** - API authentication
- **MySQL/SQLite** - Database support
- **Middleware** - Role-based access control

### Key Components
- `StudentDashboardController` - Main dashboard logic
- `EnsureStudentProfile` - Authentication middleware
- `StudentProfile` & `Log` models - Data management
- Real-time clock with JavaScript
- Responsive design for all devices

## Security Features
- CSRF protection on all forms
- Role-based access control
- Authentication middleware
- Input validation and sanitization
- Secure API endpoints

## Browser Compatibility
- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

## Mobile Responsiveness
- Fully responsive design
- Touch-friendly interface
- Optimized for mobile devices
- Progressive enhancement

## Future Enhancements
- 📱 Mobile app integration
- 📊 Advanced analytics
- 🔔 Push notifications
- 📄 PDF report generation
- 🔄 Real-time collaboration
- 📈 Progress charts and graphs

## Support

For technical support or questions about the Student Dashboard:
1. Check the system documentation
2. Contact your OJT coordinator
3. Report issues through the system feedback form

---

*Last updated: February 2026*
