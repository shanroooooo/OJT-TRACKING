# CTU OJT Tracking API

<p align="center">
  <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="200" alt="Laravel Logo">
</p>

A RESTful API for the CTU (Cebu Technological University) OJT (On-the-Job Training) Tracking System. This backend application provides comprehensive endpoints for managing student profiles, time logging, and OJT progress tracking.

## Features

- **User Authentication**: Secure registration and login with Laravel Sanctum tokens
- **Role-Based Access Control**: Support for student, supervisor, and admin roles
- **Student Profile Management**: Complete student information and OJT details
- **Time Logging**: Students can log time in/out for their OJT activities
- **Progress Tracking**: Automatic calculation of rendered hours and completion percentage
- **Company Information**: Track company details and supervisor information

## Tech Stack

- **Backend**: Laravel 12.0
- **Authentication**: Laravel Sanctum
- **Database**: MySQL/PostgreSQL (configurable)
- **API Documentation**: RESTful API design

## API Endpoints

### Authentication
- `POST /api/auth/register` - User registration
- `POST /api/auth/login` - User login
- `POST /api/auth/logout` - User logout (requires token)
- `GET /api/user` - Get current user info (requires token)

### Student Routes (requires student role)
- `POST /api/student/time-in` - Log time in
- `PATCH /api/student/time-out` - Log time out
- `GET /api/student/today` - Get today's time log
- `GET /api/student/logs` - Get student's time logs

### Supervisor Routes (requires supervisor role)
- Routes for supervisor functionality (placeholder)

### Admin Routes (requires admin role)
- Routes for admin functionality (placeholder)

## Data Models

### User
- Basic user information with role-based access
- Roles: student, supervisor, admin

### StudentProfile
- Student academic information (course, major, year level)
- Company details (name, address, supervisor)
- OJT tracking (required hours, rendered hours, dates)
- Automatic calculation of remaining hours and completion percentage

### Log
- Time logging entries for student activities

## Installation

1. Clone the repository
```bash
git clone <repository-url>
cd ctu-ojt-api
```

2. Install dependencies
```bash
composer install
```

3. Environment setup
```bash
cp .env.example .env
php artisan key:generate
```

4. Configure your database in `.env` file

5. Run migrations
```bash
php artisan migrate
```

6. Start the development server
```bash
php artisan serve
```

## Development Scripts

- `composer run setup` - Complete project setup
- `composer run dev` - Start development server with hot reload
- `composer run test` - Run test suite

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## License

This project is open-sourced software licensed under the MIT license.
