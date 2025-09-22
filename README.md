# Student Complaint Management System

A comprehensive PHP & MySQL-based web application designed to digitalize and streamline the process of handling student complaints in educational institutions.

## 🌟 Features

### Authentication System
- **Secure Login & Registration** - Students can register and login securely
- **Role-Based Access Control** - Three user roles: Student, Staff, Admin
- **Password Hashing** - Secure password storage using PHP's password_hash()

### Student Portal
- **Submit Complaints** - Easy-to-use form with categories (Academic, Hostel, Finance, Library, IT, General)
- **Track Progress** - Real-time status updates (Pending, In Progress, Resolved, Rejected)
- **View History** - Complete history of submitted complaints with filtering options
- **Interactive Dashboard** - Visual statistics and recent complaint overview

### Staff Portal
- **Assigned Complaints** - View and manage complaints assigned by administrators
- **Status Updates** - Update complaint status and add resolution notes
- **Priority Management** - Focus on pending and in-progress complaints
- **Performance Tracking** - View resolution statistics and workload

### Admin Panel
- **Comprehensive Dashboard** - Overview of all complaints with interactive charts
- **Complaint Management** - Assign complaints to staff, update status, add remarks
- **User Management** - Add, edit, and delete users across all roles
- **Advanced Reporting** - Generate and export detailed reports (CSV format)
- **Analytics** - Visual charts showing trends, categories, and performance metrics

### Responsive Design
- **Bootstrap 5** - Modern, mobile-friendly interface
- **Custom CSS** - Beautiful gradients, animations, and responsive layouts
- **Font Awesome Icons** - Professional iconography throughout the system
- **Dark Sidebar** - Elegant navigation with role-specific menus

## 🛠️ Technology Stack

- **Backend**: PHP 7.4+ with PDO for database operations
- **Database**: MySQL 5.7+ / MariaDB
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Framework**: Bootstrap 5.3.0
- **Icons**: Font Awesome 6.0.0
- **Charts**: Chart.js for data visualization
- **Server**: Compatible with XAMPP, WAMP, LAMP, or any PHP server

## 📋 Database Schema

