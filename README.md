# School Management System

A comprehensive school management system built with **FastAPI + React + MongoDB**.

## 🎯 Project Overview

This is a complete rebuild of a PHP-based school management system into a modern tech stack, featuring role-based access control, student/teacher management, attendance tracking, exam management, and more.

## 🚀 Tech Stack

### Backend
- **FastAPI** - Modern Python web framework
- **MongoDB** - NoSQL database with Motor async driver
- **JWT** - Token-based authentication
- **Passlib & Bcrypt** - Password hashing
- **Pydantic** - Data validation

### Frontend
- **React 18** - UI framework
- **React Router** - Navigation
- **Tailwind CSS** - Styling
- **Axios** - API requests
- **Heroicons** - Icons

## 📁 Project Structure

```
/app/
├── backend/
│   ├── server.py          # Main FastAPI application
│   ├── models.py          # Pydantic models & schemas
│   ├── auth.py            # Authentication utilities
│   ├── requirements.txt   # Python dependencies
│   └── .env              # Environment variables
├── frontend/
│   ├── src/
│   │   ├── components/   # Reusable components
│   │   ├── context/      # React context (Auth)
│   │   ├── pages/        # Page components
│   │   ├── App.js        # Main app with routing
│   │   └── index.css     # Global styles
│   ├── package.json      # Node dependencies
│   └── .env             # Frontend environment
└── README.md
```

## 🔐 User Roles

The system supports multiple user roles with specific permissions:

1. **Admin** - Full system access
2. **Teacher** - Manage classes, students, attendance, exams
3. **Student** - View classes, attendance, grades, assignments
4. **Parent** - View children's information, grades, attendance
5. **Accountant** - Manage finances, fees, payments
6. **Librarian** - Manage library resources

## ✅ Phase 1: Foundation & Authentication (COMPLETED)

### Features Implemented:

#### Backend APIs:
- ✅ User authentication (login, register, JWT tokens)
- ✅ Role-based access control (RBAC)
- ✅ User management (CRUD operations)
- ✅ School year management
- ✅ Section management
- ✅ Class management
- ✅ Subject management
- ✅ Teacher management
- ✅ Student management
- ✅ Parent management
- ✅ Settings management
- ✅ Dashboard statistics

#### Frontend:
- ✅ Login page with validation
- ✅ Registration page with role selection
- ✅ Protected routes with role-based access
- ✅ Responsive sidebar navigation
- ✅ Dashboard with role-specific views
- ✅ Authentication context with token management
- ✅ Beautiful UI with Tailwind CSS

## 🎬 Getting Started

### Prerequisites
- Python 3.8+
- Node.js 16+
- MongoDB running on localhost:27017

### Backend Setup

```bash
cd /app/backend
pip install -r requirements.txt

# Start backend (runs on port 8001)
sudo supervisorctl restart backend
```

### Frontend Setup

```bash
cd /app/frontend
yarn install

# Start frontend (runs on port 3000)
sudo supervisorctl restart frontend
```

## 🔑 Demo Credentials

After registration, use these test accounts:

```
Admin Account:
Username: admin
Password: admin123

(Create other role accounts via registration page)
```

## 📡 API Endpoints

### Authentication
- `POST /api/auth/register` - Register new user
- `POST /api/auth/login` - Login and get JWT token
- `GET /api/auth/me` - Get current user info

### Users
- `GET /api/users` - Get all users (Admin only)
- `GET /api/users/{user_id}` - Get user by ID
- `PUT /api/users/{user_id}` - Update user
- `DELETE /api/users/{user_id}` - Delete user (Admin only)

### School Year
- `POST /api/school-years` - Create school year
- `GET /api/school-years` - Get all school years
- `GET /api/school-years/current` - Get current school year

### Classes
- `POST /api/classes` - Create class
- `GET /api/classes` - Get all classes
- `GET /api/classes/{class_id}` - Get class by ID

### Subjects
- `POST /api/subjects` - Create subject
- `GET /api/subjects` - Get all subjects (filter by class_id)

### Teachers
- `POST /api/teachers` - Create teacher
- `GET /api/teachers` - Get all teachers
- `GET /api/teachers/{teacher_id}` - Get teacher by ID

### Students
- `POST /api/students` - Create student
- `GET /api/students` - Get all students (filter by class/section/year)
- `GET /api/students/{student_id}` - Get student by ID
- `PUT /api/students/{student_id}` - Update student

### Parents
- `POST /api/parents` - Create parent
- `GET /api/parents` - Get all parents

### Settings
- `POST /api/settings` - Create/update settings
- `GET /api/settings` - Get school settings

### Dashboard
- `GET /api/dashboard/stats` - Get role-specific statistics

## 🛣️ Roadmap

### ✅ Phase 1: Foundation & Authentication (COMPLETED)
- Multi-role authentication
- User management
- Basic academic structure (classes, subjects)
- Dashboard skeletons

### 📋 Phase 2: Academic Core (NEXT)
- Complete teacher profile management
- Complete student profile management
- Timetable/Routine management
- Academic year calendar

### 📋 Phase 3: Attendance & Exams
- Daily attendance tracking
- Subject-wise attendance
- Exam types & scheduling
- Mark entry system
- Grade calculation
- Report cards

### 📋 Phase 4: Financial Management
- Fee types & structure
- Invoice generation
- Payment tracking
- Income & Expense
- Payment gateway integration

### 📋 Phase 5: Extended Modules
- Library management
- Hostel management
- Transport management
- Assignment system
- Notice board
- Messaging system

### 📋 Phase 6: Reports & Analytics
- Attendance reports
- Fee reports
- Academic performance
- Certificate generation
- ID cards

### 📋 Phase 7: Advanced Features
- Bulk import/export
- Email/SMS notifications
- Document management
- Visitor management
- Online exams
- Mobile responsive enhancements

## 🔧 Environment Variables

### Backend (.env)
```
MONGO_URL=mongodb://localhost:27017
DB_NAME=school_management
CORS_ORIGINS=*
SECRET_KEY=your-secret-key-here
```

### Frontend (.env)
```
REACT_APP_BACKEND_URL=https://your-domain.com
```

## 🧪 Testing

```bash
# Test backend
curl -X POST http://localhost:8001/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{"username":"test","email":"test@test.com","name":"Test","password":"test123","role":"admin"}'

# Test login
curl -X POST http://localhost:8001/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"test","password":"test123"}'
```

## 📝 Database Collections

- `users` - All system users
- `teachers` - Teacher profiles
- `students` - Student profiles
- `parents` - Parent profiles
- `classes` - Class definitions
- `sections` - Section definitions
- `subjects` - Subject definitions
- `school_years` - Academic years
- `settings` - School settings

(More collections will be added in subsequent phases)

## 🎨 Features

- **Responsive Design** - Works on desktop, tablet, and mobile
- **Role-Based Access** - Different interfaces for different roles
- **Secure Authentication** - JWT tokens with bcrypt password hashing
- **Modern UI** - Clean, intuitive interface with Tailwind CSS
- **Fast & Scalable** - Async Python with MongoDB
- **Type Safety** - Pydantic models for data validation

## 📄 License

Proprietary - All rights reserved

## 👥 Support

For questions or issues, please contact the development team.

---

**Version**: 1.0.0 (Phase 1 Complete)  
**Last Updated**: December 2024
