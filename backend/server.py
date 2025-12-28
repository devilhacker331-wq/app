from fastapi import FastAPI, APIRouter, HTTPException, Depends, status, Header
from fastapi.security import HTTPBearer, HTTPAuthorizationCredentials
from dotenv import load_dotenv
from starlette.middleware.cors import CORSMiddleware
from motor.motor_asyncio import AsyncIOMotorClient
import os
import logging
from pathlib import Path
from typing import List, Optional
from datetime import datetime, timezone

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

# Include the router in the main app
app.include_router(api_router)

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
