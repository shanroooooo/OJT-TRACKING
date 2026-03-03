# CTU OJT Tracking - Admin Portal

A comprehensive admin dashboard for managing the CTU OJT Tracking System.

## Features

### 🔐 **Authentication & Security**
- Secure admin login with role-based access control
- Token-based authentication using Laravel Sanctum
- Session management and automatic logout

### 📊 **Dashboard Overview**
- Real-time statistics cards showing system metrics
- Interactive charts for OJT completion status and monthly registrations
- Quick access to all admin functions

### 👥 **User Management**
- Complete CRUD operations for users
- Role assignment (Student, Supervisor, Admin)
- User status management (Active/Inactive)
- Search and filtering capabilities
- Pagination for large datasets

### 🎓 **Student Profile Management**
- View and manage all student profiles
- Filter by status (Not Started, Active, Completed, Suspended)
- Search by student ID, company, or name
- Progress tracking with visual indicators
- Detailed profile information display

### 📝 **System Logs**
- View all time logs across the system
- Filter by status (Pending, Approved, Rejected)
- Date range filtering
- Detailed log information with student details

### 📈 **Analytics & Reports**
- Company distribution charts
- Course enrollment analytics
- OJT completion statistics
- Monthly registration trends
- Interactive data visualizations

## Technical Implementation

### **Frontend Technologies**
- **HTML5**: Semantic markup structure
- **CSS3**: Modern styling with CSS Grid and Flexbox
- **JavaScript (ES6+)**: Modern JavaScript with async/await
- **Chart.js**: Interactive data visualization
- **Font Awesome**: Icon library

### **Design Features**
- **Responsive Design**: Mobile-first approach
- **Modern UI**: Clean, professional interface
- **Accessibility**: Semantic HTML and ARIA labels
- **Performance**: Optimized loading and rendering
- **User Experience**: Intuitive navigation and feedback

### **API Integration**
- RESTful API communication
- Error handling and user feedback
- Loading states and notifications
- Data validation and sanitization

## File Structure

```
ctu-ojt-frontend/
├── admin.html          # Admin portal main page
├── admin-style.css     # Comprehensive styling
├── admin-script.js     # JavaScript functionality
├── index.html          # Student portal (existing)
├── script.js           # Student portal scripts (existing)
└── style.css           # Student portal styles (existing)
```

## Getting Started

### Prerequisites
1. Laravel API backend running on `http://localhost:8000`
2. Admin credentials configured in the database

### Default Login Credentials
- **Email**: admin@ctu-ojt.com
- **Password**: admin123

### Installation
1. Ensure the Laravel API is running
2. Open `admin.html` in a web browser
3. Login with admin credentials
4. Start managing the OJT system

## API Endpoints Used

### Authentication
- `POST /api/auth/login` - Admin login
- `GET /api/user` - Verify token and get user info

### Dashboard
- `GET /api/admin/dashboard` - System statistics
- `GET /api/admin/analytics` - Analytics data

### User Management
- `GET /api/admin/users` - List users with pagination
- `POST /api/admin/users` - Create new user
- `PUT /api/admin/users/{id}` - Update user
- `DELETE /api/admin/users/{id}` - Delete user
- `PATCH /api/admin/users/{id}/toggle-status` - Toggle user status

### Student Profiles
- `GET /api/admin/student-profiles` - List student profiles

### System Logs
- `GET /api/admin/system-logs` - List time logs

## Browser Compatibility

- Chrome 80+
- Firefox 75+
- Safari 13+
- Edge 80+

## Security Features

- **Input Validation**: Client-side and server-side validation
- **XSS Protection**: Proper output escaping
- **CSRF Protection**: Token-based requests
- **Authentication**: Role-based access control
- **Session Management**: Secure token handling

## Performance Optimizations

- **Lazy Loading**: Charts and data loaded on demand
- **Pagination**: Efficient data handling for large datasets
- **Caching**: Browser caching for static assets
- **Minification**: Optimized CSS and JavaScript
- **Responsive Images**: Optimized for different screen sizes

## Future Enhancements

- [ ] Real-time notifications
- [ ] Export functionality (CSV, PDF)
- [ ] Advanced filtering options
- [ ] Bulk operations
- [ ] Email notifications
- [ ] Audit trail
- [ ] Multi-language support
- [ ] Dark mode theme

## Support

For issues and questions:
1. Check the API documentation
2. Verify API endpoints are accessible
3. Ensure proper database configuration
4. Check browser console for errors

---

**Note**: This admin portal is designed to work with the existing CTU OJT Tracking API. Ensure the API is properly configured and accessible before using the admin interface.
