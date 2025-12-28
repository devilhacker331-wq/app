from pydantic import BaseModel, Field, EmailStr, ConfigDict
from typing import Optional, List
from datetime import datetime, timezone
import uuid
from enum import Enum

# Enums for User Roles
class UserRole(str, Enum):
    ADMIN = "admin"
    TEACHER = "teacher"
    STUDENT = "student"
    PARENT = "parent"
    ACCOUNTANT = "accountant"
    LIBRARIAN = "librarian"
    RECEPTIONIST = "receptionist"

class Gender(str, Enum):
    MALE = "male"
    FEMALE = "female"
    OTHER = "other"

class BloodGroup(str, Enum):
    A_POSITIVE = "A+"
    A_NEGATIVE = "A-"
    B_POSITIVE = "B+"
    B_NEGATIVE = "B-"
    O_POSITIVE = "O+"
    O_NEGATIVE = "O-"
    AB_POSITIVE = "AB+"
    AB_NEGATIVE = "AB-"

# Base User Model
class UserBase(BaseModel):
    username: str
    email: EmailStr
    name: str
    role: UserRole
    phone: Optional[str] = None
    address: Optional[str] = None
    photo: Optional[str] = None
    is_active: bool = True

class UserCreate(UserBase):
    password: str

class UserLogin(BaseModel):
    username: str
    password: str

class User(UserBase):
    model_config = ConfigDict(extra="ignore")
    id: str = Field(default_factory=lambda: str(uuid.uuid4()))
    created_at: datetime = Field(default_factory=lambda: datetime.now(timezone.utc))
    updated_at: datetime = Field(default_factory=lambda: datetime.now(timezone.utc))

class UserInDB(User):
    password_hash: str

# School Year Model
class SchoolYearBase(BaseModel):
    year: str  # e.g., "2024-2025"
    start_date: datetime
    end_date: datetime
    is_current: bool = False

class SchoolYearCreate(SchoolYearBase):
    pass

class SchoolYear(SchoolYearBase):
    model_config = ConfigDict(extra="ignore")
    id: str = Field(default_factory=lambda: str(uuid.uuid4()))
    created_at: datetime = Field(default_factory=lambda: datetime.now(timezone.utc))

# Section Model
class SectionBase(BaseModel):
    name: str  # e.g., "A", "B", "C"
    capacity: Optional[int] = None

class SectionCreate(SectionBase):
    pass

class Section(SectionBase):
    model_config = ConfigDict(extra="ignore")
    id: str = Field(default_factory=lambda: str(uuid.uuid4()))
    created_at: datetime = Field(default_factory=lambda: datetime.now(timezone.utc))

# Class Model
class ClassBase(BaseModel):
    name: str  # e.g., "Class 1", "Class 10"
    numeric: int  # 1-12 for sorting
    teacher_id: Optional[str] = None  # Class teacher
    school_year_id: str
    sections: List[str] = []  # List of section IDs

class ClassCreate(ClassBase):
    pass

class Class(ClassBase):
    model_config = ConfigDict(extra="ignore")
    id: str = Field(default_factory=lambda: str(uuid.uuid4()))
    created_at: datetime = Field(default_factory=lambda: datetime.now(timezone.utc))

# Subject Model
class SubjectBase(BaseModel):
    name: str
    code: str
    class_id: str
    teacher_id: Optional[str] = None
    type: Optional[str] = "mandatory"  # mandatory, optional

class SubjectCreate(SubjectBase):
    pass

class Subject(SubjectBase):
    model_config = ConfigDict(extra="ignore")
    id: str = Field(default_factory=lambda: str(uuid.uuid4()))
    created_at: datetime = Field(default_factory=lambda: datetime.now(timezone.utc))

# Teacher Model
class TeacherBase(BaseModel):
    user_id: str
    name: str
    designation: Optional[str] = None
    qualification: Optional[str] = None
    subjects: List[str] = []  # Subject IDs
    classes: List[str] = []  # Class IDs
    gender: Optional[Gender] = None
    dob: Optional[datetime] = None
    joining_date: Optional[datetime] = None
    phone: Optional[str] = None
    email: Optional[EmailStr] = None
    address: Optional[str] = None
    photo: Optional[str] = None
    salary: Optional[float] = None

