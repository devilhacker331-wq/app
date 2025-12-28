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
