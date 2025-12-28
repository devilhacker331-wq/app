import React, { useState, useEffect } from 'react';
import { useAuth } from '../context/AuthContext';
import { Button } from '../components/ui/button';
import { Input } from '../components/ui/input';
import { Label } from '../components/ui/label';
import { Card, CardContent, CardHeader, CardTitle } from '../components/ui/card';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from '../components/ui/dialog';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../components/ui/table';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '../components/ui/tabs';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '../components/ui/select';
import { Textarea } from '../components/ui/textarea';
import { Badge } from '../components/ui/badge';
import { PlusIcon, DocumentTextIcon } from '@heroicons/react/24/outline';
import { useToast } from '../hooks/use-toast';

const BACKEND_URL = process.env.REACT_APP_BACKEND_URL;

const ExamsManagement = () => {
  const { token } = useAuth();
  const { toast } = useToast();
  const [examTypes, setExamTypes] = useState([]);
  const [examSchedules, setExamSchedules] = useState([]);
  const [marks, setMarks] = useState([]);
  const [gradeRules, setGradeRules] = useState([]);
  const [classes, setClasses] = useState([]);
  const [subjects, setSubjects] = useState([]);
  const [students, setStudents] = useState([]);
  const [loading, setLoading] = useState(true);
  
  const [examTypeDialogOpen, setExamTypeDialogOpen] = useState(false);
  const [examScheduleDialogOpen, setExamScheduleDialogOpen] = useState(false);
  const [marksDialogOpen, setMarksDialogOpen] = useState(false);
  const [gradeRuleDialogOpen, setGradeRuleDialogOpen] = useState(false);
  const [reportCardDialogOpen, setReportCardDialogOpen] = useState(false);
  
  const [examTypeFormData, setExamTypeFormData] = useState({ name: '', description: '' });
  const [examScheduleFormData, setExamScheduleFormData] = useState({
    exam_type_id: '',
    class_id: '',
    subject_id: '',
    exam_date: '',
    start_time: '',
    end_time: '',
    total_marks: '',
    passing_marks: ''
  });
  const [marksFormData, setMarksFormData] = useState({
    exam_schedule_id: '',
    student_id: '',
    marks_obtained: ''
  });
  const [gradeRuleFormData, setGradeRuleFormData] = useState({
    grade: '',
    min_percentage: '',
    max_percentage: '',
    description: ''
  });
  const [selectedStudentId, setSelectedStudentId] = useState('');
  const [reportCard, setReportCard] = useState(null);

  useEffect(() => {
    fetchData();
  }, []);

  const fetchData = async () => {
    try {
      const [examTypesRes, examSchedulesRes, marksRes, gradeRulesRes, classesRes, subjectsRes, studentsRes] = await Promise.all([
        fetch(`${BACKEND_URL}/api/exam-types`, { headers: { Authorization: `Bearer ${token}` } }),
        fetch(`${BACKEND_URL}/api/exam-schedules`, { headers: { Authorization: `Bearer ${token}` } }),
        fetch(`${BACKEND_URL}/api/marks`, { headers: { Authorization: `Bearer ${token}` } }),
        fetch(`${BACKEND_URL}/api/grade-rules`, { headers: { Authorization: `Bearer ${token}` } }),
        fetch(`${BACKEND_URL}/api/classes`, { headers: { Authorization: `Bearer ${token}` } }),
        fetch(`${BACKEND_URL}/api/subjects`, { headers: { Authorization: `Bearer ${token}` } }),
        fetch(`${BACKEND_URL}/api/students`, { headers: { Authorization: `Bearer ${token}` } })
      ]);
      
      if (examTypesRes.ok) setExamTypes(await examTypesRes.json());
      if (examSchedulesRes.ok) setExamSchedules(await examSchedulesRes.json());
      if (marksRes.ok) setMarks(await marksRes.json());
      if (gradeRulesRes.ok) setGradeRules(await gradeRulesRes.json());
      if (classesRes.ok) setClasses(await classesRes.json());
      if (subjectsRes.ok) setSubjects(await subjectsRes.json());
      if (studentsRes.ok) setStudents(await studentsRes.json());
    } catch (error) {
      toast({ title: 'Error', description: 'Failed to fetch data', variant: 'destructive' });
    } finally {
      setLoading(false);
    }
  };

  const handleExamTypeSubmit = async (e) => {
    e.preventDefault();
    try {
      const response = await fetch(`${BACKEND_URL}/api/exam-types`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${token}`
        },
        body: JSON.stringify(examTypeFormData)
      });

      if (response.ok) {
        toast({ title: 'Success', description: 'Exam type created successfully' });
        setExamTypeDialogOpen(false);
        setExamTypeFormData({ name: '', description: '' });
        fetchData();
      } else {
        const error = await response.json();
        toast({ title: 'Error', description: error.detail || 'Failed to create exam type', variant: 'destructive' });
      }
    } catch (error) {
      toast({ title: 'Error', description: 'Failed to create exam type', variant: 'destructive' });
    }
  };

  const handleExamScheduleSubmit = async (e) => {
    e.preventDefault();
    try {
      const response = await fetch(`${BACKEND_URL}/api/exam-schedules`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${token}`
        },
        body: JSON.stringify({
          ...examScheduleFormData,
          total_marks: parseFloat(examScheduleFormData.total_marks),
          passing_marks: parseFloat(examScheduleFormData.passing_marks)
        })
      });

      if (response.ok) {
        toast({ title: 'Success', description: 'Exam scheduled successfully' });
        setExamScheduleDialogOpen(false);
        setExamScheduleFormData({
          exam_type_id: '',
          class_id: '',
          subject_id: '',
          exam_date: '',
          start_time: '',
          end_time: '',
          total_marks: '',
          passing_marks: ''
        });
        fetchData();
      } else {
        const error = await response.json();
        toast({ title: 'Error', description: error.detail || 'Failed to schedule exam', variant: 'destructive' });
      }
    } catch (error) {
      toast({ title: 'Error', description: 'Failed to schedule exam', variant: 'destructive' });
    }
  };

  const handleMarksSubmit = async (e) => {
    e.preventDefault();
    try {
      const response = await fetch(`${BACKEND_URL}/api/marks`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${token}`
        },
        body: JSON.stringify({
          ...marksFormData,
          marks_obtained: parseFloat(marksFormData.marks_obtained)
        })
      });

      if (response.ok) {
        toast({ title: 'Success', description: 'Marks entered successfully' });
        setMarksDialogOpen(false);
        setMarksFormData({
          exam_schedule_id: '',
          student_id: '',
          marks_obtained: ''
        });
        fetchData();
      } else {
        const error = await response.json();
        toast({ title: 'Error', description: error.detail || 'Failed to enter marks', variant: 'destructive' });
      }
    } catch (error) {
      toast({ title: 'Error', description: 'Failed to enter marks', variant: 'destructive' });
    }
  };

  const handleGradeRuleSubmit = async (e) => {
    e.preventDefault();
    try {
      const response = await fetch(`${BACKEND_URL}/api/grade-rules`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${token}`
        },
        body: JSON.stringify({
          ...gradeRuleFormData,
          min_percentage: parseFloat(gradeRuleFormData.min_percentage),
          max_percentage: parseFloat(gradeRuleFormData.max_percentage)
        })
      });

      if (response.ok) {
        toast({ title: 'Success', description: 'Grade rule created successfully' });
        setGradeRuleDialogOpen(false);
        setGradeRuleFormData({
          grade: '',
          min_percentage: '',
          max_percentage: '',
          description: ''
        });
        fetchData();
      } else {
        const error = await response.json();
        toast({ title: 'Error', description: error.detail || 'Failed to create grade rule', variant: 'destructive' });
      }
    } catch (error) {
      toast({ title: 'Error', description: 'Failed to create grade rule', variant: 'destructive' });
    }
  };

  const fetchReportCard = async () => {
    if (!selectedStudentId) {
      toast({ title: 'Error', description: 'Please select a student', variant: 'destructive' });
      return;
    }
    
    try {
      const response = await fetch(`${BACKEND_URL}/api/report-card/${selectedStudentId}`, {
        headers: { Authorization: `Bearer ${token}` }
      });

      if (response.ok) {
        const data = await response.json();
        setReportCard(data);
        setReportCardDialogOpen(true);
      } else {
        toast({ title: 'Error', description: 'Failed to fetch report card', variant: 'destructive' });
      }
    } catch (error) {
      toast({ title: 'Error', description: 'Failed to fetch report card', variant: 'destructive' });
    }
  };

  const getExamTypeById = (id) => examTypes.find(e => e.id === id);
  const getClassById = (id) => classes.find(c => c.id === id);
  const getSubjectById = (id) => subjects.find(s => s.id === id);
  const getStudentById = (id) => students.find(s => s.id === id);

  if (loading) {
    return <div className="flex justify-center items-center h-64">Loading...</div>;
  }

  return (
    <div className="space-y-6">
      <div>
        <h2 className="text-3xl font-bold text-gray-900">Exams Management</h2>
        <p className="text-gray-600 mt-1">Manage exams, schedules, marks, and report cards</p>
      </div>

      <Tabs defaultValue="exam-types" className="space-y-4">
        <TabsList>
          <TabsTrigger value="exam-types">Exam Types</TabsTrigger>
          <TabsTrigger value="schedules">Exam Schedules</TabsTrigger>
          <TabsTrigger value="marks">Marks Entry</TabsTrigger>
          <TabsTrigger value="grades">Grade Rules</TabsTrigger>
          <TabsTrigger value="report-cards">Report Cards</TabsTrigger>
        </TabsList>

        <TabsContent value="exam-types">
          <Card>
            <CardHeader>
              <div className="flex justify-between items-center">
                <CardTitle>Exam Types</CardTitle>
                <Dialog open={examTypeDialogOpen} onOpenChange={setExamTypeDialogOpen}>
                  <DialogTrigger asChild>
                    <Button className="flex items-center gap-2">
                      <PlusIcon className="w-5 h-5" />
                      Add Exam Type
                    </Button>
                  </DialogTrigger>
                  <DialogContent>
                    <DialogHeader>
                      <DialogTitle>Add Exam Type</DialogTitle>
                    </DialogHeader>
                    <form onSubmit={handleExamTypeSubmit} className="space-y-4">
                      <div>
                        <Label htmlFor="name">Exam Type Name</Label>
                        <Input
                          id="name"
                          placeholder="e.g., Mid-term, Final"
                          value={examTypeFormData.name}
                          onChange={(e) => setExamTypeFormData({ ...examTypeFormData, name: e.target.value })}
                          required
                        />
                      </div>
                      <div>
                        <Label htmlFor="description">Description</Label>
                        <Textarea
                          id="description"
                          placeholder="Description..."
                          value={examTypeFormData.description}
                          onChange={(e) => setExamTypeFormData({ ...examTypeFormData, description: e.target.value })}
                          rows={3}
                        />
                      </div>
                      <Button type="submit" className="w-full">Create Exam Type</Button>
                    </form>
                  </DialogContent>
                </Dialog>
              </div>
            </CardHeader>
            <CardContent>
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Name</TableHead>
                    <TableHead>Description</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {examTypes.map((type) => (
                    <TableRow key={type.id}>
                      <TableCell className="font-medium">{type.name}</TableCell>
                      <TableCell>{type.description || 'N/A'}</TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="schedules">
          <Card>
            <CardHeader>
              <div className="flex justify-between items-center">
                <CardTitle>Exam Schedules</CardTitle>
                <Dialog open={examScheduleDialogOpen} onOpenChange={setExamScheduleDialogOpen}>
                  <DialogTrigger asChild>
                    <Button className="flex items-center gap-2">
                      <PlusIcon className="w-5 h-5" />
                      Schedule Exam
                    </Button>
                  </DialogTrigger>
                  <DialogContent className="max-w-2xl">
                    <DialogHeader>
                      <DialogTitle>Schedule Exam</DialogTitle>
                    </DialogHeader>
                    <form onSubmit={handleExamScheduleSubmit} className="space-y-4">
                      <div className="grid md:grid-cols-2 gap-4">
                        <div>
                          <Label>Exam Type</Label>
                          <Select
                            value={examScheduleFormData.exam_type_id}
                            onValueChange={(value) => setExamScheduleFormData({ ...examScheduleFormData, exam_type_id: value })}
                          >
                            <SelectTrigger>
                              <SelectValue placeholder="Select exam type" />
                            </SelectTrigger>
                            <SelectContent>
                              {examTypes.map((type) => (
                                <SelectItem key={type.id} value={type.id}>{type.name}</SelectItem>
                              ))}
                            </SelectContent>
                          </Select>
                        </div>
                        <div>
                          <Label>Class</Label>
                          <Select
                            value={examScheduleFormData.class_id}
                            onValueChange={(value) => setExamScheduleFormData({ ...examScheduleFormData, class_id: value })}
                          >
                            <SelectTrigger>
                              <SelectValue placeholder="Select class" />
                            </SelectTrigger>
                            <SelectContent>
                              {classes.map((cls) => (
                                <SelectItem key={cls.id} value={cls.id}>{cls.name}</SelectItem>
                              ))}
                            </SelectContent>
                          </Select>
                        </div>
                        <div>
                          <Label>Subject</Label>
                          <Select
                            value={examScheduleFormData.subject_id}
                            onValueChange={(value) => setExamScheduleFormData({ ...examScheduleFormData, subject_id: value })}
                          >
                            <SelectTrigger>
                              <SelectValue placeholder="Select subject" />
                            </SelectTrigger>
                            <SelectContent>
                              {subjects.map((subject) => (
                                <SelectItem key={subject.id} value={subject.id}>{subject.name}</SelectItem>
                              ))}
                            </SelectContent>
                          </Select>
                        </div>
                        <div>
                          <Label>Exam Date</Label>
                          <Input
                            type="date"
                            value={examScheduleFormData.exam_date}
                            onChange={(e) => setExamScheduleFormData({ ...examScheduleFormData, exam_date: e.target.value })}
                            required
                          />
                        </div>
                        <div>
                          <Label>Start Time</Label>
                          <Input
                            type="time"
                            value={examScheduleFormData.start_time}
                            onChange={(e) => setExamScheduleFormData({ ...examScheduleFormData, start_time: e.target.value })}
                            required
                          />
                        </div>
                        <div>
                          <Label>End Time</Label>
                          <Input
                            type="time"
                            value={examScheduleFormData.end_time}
                            onChange={(e) => setExamScheduleFormData({ ...examScheduleFormData, end_time: e.target.value })}
                            required
                          />
                        </div>
                        <div>
                          <Label>Total Marks</Label>
                          <Input
                            type="number"
                            placeholder="100"
                            value={examScheduleFormData.total_marks}
                            onChange={(e) => setExamScheduleFormData({ ...examScheduleFormData, total_marks: e.target.value })}
                            required
                          />
                        </div>
                        <div>
                          <Label>Passing Marks</Label>
                          <Input
                            type="number"
                            placeholder="40"
                            value={examScheduleFormData.passing_marks}
                            onChange={(e) => setExamScheduleFormData({ ...examScheduleFormData, passing_marks: e.target.value })}
                            required
                          />
                        </div>
                      </div>
                      <Button type="submit" className="w-full">Schedule Exam</Button>
                    </form>
                  </DialogContent>
                </Dialog>
              </div>
            </CardHeader>
            <CardContent>
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Exam Type</TableHead>
                    <TableHead>Class</TableHead>
                    <TableHead>Subject</TableHead>
                    <TableHead>Date</TableHead>
                    <TableHead>Time</TableHead>
                    <TableHead>Marks</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {examSchedules.map((schedule) => (
                    <TableRow key={schedule.id}>
                      <TableCell className="font-medium">{getExamTypeById(schedule.exam_type_id)?.name}</TableCell>
                      <TableCell>{getClassById(schedule.class_id)?.name}</TableCell>
                      <TableCell>{getSubjectById(schedule.subject_id)?.name}</TableCell>
                      <TableCell>{new Date(schedule.exam_date).toLocaleDateString()}</TableCell>
                      <TableCell>{schedule.start_time} - {schedule.end_time}</TableCell>
                      <TableCell>{schedule.total_marks} (Pass: {schedule.passing_marks})</TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="marks">
          <Card>
            <CardHeader>
              <div className="flex justify-between items-center">
                <CardTitle>Marks Entry</CardTitle>
                <Dialog open={marksDialogOpen} onOpenChange={setMarksDialogOpen}>
                  <DialogTrigger asChild>
                    <Button className="flex items-center gap-2">
                      <PlusIcon className="w-5 h-5" />
                      Enter Marks
                    </Button>
                  </DialogTrigger>
                  <DialogContent>
                    <DialogHeader>
                      <DialogTitle>Enter Marks</DialogTitle>
                    </DialogHeader>
                    <form onSubmit={handleMarksSubmit} className="space-y-4">
                      <div>
                        <Label>Exam Schedule</Label>
                        <Select
                          value={marksFormData.exam_schedule_id}
                          onValueChange={(value) => setMarksFormData({ ...marksFormData, exam_schedule_id: value })}
                        >
                          <SelectTrigger>
                            <SelectValue placeholder="Select exam" />
                          </SelectTrigger>
                          <SelectContent>
                            {examSchedules.map((schedule) => (
                              <SelectItem key={schedule.id} value={schedule.id}>
                                {getExamTypeById(schedule.exam_type_id)?.name} - {getSubjectById(schedule.subject_id)?.name}
                              </SelectItem>
                            ))}
                          </SelectContent>
                        </Select>
                      </div>
                      <div>
                        <Label>Student</Label>
                        <Select
                          value={marksFormData.student_id}
                          onValueChange={(value) => setMarksFormData({ ...marksFormData, student_id: value })}
                        >
                          <SelectTrigger>
                            <SelectValue placeholder="Select student" />
                          </SelectTrigger>
                          <SelectContent>
                            {students.map((student) => (
                              <SelectItem key={student.id} value={student.id}>{student.name}</SelectItem>
                            ))}
                          </SelectContent>
                        </Select>
                      </div>
                      <div>
                        <Label>Marks Obtained</Label>
                        <Input
                          type="number"
                          step="0.01"
                          placeholder="75"
                          value={marksFormData.marks_obtained}
                          onChange={(e) => setMarksFormData({ ...marksFormData, marks_obtained: e.target.value })}
                          required
                        />
                      </div>
                      <Button type="submit" className="w-full">Submit Marks</Button>
                    </form>
                  </DialogContent>
                </Dialog>
              </div>
            </CardHeader>
            <CardContent>
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Student</TableHead>
                    <TableHead>Exam</TableHead>
                    <TableHead>Subject</TableHead>
                    <TableHead>Marks</TableHead>
                    <TableHead>Grade</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {marks.map((mark) => (
                    <TableRow key={mark.id}>
                      <TableCell>{getStudentById(mark.student_id)?.name}</TableCell>
                      <TableCell>{getExamTypeById(examSchedules.find(s => s.id === mark.exam_schedule_id)?.exam_type_id)?.name}</TableCell>
                      <TableCell>{getSubjectById(examSchedules.find(s => s.id === mark.exam_schedule_id)?.subject_id)?.name}</TableCell>
                      <TableCell className="font-medium">{mark.marks_obtained}</TableCell>
                      <TableCell>
                        <Badge>{mark.grade || 'N/A'}</Badge>
                      </TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="grades">
          <Card>
            <CardHeader>
              <div className="flex justify-between items-center">
                <CardTitle>Grade Rules</CardTitle>
                <Dialog open={gradeRuleDialogOpen} onOpenChange={setGradeRuleDialogOpen}>
                  <DialogTrigger asChild>
                    <Button className="flex items-center gap-2">
                      <PlusIcon className="w-5 h-5" />
                      Add Grade Rule
                    </Button>
                  </DialogTrigger>
                  <DialogContent>
                    <DialogHeader>
                      <DialogTitle>Add Grade Rule</DialogTitle>
                    </DialogHeader>
                    <form onSubmit={handleGradeRuleSubmit} className="space-y-4">
                      <div>
                        <Label>Grade</Label>
                        <Input
                          placeholder="e.g., A+, A, B"
                          value={gradeRuleFormData.grade}
                          onChange={(e) => setGradeRuleFormData({ ...gradeRuleFormData, grade: e.target.value })}
                          required
                        />
                      </div>
                      <div className="grid grid-cols-2 gap-4">
                        <div>
                          <Label>Min Percentage</Label>
                          <Input
                            type="number"
                            step="0.01"
                            placeholder="90"
                            value={gradeRuleFormData.min_percentage}
                            onChange={(e) => setGradeRuleFormData({ ...gradeRuleFormData, min_percentage: e.target.value })}
                            required
                          />
                        </div>
                        <div>
                          <Label>Max Percentage</Label>
                          <Input
                            type="number"
                            step="0.01"
                            placeholder="100"
                            value={gradeRuleFormData.max_percentage}
                            onChange={(e) => setGradeRuleFormData({ ...gradeRuleFormData, max_percentage: e.target.value })}
                            required
                          />
                        </div>
                      </div>
                      <div>
                        <Label>Description</Label>
                        <Textarea
                          placeholder="e.g., Excellent"
                          value={gradeRuleFormData.description}
                          onChange={(e) => setGradeRuleFormData({ ...gradeRuleFormData, description: e.target.value })}
                          rows={2}
                        />
                      </div>
                      <Button type="submit" className="w-full">Create Grade Rule</Button>
                    </form>
                  </DialogContent>
                </Dialog>
              </div>
            </CardHeader>
            <CardContent>
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Grade</TableHead>
                    <TableHead>Percentage Range</TableHead>
                    <TableHead>Description</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {gradeRules.map((rule) => (
                    <TableRow key={rule.id}>
                      <TableCell className="font-medium">
                        <Badge className="text-lg">{rule.grade}</Badge>
                      </TableCell>
                      <TableCell>{rule.min_percentage}% - {rule.max_percentage}%</TableCell>
                      <TableCell>{rule.description || 'N/A'}</TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="report-cards">
          <Card>
            <CardHeader>
              <CardTitle>Generate Report Card</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="space-y-4">
                <div>
                  <Label>Select Student</Label>
                  <Select value={selectedStudentId} onValueChange={setSelectedStudentId}>
                    <SelectTrigger>
                      <SelectValue placeholder="Select student" />
                    </SelectTrigger>
                    <SelectContent>
                      {students.map((student) => (
                        <SelectItem key={student.id} value={student.id}>
                          {student.name} ({student.admission_number})
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                </div>
                <Button onClick={fetchReportCard} className="w-full flex items-center gap-2">
                  <DocumentTextIcon className="w-5 h-5" />
                  Generate Report Card
                </Button>
              </div>

              <Dialog open={reportCardDialogOpen} onOpenChange={setReportCardDialogOpen}>
                <DialogContent className="max-w-3xl max-h-[90vh] overflow-y-auto">
                  <DialogHeader>
                    <DialogTitle>Report Card</DialogTitle>
                  </DialogHeader>
                  {reportCard && (
                    <div className="space-y-6">
                      <div className="bg-blue-50 p-4 rounded-lg">
                        <h3 className="font-bold text-xl mb-2">{reportCard.student.name}</h3>
                        <p className="text-sm text-gray-600">Admission No: {reportCard.student.admission_number}</p>
                        <p className="text-sm text-gray-600">Class: {getClassById(reportCard.student.class_id)?.name}</p>
                      </div>

                      <div>
                        <h4 className="font-semibold mb-3">Academic Performance</h4>
                        <Table>
                          <TableHeader>
                            <TableRow>
                              <TableHead>Subject</TableHead>
                              <TableHead>Total Marks</TableHead>
                              <TableHead>Obtained</TableHead>
                              <TableHead>Percentage</TableHead>
                              <TableHead>Grade</TableHead>
                            </TableRow>
                          </TableHeader>
                          <TableBody>
                            {reportCard.marks.map((mark) => (
                              <TableRow key={mark.id}>
                                <TableCell>{getSubjectById(mark.subject_id)?.name}</TableCell>
                                <TableCell>{mark.total_marks}</TableCell>
                                <TableCell>{mark.marks_obtained}</TableCell>
                                <TableCell>{mark.percentage.toFixed(2)}%</TableCell>
                                <TableCell>
                                  <Badge>{mark.grade}</Badge>
                                </TableCell>
                              </TableRow>
                            ))}
                          </TableBody>
                        </Table>
                      </div>

                      <div className="bg-green-50 p-4 rounded-lg">
                        <div className="grid grid-cols-2 gap-4">
                          <div>
                            <p className="text-sm text-gray-600">Overall Percentage</p>
                            <p className="text-2xl font-bold text-green-700">{reportCard.overall_percentage.toFixed(2)}%</p>
                          </div>
                          <div>
                            <p className="text-sm text-gray-600">Overall Grade</p>
                            <p className="text-2xl font-bold text-green-700">{reportCard.overall_grade}</p>
                          </div>
                        </div>
                      </div>
                    </div>
                  )}
                </DialogContent>
              </Dialog>
            </CardContent>
          </Card>
        </TabsContent>
      </Tabs>
    </div>
  );
};

export default ExamsManagement;
