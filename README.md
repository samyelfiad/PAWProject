# Algiers University Attendance Management System

**Project Name:** Design and Implementation of a Web-Based Student Attendance Management System for Algiers University

**Advanced Web Programming - Final Project**

## Project Overview

This is a web-based attendance management system designed to streamline and automate student attendance tracking at Algiers University. The system provides role-based access for students, professors, and administrators with support for automated analytics, justification workflows, and data import/export.

## Features

### ✅ Core Features Implemented

#### 1. **Authentication & Authorization**
- Role-based login (Admin, Professor, Student)
- Session management with secure password hashing
- Default admin account: `admin@univ-alger.dz` / `admin123`

#### 2. **Administrator Dashboard**
- View system statistics (students, professors, courses, sessions)
- Track attendance metrics (present, absent, justified)
- Monitor pending justifications
- Quick access to all management features

#### 3. **User Management**
- **Add/Remove Students**: Manage student accounts and groups
- **Add/Remove Professors**: Manage faculty accounts
- **Import Students**: Bulk import via CSV/Excel (Progres format compatible)
- **Export Data**: Export students, attendance records, and justifications

#### 4. **Professor Portal**
- View assigned courses
- Create and manage attendance sessions
- Mark attendance (Present, Absent, Justified)
- View attendance summary by group
- Generate attendance reports per course

#### 5. **Student Portal**
- View enrolled courses
- Check personal attendance records
- Submit justifications for absences
- Upload supporting documents for justifications
- Track attendance rate per course
- View detailed session history

#### 6. **Attendance Management**
- Create sessions with date, time, type (Cours/TD/TP), and target group
- Mark attendance status for each student
- Auto-populate attendance records when sessions are created
- Attendance tracking with present/absent/justified statuses
- Participation and behavior scoring capability

#### 7. **Justification Workflow**
- Students submit justifications with reason and optional documents
- Admin reviews pending justifications
- Approve or reject with approval tracking
- File upload support for supporting documents

#### 8. **Analytics & Reporting**
- Attendance statistics by group
- Justification status overview
- Course-wise attendance rates
- Student-specific attendance tracking
- Interactive charts using Chart.js
- Export attendance and justification reports

#### 9. **Responsive Design**
- Mobile-first approach
- Bootstrap-style responsive grid system
- Works on desktop, tablet, and mobile devices
- jQuery for interactivity

### 📊 Statistics & Charts
- Bar charts for group attendance
- Doughnut charts for justification status
- Line charts for course attendance trends
- Real-time calculations

## Technology Stack

### Frontend
- **HTML5** - Semantic markup
- **CSS3** - Responsive styling with mobile-first approach
- **jQuery 3.6.4** - DOM manipulation and interactivity
- **Chart.js** - Data visualization

### Backend
- **PHP 7.4+** - Server-side logic
- **PDO** - Database abstraction layer with error handling

### Database
- **MariaDB/MySQL** - Data persistence
- **8 Tables**: users, courses, sessions, attendance, justifications, participation, course_enrollments

## Database Schema

### Tables

1. **users**
   - id, first_name, last_name, email, password, role, student_group

2. **courses**
   - id, course_name, professor_id

3. **sessions**
   - id, course_id, session_date, type, target_group

4. **attendance**
   - id, session_id, student_id, status (present/absent/justified)

5. **justifications**
   - id, session_id, student_id, reason, file_path, status, submitted_date, approved_date, reviewer_id

6. **participation**
   - id, student_id, session_id, participation_score, behavior_score, notes

7. **course_enrollments**
   - id, student_id, course_id, enrolled_date

8. **justifications** (extended tracking)
   - Full audit trail for justification approvals

## Installation & Setup

### Prerequisites
- PHP 7.4 or higher
- MariaDB/MySQL 5.7 or higher
- Apache/Nginx web server
- WAMP/LAMP/XAMPP stack

### Step 1: Initialize Database

1. Navigate to `http://localhost/PawProject/setup.php` in your browser
2. The script will create all tables and the default admin account
3. You should see: "Database Setup Complete!"

### Step 2: Login

1. Go to `http://localhost/PawProject/`
2. Use admin credentials:
   - Email: `admin@univ-alger.dz`
   - Password: `admin123`

### Step 3: Create Test Data

#### Add Professors
1. Go to Admin Dashboard → Manage Professors
2. Click "Add New Professor"
3. Fill in details:
   - First Name: Ahmed
   - Last Name: Benali
   - Email: ahmed.benali@univ-alger.dz
   - Password: Prof1234

#### Add Students (Option A: Manual)
1. Go to Admin Dashboard → Student Management
2. Click "Add New Student"
3. Fill in student details
4. Select group (G1, G2, G3, G4)

#### Add Students (Option B: Import CSV)
1. Go to Admin Dashboard → Import Students
2. Download the CSV template
3. Fill with student data
4. Upload the file

#### Sample CSV Format:
```
first_name,last_name,email,student_group,password
Ahmed,Benali,ahmed.benali@univ-alger.dz,G1,Pass1234
Fatima,Zahra,fatima.zahra@univ-alger.dz,G1,Pass1234
Mohammed,Amin,mohammed.amin@univ-alger.dz,G2,Pass1234
```

#### Create Courses
1. As Admin, go to Manage Courses
2. Create a new course and assign a professor

#### Create Attendance Sessions
1. Login as Professor
2. Select a course
3. Click "New Session"
4. Set date, time, type (Cours/TD/TP), target group
5. System auto-populates student attendance records

