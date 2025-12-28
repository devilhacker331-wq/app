from fastapi import FastAPI, APIRouter, HTTPException, Depends, status, Header, UploadFile, File
from fastapi.security import HTTPBearer, HTTPAuthorizationCredentials
from fastapi.staticfiles import StaticFiles
from dotenv import load_dotenv
from starlette.middleware.cors import CORSMiddleware
from motor.motor_asyncio import AsyncIOMotorClient
import os
import logging
from pathlib import Path
from typing import List, Optional
from datetime import datetime, timezone, date, timedelta
import shutil
import uuid as uuid_lib

from models import (
    User, UserCreate, UserLogin, UserInDB, Token, UserRole,
    SchoolYear, SchoolYearCreate,
    Section, SectionCreate,
    Class, ClassCreate,
    Subject, SubjectCreate,
    Teacher, TeacherCreate,
    Student, StudentCreate,
    Parent, ParentCreate,
    Settings, SettingsCreate,
    # Phase 2
    TimetableEntry, TimetableEntryCreate, DayOfWeek,
    # Phase 3
    Attendance, AttendanceCreate, AttendanceStatus,
    ExamType, ExamTypeCreate,
    ExamSchedule, ExamScheduleCreate,
    MarksEntry, MarksEntryCreate,
    GradeRule, GradeRuleCreate,
    # Phase 4
    FeeType, FeeTypeCreate,
    FeeStructure, FeeStructureCreate,
    Invoice, InvoiceCreate, InvoiceStatus,
    Payment, PaymentCreate, PaymentMethod,
    Income, IncomeCreate, IncomeCategory,
    Expense, ExpenseCreate, ExpenseCategory
)
from auth import get_password_hash, verify_password, create_access_token, decode_access_token

ROOT_DIR = Path(__file__).parent
load_dotenv(ROOT_DIR / '.env')

# MongoDB connection
mongo_url = os.environ['MONGO_URL']
client = AsyncIOMotorClient(mongo_url)
db = client[os.environ['DB_NAME']]

# Create the main app
app = FastAPI(title="School Management System API")

# Create a router with the /api prefix
api_router = APIRouter(prefix="/api")

# Security
security = HTTPBearer()

# Logger
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

# ============ Authentication Helper Functions ============

async def get_current_user(credentials: HTTPAuthorizationCredentials = Depends(security)) -> User:
    """Get current authenticated user from JWT token"""
    token = credentials.credentials
    payload = decode_access_token(token)
    
    if payload is None:
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="Invalid authentication credentials",
            headers={"WWW-Authenticate": "Bearer"},
        )
    
    username: str = payload.get("sub")
    if username is None:
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="Invalid authentication credentials"
        )
    
    user = await db.users.find_one({"username": username}, {"_id": 0, "password_hash": 0})
    if user is None:
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="User not found"
        )
    
    # Convert ISO string timestamps back to datetime
    if isinstance(user.get('created_at'), str):
        user['created_at'] = datetime.fromisoformat(user['created_at'])
    if isinstance(user.get('updated_at'), str):
        user['updated_at'] = datetime.fromisoformat(user['updated_at'])
    
    return User(**user)

def require_role(allowed_roles: List[UserRole]):
    """Dependency to check if user has required role"""
    async def role_checker(current_user: User = Depends(get_current_user)):
        if current_user.role not in allowed_roles:
            raise HTTPException(
                status_code=status.HTTP_403_FORBIDDEN,
                detail="Not enough permissions"
            )
        return current_user
    return role_checker

# ============ Authentication Routes ============

@api_router.post("/auth/register", response_model=User)
async def register(user_create: UserCreate):
    """Register a new user"""
    # Check if username already exists
    existing_user = await db.users.find_one({"username": user_create.username})
    if existing_user:
        raise HTTPException(status_code=400, detail="Username already registered")
    
    # Check if email already exists
    existing_email = await db.users.find_one({"email": user_create.email})
    if existing_email:
        raise HTTPException(status_code=400, detail="Email already registered")
    
    # Create user
    password_hash = get_password_hash(user_create.password)
    user_dict = user_create.model_dump(exclude={'password'})
    user_obj = UserInDB(**user_dict, password_hash=password_hash)
    
    doc = user_obj.model_dump()
    doc['created_at'] = doc['created_at'].isoformat()
    doc['updated_at'] = doc['updated_at'].isoformat()
    
    await db.users.insert_one(doc)
    
    return User(**user_obj.model_dump(exclude={'password_hash'}))

@api_router.post("/auth/login", response_model=Token)
async def login(user_login: UserLogin):
    """Login and get access token"""
    user_doc = await db.users.find_one({"username": user_login.username}, {"_id": 0})
    
    if not user_doc or not verify_password(user_login.password, user_doc["password_hash"]):
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="Incorrect username or password",
            headers={"WWW-Authenticate": "Bearer"},
        )
    
    if not user_doc.get("is_active", True):
        raise HTTPException(
            status_code=status.HTTP_403_FORBIDDEN,
            detail="User account is inactive"
        )
    
    # Create access token
    access_token = create_access_token(data={"sub": user_doc["username"]})
    
    # Convert timestamps
    if isinstance(user_doc.get('created_at'), str):
        user_doc['created_at'] = datetime.fromisoformat(user_doc['created_at'])
    if isinstance(user_doc.get('updated_at'), str):
        user_doc['updated_at'] = datetime.fromisoformat(user_doc['updated_at'])
    
    user = User(**{k: v for k, v in user_doc.items() if k != 'password_hash'})
    
    return Token(access_token=access_token, user=user)

@api_router.get("/auth/me", response_model=User)
async def get_me(current_user: User = Depends(get_current_user)):
    """Get current user info"""
    return current_user

# ============ User Management Routes ============

@api_router.get("/users", response_model=List[User])
async def get_users(
    role: Optional[UserRole] = None,
    current_user: User = Depends(require_role([UserRole.ADMIN]))
):
    """Get all users (Admin only)"""
    query = {}
    if role:
        query["role"] = role
    
    users = await db.users.find(query, {"_id": 0, "password_hash": 0}).to_list(1000)
    
    for user in users:
        if isinstance(user.get('created_at'), str):
            user['created_at'] = datetime.fromisoformat(user['created_at'])
        if isinstance(user.get('updated_at'), str):
            user['updated_at'] = datetime.fromisoformat(user['updated_at'])
    
    return [User(**user) for user in users]

@api_router.get("/users/{user_id}", response_model=User)
async def get_user(
    user_id: str,
    current_user: User = Depends(get_current_user)
):
    """Get user by ID"""
    user = await db.users.find_one({"id": user_id}, {"_id": 0, "password_hash": 0})
    
    if not user:
        raise HTTPException(status_code=404, detail="User not found")
    
    if isinstance(user.get('created_at'), str):
        user['created_at'] = datetime.fromisoformat(user['created_at'])
    if isinstance(user.get('updated_at'), str):
        user['updated_at'] = datetime.fromisoformat(user['updated_at'])
    
    return User(**user)

