import React from 'react';
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import { AuthProvider, useAuth } from './context/AuthContext';
import ProtectedRoute from './components/ProtectedRoute';
import Layout from './components/Layout';
import Login from './pages/Login';
import Register from './pages/Register';
import Dashboard from './pages/Dashboard';

// Phase 2 - Academic Core
import SchoolYearsManagement from './pages/SchoolYearsManagement';
import ClassesManagement from './pages/ClassesManagement';
import SubjectsManagement from './pages/SubjectsManagement';
import StudentsManagement from './pages/StudentsManagement';
import TeachersManagement from './pages/TeachersManagement';
import TimetableManagement from './pages/TimetableManagement';

// Phase 3 - Attendance & Exams
import AttendanceManagement from './pages/AttendanceManagement';
import ExamsManagement from './pages/ExamsManagement';

// Phase 4 - Finance
import FinanceManagement from './pages/FinanceManagement';

import './App.css';
import { Toaster } from './components/ui/toaster';

// Placeholder pages for remaining features
const Users = () => (
  <div className="bg-white rounded-xl shadow-sm p-6">
    <h2 className="text-2xl font-bold mb-4">Users Management</h2>
    <p className="text-gray-600">Users management interface coming in next update...</p>
  </div>
);

const Parents = () => (
  <div className="bg-white rounded-xl shadow-sm p-6">
    <h2 className="text-2xl font-bold mb-4">Parents Management</h2>
    <p className="text-gray-600">Parents management interface coming in next update...</p>
  </div>
);

const Reports = () => (
  <div className="bg-white rounded-xl shadow-sm p-6">
    <h2 className="text-2xl font-bold mb-4">Reports</h2>
    <p className="text-gray-600">Reports interface coming in next update...</p>
  </div>
);

const Settings = () => (
  <div className="bg-white rounded-xl shadow-sm p-6">
    <h2 className="text-2xl font-bold mb-4">Settings</h2>
    <p className="text-gray-600">Settings interface coming in next update...</p>
  </div>
);

const Unauthorized = () => (
  <div className="min-h-screen flex items-center justify-center bg-gray-50">
    <div className="text-center">
      <h1 className="text-4xl font-bold text-gray-900 mb-4">403 - Unauthorized</h1>
      <p className="text-gray-600 mb-8">You don't have permission to access this page.</p>
      <a href="/dashboard" className="text-blue-600 hover:text-blue-700 font-medium">
        Go to Dashboard
      </a>
    </div>
  </div>
);

function AppRoutes() {
  const { user } = useAuth();

  return (
    <Routes>
      <Route path="/login" element={!user ? <Login /> : <Navigate to="/dashboard" />} />
      <Route path="/register" element={!user ? <Register /> : <Navigate to="/dashboard" />} />
      <Route path="/unauthorized" element={<Unauthorized />} />
      
      <Route
        path="/dashboard"
        element={
          <ProtectedRoute>
            <Layout>
              <Dashboard />
            </Layout>
          </ProtectedRoute>
        }
      />
      
      <Route
        path="/users"
        element={
          <ProtectedRoute allowedRoles={['admin']}>
            <Layout>
              <Users />
            </Layout>
          </ProtectedRoute>
        }
      />
      
      {/* Phase 2 - Academic Core Routes */}
      <Route
        path="/school-years"
        element={
          <ProtectedRoute allowedRoles={['admin']}>
            <Layout>
              <SchoolYearsManagement />
            </Layout>
          </ProtectedRoute>
        }
      />
      
      <Route
        path="/classes"
        element={
          <ProtectedRoute allowedRoles={['admin']}>
            <Layout>
              <ClassesManagement />
            </Layout>
          </ProtectedRoute>
        }
      />
      
      <Route
        path="/subjects"
        element={
          <ProtectedRoute allowedRoles={['admin']}>
            <Layout>
              <SubjectsManagement />
            </Layout>
          </ProtectedRoute>
        }
      />
      
      <Route
        path="/students"
        element={
          <ProtectedRoute allowedRoles={['admin', 'teacher']}>
            <Layout>
              <StudentsManagement />
            </Layout>
          </ProtectedRoute>
        }
      />
      
      <Route
        path="/teachers"
        element={
          <ProtectedRoute allowedRoles={['admin']}>
            <Layout>
              <TeachersManagement />
            </Layout>
          </ProtectedRoute>
        }
      />
      
      <Route
        path="/timetable"
        element={
          <ProtectedRoute allowedRoles={['admin', 'teacher']}>
            <Layout>
              <TimetableManagement />
            </Layout>
          </ProtectedRoute>
        }
      />
      
      {/* Phase 3 - Attendance & Exams Routes */}
      <Route
        path="/attendance"
        element={
          <ProtectedRoute allowedRoles={['admin', 'teacher']}>
            <Layout>
              <AttendanceManagement />
            </Layout>
          </ProtectedRoute>
        }
      />
      
      <Route
        path="/exams"
        element={
          <ProtectedRoute allowedRoles={['admin', 'teacher']}>
            <Layout>
              <ExamsManagement />
            </Layout>
          </ProtectedRoute>
        }
      />
      
      {/* Phase 4 - Finance Routes */}
      <Route
        path="/finance"
        element={
          <ProtectedRoute allowedRoles={['admin', 'accountant']}>
            <Layout>
              <FinanceManagement />
            </Layout>
          </ProtectedRoute>
        }
      />
      
      {/* Placeholder routes */}
      <Route
        path="/parents"
        element={
          <ProtectedRoute allowedRoles={['admin']}>
            <Layout>
              <Parents />
            </Layout>
          </ProtectedRoute>
        }
      />
      
      <Route
        path="/reports"
        element={
          <ProtectedRoute allowedRoles={['admin', 'teacher']}>
            <Layout>
              <Reports />
            </Layout>
          </ProtectedRoute>
        }
      />
      
      <Route
        path="/settings"
        element={
          <ProtectedRoute allowedRoles={['admin']}>
            <Layout>
              <Settings />
            </Layout>
          </ProtectedRoute>
        }
      />
      
      <Route path="/" element={<Navigate to="/login" />} />
      <Route path="*" element={<Navigate to="/dashboard" />} />
    </Routes>
  );
}

function App() {
  return (
    <AuthProvider>
      <BrowserRouter>
        <AppRoutes />
        <Toaster />
      </BrowserRouter>
    </AuthProvider>
  );
}

export default App;
