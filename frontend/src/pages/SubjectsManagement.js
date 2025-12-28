import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { useAuth } from '../context/AuthContext';
import { PlusIcon } from '@heroicons/react/24/outline';

const API_URL = process.env.REACT_APP_BACKEND_URL || '';

const SubjectsManagement = () => {
  const { token } = useAuth();
  const [subjects, setSubjects] = useState([]);
  const [classes, setClasses] = useState([]);
  const [teachers, setTeachers] = useState([]);
  const [loading, setLoading] = useState(true);
  const [showModal, setShowModal] = useState(false);
  const [selectedClass, setSelectedClass] = useState('');
  const [formData, setFormData] = useState({
    name: '',
    code: '',
    class_id: '',
    teacher_id: '',
    type: 'mandatory'
  });

  useEffect(() => {
    fetchData();
  }, []);

  const fetchData = async () => {
    try {
      const [subjectsRes, classesRes, teachersRes] = await Promise.all([
        axios.get(`${API_URL}/api/subjects`, { headers: { Authorization: `Bearer ${token}` } }),
        axios.get(`${API_URL}/api/classes`, { headers: { Authorization: `Bearer ${token}` } }),
        axios.get(`${API_URL}/api/teachers`, { headers: { Authorization: `Bearer ${token}` } })
      ]);
      setSubjects(subjectsRes.data);
      setClasses(classesRes.data);
      setTeachers(teachersRes.data);
    } catch (error) {
      console.error('Error fetching data:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    try {
      await axios.post(`${API_URL}/api/subjects`, formData, {
        headers: { Authorization: `Bearer ${token}` }
      });
      setShowModal(false);
      setFormData({ name: '', code: '', class_id: '', teacher_id: '', type: 'mandatory' });
      fetchData();
    } catch (error) {
      console.error('Error creating subject:', error);
      alert('Error creating subject');
    }
  };

  const filteredSubjects = selectedClass 
    ? subjects.filter(s => s.class_id === selectedClass)
    : subjects;

  if (loading) {
    return <div className="flex items-center justify-center h-64">Loading...</div>;
  }

  return (
    <div className="bg-white rounded-xl shadow-sm p-6">
      <div className="flex justify-between items-center mb-6">
        <h2 className="text-2xl font-bold text-gray-900">Subjects</h2>
        <button
          onClick={() => setShowModal(true)}
          className="flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
        >
          <PlusIcon className="w-5 h-5 mr-2" />
          Add Subject
        </button>
      </div>

      <div className="mb-4">
        <label className="block text-sm font-medium text-gray-700 mb-2">Filter by Class</label>
        <select
          value={selectedClass}
          onChange={(e) => setSelectedClass(e.target.value)}
          className="px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
        >
          <option value="">All Classes</option>
          {classes.map((cls) => (
            <option key={cls.id} value={cls.id}>{cls.name}</option>
          ))}
        </select>
      </div>

      <div className="overflow-x-auto">
        <table className="min-w-full divide-y divide-gray-200">
          <thead>
            <tr>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subject Name</th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Class</th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Teacher</th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
            </tr>
          </thead>
          <tbody className="bg-white divide-y divide-gray-200">
            {filteredSubjects.map((subject) => (
              <tr key={subject.id} className="hover:bg-gray-50">
                <td className="px-6 py-4 whitespace-nowrap font-medium">{subject.name}</td>
                <td className="px-6 py-4 whitespace-nowrap">{subject.code}</td>
                <td className="px-6 py-4 whitespace-nowrap">
                  {classes.find(c => c.id === subject.class_id)?.name || 'N/A'}
                </td>
                <td className="px-6 py-4 whitespace-nowrap">
                  {teachers.find(t => t.id === subject.teacher_id)?.name || 'Not Assigned'}
                </td>
                <td className="px-6 py-4 whitespace-nowrap">
                  <span className={`px-2 py-1 text-xs rounded-full ${
                    subject.type === 'mandatory' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800'
                  }`}>
                    {subject.type}
                  </span>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>

      {/* Add Modal */}
      {showModal && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
          <div className="bg-white rounded-xl p-6 w-full max-w-md">
            <h3 className="text-xl font-bold mb-4">Add Subject</h3>
            <form onSubmit={handleSubmit}>
              <div className="space-y-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Subject Name</label>
                  <input
                    type="text"
                    value={formData.name}
                    onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                    className="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
                    required
                  />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Subject Code</label>
                  <input
                    type="text"
                    value={formData.code}
                    onChange={(e) => setFormData({ ...formData, code: e.target.value })}
                    className="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
                    required
                  />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Class</label>
                  <select
                    value={formData.class_id}
                    onChange={(e) => setFormData({ ...formData, class_id: e.target.value })}
                    className="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
                    required
                  >
                    <option value="">Select Class</option>
                    {classes.map((cls) => (
                      <option key={cls.id} value={cls.id}>{cls.name}</option>
                    ))}
                  </select>
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Teacher (Optional)</label>
                  <select
                    value={formData.teacher_id}
                    onChange={(e) => setFormData({ ...formData, teacher_id: e.target.value })}
                    className="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
                  >
                    <option value="">Select Teacher</option>
                    {teachers.map((teacher) => (
                      <option key={teacher.id} value={teacher.id}>{teacher.name}</option>
                    ))}
                  </select>
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Type</label>
                  <select
                    value={formData.type}
                    onChange={(e) => setFormData({ ...formData, type: e.target.value })}
                    className="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
                  >
                    <option value="mandatory">Mandatory</option>
                    <option value="optional">Optional</option>
                  </select>
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

export default SubjectsManagement;