import React, { useEffect, useState } from 'react';
import { useAuth } from '../context/AuthContext';
import axios from 'axios';
import {
  UsersIcon,
  AcademicCapIcon,
  BookOpenIcon,
  ChartBarIcon,
} from '@heroicons/react/24/outline';

const API_URL = process.env.REACT_APP_BACKEND_URL + '/api';

const Dashboard = () => {
  const { user } = useAuth();
  const [stats, setStats] = useState({});
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetchStats();
  }, []);

  const fetchStats = async () => {
    try {
      const response = await axios.get(`${API_URL}/dashboard/stats`);
      setStats(response.data);
    } catch (error) {
      console.error('Failed to fetch stats:', error);
    } finally {
      setLoading(false);
    }
  };

  const getAdminStats = () => [
    {
      name: 'Total Students',
      value: stats.total_students || 0,
      icon: UsersIcon,
      color: 'bg-blue-500',
    },
    {
      name: 'Total Teachers',
      value: stats.total_teachers || 0,
      icon: AcademicCapIcon,
      color: 'bg-green-500',
    },
    {
      name: 'Total Classes',
      value: stats.total_classes || 0,
      icon: BookOpenIcon,
      color: 'bg-purple-500',
    },
    {
      name: 'Total Subjects',
      value: stats.total_subjects || 0,
      icon: ChartBarIcon,
      color: 'bg-orange-500',
    },
  ];

  return (
    <div data-testid="dashboard">
      <div className="mb-8">
        <h1 className="text-3xl font-bold text-gray-900">Dashboard</h1>
        <p className="text-gray-600 mt-2">
          Welcome back, {user?.name}! Here's what's happening today.
        </p>
      </div>

      {loading ? (
        <div className="flex items-center justify-center h-64">
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
        </div>
      ) : (
        <div>
          {user?.role === 'admin' && (
            <div>
              <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                {getAdminStats().map((stat, index) => (
                  <div
                    key={index}
                    className="bg-white rounded-xl shadow-sm p-6 border border-gray-100"
                  >
                    <div className="flex items-center justify-between">
                      <div>
                        <p className="text-gray-600 text-sm font-medium">{stat.name}</p>
                        <p className="text-3xl font-bold text-gray-900 mt-2">{stat.value}</p>
                      </div>
                      <div className={`${stat.color} p-3 rounded-lg`}>
                        <stat.icon className="w-6 h-6 text-white" />
                      </div>
                    </div>
                  </div>
                ))}
              </div>

              <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div className="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                  <h3 className="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                  <div className="space-y-3">
                    <a
                      href="/students"
                      className="block p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition"
                    >
                      <p className="font-medium text-blue-900">Add New Student</p>
                      <p className="text-sm text-blue-600">Enroll a new student in the system</p>
                    </a>
                    <a
                      href="/teachers"
                      className="block p-4 bg-green-50 rounded-lg hover:bg-green-100 transition"
                    >
                      <p className="font-medium text-green-900">Add New Teacher</p>
                      <p className="text-sm text-green-600">Register a new teacher</p>
                    </a>
                    <a
                      href="/classes"
                      className="block p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition"
                    >
                      <p className="font-medium text-purple-900">Manage Classes</p>
                      <p className="text-sm text-purple-600">View and organize classes</p>
                    </a>
                  </div>
                </div>

                <div className="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                  <h3 className="text-lg font-semibold text-gray-900 mb-4">Recent Activity</h3>
                  <div className="space-y-4">
                    <p className="text-gray-600 text-center py-8">No recent activity</p>
                  </div>
                </div>
              </div>
            </div>
          )}

          {user?.role === 'teacher' && (
            <div className="space-y-6">
              <div className="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <h3 className="text-lg font-semibold text-gray-900 mb-4">My Overview</h3>
                <div className="grid grid-cols-2 gap-4">
                  <div className="p-4 bg-blue-50 rounded-lg">
                    <p className="text-sm text-blue-600 font-medium">My Classes</p>
                    <p className="text-2xl font-bold text-blue-900 mt-1">
                      {stats.my_classes || 0}
                    </p>
                  </div>
                  <div className="p-4 bg-green-50 rounded-lg">
                    <p className="text-sm text-green-600 font-medium">My Subjects</p>
                    <p className="text-2xl font-bold text-green-900 mt-1">
                      {stats.my_subjects || 0}
                    </p>
                  </div>
                </div>
              </div>

              <div className="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <h3 className="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                <div className="space-y-3">
                  <a
                    href="/attendance"
                    className="block p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition"
                  >
                    <p className="font-medium text-blue-900">Mark Attendance</p>
                  </a>
                  <a
                    href="/students"
                    className="block p-4 bg-green-50 rounded-lg hover:bg-green-100 transition"
                  >
                    <p className="font-medium text-green-900">View Students</p>
                  </a>
                </div>
              </div>
            </div>
          )}

          {user?.role === 'student' && (
            <div className="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
              <h3 className="text-lg font-semibold text-gray-900 mb-4">My Profile</h3>
              <p className="text-gray-600">Welcome to your student dashboard!</p>
            </div>
          )}

          {user?.role === 'parent' && (
            <div className="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
              <h3 className="text-lg font-semibold text-gray-900 mb-4">My Children</h3>
              <p className="text-gray-600">
                Number of children: {stats.my_children || 0}
              </p>
            </div>
          )}
        </div>
      )}
    </div>
  );
};

export default Dashboard;
