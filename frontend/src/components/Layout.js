import React, { useState } from 'react';
import { Link, useNavigate, useLocation } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import {
  HomeIcon,
  UsersIcon,
  AcademicCapIcon,
  BookOpenIcon,
  ClipboardDocumentCheckIcon,
  CalendarIcon,
  CurrencyDollarIcon,
  ChartBarIcon,
  Cog6ToothIcon,
  ArrowRightOnRectangleIcon,
  Bars3Icon,
  XMarkIcon,
} from '@heroicons/react/24/outline';

const Layout = ({ children }) => {
  const { user, logout } = useAuth();
  const navigate = useNavigate();
  const location = useLocation();
  const [sidebarOpen, setSidebarOpen] = useState(false);

  const handleLogout = () => {
    logout();
    navigate('/login');
  };

  const getMenuItems = () => {
    const commonItems = [
      { name: 'Dashboard', path: '/dashboard', icon: HomeIcon },
    ];

    if (user?.role === 'admin') {
      return [
        ...commonItems,
        { name: 'Users', path: '/users', icon: UsersIcon },
        { name: 'School Years', path: '/school-years', icon: CalendarIcon },
        { name: 'Classes', path: '/classes', icon: BookOpenIcon },
        { name: 'Subjects', path: '/subjects', icon: BookOpenIcon },
        { name: 'Teachers', path: '/teachers', icon: AcademicCapIcon },
        { name: 'Students', path: '/students', icon: UsersIcon },
        { name: 'Parents', path: '/parents', icon: UsersIcon },
        { name: 'Timetable', path: '/timetable', icon: CalendarIcon },
        { name: 'Attendance', path: '/attendance', icon: ClipboardDocumentCheckIcon },
        { name: 'Exams', path: '/exams', icon: CalendarIcon },
        { name: 'Finance', path: '/finance', icon: CurrencyDollarIcon },
        { name: 'Reports', path: '/reports', icon: ChartBarIcon },
        { name: 'Settings', path: '/settings', icon: Cog6ToothIcon },
      ];
    } else if (user?.role === 'teacher') {
      return [
        ...commonItems,
        { name: 'My Classes', path: '/my-classes', icon: BookOpenIcon },
        { name: 'Students', path: '/students', icon: UsersIcon },
        { name: 'Timetable', path: '/timetable', icon: CalendarIcon },
        { name: 'Attendance', path: '/attendance', icon: ClipboardDocumentCheckIcon },
        { name: 'Exams', path: '/exams', icon: CalendarIcon },
        { name: 'Assignments', path: '/assignments', icon: BookOpenIcon },
      ];
    } else if (user?.role === 'student') {
      return [
        ...commonItems,
        { name: 'My Classes', path: '/my-classes', icon: BookOpenIcon },
        { name: 'Attendance', path: '/attendance', icon: ClipboardDocumentCheckIcon },
        { name: 'Exams', path: '/exams', icon: CalendarIcon },
        { name: 'Assignments', path: '/assignments', icon: BookOpenIcon },
        { name: 'Grades', path: '/grades', icon: ChartBarIcon },
      ];
    } else if (user?.role === 'parent') {
      return [
        ...commonItems,
        { name: 'My Children', path: '/my-children', icon: UsersIcon },
        { name: 'Attendance', path: '/attendance', icon: ClipboardDocumentCheckIcon },
        { name: 'Exams', path: '/exams', icon: CalendarIcon },
        { name: 'Grades', path: '/grades', icon: ChartBarIcon },
        { name: 'Finance', path: '/finance', icon: CurrencyDollarIcon },
      ];
    }

    return commonItems;
  };

  const menuItems = getMenuItems();

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Mobile sidebar backdrop */}
      {sidebarOpen && (
        <div
          className="fixed inset-0 z-40 bg-gray-600 bg-opacity-75 lg:hidden"
          onClick={() => setSidebarOpen(false)}
        />
      )}

      {/* Sidebar */}
      <div
        className={`
          fixed inset-y-0 left-0 z-50 w-64 bg-white shadow-lg transform transition-transform duration-300 ease-in-out
          lg:translate-x-0 lg:static lg:inset-0
          ${sidebarOpen ? 'translate-x-0' : '-translate-x-full'}
        `}
      >
        <div className="flex flex-col h-full">
          {/* Logo */}
          <div className="flex items-center justify-between h-16 px-4 bg-blue-600">
            <span className="text-xl font-bold text-white">School MS</span>
            <button
              onClick={() => setSidebarOpen(false)}
              className="lg:hidden text-white hover:text-gray-200"
            >
              <XMarkIcon className="w-6 h-6" />
            </button>
          </div>

          {/* User info */}
          <div className="p-4 border-b">
            <div className="flex items-center space-x-3">
              <div className="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                <span className="text-blue-600 font-semibold">
                  {user?.name?.charAt(0).toUpperCase()}
                </span>
              </div>
              <div className="flex-1 min-w-0">
                <p className="text-sm font-medium text-gray-900 truncate">{user?.name}</p>
                <p className="text-xs text-gray-500 capitalize">{user?.role}</p>
              </div>
            </div>
          </div>

          {/* Navigation */}
          <nav className="flex-1 px-2 py-4 space-y-1 overflow-y-auto">
            {menuItems.map((item) => {
              const isActive = location.pathname === item.path;
              return (
                <Link
                  key={item.path}
                  to={item.path}
                  onClick={() => setSidebarOpen(false)}
                  className={`
                    flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors
                    ${isActive
                      ? 'bg-blue-50 text-blue-700'
                      : 'text-gray-700 hover:bg-gray-50 hover:text-blue-600'
                    }
                  `}
                >
                  <item.icon className="w-5 h-5 mr-3" />
                  {item.name}
                </Link>
              );
            })}
          </nav>

          {/* Logout button */}
          <div className="p-4 border-t">
            <button
              onClick={handleLogout}
              className="flex items-center w-full px-4 py-3 text-sm font-medium text-red-600 rounded-lg hover:bg-red-50 transition-colors"
            >
              <ArrowRightOnRectangleIcon className="w-5 h-5 mr-3" />
              Logout
            </button>
          </div>
        </div>
      </div>

      {/* Main content */}
      <div className="lg:pl-64">
        {/* Top bar */}
        <div className="sticky top-0 z-10 flex items-center h-16 px-4 bg-white shadow-sm lg:px-8">
          <button
            onClick={() => setSidebarOpen(true)}
            className="lg:hidden text-gray-500 hover:text-gray-700"
          >
            <Bars3Icon className="w-6 h-6" />
          </button>
          <div className="flex-1" />
          <div className="flex items-center space-x-4">
            <span className="text-sm text-gray-600">Welcome, {user?.name}</span>
          </div>
        </div>

        {/* Page content */}
        <main className="p-4 lg:p-8">{children}</main>
      </div>
    </div>
  );
};

export default Layout;