@api_router.put("/users/{user_id}", response_model=User)
async def update_user(
    user_id: str,
    updates: dict,
    current_user: User = Depends(get_current_user)
):
    """Update user"""
    # Only admins can update other users
    if current_user.id != user_id and current_user.role != UserRole.ADMIN:
        raise HTTPException(status_code=403, detail="Not enough permissions")
    
    # Don't allow password in regular update
    if "password" in updates:
        del updates["password"]
    
    updates["updated_at"] = datetime.now(timezone.utc).isoformat()
    
    result = await db.users.update_one({"id": user_id}, {"$set": updates})
    
    if result.matched_count == 0:
        raise HTTPException(status_code=404, detail="User not found")
    
    user = await db.users.find_one({"id": user_id}, {"_id": 0, "password_hash": 0})
    
    if isinstance(user.get('created_at'), str):
        user['created_at'] = datetime.fromisoformat(user['created_at'])
    if isinstance(user.get('updated_at'), str):
        user['updated_at'] = datetime.fromisoformat(user['updated_at'])
    
    return User(**user)

@api_router.delete("/users/{user_id}")
async def delete_user(
    user_id: str,
    current_user: User = Depends(require_role([UserRole.ADMIN]))
):
    """Delete user (Admin only)"""
    result = await db.users.delete_one({"id": user_id})
    
    if result.deleted_count == 0:
        raise HTTPException(status_code=404, detail="User not found")
    
    return {"message": "User deleted successfully"}

# ============ School Year Routes ============

@api_router.post("/school-years", response_model=SchoolYear)
async def create_school_year(
    school_year: SchoolYearCreate,
    current_user: User = Depends(require_role([UserRole.ADMIN]))
):
    """Create school year"""
    # If this is set as current, unset all others
    if school_year.is_current:
        await db.school_years.update_many({}, {"$set": {"is_current": False}})
    
    year_obj = SchoolYear(**school_year.model_dump())
    doc = year_obj.model_dump()
    doc['created_at'] = doc['created_at'].isoformat()
    doc['start_date'] = doc['start_date'].isoformat()
    doc['end_date'] = doc['end_date'].isoformat()
    
    await db.school_years.insert_one(doc)
    return year_obj

@api_router.get("/school-years", response_model=List[SchoolYear])
async def get_school_years(current_user: User = Depends(get_current_user)):
    """Get all school years"""
    years = await db.school_years.find({}, {"_id": 0}).to_list(100)
    
    for year in years:
        if isinstance(year.get('created_at'), str):
            year['created_at'] = datetime.fromisoformat(year['created_at'])
        if isinstance(year.get('start_date'), str):
            year['start_date'] = datetime.fromisoformat(year['start_date'])
        if isinstance(year.get('end_date'), str):
            year['end_date'] = datetime.fromisoformat(year['end_date'])
    
    return [SchoolYear(**year) for year in years]

@api_router.get("/school-years/current", response_model=SchoolYear)
async def get_current_school_year(current_user: User = Depends(get_current_user)):
    """Get current active school year"""
    year = await db.school_years.find_one({"is_current": True}, {"_id": 0})
    
    if not year:
        raise HTTPException(status_code=404, detail="No current school year set")
    
    if isinstance(year.get('created_at'), str):
        year['created_at'] = datetime.fromisoformat(year['created_at'])
    if isinstance(year.get('start_date'), str):
        year['start_date'] = datetime.fromisoformat(year['start_date'])
    if isinstance(year.get('end_date'), str):
        year['end_date'] = datetime.fromisoformat(year['end_date'])
    
    return SchoolYear(**year)

# ============ Section Routes ============

@api_router.post("/sections", response_model=Section)
async def create_section(
    section: SectionCreate,
    current_user: User = Depends(require_role([UserRole.ADMIN]))
):
    """Create section"""
    section_obj = Section(**section.model_dump())
    doc = section_obj.model_dump()
    doc['created_at'] = doc['created_at'].isoformat()
    
    await db.sections.insert_one(doc)
    return section_obj

@api_router.get("/sections", response_model=List[Section])
async def get_sections(current_user: User = Depends(get_current_user)):
    """Get all sections"""
    sections = await db.sections.find({}, {"_id": 0}).to_list(100)
    
    for section in sections:
        if isinstance(section.get('created_at'), str):
            section['created_at'] = datetime.fromisoformat(section['created_at'])
    
    return [Section(**section) for section in sections]

# ============ Class Routes ============

@api_router.post("/classes", response_model=Class)
async def create_class(
    class_data: ClassCreate,
    current_user: User = Depends(require_role([UserRole.ADMIN]))
):
    """Create class"""
    class_obj = Class(**class_data.model_dump())
    doc = class_obj.model_dump()
    doc['created_at'] = doc['created_at'].isoformat()
    
    await db.classes.insert_one(doc)
    return class_obj

@api_router.get("/classes", response_model=List[Class])
async def get_classes(
    school_year_id: Optional[str] = None,
    current_user: User = Depends(get_current_user)
):
    """Get all classes"""
    query = {}
    if school_year_id:
        query["school_year_id"] = school_year_id
    
    classes = await db.classes.find(query, {"_id": 0}).sort("numeric", 1).to_list(100)
    
    for class_doc in classes:
        if isinstance(class_doc.get('created_at'), str):
            class_doc['created_at'] = datetime.fromisoformat(class_doc['created_at'])
    
    return [Class(**class_doc) for class_doc in classes]

@api_router.get("/classes/{class_id}", response_model=Class)
async def get_class(
    class_id: str,
    current_user: User = Depends(get_current_user)
):
    """Get class by ID"""
    class_doc = await db.classes.find_one({"id": class_id}, {"_id": 0})
    
    if not class_doc:
        raise HTTPException(status_code=404, detail="Class not found")
    
    if isinstance(class_doc.get('created_at'), str):
        class_doc['created_at'] = datetime.fromisoformat(class_doc['created_at'])
    
    return Class(**class_doc)

# ============ Subject Routes ============

@api_router.post("/subjects", response_model=Subject)
async def create_subject(
    subject: SubjectCreate,
    current_user: User = Depends(require_role([UserRole.ADMIN, UserRole.TEACHER]))
):
    """Create subject"""
    subject_obj = Subject(**subject.model_dump())
    doc = subject_obj.model_dump()
    doc['created_at'] = doc['created_at'].isoformat()
    
    await db.subjects.insert_one(doc)
    return subject_obj

