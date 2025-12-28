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
import { PlusIcon, BanknotesIcon, CurrencyDollarIcon } from '@heroicons/react/24/outline';
import { useToast } from '../hooks/use-toast';
import { format } from 'date-fns';

const BACKEND_URL = process.env.REACT_APP_BACKEND_URL;

const FinanceManagement = () => {
  const { token } = useAuth();
  const { toast } = useToast();
  const [feeTypes, setFeeTypes] = useState([]);
  const [feeStructures, setFeeStructures] = useState([]);
  const [invoices, setInvoices] = useState([]);
  const [payments, setPayments] = useState([]);
  const [income, setIncome] = useState([]);
  const [expenses, setExpenses] = useState([]);
  const [classes, setClasses] = useState([]);
  const [students, setStudents] = useState([]);
  const [financialReport, setFinancialReport] = useState(null);
  const [loading, setLoading] = useState(true);
  
  const [feeTypeDialogOpen, setFeeTypeDialogOpen] = useState(false);
  const [feeStructureDialogOpen, setFeeStructureDialogOpen] = useState(false);
  const [invoiceDialogOpen, setInvoiceDialogOpen] = useState(false);
  const [paymentDialogOpen, setPaymentDialogOpen] = useState(false);
  const [incomeDialogOpen, setIncomeDialogOpen] = useState(false);
  const [expenseDialogOpen, setExpenseDialogOpen] = useState(false);
  
  const [feeTypeFormData, setFeeTypeFormData] = useState({ name: '', description: '', amount: '' });
  const [feeStructureFormData, setFeeStructureFormData] = useState({
    class_id: '',
    fee_type_id: '',
    amount: '',
    frequency: 'monthly'
  });
  const [invoiceFormData, setInvoiceFormData] = useState({
    student_id: '',
    fee_structure_id: '',
    amount: '',
    due_date: '',
    description: ''
  });
  const [paymentFormData, setPaymentFormData] = useState({
    invoice_id: '',
    amount: '',
    payment_method: 'cash',
    transaction_id: '',
    payment_date: format(new Date(), 'yyyy-MM-dd')
  });
  const [incomeFormData, setIncomeFormData] = useState({
    amount: '',
    category: 'donation',
    description: '',
    date: format(new Date(), 'yyyy-MM-dd')
  });
  const [expenseFormData, setExpenseFormData] = useState({
    amount: '',
    category: 'salary',
    description: '',
    date: format(new Date(), 'yyyy-MM-dd')
  });

  useEffect(() => {
    fetchData();
  }, []);

  const fetchData = async () => {
    try {
      const [feeTypesRes, feeStructuresRes, invoicesRes, paymentsRes, incomeRes, expensesRes, classesRes, studentsRes] = await Promise.all([
        fetch(`${BACKEND_URL}/api/fee-types`, { headers: { Authorization: `Bearer ${token}` } }),
        fetch(`${BACKEND_URL}/api/fee-structures`, { headers: { Authorization: `Bearer ${token}` } }),
        fetch(`${BACKEND_URL}/api/invoices`, { headers: { Authorization: `Bearer ${token}` } }),
        fetch(`${BACKEND_URL}/api/payments`, { headers: { Authorization: `Bearer ${token}` } }),
        fetch(`${BACKEND_URL}/api/income`, { headers: { Authorization: `Bearer ${token}` } }),
        fetch(`${BACKEND_URL}/api/expenses`, { headers: { Authorization: `Bearer ${token}` } }),
        fetch(`${BACKEND_URL}/api/classes`, { headers: { Authorization: `Bearer ${token}` } }),
        fetch(`${BACKEND_URL}/api/students`, { headers: { Authorization: `Bearer ${token}` } })
      ]);
      
      if (feeTypesRes.ok) setFeeTypes(await feeTypesRes.json());
      if (feeStructuresRes.ok) setFeeStructures(await feeStructuresRes.json());
      if (invoicesRes.ok) setInvoices(await invoicesRes.json());
      if (paymentsRes.ok) setPayments(await paymentsRes.json());
      if (incomeRes.ok) setIncome(await incomeRes.json());
      if (expensesRes.ok) setExpenses(await expensesRes.json());
      if (classesRes.ok) setClasses(await classesRes.json());
      if (studentsRes.ok) setStudents(await studentsRes.json());
      
      fetchFinancialReport();
    } catch (error) {
      toast({ title: 'Error', description: 'Failed to fetch data', variant: 'destructive' });
    } finally {
      setLoading(false);
    }
  };

  const fetchFinancialReport = async () => {
    try {
      const response = await fetch(`${BACKEND_URL}/api/financial-reports`, {
        headers: { Authorization: `Bearer ${token}` }
      });
      if (response.ok) {
        setFinancialReport(await response.json());
      }
    } catch (error) {
      console.error('Failed to fetch financial report:', error);
    }
  };

  const handleFeeTypeSubmit = async (e) => {
    e.preventDefault();
    try {
      const response = await fetch(`${BACKEND_URL}/api/fee-types`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${token}`
        },
        body: JSON.stringify({
          ...feeTypeFormData,
          amount: parseFloat(feeTypeFormData.amount)
        })
      });

      if (response.ok) {
        toast({ title: 'Success', description: 'Fee type created successfully' });
        setFeeTypeDialogOpen(false);
        setFeeTypeFormData({ name: '', description: '', amount: '' });
        fetchData();
      } else {
        const error = await response.json();
        toast({ title: 'Error', description: error.detail || 'Failed to create fee type', variant: 'destructive' });
      }
    } catch (error) {
      toast({ title: 'Error', description: 'Failed to create fee type', variant: 'destructive' });
    }
  };

  const handleFeeStructureSubmit = async (e) => {
    e.preventDefault();
    try {
      const response = await fetch(`${BACKEND_URL}/api/fee-structures`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${token}`
        },
        body: JSON.stringify({
          ...feeStructureFormData,
          amount: parseFloat(feeStructureFormData.amount)
        })
      });

      if (response.ok) {
        toast({ title: 'Success', description: 'Fee structure created successfully' });
        setFeeStructureDialogOpen(false);
        setFeeStructureFormData({ class_id: '', fee_type_id: '', amount: '', frequency: 'monthly' });
        fetchData();
      } else {
        const error = await response.json();
        toast({ title: 'Error', description: error.detail || 'Failed to create fee structure', variant: 'destructive' });
      }
    } catch (error) {
      toast({ title: 'Error', description: 'Failed to create fee structure', variant: 'destructive' });
    }
  };

  const handleInvoiceSubmit = async (e) => {
    e.preventDefault();
    try {
      const response = await fetch(`${BACKEND_URL}/api/invoices`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${token}`
        },
        body: JSON.stringify({
          ...invoiceFormData,
          amount: parseFloat(invoiceFormData.amount)
        })
      });

      if (response.ok) {
        toast({ title: 'Success', description: 'Invoice generated successfully' });
        setInvoiceDialogOpen(false);
        setInvoiceFormData({ student_id: '', fee_structure_id: '', amount: '', due_date: '', description: '' });
        fetchData();
      } else {
        const error = await response.json();
        toast({ title: 'Error', description: error.detail || 'Failed to generate invoice', variant: 'destructive' });
      }
    } catch (error) {
      toast({ title: 'Error', description: 'Failed to generate invoice', variant: 'destructive' });
    }
  };

  const handlePaymentSubmit = async (e) => {
    e.preventDefault();
    try {
      const response = await fetch(`${BACKEND_URL}/api/payments`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${token}`
        },
        body: JSON.stringify({
          ...paymentFormData,
          amount: parseFloat(paymentFormData.amount)
        })
      });

      if (response.ok) {
        toast({ title: 'Success', description: 'Payment recorded successfully' });
        setPaymentDialogOpen(false);
        setPaymentFormData({
          invoice_id: '',
          amount: '',
          payment_method: 'cash',
          transaction_id: '',
          payment_date: format(new Date(), 'yyyy-MM-dd')
        });
        fetchData();
      } else {
        const error = await response.json();
        toast({ title: 'Error', description: error.detail || 'Failed to record payment', variant: 'destructive' });
      }
    } catch (error) {
      toast({ title: 'Error', description: 'Failed to record payment', variant: 'destructive' });
    }
  };

  const handleIncomeSubmit = async (e) => {
    e.preventDefault();
    try {
      const response = await fetch(`${BACKEND_URL}/api/income`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${token}`
        },
        body: JSON.stringify({
          ...incomeFormData,
          amount: parseFloat(incomeFormData.amount)
        })
      });

      if (response.ok) {
        toast({ title: 'Success', description: 'Income recorded successfully' });
        setIncomeDialogOpen(false);
        setIncomeFormData({ amount: '', category: 'donation', description: '', date: format(new Date(), 'yyyy-MM-dd') });
        fetchData();
      } else {
        const error = await response.json();
        toast({ title: 'Error', description: error.detail || 'Failed to record income', variant: 'destructive' });
      }
    } catch (error) {
      toast({ title: 'Error', description: 'Failed to record income', variant: 'destructive' });
    }
  };

  const handleExpenseSubmit = async (e) => {
    e.preventDefault();
    try {
      const response = await fetch(`${BACKEND_URL}/api/expenses`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${token}`
        },
        body: JSON.stringify({
          ...expenseFormData,
          amount: parseFloat(expenseFormData.amount)
        })
      });

      if (response.ok) {
        toast({ title: 'Success', description: 'Expense recorded successfully' });
        setExpenseDialogOpen(false);
        setExpenseFormData({ amount: '', category: 'salary', description: '', date: format(new Date(), 'yyyy-MM-dd') });
        fetchData();
      } else {
        const error = await response.json();
        toast({ title: 'Error', description: error.detail || 'Failed to record expense', variant: 'destructive' });
      }
    } catch (error) {
      toast({ title: 'Error', description: 'Failed to record expense', variant: 'destructive' });
    }
  };

  const getFeeTypeById = (id) => feeTypes.find(f => f.id === id);
  const getClassById = (id) => classes.find(c => c.id === id);
  const getStudentById = (id) => students.find(s => s.id === id);
  const getInvoiceById = (id) => invoices.find(i => i.id === id);

  const getStatusBadge = (status) => {
    const colors = {
      paid: 'bg-green-100 text-green-800',
      pending: 'bg-yellow-100 text-yellow-800',
      overdue: 'bg-red-100 text-red-800',
      partial: 'bg-blue-100 text-blue-800'
    };
    return <Badge className={colors[status] || 'bg-gray-100 text-gray-800'}>{status}</Badge>;
  };

  if (loading) {
    return <div className="flex justify-center items-center h-64">Loading...</div>;
  }

  return (
    <div className="space-y-6">
      <div>
        <h2 className="text-3xl font-bold text-gray-900">Finance Management</h2>
        <p className="text-gray-600 mt-1">Manage fees, payments, income, and expenses</p>
      </div>

      {financialReport && (
        <div className="grid md:grid-cols-4 gap-4">
          <Card>
            <CardContent className="pt-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm text-gray-600">Total Income</p>
                  <p className="text-2xl font-bold text-green-600">${financialReport.total_income.toFixed(2)}</p>
                </div>
                <BanknotesIcon className="w-8 h-8 text-green-600" />
              </div>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="pt-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm text-gray-600">Total Expenses</p>
                  <p className="text-2xl font-bold text-red-600">${financialReport.total_expenses.toFixed(2)}</p>
                </div>
                <CurrencyDollarIcon className="w-8 h-8 text-red-600" />
              </div>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="pt-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm text-gray-600">Net Balance</p>
                  <p className="text-2xl font-bold text-blue-600">${financialReport.net_balance.toFixed(2)}</p>
                </div>
              </div>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="pt-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm text-gray-600">Fee Collection</p>
                  <p className="text-2xl font-bold text-purple-600">${financialReport.fee_collection.toFixed(2)}</p>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>
      )}

      <Tabs defaultValue="fee-types" className="space-y-4">
        <TabsList className="grid w-full grid-cols-6">
          <TabsTrigger value="fee-types">Fee Types</TabsTrigger>
          <TabsTrigger value="fee-structures">Structures</TabsTrigger>
          <TabsTrigger value="invoices">Invoices</TabsTrigger>
          <TabsTrigger value="payments">Payments</TabsTrigger>
          <TabsTrigger value="income">Income</TabsTrigger>
          <TabsTrigger value="expenses">Expenses</TabsTrigger>
        </TabsList>

        <TabsContent value="fee-types">
          <Card>
            <CardHeader>
              <div className="flex justify-between items-center">
                <CardTitle>Fee Types</CardTitle>
                <Dialog open={feeTypeDialogOpen} onOpenChange={setFeeTypeDialogOpen}>
                  <DialogTrigger asChild>
                    <Button className="flex items-center gap-2">
                      <PlusIcon className="w-5 h-5" />
                      Add Fee Type
                    </Button>
                  </DialogTrigger>
                  <DialogContent>
                    <DialogHeader>
                      <DialogTitle>Add Fee Type</DialogTitle>
                    </DialogHeader>
                    <form onSubmit={handleFeeTypeSubmit} className="space-y-4">
                      <div>
                        <Label>Fee Type Name</Label>
                        <Input
                          placeholder="e.g., Tuition Fee"
                          value={feeTypeFormData.name}
                          onChange={(e) => setFeeTypeFormData({ ...feeTypeFormData, name: e.target.value })}
                          required
                        />
                      </div>
                      <div>
                        <Label>Default Amount</Label>
                        <Input
                          type="number"
                          step="0.01"
                          placeholder="1000"
                          value={feeTypeFormData.amount}
                          onChange={(e) => setFeeTypeFormData({ ...feeTypeFormData, amount: e.target.value })}
                          required
                        />
                      </div>
                      <div>
                        <Label>Description</Label>
                        <Textarea
                          placeholder="Description..."
                          value={feeTypeFormData.description}
                          onChange={(e) => setFeeTypeFormData({ ...feeTypeFormData, description: e.target.value })}
                          rows={3}
                        />
                      </div>
                      <Button type="submit" className="w-full">Create Fee Type</Button>
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
                    <TableHead>Default Amount</TableHead>
                    <TableHead>Description</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {feeTypes.map((type) => (
                    <TableRow key={type.id}>
                      <TableCell className="font-medium">{type.name}</TableCell>
                      <TableCell>${type.amount.toFixed(2)}</TableCell>
                      <TableCell>{type.description || 'N/A'}</TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="fee-structures">
          <Card>
            <CardHeader>
              <div className="flex justify-between items-center">
                <CardTitle>Fee Structures</CardTitle>
                <Dialog open={feeStructureDialogOpen} onOpenChange={setFeeStructureDialogOpen}>
                  <DialogTrigger asChild>
                    <Button className="flex items-center gap-2">
                      <PlusIcon className="w-5 h-5" />
                      Add Fee Structure
                    </Button>
                  </DialogTrigger>
                  <DialogContent>
                    <DialogHeader>
                      <DialogTitle>Add Fee Structure</DialogTitle>
                    </DialogHeader>
                    <form onSubmit={handleFeeStructureSubmit} className="space-y-4">
                      <div>
                        <Label>Class</Label>
                        <Select
                          value={feeStructureFormData.class_id}
                          onValueChange={(value) => setFeeStructureFormData({ ...feeStructureFormData, class_id: value })}
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
                        <Label>Fee Type</Label>
                        <Select
                          value={feeStructureFormData.fee_type_id}
                          onValueChange={(value) => setFeeStructureFormData({ ...feeStructureFormData, fee_type_id: value })}
                        >
                          <SelectTrigger>
                            <SelectValue placeholder="Select fee type" />
                          </SelectTrigger>
                          <SelectContent>
                            {feeTypes.map((type) => (
                              <SelectItem key={type.id} value={type.id}>{type.name}</SelectItem>
                            ))}
                          </SelectContent>
                        </Select>
                      </div>
                      <div>
                        <Label>Amount</Label>
                        <Input
                          type="number"
                          step="0.01"
                          placeholder="1000"
                          value={feeStructureFormData.amount}
                          onChange={(e) => setFeeStructureFormData({ ...feeStructureFormData, amount: e.target.value })}
                          required
                        />
                      </div>
                      <div>
                        <Label>Frequency</Label>
                        <Select
                          value={feeStructureFormData.frequency}
                          onValueChange={(value) => setFeeStructureFormData({ ...feeStructureFormData, frequency: value })}
                        >
                          <SelectTrigger>
                            <SelectValue />
                          </SelectTrigger>
                          <SelectContent>
                            <SelectItem value="monthly">Monthly</SelectItem>
                            <SelectItem value="quarterly">Quarterly</SelectItem>
                            <SelectItem value="annually">Annually</SelectItem>
                            <SelectItem value="one_time">One Time</SelectItem>
                          </SelectContent>
                        </Select>
                      </div>
                      <Button type="submit" className="w-full">Create Fee Structure</Button>
                    </form>
                  </DialogContent>
                </Dialog>
              </div>
            </CardHeader>
            <CardContent>
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Class</TableHead>
                    <TableHead>Fee Type</TableHead>
                    <TableHead>Amount</TableHead>
                    <TableHead>Frequency</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {feeStructures.map((structure) => (
                    <TableRow key={structure.id}>
                      <TableCell>{getClassById(structure.class_id)?.name}</TableCell>
                      <TableCell className="font-medium">{getFeeTypeById(structure.fee_type_id)?.name}</TableCell>
                      <TableCell>${structure.amount.toFixed(2)}</TableCell>
                      <TableCell>
                        <Badge variant="outline">{structure.frequency}</Badge>
                      </TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="invoices">
          <Card>
            <CardHeader>
              <div className="flex justify-between items-center">
                <CardTitle>Invoices</CardTitle>
                <Dialog open={invoiceDialogOpen} onOpenChange={setInvoiceDialogOpen}>
                  <DialogTrigger asChild>
                    <Button className="flex items-center gap-2">
                      <PlusIcon className="w-5 h-5" />
                      Generate Invoice
                    </Button>
                  </DialogTrigger>
                  <DialogContent>
                    <DialogHeader>
                      <DialogTitle>Generate Invoice</DialogTitle>
                    </DialogHeader>
                    <form onSubmit={handleInvoiceSubmit} className="space-y-4">
                      <div>
                        <Label>Student</Label>
                        <Select
                          value={invoiceFormData.student_id}
                          onValueChange={(value) => setInvoiceFormData({ ...invoiceFormData, student_id: value })}
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
                        <Label>Fee Structure</Label>
                        <Select
                          value={invoiceFormData.fee_structure_id}
                          onValueChange={(value) => setInvoiceFormData({ ...invoiceFormData, fee_structure_id: value })}
                        >
                          <SelectTrigger>
                            <SelectValue placeholder="Select fee structure" />
                          </SelectTrigger>
                          <SelectContent>
                            {feeStructures.map((structure) => (
                              <SelectItem key={structure.id} value={structure.id}>
                                {getFeeTypeById(structure.fee_type_id)?.name} - ${structure.amount}
                              </SelectItem>
                            ))}
                          </SelectContent>
                        </Select>
                      </div>
                      <div>
                        <Label>Amount</Label>
                        <Input
                          type="number"
                          step="0.01"
                          placeholder="1000"
                          value={invoiceFormData.amount}
                          onChange={(e) => setInvoiceFormData({ ...invoiceFormData, amount: e.target.value })}
                          required
                        />
                      </div>
                      <div>
                        <Label>Due Date</Label>
                        <Input
                          type="date"
                          value={invoiceFormData.due_date}
                          onChange={(e) => setInvoiceFormData({ ...invoiceFormData, due_date: e.target.value })}
                          required
                        />
                      </div>
                      <div>
                        <Label>Description</Label>
                        <Textarea
                          placeholder="Invoice description..."
                          value={invoiceFormData.description}
                          onChange={(e) => setInvoiceFormData({ ...invoiceFormData, description: e.target.value })}
                          rows={2}
                        />
                      </div>
                      <Button type="submit" className="w-full">Generate Invoice</Button>
                    </form>
                  </DialogContent>
                </Dialog>
              </div>
            </CardHeader>
            <CardContent>
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Invoice No</TableHead>
                    <TableHead>Student</TableHead>
                    <TableHead>Amount</TableHead>
                    <TableHead>Due Date</TableHead>
                    <TableHead>Status</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {invoices.map((invoice) => (
                    <TableRow key={invoice.id}>
                      <TableCell className="font-medium">{invoice.invoice_number}</TableCell>
                      <TableCell>{getStudentById(invoice.student_id)?.name}</TableCell>
                      <TableCell>${invoice.amount.toFixed(2)}</TableCell>
                      <TableCell>{new Date(invoice.due_date).toLocaleDateString()}</TableCell>
                      <TableCell>{getStatusBadge(invoice.status)}</TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="payments">
          <Card>
            <CardHeader>
              <div className="flex justify-between items-center">
                <CardTitle>Payments</CardTitle>
                <Dialog open={paymentDialogOpen} onOpenChange={setPaymentDialogOpen}>
                  <DialogTrigger asChild>
                    <Button className="flex items-center gap-2">
                      <PlusIcon className="w-5 h-5" />
                      Record Payment
                    </Button>
                  </DialogTrigger>
                  <DialogContent>
                    <DialogHeader>
                      <DialogTitle>Record Payment</DialogTitle>
                    </DialogHeader>
                    <form onSubmit={handlePaymentSubmit} className="space-y-4">
                      <div>
                        <Label>Invoice</Label>
                        <Select
                          value={paymentFormData.invoice_id}
                          onValueChange={(value) => setPaymentFormData({ ...paymentFormData, invoice_id: value })}
                        >
                          <SelectTrigger>
                            <SelectValue placeholder="Select invoice" />
                          </SelectTrigger>
                          <SelectContent>
                            {invoices.filter(inv => inv.status !== 'paid').map((invoice) => (
                              <SelectItem key={invoice.id} value={invoice.id}>
                                {invoice.invoice_number} - ${invoice.amount}
                              </SelectItem>
                            ))}
                          </SelectContent>
                        </Select>
                      </div>
                      <div>
                        <Label>Amount</Label>
                        <Input
                          type="number"
                          step="0.01"
                          placeholder="1000"
                          value={paymentFormData.amount}
                          onChange={(e) => setPaymentFormData({ ...paymentFormData, amount: e.target.value })}
                          required
                        />
                      </div>
                      <div>
                        <Label>Payment Method</Label>
                        <Select
                          value={paymentFormData.payment_method}
                          onValueChange={(value) => setPaymentFormData({ ...paymentFormData, payment_method: value })}
                        >
                          <SelectTrigger>
                            <SelectValue />
                          </SelectTrigger>
                          <SelectContent>
                            <SelectItem value="cash">Cash</SelectItem>
                            <SelectItem value="card">Card</SelectItem>
                            <SelectItem value="bank_transfer">Bank Transfer</SelectItem>
                            <SelectItem value="cheque">Cheque</SelectItem>
                            <SelectItem value="online">Online</SelectItem>
                          </SelectContent>
                        </Select>
                      </div>
                      <div>
                        <Label>Transaction ID</Label>
                        <Input
                          placeholder="Optional transaction ID"
                          value={paymentFormData.transaction_id}
                          onChange={(e) => setPaymentFormData({ ...paymentFormData, transaction_id: e.target.value })}
                        />
                      </div>
                      <div>
                        <Label>Payment Date</Label>
                        <Input
                          type="date"
                          value={paymentFormData.payment_date}
                          onChange={(e) => setPaymentFormData({ ...paymentFormData, payment_date: e.target.value })}
                          required
                        />
                      </div>
                      <Button type="submit" className="w-full">Record Payment</Button>
                    </form>
                  </DialogContent>
                </Dialog>
              </div>
            </CardHeader>
            <CardContent>
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Receipt No</TableHead>
                    <TableHead>Invoice No</TableHead>
                    <TableHead>Amount</TableHead>
                    <TableHead>Method</TableHead>
                    <TableHead>Date</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {payments.map((payment) => (
                    <TableRow key={payment.id}>
                      <TableCell className="font-medium">{payment.receipt_number}</TableCell>
                      <TableCell>{getInvoiceById(payment.invoice_id)?.invoice_number}</TableCell>
                      <TableCell className="text-green-600 font-semibold">${payment.amount.toFixed(2)}</TableCell>
                      <TableCell>
                        <Badge variant="outline">{payment.payment_method}</Badge>
                      </TableCell>
                      <TableCell>{new Date(payment.payment_date).toLocaleDateString()}</TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="income">
          <Card>
            <CardHeader>
              <div className="flex justify-between items-center">
                <CardTitle>Other Income</CardTitle>
                <Dialog open={incomeDialogOpen} onOpenChange={setIncomeDialogOpen}>
                  <DialogTrigger asChild>
                    <Button className="flex items-center gap-2">
                      <PlusIcon className="w-5 h-5" />
                      Add Income
                    </Button>
                  </DialogTrigger>
                  <DialogContent>
                    <DialogHeader>
                      <DialogTitle>Add Income</DialogTitle>
                    </DialogHeader>
                    <form onSubmit={handleIncomeSubmit} className="space-y-4">
                      <div>
                        <Label>Amount</Label>
                        <Input
                          type="number"
                          step="0.01"
                          placeholder="1000"
                          value={incomeFormData.amount}
                          onChange={(e) => setIncomeFormData({ ...incomeFormData, amount: e.target.value })}
                          required
                        />
                      </div>
                      <div>
                        <Label>Category</Label>
                        <Select
                          value={incomeFormData.category}
                          onValueChange={(value) => setIncomeFormData({ ...incomeFormData, category: value })}
                        >
                          <SelectTrigger>
                            <SelectValue />
                          </SelectTrigger>
                          <SelectContent>
                            <SelectItem value="donation">Donation</SelectItem>
                            <SelectItem value="grant">Grant</SelectItem>
                            <SelectItem value="other">Other</SelectItem>
                          </SelectContent>
                        </Select>
                      </div>
                      <div>
                        <Label>Date</Label>
                        <Input
                          type="date"
                          value={incomeFormData.date}
                          onChange={(e) => setIncomeFormData({ ...incomeFormData, date: e.target.value })}
                          required
                        />
                      </div>
                      <div>
                        <Label>Description</Label>
                        <Textarea
                          placeholder="Description..."
                          value={incomeFormData.description}
                          onChange={(e) => setIncomeFormData({ ...incomeFormData, description: e.target.value })}
                          rows={3}
                        />
                      </div>
                      <Button type="submit" className="w-full">Add Income</Button>
                    </form>
                  </DialogContent>
                </Dialog>
              </div>
            </CardHeader>
            <CardContent>
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Date</TableHead>
                    <TableHead>Category</TableHead>
                    <TableHead>Amount</TableHead>
                    <TableHead>Description</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {income.map((item) => (
                    <TableRow key={item.id}>
                      <TableCell>{new Date(item.date).toLocaleDateString()}</TableCell>
                      <TableCell>
                        <Badge variant="outline">{item.category}</Badge>
                      </TableCell>
                      <TableCell className="text-green-600 font-semibold">${item.amount.toFixed(2)}</TableCell>
                      <TableCell>{item.description || 'N/A'}</TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="expenses">
          <Card>
            <CardHeader>
              <div className="flex justify-between items-center">
                <CardTitle>Expenses</CardTitle>
                <Dialog open={expenseDialogOpen} onOpenChange={setExpenseDialogOpen}>
                  <DialogTrigger asChild>
                    <Button className="flex items-center gap-2">
                      <PlusIcon className="w-5 h-5" />
                      Add Expense
                    </Button>
                  </DialogTrigger>
                  <DialogContent>
                    <DialogHeader>
                      <DialogTitle>Add Expense</DialogTitle>
                    </DialogHeader>
                    <form onSubmit={handleExpenseSubmit} className="space-y-4">
                      <div>
                        <Label>Amount</Label>
                        <Input
                          type="number"
                          step="0.01"
                          placeholder="1000"
                          value={expenseFormData.amount}
                          onChange={(e) => setExpenseFormData({ ...expenseFormData, amount: e.target.value })}
                          required
                        />
                      </div>
                      <div>
                        <Label>Category</Label>
                        <Select
                          value={expenseFormData.category}
                          onValueChange={(value) => setExpenseFormData({ ...expenseFormData, category: value })}
                        >
                          <SelectTrigger>
                            <SelectValue />
                          </SelectTrigger>
                          <SelectContent>
                            <SelectItem value="salary">Salary</SelectItem>
                            <SelectItem value="utilities">Utilities</SelectItem>
                            <SelectItem value="maintenance">Maintenance</SelectItem>
                            <SelectItem value="supplies">Supplies</SelectItem>
                            <SelectItem value="other">Other</SelectItem>
                          </SelectContent>
                        </Select>
                      </div>
                      <div>
                        <Label>Date</Label>
                        <Input
                          type="date"
                          value={expenseFormData.date}
                          onChange={(e) => setExpenseFormData({ ...expenseFormData, date: e.target.value })}
                          required
                        />
                      </div>
                      <div>
                        <Label>Description</Label>
                        <Textarea
                          placeholder="Description..."
                          value={expenseFormData.description}
                          onChange={(e) => setExpenseFormData({ ...expenseFormData, description: e.target.value })}
                          rows={3}
                        />
                      </div>
                      <Button type="submit" className="w-full">Add Expense</Button>
                    </form>
                  </DialogContent>
                </Dialog>
              </div>
            </CardHeader>
            <CardContent>
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Date</TableHead>
                    <TableHead>Category</TableHead>
                    <TableHead>Amount</TableHead>
                    <TableHead>Description</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {expenses.map((item) => (
                    <TableRow key={item.id}>
                      <TableCell>{new Date(item.date).toLocaleDateString()}</TableCell>
                      <TableCell>
                        <Badge variant="outline">{item.category}</Badge>
                      </TableCell>
                      <TableCell className="text-red-600 font-semibold">${item.amount.toFixed(2)}</TableCell>
                      <TableCell>{item.description || 'N/A'}</TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
            </CardContent>
          </Card>
        </TabsContent>
      </Tabs>
    </div>
  );
};

export default FinanceManagement;
