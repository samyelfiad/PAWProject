# Quick Setup Guide - Algiers University Attendance System

## ⚡ Quick Start (5 minutes)

### Step 1: Initialize Database
1. Open browser: `http://localhost/PawProject/setup.php`
2. You'll see: "Database Setup Complete!"
3. Note down the admin credentials shown

### Step 2: Login
1. Go to: `http://localhost/PawProject/`
2. Login with:
   - **Email**: `admin@univ-alger.dz`
   - **Password**: `admin123`

### Step 3: Create Sample Data

#### Add a Professor
1. Go to **Manage Professors**
2. Click **Add New Professor**
3. Fill in:
   - First Name: `Ahmed`
   - Last Name: `Benali`
   - Email: `ahmed.benali@univ-alger.dz`
   - Password: `Prof1234`
4. Click **Add Professor**

#### Add Students
1. Go to **Student Management**
2. Click **Add New Student** (repeat 3 times)
3. Add students:
   
   **Student 1:**
   - First Name: `Fatima`
   - Last Name: `Zahra`
   - Email: `fatima.zahra@univ-alger.dz`
   - Group: `G1`
   - Password: `Pass1234`
   
   **Student 2:**
   - First Name: `Mohammed`
   - Last Name: `Amin`
   - Email: `mohammed.amin@univ-alger.dz`
   - Group: `G1`
   - Password: `Pass1234`
   
   **Student 3:**
   - First Name: `Leila`
   - Last Name: `Hassan`
   - Email: `leila.hassan@univ-alger.dz`
   - Group: `G2`
   - Password: `Pass1234`

#### Create a Course
1. Go to **Manage Courses**
2. Click **New Course**
3. Fill in:
   - Course Name: `Web Programming`
   - Select Professor: `Ahmed Benali`
4. Click **Create**

#### Add Student to Course
1. In course details, click **Add Student**
2. Select students: `Fatima Zahra`, `Mohammed Amin`, `Leila Hassan`
3. Click **Add to Course**

### Step 4: Test Attendance Flow

#### As Professor (Ahmed Benali):
1. Logout as admin (click logout)
2. Login as: `ahmed.benali@univ-alger.dz` / `Prof1234`
3. Click **My Courses**
4. Select "Web Programming"
5. Click **New Session**
6. Fill in:
   - Date: Today's date
   - Time: 10:00
   - Type: Cours
   - Group: All
7. Click **Create & Start Attendance**
8. Mark attendance:
   - Fatima: Present
   - Mohammed: Absent
   - Leila: Present
9. Click **Save Changes**

#### As Student (Mohammed Amin):
1. Logout
2. Login as: `mohammed.amin@univ-alger.dz` / `Pass1234`
3. Click **My Courses**
4. Click **View Details** on "Web Programming"
5. See your attendance (should show Absent)
6. Click **Submit** to submit justification
7. Enter reason: "I was sick"
8. Optionally upload document
9. Click **Submit**

#### Back as Admin:
1. Logout and login as admin
2. Go to **Manage Justifications**
3. See pending justification from Mohammed
4. Click **Approve** or **Reject**
5. Go to **Attendance Summary** to verify updates

---

## 🎯 Testing Scenarios

### Scenario 1: Simple Attendance
1. Create session
2. Mark all present
3. Verify records in student dashboard

### Scenario 2: Justification Workflow
1. Mark student absent
2. Student submits justification
3. Admin approves
4. Check that status updates to "justified"

### Scenario 3: Import Students
1. Go to **Import Students**
2. Download CSV template
3. Fill in sample data
4. Upload CSV
5. Verify students appear in student list

### Scenario 4: Export Data
1. Go to **Export Reports**
2. Click export buttons
3. Verify CSV files download correctly

### Scenario 5: View Statistics
1. Go to **Statistics**
2. Check charts render correctly
3. Verify numbers match actual data

---

## 📋 Test Users Credentials

| Role | Email | Password | Purpose |
|------|-------|----------|---------|
| Admin | admin@univ-alger.dz | admin123 | System administration |
| Professor | ahmed.benali@univ-alger.dz | Prof1234 | Mark attendance |
| Student | fatima.zahra@univ-alger.dz | Pass1234 | View attendance |
| Student | mohammed.amin@univ-alger.dz | Pass1234 | Submit justifications |
| Student | leila.hassan@univ-alger.dz | Pass1234 | View attendance |

---

## 🔧 Troubleshooting

### Issue: "Connection failed: SQLSTATE[HY000]"
**Solution**: 
- Check MariaDB/MySQL is running
- Verify database credentials in `includes/db.php`
- Run setup.php again

### Issue: "Login page loops"
**Solution**:
- Clear browser cache and cookies
- Try incognito/private browser window
- Check PHP session directory permissions

### Issue: "Can't upload files"
**Solution**:
- Create `uploads/justifications/` folder
- Set permissions: `chmod 755 uploads/`
- Verify file size < 5MB

### Issue: "Charts not displaying"
**Solution**:
- Check browser console for JavaScript errors
- Verify Chart.js is loaded from CDN
- Check data in database

---

## 📱 Mobile Testing

1. Open on phone: `http://[your-ip]/PawProject/`
2. Try login
3. Test responsive menu
4. Check attendance marking on mobile
5. Test justification form

---

## ✅ Pre-Submission Checklist

- [ ] Database initialized (setup.php runs)
- [ ] Admin login works
- [ ] Professor can create sessions
- [ ] Professor can mark attendance
- [ ] Students can view attendance
- [ ] Students can submit justifications
- [ ] Admin can approve justifications
- [ ] Import/export functions work
- [ ] Statistics page shows data
- [ ] System is responsive on mobile
- [ ] All pages load without errors

---

## 🚀 Deployment Tips

### For Local Testing
```bash
# Ensure WAMP/XAMPP is running
# Navigate to: http://localhost/PawProject/setup.php
# Then: http://localhost/PawProject/
```

### For Server Deployment
1. Upload files to web server
2. Create database on hosting
3. Update credentials in `includes/db.php`
4. Run `setup.php` on server
5. Set proper file permissions
6. Enable uploads directory

---

## 📞 Support

For issues during setup, check:
1. README.md - Comprehensive documentation
2. Browser console (F12) - JavaScript errors
3. Database logs - SQL errors
4. PHP error logs - Server errors

---

**Good luck with your project! 🎓**

Deadline: **28 November 2025**