@api_router.get("/subjects", response_model=List[Subject])
async def get_subjects(
    class_id: Optional[str] = None,
    current_user: User = Depends(get_current_user)
):
    """Get all subjects"""
    query = {}
    if class_id:
        query["class_id"] = class_id
    
    subjects = await db.subjects.find(query, {"_id": 0}).to_list(100)
    
    for subject in subjects:
        if isinstance(subject.get('created_at'), str):
            subject['created_at'] = datetime.fromisoformat(subject['created_at'])
    
    return [Subject(**subject) for subject in subjects]

# ============ Teacher Routes ============

@api_router.post("/teachers", response_model=Teacher)
async def create_teacher(
    teacher: TeacherCreate,
    current_user: User = Depends(require_role([UserRole.ADMIN]))
):
    """Create teacher"""
    teacher_obj = Teacher(**teacher.model_dump())
    doc = teacher_obj.model_dump()
    doc['created_at'] = doc['created_at'].isoformat()
    doc['updated_at'] = doc['updated_at'].isoformat()
    if doc.get('dob'):
        doc['dob'] = doc['dob'].isoformat()
    if doc.get('joining_date'):
        doc['joining_date'] = doc['joining_date'].isoformat()
    
    await db.teachers.insert_one(doc)
    return teacher_obj

@api_router.get("/teachers", response_model=List[Teacher])
async def get_teachers(current_user: User = Depends(get_current_user)):
    """Get all teachers"""
    teachers = await db.teachers.find({}, {"_id": 0}).to_list(1000)
    
    for teacher in teachers:
        if isinstance(teacher.get('created_at'), str):
            teacher['created_at'] = datetime.fromisoformat(teacher['created_at'])
        if isinstance(teacher.get('updated_at'), str):
            teacher['updated_at'] = datetime.fromisoformat(teacher['updated_at'])
        if teacher.get('dob') and isinstance(teacher['dob'], str):
            teacher['dob'] = datetime.fromisoformat(teacher['dob'])
        if teacher.get('joining_date') and isinstance(teacher['joining_date'], str):
            teacher['joining_date'] = datetime.fromisoformat(teacher['joining_date'])
    
    return [Teacher(**teacher) for teacher in teachers]

@api_router.get("/teachers/{teacher_id}", response_model=Teacher)
async def get_teacher(
    teacher_id: str,
    current_user: User = Depends(get_current_user)
):
    """Get teacher by ID"""
    teacher = await db.teachers.find_one({"id": teacher_id}, {"_id": 0})
    
    if not teacher:
        raise HTTPException(status_code=404, detail="Teacher not found")
    
    if isinstance(teacher.get('created_at'), str):
        teacher['created_at'] = datetime.fromisoformat(teacher['created_at'])
    if isinstance(teacher.get('updated_at'), str):
        teacher['updated_at'] = datetime.fromisoformat(teacher['updated_at'])
    if teacher.get('dob') and isinstance(teacher['dob'], str):
        teacher['dob'] = datetime.fromisoformat(teacher['dob'])
    if teacher.get('joining_date') and isinstance(teacher['joining_date'], str):
        teacher['joining_date'] = datetime.fromisoformat(teacher['joining_date'])
    
    return Teacher(**teacher)

# ============ Student Routes ============

@api_router.post("/students", response_model=Student)
async def create_student(
    student: StudentCreate,
    current_user: User = Depends(require_role([UserRole.ADMIN]))
):
    """Create student"""
    # Check if roll number already exists in the same class
    existing = await db.students.find_one({
        "roll_no": student.roll_no,
        "class_id": student.class_id,
        "school_year_id": student.school_year_id
    })
    if existing:
        raise HTTPException(status_code=400, detail="Roll number already exists in this class")
    
    student_obj = Student(**student.model_dump())
    doc = student_obj.model_dump()
    doc['created_at'] = doc['created_at'].isoformat()
    doc['updated_at'] = doc['updated_at'].isoformat()
    if doc.get('dob'):
        doc['dob'] = doc['dob'].isoformat()
    if doc.get('admission_date'):
        doc['admission_date'] = doc['admission_date'].isoformat()
    
    await db.students.insert_one(doc)
    return student_obj

@api_router.get("/students", response_model=List[Student])
async def get_students(
    class_id: Optional[str] = None,
    section_id: Optional[str] = None,
    school_year_id: Optional[str] = None,
    current_user: User = Depends(get_current_user)
):
    """Get all students"""
    query = {}
    if class_id:
        query["class_id"] = class_id
    if section_id:
        query["section_id"] = section_id
    if school_year_id:
        query["school_year_id"] = school_year_id
    
    students = await db.students.find(query, {"_id": 0}).to_list(1000)
    
    for student in students:
        if isinstance(student.get('created_at'), str):
            student['created_at'] = datetime.fromisoformat(student['created_at'])
        if isinstance(student.get('updated_at'), str):
            student['updated_at'] = datetime.fromisoformat(student['updated_at'])
        if student.get('dob') and isinstance(student['dob'], str):
            student['dob'] = datetime.fromisoformat(student['dob'])
        if student.get('admission_date') and isinstance(student['admission_date'], str):
            student['admission_date'] = datetime.fromisoformat(student['admission_date'])
    
    return [Student(**student) for student in students]

@api_router.get("/students/{student_id}", response_model=Student)
async def get_student(
    student_id: str,
    current_user: User = Depends(get_current_user)
):
    """Get student by ID"""
    student = await db.students.find_one({"id": student_id}, {"_id": 0})
    
    if not student:
        raise HTTPException(status_code=404, detail="Student not found")
    
    if isinstance(student.get('created_at'), str):
        student['created_at'] = datetime.fromisoformat(student['created_at'])
    if isinstance(student.get('updated_at'), str):
        student['updated_at'] = datetime.fromisoformat(student['updated_at'])
    if student.get('dob') and isinstance(student['dob'], str):
        student['dob'] = datetime.fromisoformat(student['dob'])
    if student.get('admission_date') and isinstance(student['admission_date'], str):
        student['admission_date'] = datetime.fromisoformat(student['admission_date'])
    
    return Student(**student)

@api_router.put("/students/{student_id}", response_model=Student)
async def update_student(
    student_id: str,
    updates: dict,
    current_user: User = Depends(require_role([UserRole.ADMIN, UserRole.TEACHER]))
):
    """Update student"""
    updates["updated_at"] = datetime.now(timezone.utc).isoformat()
    
    result = await db.students.update_one({"id": student_id}, {"$set": updates})
    
    if result.matched_count == 0:
        raise HTTPException(status_code=404, detail="Student not found")
    
    student = await db.students.find_one({"id": student_id}, {"_id": 0})
    
    if isinstance(student.get('created_at'), str):
        student['created_at'] = datetime.fromisoformat(student['created_at'])
    if isinstance(student.get('updated_at'), str):
        student['updated_at'] = datetime.fromisoformat(student['updated_at'])
    if student.get('dob') and isinstance(student['dob'], str):
        student['dob'] = datetime.fromisoformat(student['dob'])
    if student.get('admission_date') and isinstance(student['admission_date'], str):
        student['admission_date'] = datetime.fromisoformat(student['admission_date'])
    
    return Student(**student)