class TeacherCreate(TeacherBase):
    pass

class Teacher(TeacherBase):
    model_config = ConfigDict(extra="ignore")
    id: str = Field(default_factory=lambda: str(uuid.uuid4()))
    created_at: datetime = Field(default_factory=lambda: datetime.now(timezone.utc))
    updated_at: datetime = Field(default_factory=lambda: datetime.now(timezone.utc))

# Parent Model
class ParentBase(BaseModel):
    user_id: str
    name: str
    phone: str
    email: Optional[EmailStr] = None
    address: Optional[str] = None
    occupation: Optional[str] = None
    student_ids: List[str] = []  # Children student IDs

class ParentCreate(ParentBase):
    pass

class Parent(ParentBase):
    model_config = ConfigDict(extra="ignore")
    id: str = Field(default_factory=lambda: str(uuid.uuid4()))
    created_at: datetime = Field(default_factory=lambda: datetime.now(timezone.utc))

# Student Model
class StudentBase(BaseModel):
    user_id: str
    name: str
    roll_no: str
    class_id: str
    section_id: str
    school_year_id: str
    gender: Optional[Gender] = None
    dob: Optional[datetime] = None
    blood_group: Optional[BloodGroup] = None
    religion: Optional[str] = None
    email: Optional[EmailStr] = None
    phone: Optional[str] = None
    address: Optional[str] = None
    photo: Optional[str] = None
    parent_id: Optional[str] = None
    admission_date: Optional[datetime] = None
    guardian_name: Optional[str] = None
    guardian_phone: Optional[str] = None
    guardian_relation: Optional[str] = None

class StudentCreate(StudentBase):
    pass

class Student(StudentBase):
    model_config = ConfigDict(extra="ignore")
    id: str = Field(default_factory=lambda: str(uuid.uuid4()))
    created_at: datetime = Field(default_factory=lambda: datetime.now(timezone.utc))
    updated_at: datetime = Field(default_factory=lambda: datetime.now(timezone.utc))

# Settings Model
class SettingsBase(BaseModel):
    school_name: str
    school_code: Optional[str] = None
    address: Optional[str] = None
    phone: Optional[str] = None
    email: Optional[EmailStr] = None
    website: Optional[str] = None
    logo: Optional[str] = None
    currency: str = "USD"
    currency_symbol: str = "$"
    timezone: str = "UTC"
    language: str = "en"
    date_format: str = "YYYY-MM-DD"
    time_format: str = "HH:mm"

class SettingsCreate(SettingsBase):
    pass

class Settings(SettingsBase):
    model_config = ConfigDict(extra="ignore")
    id: str = Field(default_factory=lambda: str(uuid.uuid4()))
    updated_at: datetime = Field(default_factory=lambda: datetime.now(timezone.utc))

# Token Models
class Token(BaseModel):
    access_token: str
    token_type: str = "bearer"
    user: User

class TokenData(BaseModel):
    username: Optional[str] = None

# ============ PHASE 2: Timetable Models ============

class DayOfWeek(str, Enum):
    MONDAY = "monday"
    TUESDAY = "tuesday"
    WEDNESDAY = "wednesday"
    THURSDAY = "thursday"
    FRIDAY = "friday"
    SATURDAY = "saturday"
    SUNDAY = "sunday"

class TimetableEntryBase(BaseModel):
    class_id: str
    section_id: str
    day: DayOfWeek
    period_number: int  # 1, 2, 3, etc.
    start_time: str  # "09:00"
    end_time: str  # "10:00"
    subject_id: str
    teacher_id: str
    room_number: Optional[str] = None

class TimetableEntryCreate(TimetableEntryBase):
    pass

