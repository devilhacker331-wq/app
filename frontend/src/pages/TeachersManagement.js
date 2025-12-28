import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { useAuth } from '../context/AuthContext';
import { PlusIcon, PhotoIcon } from '@heroicons/react/24/outline';

const API_URL = process.env.REACT_APP_BACKEND_URL || '';

const TeachersManagement = () => {
  const { token } = useAuth();
  const [teachers, setTeachers] = useState([]);
  const [users, setUsers] = useState([]);
  const [loading, setLoading] = useState(true);
  const [showModal, setShowModal] = useState(false);
  const [uploadingPhoto, setUploadingPhoto] = useState(false);
  const [formData, setFormData] = useState({
    user_id: '',
    name: '',
    designation: '',
    qualification: '',
    gender: '',
    dob: '',
    joining_date: '',
    phone: '',
    email: '',
    address: '',
    photo: '',
    salary: ''
  });

  useEffect(() => {
    fetchData();
  }, []);

  const fetchData = async () => {
    try {
      const [teachersRes, usersRes] = await Promise.all([
        axios.get(`${API_URL}/api/teachers`, { headers: { Authorization: `Bearer ${token}` } }),
        axios.get(`${API_URL}/api/users?role=teacher`, { headers: { Authorization: `Bearer ${token}` } })
      ]);
      setTeachers(teachersRes.data);
      setUsers(usersRes.data);
    } catch (error) {
      console.error('Error fetching data:', error);
    } finally {
      setLoading(false);
    }
  };

  const handlePhotoUpload = async (e) => {
    const file = e.target.files[0];
    if (!file) return;

    setUploadingPhoto(true);
    const formData = new FormData();
    formData.append('file', file);

    try {
      const response = await axios.post(`${API_URL}/api/upload`, formData, {
        headers: { 
          Authorization: `Bearer ${token}`,
          'Content-Type': 'multipart/form-data'
        }
      });
      setFormData(prev => ({ ...prev, photo: response.data.url }));
    } catch (error) {
      console.error('Error uploading photo:', error);
      alert('Error uploading photo');
    } finally {
      setUploadingPhoto(false);
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    try {
      await axios.post(`${API_URL}/api/teachers`, formData, {
        headers: { Authorization: `Bearer ${token}` }
      });
      setShowModal(false);
      setFormData({
        user_id: '', name: '', designation: '', qualification: '', gender: '',
        dob: '', joining_date: '', phone: '', email: '', address: '', photo: '', salary: ''
      });
      fetchData();
    } catch (error) {
      console.error('Error creating teacher:', error);
      alert('Error creating teacher');
    }
  };

  if (loading) {
    return <div className="flex items-center justify-center h-64">Loading...</div>;
  }

  return (
    <div className="bg-white rounded-xl shadow-sm p-6">
      <div className="flex justify-between items-center mb-6">
        <h2 className="text-2xl font-bold text-gray-900">Teachers</h2>
        <button
          onClick={() => setShowModal(true)}
          className="flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
        >
          <PlusIcon className="w-5 h-5 mr-2" />
          Add Teacher
        </button>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        {teachers.map((teacher) => (
          <div key={teacher.id} className="border rounded-lg p-4 hover:shadow-lg transition-shadow">
            <div className="flex items-start space-x-4">
              <div className="w-16 h-16 rounded-full bg-gray-200 flex items-center justify-center overflow-hidden">
                {teacher.photo ? (
                  <img src={`${API_URL}${teacher.photo}`} alt={teacher.name} className="w-full h-full object-cover" />
                ) : (
                  <span className="text-2xl text-gray-500">{teacher.name.charAt(0)}</span>
                )}
              </div>
              <div className="flex-1">
                <h3 className="font-semibold text-lg">{teacher.name}</h3>
                <p className="text-sm text-gray-600">{teacher.designation || 'Teacher'}</p>
                <p className="text-sm text-gray-500 mt-1">{teacher.email}</p>
                <p className="text-sm text-gray-500">{teacher.phone}</p>
              </div>
            </div>
            {teacher.qualification && (
              <div className="mt-3 pt-3 border-t">
                <p className="text-sm text-gray-600">
                  <span className="font-medium">Qualification:</span> {teacher.qualification}
                </p>
              </div>
            )}
          </div>
        ))}
      </div>

      {/* Add Modal */}
      {showModal && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 overflow-y-auto">
          <div className="bg-white rounded-xl p-6 w-full max-w-2xl m-4">
            <h3 className="text-xl font-bold mb-4">Add Teacher</h3>
            <form onSubmit={handleSubmit}>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div className="md:col-span-2">
                  <label className="block text-sm font-medium text-gray-700 mb-1">User Account</label>
                  <select
                    value={formData.user_id}
                    onChange={(e) => {
                      const user = users.find(u => u.id === e.target.value);
                      setFormData({ 
                        ...formData, 
                        user_id: e.target.value,
                        name: user?.name || '',
                        email: user?.email || '',
                        phone: user?.phone || ''
                      });
                    }}
                    className="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
                    required
                  >
                    <option value="">Select User</option>
                    {users.map((user) => (
                      <option key={user.id} value={user.id}>{user.name} ({user.username})</option>
                    ))}
                  </select>
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                  <input
                    type="text"
                    value={formData.name}
                    onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                    className="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
                    required
                  />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Designation</label>
                  <input
                    type="text"
                    value={formData.designation}
                    onChange={(e) => setFormData({ ...formData, designation: e.target.value })}
                    className="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
                  />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Qualification</label>
                  <input
                    type="text"
                    value={formData.qualification}
                    onChange={(e) => setFormData({ ...formData, qualification: e.target.value })}
                    className="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
                  />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Gender</label>
                  <select
                    value={formData.gender}
                    onChange={(e) => setFormData({ ...formData, gender: e.target.value })}
                    className="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
                  >
                    <option value="">Select Gender</option>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                    <option value="other">Other</option>
                  </select>
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Date of Birth</label>
                  <input
                    type="date"
                    value={formData.dob}
                    onChange={(e) => setFormData({ ...formData, dob: e.target.value })}
                    className="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
                  />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Joining Date</label>
                  <input
                    type="date"
                    value={formData.joining_date}
                    onChange={(e) => setFormData({ ...formData, joining_date: e.target.value })}
                    className="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
                  />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                  <input
                    type="tel"
                    value={formData.phone}
                    onChange={(e) => setFormData({ ...formData, phone: e.target.value })}
                    className="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
                  />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Email</label>
                  <input
                    type="email"
                    value={formData.email}
                    onChange={(e) => setFormData({ ...formData, email: e.target.value })}
                    className="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
                  />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Salary</label>
                  <input
                    type="number"
                    value={formData.salary}
                    onChange={(e) => setFormData({ ...formData, salary: e.target.value })}
                    className="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
                  />
                </div>
                <div className="md:col-span-2">
                  <label className="block text-sm font-medium text-gray-700 mb-1">Address</label>
                  <textarea
                    value={formData.address}
                    onChange={(e) => setFormData({ ...formData, address: e.target.value })}
                    className="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
                    rows="2"
                  />
                </div>
                <div className="md:col-span-2">
                  <label className="block text-sm font-medium text-gray-700 mb-1">Photo</label>
                  <div className="flex items-center space-x-4">
                    <input
                      type="file"
                      accept="image/*"
                      onChange={handlePhotoUpload}
                      className="hidden"
                      id="photo-upload"
                    />
                    <label
                      htmlFor="photo-upload"
                      className="flex items-center px-4 py-2 bg-gray-100 rounded-lg cursor-pointer hover:bg-gray-200"
                    >
                      <PhotoIcon className="w-5 h-5 mr-2" />
                      {uploadingPhoto ? 'Uploading...' : 'Upload Photo'}
                    </label>
                    {formData.photo && (
                      <img src={`${API_URL}${formData.photo}`} alt="Preview" className="w-16 h-16 rounded-lg object-cover" />
                    )}
                  </div>
                </div>
              </div>
              <div className="flex gap-2 mt-6">
                <button
                  type="button"
                  onClick={() => setShowModal(false)}
                  className="flex-1 px-4 py-2 border rounded-lg hover:bg-gray-50"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  className="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                >
                  Create
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
};

export default TeachersManagement;