# ============ Parent Routes ============

@api_router.post("/parents", response_model=Parent)
async def create_parent(
    parent: ParentCreate,
    current_user: User = Depends(require_role([UserRole.ADMIN]))
):
    """Create parent"""
    parent_obj = Parent(**parent.model_dump())
    doc = parent_obj.model_dump()
    doc['created_at'] = doc['created_at'].isoformat()
    
    await db.parents.insert_one(doc)
    return parent_obj

@api_router.get("/parents", response_model=List[Parent])
async def get_parents(current_user: User = Depends(get_current_user)):
    """Get all parents"""
    parents = await db.parents.find({}, {"_id": 0}).to_list(1000)
    
    for parent in parents:
        if isinstance(parent.get('created_at'), str):
            parent['created_at'] = datetime.fromisoformat(parent['created_at'])
    
    return [Parent(**parent) for parent in parents]

# ============ Settings Routes ============

@api_router.post("/settings", response_model=Settings)
async def create_settings(
    settings: SettingsCreate,
    current_user: User = Depends(require_role([UserRole.ADMIN]))
):
    """Create or update school settings"""
    # Delete existing settings (only one settings document should exist)
    await db.settings.delete_many({})
    
    settings_obj = Settings(**settings.model_dump())
    doc = settings_obj.model_dump()
    doc['updated_at'] = doc['updated_at'].isoformat()
    
    await db.settings.insert_one(doc)
    return settings_obj

@api_router.get("/settings", response_model=Settings)
async def get_settings():
    """Get school settings (public)"""
    settings = await db.settings.find_one({}, {"_id": 0})
    
    if not settings:
        # Return default settings
        return Settings(school_name="School Management System")
    
    if isinstance(settings.get('updated_at'), str):
        settings['updated_at'] = datetime.fromisoformat(settings['updated_at'])
    
    return Settings(**settings)

# ============ Dashboard Statistics ============

@api_router.get("/dashboard/stats")
async def get_dashboard_stats(current_user: User = Depends(get_current_user)):
    """Get dashboard statistics"""
    stats = {}
    
    if current_user.role == UserRole.ADMIN:
        stats['total_students'] = await db.students.count_documents({})
        stats['total_teachers'] = await db.teachers.count_documents({})
        stats['total_parents'] = await db.parents.count_documents({})
        stats['total_classes'] = await db.classes.count_documents({})
        stats['total_subjects'] = await db.subjects.count_documents({})
    
    elif current_user.role == UserRole.TEACHER:
        teacher = await db.teachers.find_one({"user_id": current_user.id})
        if teacher:
            stats['my_classes'] = len(teacher.get('classes', []))
            stats['my_subjects'] = len(teacher.get('subjects', []))
    
    elif current_user.role == UserRole.STUDENT:
        student = await db.students.find_one({"user_id": current_user.id})
        if student:
            stats['my_class'] = student.get('class_id')
            stats['my_section'] = student.get('section_id')
    
    elif current_user.role == UserRole.PARENT:
        parent = await db.parents.find_one({"user_id": current_user.id})
        if parent:
            stats['my_children'] = len(parent.get('student_ids', []))
    
    return stats

# ============ File Upload Routes ============

# Create uploads directory
UPLOAD_DIR = ROOT_DIR / "uploads"
UPLOAD_DIR.mkdir(exist_ok=True)

@api_router.post("/upload")
async def upload_file(
    file: UploadFile = File(...),
    current_user: User = Depends(get_current_user)
):
    """Upload a file (images, documents)"""
    # Validate file type
    allowed_extensions = {'.jpg', '.jpeg', '.png', '.gif', '.pdf', '.doc', '.docx'}
    file_ext = Path(file.filename).suffix.lower()
    
    if file_ext not in allowed_extensions:
        raise HTTPException(status_code=400, detail="File type not allowed")
    
    # Generate unique filename
    unique_filename = f"{uuid_lib.uuid4()}{file_ext}"
    file_path = UPLOAD_DIR / unique_filename
    
    # Save file
    with open(file_path, "wb") as buffer:
        shutil.copyfileobj(file.file, buffer)
    
    # Return URL
    file_url = f"/api/uploads/{unique_filename}"
    return {"url": file_url, "filename": unique_filename}

# ============ PHASE 2: Timetable Routes ============

@api_router.post("/timetable", response_model=TimetableEntry)
async def create_timetable_entry(
    entry: TimetableEntryCreate,
    current_user: User = Depends(require_role([UserRole.ADMIN]))
):
    """Create timetable entry"""
    entry_obj = TimetableEntry(**entry.model_dump())
    doc = entry_obj.model_dump()
    doc['created_at'] = doc['created_at'].isoformat()
    doc['updated_at'] = doc['updated_at'].isoformat()
    
    await db.timetable.insert_one(doc)
    return entry_obj

@api_router.get("/timetable", response_model=List[TimetableEntry])
async def get_timetable(
    class_id: Optional[str] = None,
    section_id: Optional[str] = None,
    teacher_id: Optional[str] = None,
    day: Optional[DayOfWeek] = None,
    current_user: User = Depends(get_current_user)
):
    """Get timetable entries"""
    query = {}
    if class_id:
        query["class_id"] = class_id
    if section_id:
        query["section_id"] = section_id
    if teacher_id:
        query["teacher_id"] = teacher_id
    if day:
        query["day"] = day
    
    entries = await db.timetable.find(query, {"_id": 0}).sort([("day", 1), ("period_number", 1)]).to_list(1000)
    
    for entry in entries:
        if isinstance(entry.get('created_at'), str):
            entry['created_at'] = datetime.fromisoformat(entry['created_at'])
        if isinstance(entry.get('updated_at'), str):
            entry['updated_at'] = datetime.fromisoformat(entry['updated_at'])
    
    return [TimetableEntry(**entry) for entry in entries]

@api_router.put("/timetable/{entry_id}", response_model=TimetableEntry)
async def update_timetable_entry(
    entry_id: str,
    updates: dict,
    current_user: User = Depends(require_role([UserRole.ADMIN]))
):
    """Update timetable entry"""
    updates["updated_at"] = datetime.now(timezone.utc).isoformat()
    
    result = await db.timetable.update_one({"id": entry_id}, {"$set": updates})
    
    if result.matched_count == 0:
        raise HTTPException(status_code=404, detail="Timetable entry not found")
    
    entry = await db.timetable.find_one({"id": entry_id}, {"_id": 0})
    
    if isinstance(entry.get('created_at'), str):
        entry['created_at'] = datetime.fromisoformat(entry['created_at'])
    if isinstance(entry.get('updated_at'), str):
        entry['updated_at'] = datetime.fromisoformat(entry['updated_at'])
    
    return TimetableEntry(**entry)