class TimetableEntry(TimetableEntryBase):
    model_config = ConfigDict(extra="ignore")
    id: str = Field(default_factory=lambda: str(uuid.uuid4()))
    created_at: datetime = Field(default_factory=lambda: datetime.now(timezone.utc))
    updated_at: datetime = Field(default_factory=lambda: datetime.now(timezone.utc))

# ============ PHASE 3: Attendance Models ============

class AttendanceStatus(str, Enum):
    PRESENT = "present"
    ABSENT = "absent"
    LATE = "late"
    EXCUSED = "excused"
    HALF_DAY = "half_day"

class AttendanceBase(BaseModel):
    student_id: str
    class_id: str
    section_id: str
    date: datetime
    status: AttendanceStatus
    subject_id: Optional[str] = None  # For subject-wise attendance
    remarks: Optional[str] = None
    marked_by: str  # teacher user_id

class AttendanceCreate(AttendanceBase):
    pass

class Attendance(AttendanceBase):
    model_config = ConfigDict(extra="ignore")
    id: str = Field(default_factory=lambda: str(uuid.uuid4()))
    created_at: datetime = Field(default_factory=lambda: datetime.now(timezone.utc))

# Exam Models
class ExamTypeBase(BaseModel):
    name: str  # "Midterm", "Final", "Quiz", "Unit Test"
    description: Optional[str] = None
    weightage: Optional[float] = None  # Percentage contribution to final grade

class ExamTypeCreate(ExamTypeBase):
    pass

class ExamType(ExamTypeBase):
    model_config = ConfigDict(extra="ignore")
    id: str = Field(default_factory=lambda: str(uuid.uuid4()))
    created_at: datetime = Field(default_factory=lambda: datetime.now(timezone.utc))

class ExamScheduleBase(BaseModel):
    exam_type_id: str
    name: str  # "Midterm Exam 2024"
    class_id: str
    section_id: Optional[str] = None
    subject_id: str
    exam_date: datetime
    start_time: str  # "09:00"
    end_time: str  # "12:00"
    total_marks: float
    pass_marks: float
    room_number: Optional[str] = None
    instructions: Optional[str] = None

class ExamScheduleCreate(ExamScheduleBase):
    pass

class ExamSchedule(ExamScheduleBase):
    model_config = ConfigDict(extra="ignore")
    id: str = Field(default_factory=lambda: str(uuid.uuid4()))
    created_at: datetime = Field(default_factory=lambda: datetime.now(timezone.utc))
    updated_at: datetime = Field(default_factory=lambda: datetime.now(timezone.utc))

class MarksEntryBase(BaseModel):
    exam_schedule_id: str
    student_id: str
    marks_obtained: float
    remarks: Optional[str] = None
    is_absent: bool = False
    entered_by: str  # teacher user_id

class MarksEntryCreate(MarksEntryBase):
    pass

class MarksEntry(MarksEntryBase):
    model_config = ConfigDict(extra="ignore")
    id: str = Field(default_factory=lambda: str(uuid.uuid4()))
    created_at: datetime = Field(default_factory=lambda: datetime.now(timezone.utc))
    updated_at: datetime = Field(default_factory=lambda: datetime.now(timezone.utc))

class GradeRuleBase(BaseModel):
    name: str  # "A+", "A", "B", etc.
    min_percentage: float
    max_percentage: float
    grade_point: Optional[float] = None
    description: Optional[str] = None

class GradeRuleCreate(GradeRuleBase):
    pass

class GradeRule(GradeRuleBase):
    model_config = ConfigDict(extra="ignore")
    id: str = Field(default_factory=lambda: str(uuid.uuid4()))
    created_at: datetime = Field(default_factory=lambda: datetime.now(timezone.utc))

# ============ PHASE 4: Financial Models ============

class FeeTypeBase(BaseModel):
    name: str  # "Tuition", "Transport", "Library", "Lab", "Sports"
    description: Optional[str] = None
    is_mandatory: bool = True

class FeeTypeCreate(FeeTypeBase):
    pass

