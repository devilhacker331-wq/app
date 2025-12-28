import React from 'react';
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import { AuthProvider, useAuth } from './context/AuthContext';
import ProtectedRoute from './components/ProtectedRoute';
import Layout from './components/Layout';
import Login from './pages/Login';
import Register from './pages/Register';
import Dashboard from './pages/Dashboard';
import './App.css';

// Placeholder pages for Phase 2+
const Users = () => (
  <div className="bg-white rounded-xl shadow-sm p-6">
    <h2 className="text-2xl font-bold mb-4">Users Management</h2>
    <p className="text-gray-600">Users management interface coming in next update...</p>
  </div>
);

const Teachers = () => (
  <div className="bg-white rounded-xl shadow-sm p-6">
    <h2 className="text-2xl font-bold mb-4">Teachers Management</h2>
    <p className="text-gray-600">Teachers management interface coming in next update...</p>
  </div>
);

const Students = () => (
  <div className="bg-white rounded-xl shadow-sm p-6">
    <h2 className="text-2xl font-bold mb-4">Students Management</h2>
    <p className="text-gray-600">Students management interface coming in next update...</p>
  </div>
);

const Parents = () => (
  <div className="bg-white rounded-xl shadow-sm p-6">
    <h2 className="text-2xl font-bold mb-4">Parents Management</h2>
    <p className="text-gray-600">Parents management interface coming in next update...</p>
  </div>
);

const Classes = () => (
  <div className="bg-white rounded-xl shadow-sm p-6">
    <h2 className="text-2xl font-bold mb-4">Classes Management</h2>
    <p className="text-gray-600">Classes management interface coming in next update...</p>
  </div>
);

const Subjects = () => (
  <div className="bg-white rounded-xl shadow-sm p-6">
    <h2 className="text-2xl font-bold mb-4">Subjects Management</h2>
    <p className="text-gray-600">Subjects management interface coming in next update...</p>
  </div>
);

const Attendance = () => (
  <div className="bg-white rounded-xl shadow-sm p-6">
    <h2 className="text-2xl font-bold mb-4">Attendance Management</h2>
    <p className="text-gray-600">Attendance management interface coming in next update...</p>
  </div>
);

const Exams = () => (
  <div className="bg-white rounded-xl shadow-sm p-6">
    <h2 className="text-2xl font-bold mb-4">Exams Management</h2>
    <p className="text-gray-600">Exams management interface coming in next update...</p>
  </div>
);

const Finance = () => (
  <div className="bg-white rounded-xl shadow-sm p-6">
    <h2 className="text-2xl font-bold mb-4">Finance Management</h2>
    <p className="text-gray-600">Finance management interface coming in next update...</p>
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
      
      <Route
        path="/teachers"
        element={
          <ProtectedRoute allowedRoles={['admin']}>
            <Layout>
              <Teachers />
            </Layout>
          </ProtectedRoute>
        }
      />
      
      <Route
        path="/students"
        element={
          <ProtectedRoute allowedRoles={['admin', 'teacher']}>
            <Layout>
              <Students />
            </Layout>
          </ProtectedRoute>
        }
      />
      
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
        path="/classes"
        element={
          <ProtectedRoute allowedRoles={['admin', 'teacher']}>
            <Layout>
              <Classes />
            </Layout>
          </ProtectedRoute>
        }
      />
      
      <Route
        path="/subjects"
        element={
          <ProtectedRoute allowedRoles={['admin', 'teacher']}>
            <Layout>
              <Subjects />
            </Layout>
          </ProtectedRoute>
        }
      />
      
      <Route
        path="/attendance"
        element={
          <ProtectedRoute>
            <Layout>
              <Attendance />
            </Layout>
          </ProtectedRoute>
        }
      />
      
      <Route
        path="/exams"
        element={
          <ProtectedRoute>
            <Layout>
              <Exams />
            </Layout>
          </ProtectedRoute>
        }
      />
      
      <Route
        path="/finance"
        element={
          <ProtectedRoute allowedRoles={['admin', 'accountant', 'parent']}>
            <Layout>
              <Finance />
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
      </BrowserRouter>
    </AuthProvider>
  );
}

export default App;