@api_router.delete("/timetable/{entry_id}")
async def delete_timetable_entry(
    entry_id: str,
    current_user: User = Depends(require_role([UserRole.ADMIN]))
):
    """Delete timetable entry"""
    result = await db.timetable.delete_one({"id": entry_id})
    
    if result.deleted_count == 0:
        raise HTTPException(status_code=404, detail="Timetable entry not found")
    
    return {"message": "Timetable entry deleted successfully"}

# ============ PHASE 3: Attendance Routes ============

@api_router.post("/attendance", response_model=Attendance)
async def mark_attendance(
    attendance: AttendanceCreate,
    current_user: User = Depends(require_role([UserRole.ADMIN, UserRole.TEACHER]))
):
    """Mark student attendance"""
    attendance_obj = Attendance(**attendance.model_dump())
    doc = attendance_obj.model_dump()
    doc['created_at'] = doc['created_at'].isoformat()
    doc['date'] = doc['date'].isoformat()
    
    await db.attendance.insert_one(doc)
    return attendance_obj

@api_router.post("/attendance/bulk")
async def mark_bulk_attendance(
    attendance_list: List[AttendanceCreate],
    current_user: User = Depends(require_role([UserRole.ADMIN, UserRole.TEACHER]))
):
    """Mark attendance for multiple students at once"""
    docs = []
    for attendance in attendance_list:
        attendance_obj = Attendance(**attendance.model_dump())
        doc = attendance_obj.model_dump()
        doc['created_at'] = doc['created_at'].isoformat()
        doc['date'] = doc['date'].isoformat()
        docs.append(doc)
    
    if docs:
        await db.attendance.insert_many(docs)
    
    return {"message": f"Marked attendance for {len(docs)} students"}

@api_router.get("/attendance", response_model=List[Attendance])
async def get_attendance(
    student_id: Optional[str] = None,
    class_id: Optional[str] = None,
    section_id: Optional[str] = None,
    date_from: Optional[str] = None,
    date_to: Optional[str] = None,
    current_user: User = Depends(get_current_user)
):
    """Get attendance records"""
    query = {}
    if student_id:
        query["student_id"] = student_id
    if class_id:
        query["class_id"] = class_id
    if section_id:
        query["section_id"] = section_id
    
    if date_from or date_to:
        query["date"] = {}
        if date_from:
            query["date"]["$gte"] = date_from
        if date_to:
            query["date"]["$lte"] = date_to
    
    records = await db.attendance.find(query, {"_id": 0}).sort("date", -1).to_list(1000)
    
    for record in records:
        if isinstance(record.get('created_at'), str):
            record['created_at'] = datetime.fromisoformat(record['created_at'])
        if isinstance(record.get('date'), str):
            record['date'] = datetime.fromisoformat(record['date'])
    
    return [Attendance(**record) for record in records]

@api_router.get("/attendance/stats")
async def get_attendance_stats(
    student_id: str,
    date_from: Optional[str] = None,
    date_to: Optional[str] = None,
    current_user: User = Depends(get_current_user)
):
    """Get attendance statistics for a student"""
    query = {"student_id": student_id}
    
    if date_from or date_to:
        query["date"] = {}
        if date_from:
            query["date"]["$gte"] = date_from
        if date_to:
            query["date"]["$lte"] = date_to
    
    records = await db.attendance.find(query, {"_id": 0}).to_list(1000)
    
    total = len(records)
    present = len([r for r in records if r['status'] == AttendanceStatus.PRESENT])
    absent = len([r for r in records if r['status'] == AttendanceStatus.ABSENT])
    late = len([r for r in records if r['status'] == AttendanceStatus.LATE])
    
    percentage = (present / total * 100) if total > 0 else 0
    
    return {
        "total_days": total,
        "present": present,
        "absent": absent,
        "late": late,
        "percentage": round(percentage, 2)
    }

# ============ PHASE 3: Exam Management Routes ============

@api_router.post("/exam-types", response_model=ExamType)
async def create_exam_type(
    exam_type: ExamTypeCreate,
    current_user: User = Depends(require_role([UserRole.ADMIN]))
):
    """Create exam type"""
    exam_type_obj = ExamType(**exam_type.model_dump())
    doc = exam_type_obj.model_dump()
    doc['created_at'] = doc['created_at'].isoformat()
    
    await db.exam_types.insert_one(doc)
    return exam_type_obj

@api_router.get("/exam-types", response_model=List[ExamType])
async def get_exam_types(current_user: User = Depends(get_current_user)):
    """Get all exam types"""
    exam_types = await db.exam_types.find({}, {"_id": 0}).to_list(100)
    
    for exam_type in exam_types:
        if isinstance(exam_type.get('created_at'), str):
            exam_type['created_at'] = datetime.fromisoformat(exam_type['created_at'])
    
    return [ExamType(**exam_type) for exam_type in exam_types]

@api_router.post("/exam-schedules", response_model=ExamSchedule)
async def create_exam_schedule(
    schedule: ExamScheduleCreate,
    current_user: User = Depends(require_role([UserRole.ADMIN]))
):
    """Create exam schedule"""
    schedule_obj = ExamSchedule(**schedule.model_dump())
    doc = schedule_obj.model_dump()
    doc['created_at'] = doc['created_at'].isoformat()
    doc['updated_at'] = doc['updated_at'].isoformat()
    doc['exam_date'] = doc['exam_date'].isoformat()
    
    await db.exam_schedules.insert_one(doc)
    return schedule_obj

@api_router.get("/exam-schedules", response_model=List[ExamSchedule])
async def get_exam_schedules(
    class_id: Optional[str] = None,
    exam_type_id: Optional[str] = None,
    current_user: User = Depends(get_current_user)
):
    """Get exam schedules"""
    query = {}
    if class_id:
        query["class_id"] = class_id
    if exam_type_id:
        query["exam_type_id"] = exam_type_id
    
    schedules = await db.exam_schedules.find(query, {"_id": 0}).sort("exam_date", 1).to_list(1000)
    
    for schedule in schedules:
        if isinstance(schedule.get('created_at'), str):
            schedule['created_at'] = datetime.fromisoformat(schedule['created_at'])
        if isinstance(schedule.get('updated_at'), str):
            schedule['updated_at'] = datetime.fromisoformat(schedule['updated_at'])
        if isinstance(schedule.get('exam_date'), str):
            schedule['exam_date'] = datetime.fromisoformat(schedule['exam_date'])
    
    return [ExamSchedule(**schedule) for schedule in schedules]

