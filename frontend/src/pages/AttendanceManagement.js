import React, { useState, useEffect } from 'react';
import { useAuth } from '../context/AuthContext';
import { Button } from '../components/ui/button';
import { Label } from '../components/ui/label';
import { Card, CardContent, CardHeader, CardTitle } from '../components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '../components/ui/select';
import { Badge } from '../components/ui/badge';
import { Calendar } from '../components/ui/calendar';
import { Popover, PopoverContent, PopoverTrigger } from '../components/ui/popover';
import { CalendarIcon, CheckIcon, XMarkIcon } from '@heroicons/react/24/outline';
import { useToast } from '../hooks/use-toast';
import { format } from 'date-fns';

const BACKEND_URL = process.env.REACT_APP_BACKEND_URL;

const AttendanceManagement = () => {
  const { token } = useAuth();
  const { toast } = useToast();
  const [classes, setClasses] = useState([]);
  const [sections, setSections] = useState([]);
  const [students, setStudents] = useState([]);
  const [attendanceRecords, setAttendanceRecords] = useState({});
  const [loading, setLoading] = useState(true);
  const [selectedClass, setSelectedClass] = useState('');
  const [selectedSection, setSelectedSection] = useState('');
  const [selectedDate, setSelectedDate] = useState(new Date());
  const [stats, setStats] = useState(null);

  useEffect(() => {
    fetchData();
  }, []);

  useEffect(() => {
    if (selectedClass && selectedSection) {
      fetchStudents();
      fetchAttendance();
      fetchStats();
    }
  }, [selectedClass, selectedSection, selectedDate]);

  const fetchData = async () => {
    try {
      const [classesRes, sectionsRes] = await Promise.all([
        fetch(`${BACKEND_URL}/api/classes`, { headers: { Authorization: `Bearer ${token}` } }),
        fetch(`${BACKEND_URL}/api/sections`, { headers: { Authorization: `Bearer ${token}` } })
      ]);
      if (classesRes.ok) setClasses(await classesRes.json());
      if (sectionsRes.ok) setSections(await sectionsRes.json());
    } catch (error) {
      toast({ title: 'Error', description: 'Failed to fetch data', variant: 'destructive' });
    } finally {
      setLoading(false);
    }
  };

  const fetchStudents = async () => {
    try {
      const response = await fetch(
        `${BACKEND_URL}/api/students?class_id=${selectedClass}&section_id=${selectedSection}`,
        { headers: { Authorization: `Bearer ${token}` } }
      );
      if (response.ok) {
        const data = await response.json();
        setStudents(data);
        
        // Initialize attendance records
        const records = {};
        data.forEach(student => {
          records[student.id] = 'present';
        });
        setAttendanceRecords(records);
      }
    } catch (error) {
      toast({ title: 'Error', description: 'Failed to fetch students', variant: 'destructive' });
    }
  };

  const fetchAttendance = async () => {
    try {
      const dateStr = format(selectedDate, 'yyyy-MM-dd');
      const response = await fetch(
        `${BACKEND_URL}/api/attendance?class_id=${selectedClass}&section_id=${selectedSection}&date=${dateStr}`,
        { headers: { Authorization: `Bearer ${token}` } }
      );
      if (response.ok) {
        const data = await response.json();
        const records = {};
        data.forEach(record => {
          records[record.student_id] = record.status;
        });
        setAttendanceRecords(records);
      }
    } catch (error) {
      console.error('Failed to fetch attendance:', error);
    }
  };

  const fetchStats = async () => {
    try {
      const response = await fetch(
        `${BACKEND_URL}/api/attendance/stats?class_id=${selectedClass}&section_id=${selectedSection}`,
        { headers: { Authorization: `Bearer ${token}` } }
      );
      if (response.ok) {
        const data = await response.json();
        setStats(data);
      }
    } catch (error) {
      console.error('Failed to fetch stats:', error);
    }
  };

  const handleAttendanceChange = (studentId, status) => {
    setAttendanceRecords(prev => ({
      ...prev,
      [studentId]: status
    }));
  };

  const handleSubmitAttendance = async () => {
    try {
      const attendanceData = students.map(student => ({
        student_id: student.id,
        class_id: selectedClass,
        section_id: selectedSection,
        date: format(selectedDate, 'yyyy-MM-dd'),
        status: attendanceRecords[student.id] || 'present'
      }));

      const response = await fetch(`${BACKEND_URL}/api/attendance/bulk`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${token}`
        },
        body: JSON.stringify({ attendance_records: attendanceData })
      });

      if (response.ok) {
        toast({ title: 'Success', description: 'Attendance marked successfully' });
        fetchStats();
      } else {
        const error = await response.json();
        toast({ title: 'Error', description: error.detail || 'Failed to mark attendance', variant: 'destructive' });
      }
    } catch (error) {
      toast({ title: 'Error', description: 'Failed to mark attendance', variant: 'destructive' });
    }
  };

  if (loading) {
    return <div className="flex justify-center items-center h-64">Loading...</div>;
  }

  return (
    <div className="space-y-6">
      <div>
        <h2 className="text-3xl font-bold text-gray-900">Attendance Management</h2>
        <p className="text-gray-600 mt-1">Mark and track student attendance</p>
      </div>

      <div className="grid md:grid-cols-3 gap-4">
        <Card>
          <CardHeader>
            <CardTitle className="text-sm">Select Class</CardTitle>
          </CardHeader>
          <CardContent>
            <Select value={selectedClass} onValueChange={setSelectedClass}>
              <SelectTrigger>
                <SelectValue placeholder="Select class" />
              </SelectTrigger>
              <SelectContent>
                {classes.map((cls) => (
                  <SelectItem key={cls.id} value={cls.id}>{cls.name}</SelectItem>
                ))}
              </SelectContent>
            </Select>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle className="text-sm">Select Section</CardTitle>
          </CardHeader>
          <CardContent>
            <Select value={selectedSection} onValueChange={setSelectedSection} disabled={!selectedClass}>
              <SelectTrigger>
                <SelectValue placeholder="Select section" />
              </SelectTrigger>
              <SelectContent>
                {sections.filter(s => s.class_id === selectedClass).map((section) => (
                  <SelectItem key={section.id} value={section.id}>{section.name}</SelectItem>
                ))}
              </SelectContent>
            </Select>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle className="text-sm">Select Date</CardTitle>
          </CardHeader>
          <CardContent>
            <Popover>
              <PopoverTrigger asChild>
                <Button variant="outline" className="w-full justify-start text-left font-normal">
                  <CalendarIcon className="mr-2 h-4 w-4" />
                  {format(selectedDate, 'PPP')}
                </Button>
              </PopoverTrigger>
              <PopoverContent className="w-auto p-0" align="start">
                <Calendar
                  mode="single"
                  selected={selectedDate}
                  onSelect={setSelectedDate}
                  initialFocus
                />
              </PopoverContent>
            </Popover>
          </CardContent>
        </Card>
      </div>

      {stats && (
        <div className="grid md:grid-cols-3 gap-4">
          <Card>
            <CardContent className="pt-6">
              <div className="text-2xl font-bold text-green-600">{stats.present_count}</div>
              <p className="text-sm text-gray-600">Present Today</p>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="pt-6">
              <div className="text-2xl font-bold text-red-600">{stats.absent_count}</div>
              <p className="text-sm text-gray-600">Absent Today</p>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="pt-6">
              <div className="text-2xl font-bold text-blue-600">{stats.attendance_percentage.toFixed(1)}%</div>
              <p className="text-sm text-gray-600">Attendance Rate</p>
            </CardContent>
          </Card>
        </div>
      )}

      {selectedClass && selectedSection && (
        <Card>
          <CardHeader>
            <CardTitle>Mark Attendance - {format(selectedDate, 'MMMM dd, yyyy')}</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="space-y-3">
              {students.map((student) => (
                <div key={student.id} className="flex items-center justify-between p-4 border rounded-lg hover:bg-gray-50">
                  <div className="flex items-center space-x-4">
                    <div className="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                      <span className="text-blue-600 font-semibold">{student.name.charAt(0)}</span>
                    </div>
                    <div>
                      <p className="font-medium">{student.name}</p>
                      <p className="text-sm text-gray-500">{student.admission_number}</p>
                    </div>
                  </div>
                  <div className="flex gap-2">
                    <Button
                      variant={attendanceRecords[student.id] === 'present' ? 'default' : 'outline'}
                      size="sm"
                      onClick={() => handleAttendanceChange(student.id, 'present')}
                      className="flex items-center gap-1"
                    >
                      <CheckIcon className="w-4 h-4" />
                      Present
                    </Button>
                    <Button
                      variant={attendanceRecords[student.id] === 'absent' ? 'destructive' : 'outline'}
                      size="sm"
                      onClick={() => handleAttendanceChange(student.id, 'absent')}
                      className="flex items-center gap-1"
                    >
                      <XMarkIcon className="w-4 h-4" />
                      Absent
                    </Button>
                    <Button
                      variant={attendanceRecords[student.id] === 'late' ? 'default' : 'outline'}
                      size="sm"
                      onClick={() => handleAttendanceChange(student.id, 'late')}
                    >
                      Late
                    </Button>
                  </div>
                </div>
              ))}
            </div>
            {students.length > 0 && (
              <Button onClick={handleSubmitAttendance} className="w-full mt-4">
                Submit Attendance
              </Button>
            )}
            {students.length === 0 && (
              <div className="text-center py-8 text-gray-500">
                No students found in this class/section
              </div>
            )}
          </CardContent>
        </Card>
      )}
    </div>
  );
};

export default AttendanceManagement;
