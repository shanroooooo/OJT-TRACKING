# OJT Tracking API Documentation

## Admin Panel Endpoints

### Authentication Required: All endpoints require valid Sanctum token + Admin role

---

## Admin Dashboard

### `GET /api/admin/dashboard`
Get system overview statistics

**Response:**
```json
{
  "success": true,
  "data": {
    "total_users": 150,
    "total_students": 120,
    "total_supervisors": 25,
    "total_admins": 5,
    "active_students": 85,
    "completed_ojt": 35,
    "total_hours_rendered": 12500.5,
    "pending_logs": 45
  }
}
```

---

## User Management

### `GET /api/admin/users`
Get all users with pagination and filtering

**Query Parameters:**
- `role` (optional): Filter by role (student, supervisor, admin, all)
- `search` (optional): Search by name or email
- `per_page` (optional): Items per page (default: 15)

**Response:**
```json
{
  "success": true,
  "data": {
    "data": [...],
    "current_page": 1,
    "total": 150
  }
}
```

### `POST /api/admin/users`
Create new user

**Request Body:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "role": "student",
  "is_active": true
}
```

### `PUT /api/admin/users/{user}`
Update user details

### `DELETE /api/admin/users/{user}`
Delete user (prevents deletion of last admin)

### `PATCH /api/admin/users/{user}/toggle-status`
Toggle user active/inactive status

---

## Student Profile Management

### `GET /api/admin/student-profiles`
Get all student profiles with filtering

**Query Parameters:**
- `status` (optional): Filter by status (active, completed, suspended, not_started)
- `search` (optional): Search by student ID, company, or name
- `per_page` (optional): Items per page (default: 15)

---

## System Logs

### `GET /api/admin/system-logs`
Get all time logs with filtering

**Query Parameters:**
- `status` (optional): Filter by status (pending, approved, rejected)
- `date_from` (optional): Filter from date (YYYY-MM-DD)
- `date_to` (optional): Filter to date (YYYY-MM-DD)
- `per_page` (optional): Items per page (default: 20)

---

## Analytics

### `GET /api/admin/analytics`
Get system analytics and reports

**Response:**
```json
{
  "success": true,
  "data": {
    "monthly_registrations": [...],
    "company_distribution": [...],
    "course_distribution": [...],
    "completion_stats": {
      "not_started": 10,
      "active": 85,
      "completed": 35,
      "suspended": 5
    }
  }
}
```

---

## Supervisor Panel Endpoints

### Authentication Required: Sanctum token + Supervisor role

---

## Supervisor Dashboard

### `GET /api/supervisor/dashboard`
Get supervisor-specific statistics

**Response:**
```json
{
  "success": true,
  "data": {
    "stats": {
      "total_students": 15,
      "active_students": 12,
      "completed_students": 3,
      "pending_logs": 8,
      "total_hours_supervised": 1250.5
    },
    "recent_activities": [...]
  }
}
```

---

## Student Management

### `GET /api/supervisor/students`
Get assigned students

### `GET /api/supervisor/students/{studentProfile}`
Get detailed student information

### `GET /api/supervisor/students/{studentProfile}/progress`
Get student progress analytics

### `GET /api/supervisor/students/{studentProfile}/export`
Export student report (JSON/CSV)

---

## Time Log Review

### `GET /api/supervisor/time-logs`
Get time logs for assigned students

### `POST /api/supervisor/time-logs/{log}/review`
Review and approve/reject time log

**Request Body:**
```json
{
  "status": "approved",
  "review_notes": "Good work on the project documentation",
  "adjusted_hours": 8.5
}
```

### `POST /api/supervisor/time-logs/bulk-review`
Bulk review multiple time logs

**Request Body:**
```json
{
  "log_ids": [1, 2, 3, 4],
  "status": "approved",
  "review_notes": "Weekly batch approval"
}
```

---

## Default Login Credentials

### Admin Account
- **Email:** admin@ctu-ojt.com
- **Password:** admin123

### Supervisor Account
- **Email:** supervisor@company.com
- **Password:** supervisor123

---

## Error Responses

### 401 Unauthorized
```json
{
  "message": "Unauthenticated."
}
```

### 403 Forbidden
```json
{
  "message": "Forbidden. You do not have the required role.",
  "your_role": "student",
  "required_roles": ["admin"]
}
```

### 422 Validation Error
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email has already been taken."]
  }
}
```

---

## Rate Limiting
- API requests are limited to 60 requests per minute per user
- Exceeded limits return HTTP 429 Too Many Requests

---

## Pagination
- All list endpoints support pagination
- Default page size: 15-20 items
- Use `per_page` parameter to adjust
- Response includes pagination metadata
