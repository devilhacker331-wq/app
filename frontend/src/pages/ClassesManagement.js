import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { useAuth } from '../context/AuthContext';
import { PlusIcon } from '@heroicons/react/24/outline';

const API_URL = process.env.REACT_APP_BACKEND_URL || '';

const ClassesManagement = () => {
  const { token } = useAuth();
  const [classes, setClasses] = useState([]);
  const [sections, setSections] = useState([]);
  const [schoolYears, setSchoolYears] = useState([]);
  const [loading, setLoading] = useState(true);
  const [showClassModal, setShowClassModal] = useState(false);
  const [showSectionModal, setShowSectionModal] = useState(false);
  const [classFormData, setClassFormData] = useState({
    name: '',
    numeric: '',
    school_year_id: '',
    sections: []
  });
  const [sectionFormData, setSectionFormData] = useState({
    name: '',
    capacity: ''
  });

  useEffect(() => {
    fetchData();
  }, []);

  const fetchData = async () => {
    try {
      const [classesRes, sectionsRes, yearsRes] = await Promise.all([
        axios.get(`${API_URL}/api/classes`, { headers: { Authorization: `Bearer ${token}` } }),
        axios.get(`${API_URL}/api/sections`, { headers: { Authorization: `Bearer ${token}` } }),
        axios.get(`${API_URL}/api/school-years`, { headers: { Authorization: `Bearer ${token}` } })
      ]);
      setClasses(classesRes.data);
      setSections(sectionsRes.data);
      setSchoolYears(yearsRes.data);
    } catch (error) {
      console.error('Error fetching data:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleCreateClass = async (e) => {
    e.preventDefault();
    try {
      await axios.post(`${API_URL}/api/classes`, classFormData, {
        headers: { Authorization: `Bearer ${token}` }
      });
      setShowClassModal(false);
      setClassFormData({ name: '', numeric: '', school_year_id: '', sections: [] });
      fetchData();
    } catch (error) {
      console.error('Error creating class:', error);
      alert('Error creating class');
    }
  };

  const handleCreateSection = async (e) => {
    e.preventDefault();
    try {
      await axios.post(`${API_URL}/api/sections`, sectionFormData, {
        headers: { Authorization: `Bearer ${token}` }
      });
      setShowSectionModal(false);
      setSectionFormData({ name: '', capacity: '' });
      fetchData();
    } catch (error) {
      console.error('Error creating section:', error);
      alert('Error creating section');
    }
  };

  if (loading) {
    return <div className="flex items-center justify-center h-64">Loading...</div>;
  }

  return (
    <div className="space-y-6">
      {/* Sections */}
      <div className="bg-white rounded-xl shadow-sm p-6">
        <div className="flex justify-between items-center mb-6">
          <h2 className="text-2xl font-bold text-gray-900">Sections</h2>
          <button
            onClick={() => setShowSectionModal(true)}
            className="flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
          >
            <PlusIcon className="w-5 h-5 mr-2" />
            Add Section
          </button>
        </div>
        <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
          {sections.map((section) => (
            <div key={section.id} className="border rounded-lg p-3 text-center hover:shadow-md transition-shadow">
              <h3 className="text-lg font-semibold">Section {section.name}</h3>
              {section.capacity && (
                <p className="text-sm text-gray-600">Capacity: {section.capacity}</p>
              )}
            </div>
          ))}
        </div>
      </div>

      {/* Classes */}
      <div className="bg-white rounded-xl shadow-sm p-6">
        <div className="flex justify-between items-center mb-6">
          <h2 className="text-2xl font-bold text-gray-900">Classes</h2>
          <button
            onClick={() => setShowClassModal(true)}
            className="flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700"
          >
            <PlusIcon className="w-5 h-5 mr-2" />
            Add Class
          </button>
        </div>
        <div className="overflow-x-auto">
          <table className="min-w-full divide-y divide-gray-200">
            <thead>
              <tr>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Class Name</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Numeric</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">School Year</th>
              </tr>
            </thead>
            <tbody className="bg-white divide-y divide-gray-200">
              {classes.map((cls) => (
                <tr key={cls.id} className="hover:bg-gray-50">
                  <td className="px-6 py-4 whitespace-nowrap font-medium">{cls.name}</td>
                  <td className="px-6 py-4 whitespace-nowrap">{cls.numeric}</td>
                  <td className="px-6 py-4 whitespace-nowrap">
                    {schoolYears.find(y => y.id === cls.school_year_id)?.year || 'N/A'}
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>

      {/* Section Modal */}
      {showSectionModal && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
          <div className="bg-white rounded-xl p-6 w-full max-w-md">
            <h3 className="text-xl font-bold mb-4">Add Section</h3>
            <form onSubmit={handleCreateSection}>
              <div className="space-y-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Section Name</label>
                  <input
                    type="text"
                    placeholder="A"
                    value={sectionFormData.name}
                    onChange={(e) => setSectionFormData({ ...sectionFormData, name: e.target.value })}
                    className="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
                    required
                  />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Capacity (Optional)</label>
                  <input
                    type="number"
                    placeholder="30"
                    value={sectionFormData.capacity}
                    onChange={(e) => setSectionFormData({ ...sectionFormData, capacity: e.target.value })}
                    className="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
                  />
                </div>
              </div>
              <div className="flex gap-2 mt-6">
                <button
                  type="button"
                  onClick={() => setShowSectionModal(false)}
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

      {/* Class Modal */}
      {showClassModal && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
          <div className="bg-white rounded-xl p-6 w-full max-w-md">
            <h3 className="text-xl font-bold mb-4">Add Class</h3>
            <form onSubmit={handleCreateClass}>
              <div className="space-y-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Class Name</label>
                  <input
                    type="text"
                    placeholder="Class 1"
                    value={classFormData.name}
                    onChange={(e) => setClassFormData({ ...classFormData, name: e.target.value })}
                    className="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
                    required
                  />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Numeric (1-12)</label>
                  <input
                    type="number"
                    min="1"
                    max="12"
                    value={classFormData.numeric}
                    onChange={(e) => setClassFormData({ ...classFormData, numeric: e.target.value })}
                    className="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
                    required
                  />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">School Year</label>
                  <select
                    value={classFormData.school_year_id}
                    onChange={(e) => setClassFormData({ ...classFormData, school_year_id: e.target.value })}
                    className="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
                    required
                  >
                    <option value="">Select School Year</option>
                    {schoolYears.map((year) => (
                      <option key={year.id} value={year.id}>{year.year}</option>
                    ))}
                  </select>
                </div>
              </div>
              <div className="flex gap-2 mt-6">
                <button
                  type="button"
                  onClick={() => setShowClassModal(false)}
                  className="flex-1 px-4 py-2 border rounded-lg hover:bg-gray-50"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  className="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700"
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

export default ClassesManagement;