@api_router.post("/marks", response_model=MarksEntry)
async def create_marks_entry(
    marks: MarksEntryCreate,
    current_user: User = Depends(require_role([UserRole.ADMIN, UserRole.TEACHER]))
):
    """Enter marks for a student"""
    marks_obj = MarksEntry(**marks.model_dump())
    doc = marks_obj.model_dump()
    doc['created_at'] = doc['created_at'].isoformat()
    doc['updated_at'] = doc['updated_at'].isoformat()
    
    await db.marks.insert_one(doc)
    return marks_obj

@api_router.post("/marks/bulk")
async def create_bulk_marks(
    marks_list: List[MarksEntryCreate],
    current_user: User = Depends(require_role([UserRole.ADMIN, UserRole.TEACHER]))
):
    """Enter marks for multiple students at once"""
    docs = []
    for marks in marks_list:
        marks_obj = MarksEntry(**marks.model_dump())
        doc = marks_obj.model_dump()
        doc['created_at'] = doc['created_at'].isoformat()
        doc['updated_at'] = doc['updated_at'].isoformat()
        docs.append(doc)
    
    if docs:
        await db.marks.insert_many(docs)
    
    return {"message": f"Entered marks for {len(docs)} students"}

@api_router.get("/marks", response_model=List[MarksEntry])
async def get_marks(
    student_id: Optional[str] = None,
    exam_schedule_id: Optional[str] = None,
    current_user: User = Depends(get_current_user)
):
    """Get marks entries"""
    query = {}
    if student_id:
        query["student_id"] = student_id
    if exam_schedule_id:
        query["exam_schedule_id"] = exam_schedule_id
    
    marks = await db.marks.find(query, {"_id": 0}).to_list(1000)
    
    for mark in marks:
        if isinstance(mark.get('created_at'), str):
            mark['created_at'] = datetime.fromisoformat(mark['created_at'])
        if isinstance(mark.get('updated_at'), str):
            mark['updated_at'] = datetime.fromisoformat(mark['updated_at'])
    
    return [MarksEntry(**mark) for mark in marks]

@api_router.put("/marks/{marks_id}", response_model=MarksEntry)
async def update_marks(
    marks_id: str,
    updates: dict,
    current_user: User = Depends(require_role([UserRole.ADMIN, UserRole.TEACHER]))
):
    """Update marks entry"""
    updates["updated_at"] = datetime.now(timezone.utc).isoformat()
    
    result = await db.marks.update_one({"id": marks_id}, {"$set": updates})
    
    if result.matched_count == 0:
        raise HTTPException(status_code=404, detail="Marks entry not found")
    
    marks = await db.marks.find_one({"id": marks_id}, {"_id": 0})
    
    if isinstance(marks.get('created_at'), str):
        marks['created_at'] = datetime.fromisoformat(marks['created_at'])
    if isinstance(marks.get('updated_at'), str):
        marks['updated_at'] = datetime.fromisoformat(marks['updated_at'])
    
    return MarksEntry(**marks)

@api_router.post("/grade-rules", response_model=GradeRule)
async def create_grade_rule(
    grade_rule: GradeRuleCreate,
    current_user: User = Depends(require_role([UserRole.ADMIN]))
):
    """Create grade rule"""
    grade_rule_obj = GradeRule(**grade_rule.model_dump())
    doc = grade_rule_obj.model_dump()
    doc['created_at'] = doc['created_at'].isoformat()
    
    await db.grade_rules.insert_one(doc)
    return grade_rule_obj

@api_router.get("/grade-rules", response_model=List[GradeRule])
async def get_grade_rules(current_user: User = Depends(get_current_user)):
    """Get all grade rules"""
    grade_rules = await db.grade_rules.find({}, {"_id": 0}).sort("min_percentage", -1).to_list(100)
    
    for grade_rule in grade_rules:
        if isinstance(grade_rule.get('created_at'), str):
            grade_rule['created_at'] = datetime.fromisoformat(grade_rule['created_at'])
    
    return [GradeRule(**grade_rule) for grade_rule in grade_rules]

@api_router.get("/report-card/{student_id}")
async def get_report_card(
    student_id: str,
    exam_type_id: Optional[str] = None,
    current_user: User = Depends(get_current_user)
):
    """Generate report card for a student"""
    # Get student info
    student = await db.students.find_one({"id": student_id}, {"_id": 0})
    if not student:
        raise HTTPException(status_code=404, detail="Student not found")
    
    # Get exam schedules for student's class
    query = {"class_id": student['class_id']}
    if exam_type_id:
        query["exam_type_id"] = exam_type_id
    
    schedules = await db.exam_schedules.find(query, {"_id": 0}).to_list(1000)
    
    # Get marks for each schedule
    results = []
    total_marks_obtained = 0
    total_marks_possible = 0
    
    for schedule in schedules:
        marks = await db.marks.find_one({
            "student_id": student_id,
            "exam_schedule_id": schedule['id']
        }, {"_id": 0})
        
        if marks:
            total_marks_obtained += marks['marks_obtained']
            total_marks_possible += schedule['total_marks']
            
            # Get subject name
            subject = await db.subjects.find_one({"id": schedule['subject_id']}, {"_id": 0})
            
            results.append({
                "subject_name": subject['name'] if subject else "Unknown",
                "subject_code": subject['code'] if subject else "",
                "marks_obtained": marks['marks_obtained'],
                "total_marks": schedule['total_marks'],
                "percentage": round((marks['marks_obtained'] / schedule['total_marks'] * 100), 2),
                "remarks": marks.get('remarks', '')
            })
    
    # Calculate overall percentage
    overall_percentage = (total_marks_obtained / total_marks_possible * 100) if total_marks_possible > 0 else 0
    
    # Determine grade
    grade_rules = await db.grade_rules.find({}, {"_id": 0}).sort("min_percentage", -1).to_list(100)
    grade = "N/A"
    for rule in grade_rules:
        if rule['min_percentage'] <= overall_percentage <= rule['max_percentage']:
            grade = rule['name']
            break
    
    return {
        "student": student,
        "results": results,
        "total_marks_obtained": total_marks_obtained,
        "total_marks_possible": total_marks_possible,
        "overall_percentage": round(overall_percentage, 2),
        "grade": grade
    }

# ============ PHASE 4: Financial Management Routes ============

@api_router.post("/fee-types", response_model=FeeType)
async def create_fee_type(
    fee_type: FeeTypeCreate,
    current_user: User = Depends(require_role([UserRole.ADMIN, UserRole.ACCOUNTANT]))
):
    """Create fee type"""
    fee_type_obj = FeeType(**fee_type.model_dump())
    doc = fee_type_obj.model_dump()
    doc['created_at'] = doc['created_at'].isoformat()
    
    await db.fee_types.insert_one(doc)
    return fee_type_obj