class FeeType(FeeTypeBase):
    model_config = ConfigDict(extra="ignore")
    id: str = Field(default_factory=lambda: str(uuid.uuid4()))
    created_at: datetime = Field(default_factory=lambda: datetime.now(timezone.utc))

class FeeStructureBase(BaseModel):
    class_id: str
    school_year_id: str
    fee_type_id: str
    amount: float
    due_date: Optional[datetime] = None
    frequency: str = "annual"  # "annual", "monthly", "quarterly", "semester"

class FeeStructureCreate(FeeStructureBase):
    pass

class FeeStructure(FeeStructureBase):
    model_config = ConfigDict(extra="ignore")
    id: str = Field(default_factory=lambda: str(uuid.uuid4()))
    created_at: datetime = Field(default_factory=lambda: datetime.now(timezone.utc))

class InvoiceStatus(str, Enum):
    PENDING = "pending"
    PAID = "paid"
    PARTIALLY_PAID = "partially_paid"
    OVERDUE = "overdue"
    CANCELLED = "cancelled"

class InvoiceBase(BaseModel):
    invoice_number: str
    student_id: str
    class_id: str
    school_year_id: str
    issue_date: datetime
    due_date: datetime
    total_amount: float
    paid_amount: float = 0.0
    status: InvoiceStatus = InvoiceStatus.PENDING
    items: List[dict] = []  # [{fee_type_id, fee_type_name, amount}]
    remarks: Optional[str] = None

class InvoiceCreate(InvoiceBase):
    pass

class Invoice(InvoiceBase):
    model_config = ConfigDict(extra="ignore")
    id: str = Field(default_factory=lambda: str(uuid.uuid4()))
    created_at: datetime = Field(default_factory=lambda: datetime.now(timezone.utc))
    updated_at: datetime = Field(default_factory=lambda: datetime.now(timezone.utc))

class PaymentMethod(str, Enum):
    CASH = "cash"
    CARD = "card"
    BANK_TRANSFER = "bank_transfer"
    CHEQUE = "cheque"
    ONLINE = "online"
    STRIPE = "stripe"

class PaymentBase(BaseModel):
    invoice_id: str
    student_id: str
    amount: float
    payment_date: datetime
    payment_method: PaymentMethod
    transaction_id: Optional[str] = None
    remarks: Optional[str] = None
    received_by: str  # user_id of accountant/admin

class PaymentCreate(PaymentBase):
    pass

class Payment(PaymentBase):
    model_config = ConfigDict(extra="ignore")
    id: str = Field(default_factory=lambda: str(uuid.uuid4()))
    created_at: datetime = Field(default_factory=lambda: datetime.now(timezone.utc))

class IncomeCategory(str, Enum):
    FEE = "fee"
    DONATION = "donation"
    GRANT = "grant"
    OTHER = "other"

class IncomeBase(BaseModel):
    category: IncomeCategory
    amount: float
    date: datetime
    description: str
    reference_id: Optional[str] = None  # invoice_id or payment_id
    received_by: str  # user_id

class IncomeCreate(IncomeBase):
    pass

class Income(IncomeBase):
    model_config = ConfigDict(extra="ignore")
    id: str = Field(default_factory=lambda: str(uuid.uuid4()))
    created_at: datetime = Field(default_factory=lambda: datetime.now(timezone.utc))

class ExpenseCategory(str, Enum):
    SALARY = "salary"
    MAINTENANCE = "maintenance"
    UTILITIES = "utilities"
    SUPPLIES = "supplies"
    TRANSPORT = "transport"
    OTHER = "other"

class ExpenseBase(BaseModel):
    category: ExpenseCategory
    amount: float
    date: datetime
    description: str
    vendor: Optional[str] = None
    invoice_number: Optional[str] = None
    approved_by: str  # user_id

class ExpenseCreate(ExpenseBase):
    pass

class Expense(ExpenseBase):
    model_config = ConfigDict(extra="ignore")
    id: str = Field(default_factory=lambda: str(uuid.uuid4()))
    created_at: datetime = Field(default_factory=lambda: datetime.now(timezone.utc))