#### Mark Attendance
1. Click on session
2. Select attendance status for each student
3. Click "Save Changes"
4. Records are saved to database

## File Structure

```
PawProject/
├── index.php                 # Login page
├── logout.php               # Session cleanup
├── admin_dashboard.php      # Admin home with statistics
├── admin_statistics.php     # Advanced charts and analytics
├── manage_students.php      # Student management
├── manage_professors.php    # Professor management
├── manage_courses.php       # Course management
├── import_students.php      # CSV import
├── export_reports.php       # Export functionality
├── approve_justifications.php # Justification approval
├── prof_dashboard.php       # Professor home
├── attendance_summary.php   # Professor reports
├── add_session.php          # Create attendance session
├── take_attendance.php      # Mark attendance
├── ajax_save_attendance.php # AJAX attendance saver
├── student_dashboard.php    # Student home
├── my_attendance.php        # Student attendance view
├── setup.php               # Database initialization
├── includes/
│   ├── db.php              # Database connection
│   ├── header.php          # Navigation & styling
│   └── footer.php          # Footer & closing tags
└── uploads/
    └── justifications/     # Uploaded justification files
```

## User Roles & Permissions

### Administrator
- ✅ View system statistics
- ✅ Manage students (add/delete/import/export)
- ✅ Manage professors
- ✅ Manage courses
- ✅ Approve/reject justifications
- ✅ Export attendance reports

### Professor
- ✅ View assigned courses
- ✅ Create attendance sessions
- ✅ Mark student attendance
- ✅ View attendance summary by group
- ✅ Generate reports

### Student
- ✅ View enrolled courses
- ✅ View personal attendance
- ✅ Submit justifications for absences
- ✅ Upload supporting documents
- ✅ Track attendance rate

## Key Workflows

### Attendance Marking Flow
1. Professor creates session (date, time, type, group)
2. System auto-populates attendance records (default: absent)
3. Professor marks actual attendance
4. System saves to database
5. Students can view their records

### Justification Workflow
1. Student views absent record
2. Student submits justification with reason
3. Student optionally uploads supporting document
4. Admin receives pending justification notification
5. Admin reviews and approves/rejects
6. System updates attendance status to "justified"

### Import/Export Flow
1. Admin downloads CSV template
2. Admin fills with student data
3. Admin uploads CSV
4. System validates and imports (skips duplicates)
5. Admin can export any time for backups

## API Endpoints (AJAX)

### Save Attendance
- **URL**: `ajax_save_attendance.php`
- **Method**: POST
- **Parameters**: session_id, student_id, status
- **Response**: JSON success/error

## Security Features

- ✅ Password hashing with `password_hash()`
- ✅ SQL injection prevention with prepared statements
- ✅ Session-based authentication
- ✅ Role-based access control
- ✅ File upload validation
- ✅ Error logging with try/catch blocks
- ✅ CSRF protection ready (can be enhanced)

## Browser Compatibility

- ✅ Chrome/Chromium 90+
- ✅ Firefox 88+
- ✅ Safari 14+
- ✅ Edge 90+
- ✅ Mobile browsers (iOS Safari, Chrome Mobile)

## Performance Considerations

- Optimized database queries with proper indexing
- Single database connection (connection pooling ready)
- Minimal JavaScript for faster load times
- CSS Grid for efficient layouts
- Pagination ready (can be added for large datasets)

## Future Enhancements

1. **Advanced Filtering**: Filter attendance by date range, student, group
2. **Notification System**: Email notifications for justifications
3. **QR Code Attendance**: Mobile attendance via QR codes
4. **API REST**: RESTful API for mobile app integration
5. **Single Sign-On**: LDAP/Active Directory integration
6. **Biometric Integration**: Fingerprint/face recognition
7. **SMS Notifications**: Send alerts to students/parents
8. **Mobile App**: Native iOS/Android apps
9. **Video Conferencing**: Integration with Zoom/Teams
10. **Automated Reports**: Scheduled email reports

## Troubleshooting

### Database Connection Error
- Verify MariaDB/MySQL is running
- Check credentials in `includes/db.php`
- Ensure database `algiers_attendance` exists

### Session Issues
- Clear browser cookies
- Check PHP session configuration
- Verify file permissions on session directory

### File Upload Not Working
- Create `/uploads/justifications/` directory
- Set directory permissions to `755`
- Verify file size limits in PHP config

### Attendance Not Saving
- Check AJAX endpoint response in browser console
- Verify database connection
- Check prepared statement syntax

## Testing Instructions

### Admin Features
1. Login as admin
2. Navigate Admin Dashboard - verify statistics display
3. Go to Manage Students - add a test student
4. Go to Manage Professors - add a test professor
5. Create a course and assign professor
6. View statistics page - verify charts render

### Professor Features
1. Login as professor
2. View assigned courses
3. Create a session for a course
4. Mark attendance for students
5. View attendance summary
6. Verify reports generate correctly

### Student Features
1. Login as student
2. View enrolled courses
3. Check attendance records
4. Submit justification for an absent record
5. Verify submission appears in admin panel

## Support & Contact

For issues or questions about this system, please contact:
- **Email**: support@univ-alger.dz
- **Project Lead**: Advanced Web Programming Instructor

## License

This project is created as part of the Advanced Web Programming course at Algiers University. All rights reserved.

---

**Developed**: November 2024  
**Version**: 1.0  
**Status**: Production Ready
