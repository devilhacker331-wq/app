import React, { useState, useEffect } from 'react';
import { useAuth } from '../context/AuthContext';
import { Button } from '../components/ui/button';
import { Input } from '../components/ui/input';
import { Label } from '../components/ui/label';
import { Card, CardContent, CardHeader, CardTitle } from '../components/ui/card';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from '../components/ui/dialog';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../components/ui/table';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '../components/ui/select';
import { PlusIcon } from '@heroicons/react/24/outline';
import { useToast } from '../hooks/use-toast';

const BACKEND_URL = process.env.REACT_APP_BACKEND_URL;

const ClassesManagement = () => {
  const { token } = useAuth();
  const { toast } = useToast();
  const [classes, setClasses] = useState([]);
  const [sections, setSections] = useState([]);
  const [loading, setLoading] = useState(true);
  const [classDialogOpen, setClassDialogOpen] = useState(false);
  const [sectionDialogOpen, setSectionDialogOpen] = useState(false);
  const [classFormData, setClassFormData] = useState({ name: '', grade_level: '' });
  const [sectionFormData, setSectionFormData] = useState({ name: '', class_id: '', capacity: '' });

  useEffect(() => {
    fetchData();
  }, []);

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

  const handleClassSubmit = async (e) => {
    e.preventDefault();
    try {
      const response = await fetch(`${BACKEND_URL}/api/classes`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${token}`
        },
        body: JSON.stringify({ ...classFormData, grade_level: parseInt(classFormData.grade_level) })
      });

      if (response.ok) {
        toast({ title: 'Success', description: 'Class created successfully' });
        setClassDialogOpen(false);
        setClassFormData({ name: '', grade_level: '' });
        fetchData();
      } else {
        const error = await response.json();
        toast({ title: 'Error', description: error.detail || 'Failed to create class', variant: 'destructive' });
      }
    } catch (error) {
      toast({ title: 'Error', description: 'Failed to create class', variant: 'destructive' });
    }
  };

  const handleSectionSubmit = async (e) => {
    e.preventDefault();
    try {
      const response = await fetch(`${BACKEND_URL}/api/sections`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${token}`
        },
        body: JSON.stringify({ ...sectionFormData, capacity: parseInt(sectionFormData.capacity) })
      });

      if (response.ok) {
        toast({ title: 'Success', description: 'Section created successfully' });
        setSectionDialogOpen(false);
        setSectionFormData({ name: '', class_id: '', capacity: '' });
        fetchData();
      } else {
        const error = await response.json();
        toast({ title: 'Error', description: error.detail || 'Failed to create section', variant: 'destructive' });
      }
    } catch (error) {
      toast({ title: 'Error', description: 'Failed to create section', variant: 'destructive' });
    }
  };

  const getClassById = (classId) => classes.find(c => c.id === classId);

  if (loading) {
    return <div className="flex justify-center items-center h-64">Loading...</div>;
  }

  return (
    <div className="space-y-6">
      <div className="flex justify-between items-center">
        <div>
          <h2 className="text-3xl font-bold text-gray-900">Classes & Sections</h2>
          <p className="text-gray-600 mt-1">Manage classes and their sections</p>
        </div>
        <div className="flex gap-2">
          <Dialog open={classDialogOpen} onOpenChange={setClassDialogOpen}>
            <DialogTrigger asChild>
              <Button variant="outline" className="flex items-center gap-2">
                <PlusIcon className="w-5 h-5" />
                Add Class
              </Button>
            </DialogTrigger>
            <DialogContent>
              <DialogHeader>
                <DialogTitle>Add New Class</DialogTitle>
              </DialogHeader>
              <form onSubmit={handleClassSubmit} className="space-y-4">
                <div>
                  <Label htmlFor="name">Class Name</Label>
                  <Input
                    id="name"
                    placeholder="e.g., Grade 10"
                    value={classFormData.name}
                    onChange={(e) => setClassFormData({ ...classFormData, name: e.target.value })}
                    required
                  />
                </div>
                <div>
                  <Label htmlFor="grade_level">Grade Level</Label>
                  <Input
                    id="grade_level"
                    type="number"
                    placeholder="e.g., 10"
                    value={classFormData.grade_level}
                    onChange={(e) => setClassFormData({ ...classFormData, grade_level: e.target.value })}
                    required
                  />
                </div>
                <Button type="submit" className="w-full">Create Class</Button>
              </form>
            </DialogContent>
          </Dialog>

          <Dialog open={sectionDialogOpen} onOpenChange={setSectionDialogOpen}>
            <DialogTrigger asChild>
              <Button className="flex items-center gap-2">
                <PlusIcon className="w-5 h-5" />
                Add Section
              </Button>
            </DialogTrigger>
            <DialogContent>
              <DialogHeader>
                <DialogTitle>Add New Section</DialogTitle>
              </DialogHeader>
              <form onSubmit={handleSectionSubmit} className="space-y-4">
                <div>
                  <Label htmlFor="section_name">Section Name</Label>
                  <Input
                    id="section_name"
                    placeholder="e.g., A, B, C"
                    value={sectionFormData.name}
                    onChange={(e) => setSectionFormData({ ...sectionFormData, name: e.target.value })}
                    required
                  />
                </div>
                <div>
                  <Label htmlFor="class_id">Select Class</Label>
                  <Select
                    value={sectionFormData.class_id}
                    onValueChange={(value) => setSectionFormData({ ...sectionFormData, class_id: value })}
                  >
                    <SelectTrigger>
                      <SelectValue placeholder="Select a class" />
                    </SelectTrigger>
                    <SelectContent>
                      {classes.map((cls) => (
                        <SelectItem key={cls.id} value={cls.id}>{cls.name}</SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                </div>
                <div>
                  <Label htmlFor="capacity">Capacity</Label>
                  <Input
                    id="capacity"
                    type="number"
                    placeholder="e.g., 40"
                    value={sectionFormData.capacity}
                    onChange={(e) => setSectionFormData({ ...sectionFormData, capacity: e.target.value })}
                    required
                  />
                </div>
                <Button type="submit" className="w-full">Create Section</Button>
              </form>
            </DialogContent>
          </Dialog>
        </div>
      </div>

      <div className="grid md:grid-cols-2 gap-6">
        <Card>
          <CardHeader>
            <CardTitle>Classes</CardTitle>
          </CardHeader>
          <CardContent>
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Class Name</TableHead>
                  <TableHead>Grade Level</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {classes.map((cls) => (
                  <TableRow key={cls.id}>
                    <TableCell className="font-medium">{cls.name}</TableCell>
                    <TableCell>{cls.grade_level}</TableCell>
                  </TableRow>
                ))}
              </TableBody>
            </Table>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Sections</CardTitle>
          </CardHeader>
          <CardContent>
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Section</TableHead>
                  <TableHead>Class</TableHead>
                  <TableHead>Capacity</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {sections.map((section) => (
                  <TableRow key={section.id}>
                    <TableCell className="font-medium">{section.name}</TableCell>
                    <TableCell>{getClassById(section.class_id)?.name || 'N/A'}</TableCell>
                    <TableCell>{section.capacity}</TableCell>
                  </TableRow>
                ))}
              </TableBody>
            </Table>
          </CardContent>
        </Card>
      </div>
    </div>
  );
};

export default ClassesManagement;
