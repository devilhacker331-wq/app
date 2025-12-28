#!/usr/bin/env python3
"""
Comprehensive Backend API Testing for School Management System
Tests Phase 2-4 implementations:
- File Upload API
- Timetable Management APIs
- Attendance Management APIs  
- Exam Management APIs
- Financial Management APIs
"""

import requests
import json
import os
from datetime import datetime, timedelta
from pathlib import Path
import tempfile
import uuid

# Get backend URL from frontend .env
def get_backend_url():
    env_path = Path("/app/frontend/.env")
    if env_path.exists():
        with open(env_path, 'r') as f:
            for line in f:
                if line.startswith('REACT_APP_BACKEND_URL='):
                    return line.split('=', 1)[1].strip()
    return "http://localhost:8001"

BASE_URL = get_backend_url()
API_URL = f"{BASE_URL}/api"

class SchoolAPITester:
    def __init__(self):
        self.session = requests.Session()
        self.auth_token = None
        self.test_data = {}
        self.results = {
            "passed": [],
            "failed": [],
            "errors": []
        }
    
    def log_result(self, test_name, success, details=""):
        """Log test result"""
        if success:
            self.results["passed"].append(f"✅ {test_name}")
            print(f"✅ PASS: {test_name}")
        else:
            self.results["failed"].append(f"❌ {test_name}: {details}")
            print(f"❌ FAIL: {test_name} - {details}")
    
    def log_error(self, test_name, error):
        """Log test error"""
        self.results["errors"].append(f"🔥 {test_name}: {str(error)}")
        print(f"🔥 ERROR: {test_name} - {str(error)}")
    
    def make_request(self, method, endpoint, data=None, files=None, params=None):
        """Make authenticated API request"""
        headers = {}
        if self.auth_token:
            headers["Authorization"] = f"Bearer {self.auth_token}"
        
        url = f"{API_URL}{endpoint}"
        
        try:
            if method.upper() == "GET":
                response = self.session.get(url, headers=headers, params=params)
            elif method.upper() == "POST":
                if files:
                    response = self.session.post(url, headers=headers, files=files, data=data)
                else:
                    headers["Content-Type"] = "application/json"
                    response = self.session.post(url, headers=headers, json=data)
            elif method.upper() == "PUT":
                headers["Content-Type"] = "application/json"
                response = self.session.put(url, headers=headers, json=data)
            elif method.upper() == "DELETE":
                response = self.session.delete(url, headers=headers)
            
            return response
        except Exception as e:
            raise Exception(f"Request failed: {str(e)}")
    
    def test_authentication(self):
        """Test authentication and get token"""
        print("\n=== Testing Authentication ===")
        
        try:
            # Try to login as admin
            login_data = {
                "username": "admin",
                "password": "admin123"
            }
            
            response = self.make_request("POST", "/auth/login", login_data)
            
            if response.status_code == 200:
                data = response.json()
                self.auth_token = data["access_token"]
                self.test_data["admin_user"] = data["user"]
                self.log_result("Admin Login", True)
                return True
            else:
                # Try to create admin user first
                admin_user = {
                    "username": "admin",
                    "email": "admin@school.edu",
                    "name": "System Administrator",
                    "role": "admin",
                    "password": "admin123"
                }
                
                reg_response = self.make_request("POST", "/auth/register", admin_user)
                if reg_response.status_code == 200:
                    self.log_result("Admin Registration", True)
                    
                    # Now try login again
                    login_response = self.make_request("POST", "/auth/login", login_data)
                    if login_response.status_code == 200:
                        data = login_response.json()
                        self.auth_token = data["access_token"]
                        self.test_data["admin_user"] = data["user"]
                        self.log_result("Admin Login After Registration", True)
                        return True
                
                self.log_result("Authentication", False, f"Status: {response.status_code}, Response: {response.text}")
                return False
                
        except Exception as e:
            self.log_error("Authentication", e)
            return False
    
    def setup_test_data(self):
        """Create basic test data needed for Phase 2-4 testing"""
        print("\n=== Setting Up Test Data ===")
        
        try:
            # Create school year
            school_year_data = {
                "year": "2024-2025",
                "start_date": "2024-09-01T00:00:00Z",
                "end_date": "2025-06-30T23:59:59Z",
                "is_current": True
            }
            
            response = self.make_request("POST", "/school-years", school_year_data)
            if response.status_code == 200:
                self.test_data["school_year"] = response.json()
                self.log_result("School Year Creation", True)
            else:
                self.log_result("School Year Creation", False, f"Status: {response.status_code}")
            
            # Create section
            section_data = {
                "name": "A",
                "capacity": 30
            }
            
            response = self.make_request("POST", "/sections", section_data)
            if response.status_code == 200:
                self.test_data["section"] = response.json()
                self.log_result("Section Creation", True)
            else:
                self.log_result("Section Creation", False, f"Status: {response.status_code}")
            
            # Create class
            class_data = {
                "name": "Grade 10",
                "numeric": 10,
                "school_year_id": self.test_data["school_year"]["id"],
                "sections": [self.test_data["section"]["id"]]
            }
            
            response = self.make_request("POST", "/classes", class_data)
            if response.status_code == 200:
                self.test_data["class"] = response.json()
                self.log_result("Class Creation", True)
            else:
                self.log_result("Class Creation", False, f"Status: {response.status_code}")
            
            # Create teacher user
            teacher_user_data = {
                "username": "john_teacher",
                "email": "john.smith@school.edu",
                "name": "John Smith",
                "role": "teacher",
                "password": "teacher123"
            }
            
            response = self.make_request("POST", "/auth/register", teacher_user_data)
            if response.status_code == 200:
                teacher_user = response.json()
                
                # Create teacher profile
                teacher_data = {
                    "user_id": teacher_user["id"],
                    "name": "John Smith",
                    "designation": "Senior Mathematics Teacher",
                    "qualification": "M.Sc Mathematics",
                    "subjects": [],
                    "classes": [self.test_data["class"]["id"]],
                    "gender": "male",
                    "phone": "+1-555-0123",
                    "email": "john.smith@school.edu"
                }
                
                response = self.make_request("POST", "/teachers", teacher_data)
                if response.status_code == 200:
                    self.test_data["teacher"] = response.json()
                    self.log_result("Teacher Creation", True)
                else:
                    self.log_result("Teacher Creation", False, f"Status: {response.status_code}")
            
            # Create subject
            subject_data = {
                "name": "Mathematics",
                "code": "MATH10",
                "class_id": self.test_data["class"]["id"],
                "teacher_id": self.test_data.get("teacher", {}).get("id"),
                "type": "mandatory"
            }
            
            response = self.make_request("POST", "/subjects", subject_data)
            if response.status_code == 200:
                self.test_data["subject"] = response.json()
                self.log_result("Subject Creation", True)
            else:
                self.log_result("Subject Creation", False, f"Status: {response.status_code}")
            
            # Create student user
            student_user_data = {
                "username": "emma_student",
                "email": "emma.wilson@student.school.edu",
                "name": "Emma Wilson",
                "role": "student",
                "password": "student123"
            }
            
            response = self.make_request("POST", "/auth/register", student_user_data)
            if response.status_code == 200:
                student_user = response.json()
                
                # Create student profile
                student_data = {
                    "user_id": student_user["id"],
                    "name": "Emma Wilson",
                    "roll_no": "2024001",
                    "class_id": self.test_data["class"]["id"],
                    "section_id": self.test_data["section"]["id"],
                    "school_year_id": self.test_data["school_year"]["id"],
                    "gender": "female",
                    "email": "emma.wilson@student.school.edu",
                    "phone": "+1-555-0456",
                    "admission_date": "2024-09-01T00:00:00Z"
                }
                
                response = self.make_request("POST", "/students", student_data)
                if response.status_code == 200:
                    self.test_data["student"] = response.json()
                    self.log_result("Student Creation", True)
                else:
                    self.log_result("Student Creation", False, f"Status: {response.status_code}")
            
            return True
            
        except Exception as e:
            self.log_error("Test Data Setup", e)
            return False
    
    def test_file_upload_api(self):
        """Test Phase 2: File Upload API"""
        print("\n=== Testing File Upload API ===")
        
        try:
            # Create a temporary test image file
            with tempfile.NamedTemporaryFile(suffix='.jpg', delete=False) as tmp_file:
                # Write some dummy image data
                tmp_file.write(b'\xff\xd8\xff\xe0\x00\x10JFIF\x00\x01\x01\x01\x00H\x00H\x00\x00\xff\xdb\x00C\x00')
                tmp_file_path = tmp_file.name
            
            try:
                with open(tmp_file_path, 'rb') as f:
                    files = {'file': ('test_photo.jpg', f, 'image/jpeg')}
                    response = self.make_request("POST", "/upload", files=files)
                
                if response.status_code == 200:
                    data = response.json()
                    if 'url' in data and 'filename' in data:
                        self.test_data["uploaded_file"] = data
                        self.log_result("File Upload", True)
                    else:
                        self.log_result("File Upload", False, "Missing url or filename in response")
                else:
                    self.log_result("File Upload", False, f"Status: {response.status_code}, Response: {response.text}")
            
            finally:
                # Clean up temp file
                os.unlink(tmp_file_path)
                
        except Exception as e:
            self.log_error("File Upload API", e)
    
    def test_timetable_apis(self):
        """Test Phase 2: Timetable Management APIs"""
        print("\n=== Testing Timetable Management APIs ===")
        
        try:
            # Test CREATE timetable entry
            timetable_data = {
                "class_id": self.test_data["class"]["id"],
                "section_id": self.test_data["section"]["id"],
                "day": "monday",
                "period_number": 1,
                "start_time": "09:00",
                "end_time": "10:00",
                "subject_id": self.test_data["subject"]["id"],
                "teacher_id": self.test_data["teacher"]["id"],
                "room_number": "Room 101"
            }
            
            response = self.make_request("POST", "/timetable", timetable_data)
            if response.status_code == 200:
                timetable_entry = response.json()
                self.test_data["timetable_entry"] = timetable_entry
                self.log_result("Timetable Entry Creation", True)
                
                # Test GET timetable entries
                response = self.make_request("GET", "/timetable", params={"class_id": self.test_data["class"]["id"]})
                if response.status_code == 200:
                    entries = response.json()
                    if len(entries) > 0:
                        self.log_result("Timetable Entries Retrieval", True)
                    else:
                        self.log_result("Timetable Entries Retrieval", False, "No entries returned")
                else:
                    self.log_result("Timetable Entries Retrieval", False, f"Status: {response.status_code}")
                
                # Test UPDATE timetable entry
                update_data = {"room_number": "Room 102"}
                response = self.make_request("PUT", f"/timetable/{timetable_entry['id']}", update_data)
                if response.status_code == 200:
                    self.log_result("Timetable Entry Update", True)
                else:
                    self.log_result("Timetable Entry Update", False, f"Status: {response.status_code}")
                
                # Test DELETE timetable entry
                response = self.make_request("DELETE", f"/timetable/{timetable_entry['id']}")
                if response.status_code == 200:
                    self.log_result("Timetable Entry Deletion", True)
                else:
                    self.log_result("Timetable Entry Deletion", False, f"Status: {response.status_code}")
                    
            else:
                self.log_result("Timetable Entry Creation", False, f"Status: {response.status_code}, Response: {response.text}")
                
        except Exception as e:
            self.log_error("Timetable APIs", e)
    
    def test_attendance_apis(self):
        """Test Phase 3: Attendance Management APIs"""
        print("\n=== Testing Attendance Management APIs ===")
        
        try:
            # Test single attendance marking
            attendance_data = {
                "student_id": self.test_data["student"]["id"],
                "class_id": self.test_data["class"]["id"],
                "section_id": self.test_data["section"]["id"],
                "date": datetime.now().isoformat(),
                "status": "present",
                "subject_id": self.test_data["subject"]["id"],
                "remarks": "On time",
                "marked_by": self.test_data["admin_user"]["id"]
            }
            
            response = self.make_request("POST", "/attendance", attendance_data)
            if response.status_code == 200:
                attendance_record = response.json()
                self.test_data["attendance"] = attendance_record
                self.log_result("Single Attendance Marking", True)
            else:
                self.log_result("Single Attendance Marking", False, f"Status: {response.status_code}, Response: {response.text}")
            
            # Test bulk attendance marking
            bulk_attendance = [
                {
                    "student_id": self.test_data["student"]["id"],
                    "class_id": self.test_data["class"]["id"],
                    "section_id": self.test_data["section"]["id"],
                    "date": (datetime.now() - timedelta(days=1)).isoformat(),
                    "status": "absent",
                    "remarks": "Sick leave",
                    "marked_by": self.test_data["admin_user"]["id"]
                }
            ]
            
            response = self.make_request("POST", "/attendance/bulk", bulk_attendance)
            if response.status_code == 200:
                self.log_result("Bulk Attendance Marking", True)
            else:
                self.log_result("Bulk Attendance Marking", False, f"Status: {response.status_code}")
            
            # Test attendance retrieval
            params = {
                "student_id": self.test_data["student"]["id"],
                "date_from": (datetime.now() - timedelta(days=7)).isoformat()[:10],
                "date_to": datetime.now().isoformat()[:10]
            }
            
            response = self.make_request("GET", "/attendance", params=params)
            if response.status_code == 200:
                records = response.json()
                if len(records) > 0:
                    self.log_result("Attendance Records Retrieval", True)
                else:
                    self.log_result("Attendance Records Retrieval", False, "No records returned")
            else:
                self.log_result("Attendance Records Retrieval", False, f"Status: {response.status_code}")
            
            # Test attendance statistics
            stats_params = {
                "student_id": self.test_data["student"]["id"],
                "date_from": (datetime.now() - timedelta(days=30)).isoformat()[:10],
                "date_to": datetime.now().isoformat()[:10]
            }
            
            response = self.make_request("GET", "/attendance/stats", params=stats_params)
            if response.status_code == 200:
                stats = response.json()
                if "total_days" in stats and "percentage" in stats:
                    self.log_result("Attendance Statistics", True)
                else:
                    self.log_result("Attendance Statistics", False, "Missing required fields in stats")
            else:
                self.log_result("Attendance Statistics", False, f"Status: {response.status_code}")
                
        except Exception as e:
            self.log_error("Attendance APIs", e)
    
    def test_exam_apis(self):
        """Test Phase 3: Exam Management APIs"""
        print("\n=== Testing Exam Management APIs ===")
        
        try:
            # Test exam type creation
            exam_type_data = {
                "name": "Midterm Examination",
                "description": "Mid-semester examination",
                "weightage": 40.0
            }
            
            response = self.make_request("POST", "/exam-types", exam_type_data)
            if response.status_code == 200:
                exam_type = response.json()
                self.test_data["exam_type"] = exam_type
                self.log_result("Exam Type Creation", True)
            else:
                self.log_result("Exam Type Creation", False, f"Status: {response.status_code}")
            
            # Test exam types retrieval
            response = self.make_request("GET", "/exam-types")
            if response.status_code == 200:
                exam_types = response.json()
                if len(exam_types) > 0:
                    self.log_result("Exam Types Retrieval", True)
                else:
                    self.log_result("Exam Types Retrieval", False, "No exam types returned")
            else:
                self.log_result("Exam Types Retrieval", False, f"Status: {response.status_code}")
            
            # Test exam schedule creation
            exam_schedule_data = {
                "exam_type_id": self.test_data["exam_type"]["id"],
                "name": "Mathematics Midterm 2024",
                "class_id": self.test_data["class"]["id"],
                "section_id": self.test_data["section"]["id"],
                "subject_id": self.test_data["subject"]["id"],
                "exam_date": (datetime.now() + timedelta(days=7)).isoformat(),
                "start_time": "09:00",
                "end_time": "12:00",
                "total_marks": 100.0,
                "pass_marks": 40.0,
                "room_number": "Exam Hall A",
                "instructions": "Bring calculator and pencil"
            }
            
            response = self.make_request("POST", "/exam-schedules", exam_schedule_data)
            if response.status_code == 200:
                exam_schedule = response.json()
                self.test_data["exam_schedule"] = exam_schedule
                self.log_result("Exam Schedule Creation", True)
            else:
                self.log_result("Exam Schedule Creation", False, f"Status: {response.status_code}")
            
            # Test exam schedules retrieval
            response = self.make_request("GET", "/exam-schedules", params={"class_id": self.test_data["class"]["id"]})
            if response.status_code == 200:
                schedules = response.json()
                if len(schedules) > 0:
                    self.log_result("Exam Schedules Retrieval", True)
                else:
                    self.log_result("Exam Schedules Retrieval", False, "No schedules returned")
            else:
                self.log_result("Exam Schedules Retrieval", False, f"Status: {response.status_code}")
            
            # Test marks entry
            marks_data = {
                "exam_schedule_id": self.test_data["exam_schedule"]["id"],
                "student_id": self.test_data["student"]["id"],
                "marks_obtained": 85.0,
                "remarks": "Excellent performance",
                "is_absent": False,
                "entered_by": self.test_data["admin_user"]["id"]
            }
            
            response = self.make_request("POST", "/marks", marks_data)
            if response.status_code == 200:
                marks_entry = response.json()
                self.test_data["marks"] = marks_entry
                self.log_result("Marks Entry", True)
            else:
                self.log_result("Marks Entry", False, f"Status: {response.status_code}")
            
            # Test bulk marks entry
            bulk_marks = [
                {
                    "exam_schedule_id": self.test_data["exam_schedule"]["id"],
                    "student_id": self.test_data["student"]["id"],
                    "marks_obtained": 78.0,
                    "remarks": "Good work",
                    "is_absent": False,
                    "entered_by": self.test_data["admin_user"]["id"]
                }
            ]
            
            response = self.make_request("POST", "/marks/bulk", bulk_marks)
            if response.status_code == 200:
                self.log_result("Bulk Marks Entry", True)
            else:
                self.log_result("Bulk Marks Entry", False, f"Status: {response.status_code}")
            
            # Test grade rules creation
            grade_rule_data = {
                "name": "A+",
                "min_percentage": 90.0,
                "max_percentage": 100.0,
                "grade_point": 4.0,
                "description": "Excellent"
            }
            
            response = self.make_request("POST", "/grade-rules", grade_rule_data)
            if response.status_code == 200:
                self.log_result("Grade Rule Creation", True)
            else:
                self.log_result("Grade Rule Creation", False, f"Status: {response.status_code}")
            
            # Test report card generation
            response = self.make_request("GET", f"/report-card/{self.test_data['student']['id']}")
            if response.status_code == 200:
                report_card = response.json()
                if "student" in report_card and "results" in report_card:
                    self.log_result("Report Card Generation", True)
                else:
                    self.log_result("Report Card Generation", False, "Missing required fields in report card")
            else:
                self.log_result("Report Card Generation", False, f"Status: {response.status_code}")
                
        except Exception as e:
            self.log_error("Exam APIs", e)
    
    def test_financial_apis(self):
        """Test Phase 4: Financial Management APIs"""
        print("\n=== Testing Financial Management APIs ===")
        
        try:
            # Test fee type creation
            fee_type_data = {
                "name": "Tuition Fee",
                "description": "Monthly tuition fee",
                "is_mandatory": True
            }
            
            response = self.make_request("POST", "/fee-types", fee_type_data)
            if response.status_code == 200:
                fee_type = response.json()
                self.test_data["fee_type"] = fee_type
                self.log_result("Fee Type Creation", True)
            else:
                self.log_result("Fee Type Creation", False, f"Status: {response.status_code}")
            
            # Test fee types retrieval
            response = self.make_request("GET", "/fee-types")
            if response.status_code == 200:
                fee_types = response.json()
                if len(fee_types) > 0:
                    self.log_result("Fee Types Retrieval", True)
                else:
                    self.log_result("Fee Types Retrieval", False, "No fee types returned")
            else:
                self.log_result("Fee Types Retrieval", False, f"Status: {response.status_code}")
            
            # Test fee structure creation
            fee_structure_data = {
                "class_id": self.test_data["class"]["id"],
                "school_year_id": self.test_data["school_year"]["id"],
                "fee_type_id": self.test_data["fee_type"]["id"],
                "amount": 500.0,
                "due_date": (datetime.now() + timedelta(days=30)).isoformat(),
                "frequency": "monthly"
            }
            
            response = self.make_request("POST", "/fee-structures", fee_structure_data)
            if response.status_code == 200:
                fee_structure = response.json()
                self.test_data["fee_structure"] = fee_structure
                self.log_result("Fee Structure Creation", True)
            else:
                self.log_result("Fee Structure Creation", False, f"Status: {response.status_code}")
            
            # Test fee structures retrieval
            response = self.make_request("GET", "/fee-structures", params={"class_id": self.test_data["class"]["id"]})
            if response.status_code == 200:
                structures = response.json()
                if len(structures) > 0:
                    self.log_result("Fee Structures Retrieval", True)
                else:
                    self.log_result("Fee Structures Retrieval", False, "No structures returned")
            else:
                self.log_result("Fee Structures Retrieval", False, f"Status: {response.status_code}")
            
            # Test invoice creation
            invoice_data = {
                "invoice_number": f"INV-{datetime.now().strftime('%Y%m%d')}-001",
                "student_id": self.test_data["student"]["id"],
                "class_id": self.test_data["class"]["id"],
                "school_year_id": self.test_data["school_year"]["id"],
                "issue_date": datetime.now().isoformat(),
                "due_date": (datetime.now() + timedelta(days=30)).isoformat(),
                "total_amount": 500.0,
                "paid_amount": 0.0,
                "status": "pending",
                "items": [
                    {
                        "fee_type_id": self.test_data["fee_type"]["id"],
                        "fee_type_name": "Tuition Fee",
                        "amount": 500.0
                    }
                ],
                "remarks": "Monthly tuition fee for October 2024"
            }
            
            response = self.make_request("POST", "/invoices", invoice_data)
            if response.status_code == 200:
                invoice = response.json()
                self.test_data["invoice"] = invoice
                self.log_result("Invoice Creation", True)
            else:
                self.log_result("Invoice Creation", False, f"Status: {response.status_code}")
            
            # Test invoices retrieval
            response = self.make_request("GET", "/invoices", params={"student_id": self.test_data["student"]["id"]})
            if response.status_code == 200:
                invoices = response.json()
                if len(invoices) > 0:
                    self.log_result("Invoices Retrieval", True)
                else:
                    self.log_result("Invoices Retrieval", False, "No invoices returned")
            else:
                self.log_result("Invoices Retrieval", False, f"Status: {response.status_code}")
            
            # Test payment creation
            payment_data = {
                "invoice_id": self.test_data["invoice"]["id"],
                "student_id": self.test_data["student"]["id"],
                "amount": 250.0,
                "payment_date": datetime.now().isoformat(),
                "payment_method": "cash",
                "transaction_id": f"TXN-{uuid.uuid4()}",
                "remarks": "Partial payment",
                "received_by": self.test_data["admin_user"]["id"]
            }
            
            response = self.make_request("POST", "/payments", payment_data)
            if response.status_code == 200:
                payment = response.json()
                self.test_data["payment"] = payment
                self.log_result("Payment Creation", True)
            else:
                self.log_result("Payment Creation", False, f"Status: {response.status_code}")
            
            # Test payments retrieval
            response = self.make_request("GET", "/payments", params={"student_id": self.test_data["student"]["id"]})
            if response.status_code == 200:
                payments = response.json()
                if len(payments) > 0:
                    self.log_result("Payments Retrieval", True)
                else:
                    self.log_result("Payments Retrieval", False, "No payments returned")
            else:
                self.log_result("Payments Retrieval", False, f"Status: {response.status_code}")
            
            # Test income creation
            income_data = {
                "category": "fee",
                "amount": 250.0,
                "date": datetime.now().isoformat(),
                "description": "Tuition fee payment from Emma Wilson",
                "reference_id": self.test_data["payment"]["id"],
                "received_by": self.test_data["admin_user"]["id"]
            }
            
            response = self.make_request("POST", "/income", income_data)
            if response.status_code == 200:
                self.log_result("Income Creation", True)
            else:
                self.log_result("Income Creation", False, f"Status: {response.status_code}")
            
            # Test expense creation
            expense_data = {
                "category": "utilities",
                "amount": 150.0,
                "date": datetime.now().isoformat(),
                "description": "Monthly electricity bill",
                "vendor": "City Electric Company",
                "invoice_number": "ELEC-2024-10-001",
                "approved_by": self.test_data["admin_user"]["id"]
            }
            
            response = self.make_request("POST", "/expenses", expense_data)
            if response.status_code == 200:
                self.log_result("Expense Creation", True)
            else:
                self.log_result("Expense Creation", False, f"Status: {response.status_code}")
            
            # Test financial reports
            report_params = {
                "date_from": (datetime.now() - timedelta(days=30)).isoformat()[:10],
                "date_to": datetime.now().isoformat()[:10]
            }
            
            response = self.make_request("GET", "/financial-reports", params=report_params)
            if response.status_code == 200:
                report = response.json()
                required_fields = ["total_income", "total_expenses", "total_fee_collected", "net_profit"]
                if all(field in report for field in required_fields):
                    self.log_result("Financial Reports", True)
                else:
                    self.log_result("Financial Reports", False, "Missing required fields in report")
            else:
                self.log_result("Financial Reports", False, f"Status: {response.status_code}")
                
        except Exception as e:
            self.log_error("Financial APIs", e)
    
    def run_all_tests(self):
        """Run all API tests"""
        print(f"🚀 Starting comprehensive backend API testing...")
        print(f"📍 Backend URL: {BASE_URL}")
        
        # Test authentication first
        if not self.test_authentication():
            print("❌ Authentication failed. Cannot proceed with other tests.")
            return False
        
        # Setup test data
        if not self.setup_test_data():
            print("❌ Test data setup failed. Some tests may fail.")
        
        # Run all Phase 2-4 tests
        self.test_file_upload_api()
        self.test_timetable_apis()
        self.test_attendance_apis()
        self.test_exam_apis()
        self.test_financial_apis()
        
        # Print summary
        print("\n" + "="*60)
        print("📊 TEST SUMMARY")
        print("="*60)
        
        print(f"\n✅ PASSED TESTS ({len(self.results['passed'])}):")
        for test in self.results["passed"]:
            print(f"  {test}")
        
        if self.results["failed"]:
            print(f"\n❌ FAILED TESTS ({len(self.results['failed'])}):")
            for test in self.results["failed"]:
                print(f"  {test}")
        
        if self.results["errors"]:
            print(f"\n🔥 ERROR TESTS ({len(self.results['errors'])}):")
            for test in self.results["errors"]:
                print(f"  {test}")
        
        total_tests = len(self.results["passed"]) + len(self.results["failed"]) + len(self.results["errors"])
        success_rate = (len(self.results["passed"]) / total_tests * 100) if total_tests > 0 else 0
        
        print(f"\n📈 SUCCESS RATE: {success_rate:.1f}% ({len(self.results['passed'])}/{total_tests})")
        
        return len(self.results["failed"]) == 0 and len(self.results["errors"]) == 0

if __name__ == "__main__":
    tester = SchoolAPITester()
    success = tester.run_all_tests()
    
    if success:
        print("\n🎉 All tests passed successfully!")
        exit(0)
    else:
        print("\n💥 Some tests failed. Check the details above.")
        exit(1)