@api_router.get("/fee-types", response_model=List[FeeType])
async def get_fee_types(current_user: User = Depends(get_current_user)):
    """Get all fee types"""
    fee_types = await db.fee_types.find({}, {"_id": 0}).to_list(100)
    
    for fee_type in fee_types:
        if isinstance(fee_type.get('created_at'), str):
            fee_type['created_at'] = datetime.fromisoformat(fee_type['created_at'])
    
    return [FeeType(**fee_type) for fee_type in fee_types]

@api_router.post("/fee-structures", response_model=FeeStructure)
async def create_fee_structure(
    fee_structure: FeeStructureCreate,
    current_user: User = Depends(require_role([UserRole.ADMIN, UserRole.ACCOUNTANT]))
):
    """Create fee structure"""
    fee_structure_obj = FeeStructure(**fee_structure.model_dump())
    doc = fee_structure_obj.model_dump()
    doc['created_at'] = doc['created_at'].isoformat()
    if doc.get('due_date'):
        doc['due_date'] = doc['due_date'].isoformat()
    
    await db.fee_structures.insert_one(doc)
    return fee_structure_obj

@api_router.get("/fee-structures", response_model=List[FeeStructure])
async def get_fee_structures(
    class_id: Optional[str] = None,
    school_year_id: Optional[str] = None,
    current_user: User = Depends(get_current_user)
):
    """Get fee structures"""
    query = {}
    if class_id:
        query["class_id"] = class_id
    if school_year_id:
        query["school_year_id"] = school_year_id
    
    structures = await db.fee_structures.find(query, {"_id": 0}).to_list(1000)
    
    for structure in structures:
        if isinstance(structure.get('created_at'), str):
            structure['created_at'] = datetime.fromisoformat(structure['created_at'])
        if structure.get('due_date') and isinstance(structure['due_date'], str):
            structure['due_date'] = datetime.fromisoformat(structure['due_date'])
    
    return [FeeStructure(**structure) for structure in structures]

@api_router.post("/invoices", response_model=Invoice)
async def create_invoice(
    invoice: InvoiceCreate,
    current_user: User = Depends(require_role([UserRole.ADMIN, UserRole.ACCOUNTANT]))
):
    """Create invoice"""
    invoice_obj = Invoice(**invoice.model_dump())
    doc = invoice_obj.model_dump()
    doc['created_at'] = doc['created_at'].isoformat()
    doc['updated_at'] = doc['updated_at'].isoformat()
    doc['issue_date'] = doc['issue_date'].isoformat()
    doc['due_date'] = doc['due_date'].isoformat()
    
    await db.invoices.insert_one(doc)
    return invoice_obj

@api_router.get("/invoices", response_model=List[Invoice])
async def get_invoices(
    student_id: Optional[str] = None,
    status: Optional[InvoiceStatus] = None,
    current_user: User = Depends(get_current_user)
):
    """Get invoices"""
    query = {}
    if student_id:
        query["student_id"] = student_id
    if status:
        query["status"] = status
    
    invoices = await db.invoices.find(query, {"_id": 0}).sort("issue_date", -1).to_list(1000)
    
    for invoice in invoices:
        if isinstance(invoice.get('created_at'), str):
            invoice['created_at'] = datetime.fromisoformat(invoice['created_at'])
        if isinstance(invoice.get('updated_at'), str):
            invoice['updated_at'] = datetime.fromisoformat(invoice['updated_at'])
        if isinstance(invoice.get('issue_date'), str):
            invoice['issue_date'] = datetime.fromisoformat(invoice['issue_date'])
        if isinstance(invoice.get('due_date'), str):
            invoice['due_date'] = datetime.fromisoformat(invoice['due_date'])
    
    return [Invoice(**invoice) for invoice in invoices]

@api_router.get("/invoices/{invoice_id}", response_model=Invoice)
async def get_invoice(
    invoice_id: str,
    current_user: User = Depends(get_current_user)
):
    """Get invoice by ID"""
    invoice = await db.invoices.find_one({"id": invoice_id}, {"_id": 0})
    
    if not invoice:
        raise HTTPException(status_code=404, detail="Invoice not found")
    
    if isinstance(invoice.get('created_at'), str):
        invoice['created_at'] = datetime.fromisoformat(invoice['created_at'])
    if isinstance(invoice.get('updated_at'), str):
        invoice['updated_at'] = datetime.fromisoformat(invoice['updated_at'])
    if isinstance(invoice.get('issue_date'), str):
        invoice['issue_date'] = datetime.fromisoformat(invoice['issue_date'])
    if isinstance(invoice.get('due_date'), str):
        invoice['due_date'] = datetime.fromisoformat(invoice['due_date'])
    
    return Invoice(**invoice)

@api_router.put("/invoices/{invoice_id}", response_model=Invoice)
async def update_invoice(
    invoice_id: str,
    updates: dict,
    current_user: User = Depends(require_role([UserRole.ADMIN, UserRole.ACCOUNTANT]))
):
    """Update invoice"""
    updates["updated_at"] = datetime.now(timezone.utc).isoformat()
    
    result = await db.invoices.update_one({"id": invoice_id}, {"$set": updates})
    
    if result.matched_count == 0:
        raise HTTPException(status_code=404, detail="Invoice not found")
    
    invoice = await db.invoices.find_one({"id": invoice_id}, {"_id": 0})
    
    if isinstance(invoice.get('created_at'), str):
        invoice['created_at'] = datetime.fromisoformat(invoice['created_at'])
    if isinstance(invoice.get('updated_at'), str):
        invoice['updated_at'] = datetime.fromisoformat(invoice['updated_at'])
    if isinstance(invoice.get('issue_date'), str):
        invoice['issue_date'] = datetime.fromisoformat(invoice['issue_date'])
    if isinstance(invoice.get('due_date'), str):
        invoice['due_date'] = datetime.fromisoformat(invoice['due_date'])
    
    return Invoice(**invoice)

@api_router.post("/payments", response_model=Payment)
async def create_payment(
    payment: PaymentCreate,
    current_user: User = Depends(require_role([UserRole.ADMIN, UserRole.ACCOUNTANT]))
):
    """Record payment"""
    payment_obj = Payment(**payment.model_dump())
    doc = payment_obj.model_dump()
    doc['created_at'] = doc['created_at'].isoformat()
    doc['payment_date'] = doc['payment_date'].isoformat()
    
    await db.payments.insert_one(doc)
    
    # Update invoice paid amount and status
    invoice = await db.invoices.find_one({"id": payment.invoice_id}, {"_id": 0})
    if invoice:
        new_paid_amount = invoice['paid_amount'] + payment.amount
        new_status = InvoiceStatus.PAID if new_paid_amount >= invoice['total_amount'] else InvoiceStatus.PARTIALLY_PAID
        
        await db.invoices.update_one(
            {"id": payment.invoice_id},
            {"$set": {
                "paid_amount": new_paid_amount,
                "status": new_status,
                "updated_at": datetime.now(timezone.utc).isoformat()
            }}
        )
    
    return payment_obj