### Users Table (SQLite)
```sql
CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL,
    email TEXT UNIQUE NOT NULL,
    password TEXT NOT NULL,
    role TEXT CHECK(role IN ('student','admin','staff')) DEFAULT 'student',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

### Complaints Table (SQLite)
```sql
CREATE TABLE complaints (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    student_id INTEGER NOT NULL,
    category TEXT NOT NULL,
    description TEXT NOT NULL,
    status TEXT CHECK(status IN ('pending','in_progress','resolved','rejected')) DEFAULT 'pending',
    assigned_to INTEGER NULL,
    admin_remarks TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL
);
```

### Notifications Table (SQLite)
```sql
CREATE TABLE notifications (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    message TEXT NOT NULL,
    is_read INTEGER DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

## 🚀 Installation & Setup (XAMPP)

### Prerequisites
- **XAMPP** installed and running (Apache service started)
- PHP 7.4+ (included with XAMPP)
- SQLite extension enabled (included by default in XAMPP)
- **No MySQL needed!** - Uses SQLite database file

### Step 1: Setup Project
1. Ensure XAMPP is installed and Apache is running
2. Place project files in `C:\xampp\htdocs\student_complaient`
3. Open browser and go to `http://localhost/student_complaient`

### Step 2: Database Setup (One-Click)
1. Navigate to `http://localhost/student_complaient/setup_database.php`
2. The system will automatically:
   - Create the SQLite database file (`database.db`) in your project folder
   - Create all required tables with proper relationships
   - Insert default admin and staff users
   - Display success message with login credentials

### Step 3: Login & Start Using
**Default Credentials:**
- **Admin**: admin@example.com / admin123
- **Staff**: staff@example.com / staff123
- **Students**: Can register using the registration form

**Access URLs:**
- **Main System**: `http://localhost/student_complaient`
- **Database Setup**: `http://localhost/student_complaient/setup_database.php`

### 💾 **Database File Information (XAMPP)**
- **File Location**: `C:\xampp\htdocs\student_complaient\database.db`
- **Initial Size**: ~20KB (grows with data)
- **Backup Method**: Simply copy the `database.db` file
- **Portable**: Move the entire project folder to any XAMPP installation
- **No MySQL Required**: SQLite is built into PHP/XAMPP

## 📁 Project Structure

```
/student_complaient
│── index.php              # Login page
│── register.php           # Student registration
│── logout.php             # Logout functionality
│── setup_database.php     # Database setup script
│
│── /student               # Student portal
│   ├── dashboard.php      # Student dashboard
│   ├── complaint_add.php  # Submit new complaint
│   ├── complaint_view.php # View all complaints
│   └── complaint_details.php # AJAX complaint details
│
│── /admin                 # Admin panel
│   ├── dashboard.php      # Admin dashboard with charts
│   ├── complaints.php     # Manage all complaints
│   ├── complaint_manage.php # AJAX complaint management
│   ├── manage_users.php   # User management
│   └── reports.php        # Reports and analytics
│
│── /staff                 # Staff portal
│   ├── dashboard.php      # Staff dashboard
│   ├── assigned_complaints.php # View assigned complaints
│   ├── complaint_details.php # AJAX complaint details
│   └── complaint_update.php # Update complaint status
│
│── /includes              # Core files
│   ├── config.php         # Database & site configuration
│   └── functions.php      # Common functions
│
│── /assets                # Static assets
│   └── css/
│       └── style.css      # Custom CSS styles
```

## 🎯 User Workflows

### Student Workflow
1. **Register** → Create account with email verification
2. **Login** → Access student dashboard
3. **Submit Complaint** → Choose category and describe issue
4. **Track Progress** → Monitor status updates and remarks
5. **View History** → Access all previous complaints

### Staff Workflow
1. **Login** → Access staff portal
2. **View Assignments** → See complaints assigned by admin
3. **Update Status** → Change status and add resolution notes
4. **Monitor Workload** → Track personal performance metrics

### Admin Workflow
1. **Login** → Access admin panel
2. **Monitor Dashboard** → View system overview and charts
3. **Manage Complaints** → Assign to staff, update status
4. **User Management** → Add/edit/delete users
5. **Generate Reports** → Export data for analysis

## 🔧 Key Features Explained

### Security Features
- **SQL Injection Protection** - Using PDO prepared statements
- **XSS Prevention** - Input sanitization and output encoding
- **Session Management** - Secure session handling
- **Role-Based Access** - Proper authorization checks

### User Experience
- **Responsive Design** - Works on desktop, tablet, and mobile
- **Real-time Updates** - AJAX for smooth interactions
- **Visual Feedback** - Status badges, progress indicators
- **Intuitive Navigation** - Clear, role-specific menus

### Performance Optimizations
- **Efficient Queries** - Optimized database queries with proper indexing
- **Lazy Loading** - Modal content loaded on demand
- **Caching Headers** - Browser caching for static assets
- **Compressed Assets** - Minified CSS and optimized images

## 📊 Reporting & Analytics

### Available Reports
- **Complaint Summary** - Total, pending, resolved statistics
- **Category Analysis** - Breakdown by complaint categories
- **Monthly Trends** - Time-based complaint patterns
- **Staff Performance** - Resolution rates and workload
- **Export Options** - CSV download with date range filters

### Chart Types
- **Doughnut Charts** - Status distribution
- **Line Charts** - Monthly trends
- **Bar Charts** - Category distribution
- **Progress Bars** - Resolution rates

## 🔒 Security Considerations

- All user inputs are sanitized using `htmlspecialchars()`
- Database queries use PDO prepared statements
- Passwords are hashed using `password_hash()` with default algorithm
- Session management with proper timeout handling
- Role-based access control on all sensitive pages

## 🎨 Customization

### Theming
- Modify `assets/css/style.css` for custom styling
- Update color schemes in CSS variables
- Change gradients and animations as needed

### Functionality
- Add new complaint categories in `includes/functions.php`
- Extend user roles by modifying database schema
- Integrate email notifications (SMTP configuration required)

## 🐛 Troubleshooting

### XAMPP-Specific Issues
1. **Apache not starting**
   - Check if port 80 is available (close Skype, IIS, etc.)
   - Try changing Apache port in XAMPP Control Panel
   - Run XAMPP as Administrator

2. **Database file permissions**
   - Ensure XAMPP has write permissions to the project folder
   - Check that `database.db` is created in the project root
   - File location: `C:\xampp\htdocs\student_complaient\database.db`

3. **SQLite not working**
   - SQLite is included by default in XAMPP
   - Check `phpinfo()` to verify SQLite extension is loaded
   - Restart Apache after any PHP configuration changes

### Common Issues
1. **Page not loading**
   - Ensure Apache is running in XAMPP Control Panel
   - Check URL: `http://localhost/student_complaient` (not https)
   - Verify project folder is in `htdocs`

2. **Database errors**
   - Run the setup script first: `/setup_database.php`
   - Check file permissions on the project folder
   - Ensure SQLite extension is enabled

## 📝 License

This project is open source and available under the [MIT License](LICENSE).

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request

## 📞 Support

For support, issues, or feature requests, please create an issue in the GitHub repository or contact the development team.

---

**Built with ❤️ for educational institutions to improve student services and complaint resolution processes.**