@api_router.get("/payments", response_model=List[Payment])
async def get_payments(
    student_id: Optional[str] = None,
    invoice_id: Optional[str] = None,
    current_user: User = Depends(get_current_user)
):
    """Get payments"""
    query = {}
    if student_id:
        query["student_id"] = student_id
    if invoice_id:
        query["invoice_id"] = invoice_id
    
    payments = await db.payments.find(query, {"_id": 0}).sort("payment_date", -1).to_list(1000)
    
    for payment in payments:
        if isinstance(payment.get('created_at'), str):
            payment['created_at'] = datetime.fromisoformat(payment['created_at'])
        if isinstance(payment.get('payment_date'), str):
            payment['payment_date'] = datetime.fromisoformat(payment['payment_date'])
    
    return [Payment(**payment) for payment in payments]

@api_router.post("/income", response_model=Income)
async def create_income(
    income: IncomeCreate,
    current_user: User = Depends(require_role([UserRole.ADMIN, UserRole.ACCOUNTANT]))
):
    """Record income"""
    income_obj = Income(**income.model_dump())
    doc = income_obj.model_dump()
    doc['created_at'] = doc['created_at'].isoformat()
    doc['date'] = doc['date'].isoformat()
    
    await db.income.insert_one(doc)
    return income_obj

@api_router.get("/income", response_model=List[Income])
async def get_income(
    date_from: Optional[str] = None,
    date_to: Optional[str] = None,
    category: Optional[IncomeCategory] = None,
    current_user: User = Depends(require_role([UserRole.ADMIN, UserRole.ACCOUNTANT]))
):
    """Get income records"""
    query = {}
    if category:
        query["category"] = category
    
    if date_from or date_to:
        query["date"] = {}
        if date_from:
            query["date"]["$gte"] = date_from
        if date_to:
            query["date"]["$lte"] = date_to
    
    income_records = await db.income.find(query, {"_id": 0}).sort("date", -1).to_list(1000)
    
    for income in income_records:
        if isinstance(income.get('created_at'), str):
            income['created_at'] = datetime.fromisoformat(income['created_at'])
        if isinstance(income.get('date'), str):
            income['date'] = datetime.fromisoformat(income['date'])
    
    return [Income(**income) for income in income_records]

@api_router.post("/expenses", response_model=Expense)
async def create_expense(
    expense: ExpenseCreate,
    current_user: User = Depends(require_role([UserRole.ADMIN, UserRole.ACCOUNTANT]))
):
    """Record expense"""
    expense_obj = Expense(**expense.model_dump())
    doc = expense_obj.model_dump()
    doc['created_at'] = doc['created_at'].isoformat()
    doc['date'] = doc['date'].isoformat()
    
    await db.expenses.insert_one(doc)
    return expense_obj

@api_router.get("/expenses", response_model=List[Expense])
async def get_expenses(
    date_from: Optional[str] = None,
    date_to: Optional[str] = None,
    category: Optional[ExpenseCategory] = None,
    current_user: User = Depends(require_role([UserRole.ADMIN, UserRole.ACCOUNTANT]))
):
    """Get expense records"""
    query = {}
    if category:
        query["category"] = category
    
    if date_from or date_to:
        query["date"] = {}
        if date_from:
            query["date"]["$gte"] = date_from
        if date_to:
            query["date"]["$lte"] = date_to
    
    expense_records = await db.expenses.find(query, {"_id": 0}).sort("date", -1).to_list(1000)
    
    for expense in expense_records:
        if isinstance(expense.get('created_at'), str):
            expense['created_at'] = datetime.fromisoformat(expense['created_at'])
        if isinstance(expense.get('date'), str):
            expense['date'] = datetime.fromisoformat(expense['date'])
    
    return [Expense(**expense) for expense in expense_records]

@api_router.get("/financial-reports")
async def get_financial_reports(
    date_from: Optional[str] = None,
    date_to: Optional[str] = None,
    current_user: User = Depends(require_role([UserRole.ADMIN, UserRole.ACCOUNTANT]))
):
    """Get financial summary report"""
    query = {}
    if date_from or date_to:
        query["date"] = {}
        if date_from:
            query["date"]["$gte"] = date_from
        if date_to:
            query["date"]["$lte"] = date_to
    
    # Get income
    income_records = await db.income.find(query, {"_id": 0}).to_list(10000)
    total_income = sum(record['amount'] for record in income_records)
    
    # Get expenses
    expense_records = await db.expenses.find(query, {"_id": 0}).to_list(10000)
    total_expenses = sum(record['amount'] for record in expense_records)
    
    # Get fee collection
    payment_query = {}
    if date_from or date_to:
        payment_query["payment_date"] = {}
        if date_from:
            payment_query["payment_date"]["$gte"] = date_from
        if date_to:
            payment_query["payment_date"]["$lte"] = date_to
    
    payments = await db.payments.find(payment_query, {"_id": 0}).to_list(10000)
    total_fee_collected = sum(payment['amount'] for payment in payments)
    
    # Get pending fees
    pending_invoices = await db.invoices.find({
        "status": {"$in": [InvoiceStatus.PENDING, InvoiceStatus.PARTIALLY_PAID, InvoiceStatus.OVERDUE]}
    }, {"_id": 0}).to_list(10000)
    total_pending = sum(invoice['total_amount'] - invoice['paid_amount'] for invoice in pending_invoices)
    
    return {
        "total_income": total_income,
        "total_expenses": total_expenses,
        "total_fee_collected": total_fee_collected,
        "total_pending_fees": total_pending,
        "net_profit": total_income - total_expenses,
        "income_by_category": {},
        "expenses_by_category": {}
    }

# Include the router in the main app
app.include_router(api_router)

# Mount static files for uploads
app.mount("/api/uploads", StaticFiles(directory=str(UPLOAD_DIR)), name="uploads")

app.add_middleware(
    CORSMiddleware,
    allow_credentials=True,
    allow_origins=os.environ.get('CORS_ORIGINS', '*').split(','),
    allow_methods=["*"],
    allow_headers=["*"],
)

@app.on_event("shutdown")
async def shutdown_db_client():
    client.close()

if __name__ == "__main__":
    import uvicorn
    uvicorn.run(app, host="0.0.0.0", port=8001)
