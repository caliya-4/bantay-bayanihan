# 🛡️ Bantay Bayanihan - Disaster Preparedness & Emergency Response System

> **Empowering Baguio City communities through technology-driven emergency preparedness**

A comprehensive web-based disaster preparedness and emergency response platform designed for Baguio City, Philippines. The system connects barangay responders, administrators, and citizens through real-time emergency management, evacuation planning, and community preparedness tools.

---

## 🌟 Features

### For Emergency Responders
- 🚨 **Emergency Reporting** - Submit real-time emergency incidents with photo documentation
- 📋 **Drill Management** - Create, manage, and participate in emergency preparedness drills
- 📊 **Barangay Dashboard** - View community-specific statistics and alerts
- 🗺️ **Evacuation Centers** - Locate nearest evacuation sites with interactive maps
- 📢 **Announcements** - Receive critical updates and emergency alerts

### For Administrators
- 📈 **Analytics Dashboard** - Monitor city-wide emergency response metrics
- 👥 **Responder Management** - Approve and manage barangay responder accounts
- 🎯 **Performance Tracking** - Track response times and drill participation
- 🗺️ **Road Closures** - Manage and publish road closure information
- 📊 **Comprehensive Reports** - Generate detailed analytics and reports

### For the Community
- 🤖 **AI Chatbot (Bantay Bot)** - Get instant answers to emergency preparedness questions
- 🎮 **Gamification** - Earn points and certifications through quiz completion
- 🏆 **Leaderboards** - Compete with other responders in preparedness activities
- 📍 **Interactive Maps** - View evacuation centers, routes, and emergency locations

---

## 🚀 Quick Start

### Prerequisites
- PHP 7.4 or higher (8.0+ recommended)
- MariaDB 10.3+ or MySQL 5.7+
- Apache 2.4+ (via XAMPP for local development)
- Composer

### Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd cbantay-bayanihan
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Configure environment**
   ```bash
   cp .env.example .env
   # Edit .env with your database credentials and API keys
   ```

4. **Set up database**
   ```bash
   mysql -u username -p database_name < db/bantay_bayanihan.sql
   ```

5. **Migrate existing passwords** (if upgrading)
   ```bash
   php migrate-passwords.php
   # Delete this file after migration!
   ```

6. **Start your server** (XAMPP)
   - Start Apache and MySQL in XAMPP Control Panel
   - Navigate to `http://localhost/cbantay-bayanihan`

---

## 📚 Documentation

- **[REQUIREMENTS.md](REQUIREMENTS.md)** - System requirements and dependencies
- **[DEPLOYMENT.md](DEPLOYMENT.md)** - Complete production deployment guide
- **[SECURITY_IMPROVEMENTS.md](SECURITY_IMPROVEMENTS.md)** - Security audit and improvements

---

## 🔐 Security Features

- ✅ **Password Hashing** - bcrypt encryption for all user passwords
- ✅ **CSRF Protection** - Token-based request validation
- ✅ **Session Security** - Automatic session regeneration
- ✅ **SQL Injection Prevention** - PDO prepared statements
- ✅ **XSS Protection** - Output encoding on all user inputs
- ✅ **Environment Variables** - Secure credential management
- ✅ **Security Headers** - XSS, clickjacking, and MIME sniffing protection
- ✅ **File Upload Security** - Prevented PHP execution in uploads

See [SECURITY_IMPROVEMENTS.md](SECURITY_IMPROVEMENTS.md) for complete security documentation.

---

## 🏗️ Architecture

### Technology Stack
- **Backend:** PHP 7.4+ (No framework - lightweight custom implementation)
- **Database:** MariaDB / MySQL
- **Frontend:** HTML5, CSS3, JavaScript (Vanilla)
- **Maps:** Leaflet.js with OpenStreetMap
- **Icons:** Font Awesome 6.5.0
- **AI Integration:** Google Gemini API
- **Email:** PHPMailer 7.x

### Project Structure
```
cbantay-bayanihan/
├── admin/                    # Admin dashboard and management
├── api/                      # RESTful API endpoints
│   ├── admin/               # Admin-specific APIs
│   ├── ai/                  # AI chatbot integration
│   ├── announcements/       # Announcement APIs
│   ├── drills/              # Drill management APIs
│   ├── evacuation/          # Evacuation center APIs
│   └── gamification/        # Quiz and gamification APIs
├── assets/                   # Static resources
│   ├── css/                 # Stylesheets
│   └── js/                  # JavaScript files
├── barangay_responder/       # Responder dashboard and features
├── db/                       # Database schema and migrations
├── includes/                 # Shared PHP components
├── uploads/                  # User-generated content
├── vendor/                   # Composer dependencies
├── .env.example             # Environment configuration template
├── config.php               # Configuration loader
├── db_connect.php           # Database connection
├── index.php                # Public portal
├── login.php                # Authentication
└── register.php             # User registration
```

---

## 🗄️ Database Setup

### Create Database
```sql
CREATE DATABASE bantay_bayanihan CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'bantay_user'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON bantay_bayanihan.* TO 'bantay_user'@'localhost';
FLUSH PRIVILEGES;
```

### Import Schema
```bash
mysql -u bantay_user -p bantay_bayanihan < db/bantay_bayanihan.sql
```

---

## 🔧 Configuration

### Required Environment Variables (`.env`)

```env
# Database
DB_HOST=localhost
DB_NAME=bantay_bayanihan
DB_USERNAME=bantay_user
DB_PASSWORD=secure_password

# Application
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
APP_SECRET=your_random_32_character_secret

# Google Gemini AI (for chatbot)
GEMINI_API_KEY=your_gemini_api_key
GEMINI_MODEL=gemini-2.5-flash

# Email (optional)
SMTP_ENABLED=false
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USERNAME=your_email@gmail.com
SMTP_PASSWORD=your_app_password
```

Generate `APP_SECRET`:
```bash
openssl rand -base64 32
```

---

## 📱 User Roles

### Admin
- Full system access
- Manage emergencies, drills, and responders
- View analytics and generate reports
- Configure evacuation centers and road closures

### Barangay Responder
- Report emergencies with photos
- Participate in drills and trainings
- View barangay-specific data
- Access evacuation maps and routes

### Public User
- View announcements and published drills
- Take certification quizzes
- Use AI chatbot for information
- Access evacuation center information

---

## 🤖 AI Chatbot Integration

The Bantay Bot uses Google Gemini AI to provide:
- Emergency preparedness guidance
- Evacuation procedure information
- Safety tips and best practices
- Real-time question answering

**Setup:**
1. Get API key from [Google AI Studio](https://ai.google.dev/)
2. Add to `.env`: `GEMINI_API_KEY=your_key_here`

---

## 📧 Email Notifications

Optional email features:
- Drill registration confirmations
- Emergency alert notifications
- Account approval notifications

**Setup:**
1. Enable SMTP in `.env`: `SMTP_ENABLED=true`
2. Configure Gmail or other SMTP provider
3. For Gmail: Use App Password (not regular password)

---

## 🎮 Gamification System

Engage responders through:
- **Quiz System** - Test emergency preparedness knowledge
- **Certifications** - Earn certificates for completed training
- **Leaderboards** - Compete with other responders
- **Progress Tracking** - Monitor completed activities

---

## 🗺️ Mapping Features

Powered by Leaflet.js and OpenStreetMap:
- Interactive evacuation center maps
- Nearest site finder using geolocation
- Emergency location plotting
- Road closure visualization
- Evacuation route planning

---

## 🔒 Security Best Practices

### For Administrators
1. Use strong passwords (8+ characters)
2. Never share credentials
3. Regularly update passwords
4. Monitor user activity logs
5. Approve responder registrations carefully

### For Developers
1. Never commit `.env` file
2. Keep dependencies updated
3. Review security advisories
4. Test in development first
5. Follow OWASP guidelines

See [DEPLOYMENT.md](DEPLOYMENT.md) for production security checklist.

---

## 🧪 Testing

### Manual Testing Checklist
- [ ] User registration and login
- [ ] Password hashing verification
- [ ] Emergency report submission
- [ ] Photo upload functionality
- [ ] Drill creation and participation
- [ ] Map interactions
- [ ] Chatbot responses
- [ ] Email notifications (if enabled)
- [ ] CSRF token validation

### API Testing
```bash
# Test database connection
php -r "require 'db_connect.php'; echo 'Connected';"

# Test environment loading
php -r "require 'config.php'; echo env('DB_HOST');"
```

---

## 📦 Dependencies

### PHP (Composer)
- PHPMailer 7.x - Email functionality

### JavaScript (CDN)
- Leaflet.js 1.9.4 - Interactive maps
- Font Awesome 6.5.0 - Icon library
- Canvas Confetti 1.6.0 - Celebration animations

### External Services
- Google Gemini AI - Chatbot functionality
- OpenStreetMap - Map tiles
- Gmail SMTP (optional) - Email delivery

See [REQUIREMENTS.md](REQUIREMENTS.md) for complete list.

---

## 🚀 Deployment

### Free Hosting Options

1. **Oracle Cloud Free Tier** (Recommended)
   - 2 VMs always free
   - 200 GB storage
   - 10 TB/month data transfer
   - Full root access

2. **Railway.app**
   - $5 free credit/month
   - Easy deployment
   - Built-in database

3. **Heroku**
   - Free tier available
   - Git-based deployment
   - Automatic HTTPS

4. **InfinityFree**
   - Completely free
   - PHP and MySQL included
   - No SSH access

See [DEPLOYMENT.md](DEPLOYMENT.md) for complete deployment guide.

---

## 🛠️ Development Workflow

1. **Fork and clone** the repository
2. **Create feature branch** (`git checkout -b feature/amazing-feature`)
3. **Make changes** and test locally
4. **Commit changes** (`git commit -m 'Add amazing feature'`)
5. **Push to branch** (`git push origin feature/amazing-feature`)
6. **Open Pull Request**

### Code Standards
- PSR-12 coding style for PHP
- Semantic HTML5
- CSS custom properties for theming
- Vanilla JavaScript (no frameworks)
- Comment complex logic

---

## 🤝 Contributing

Contributions are welcome! Please read our contributing guidelines and submit pull requests for any improvements.

### Areas for Contribution
- Additional language support
- Enhanced analytics features
- Mobile application development
- Integration with emergency alert systems
- Accessibility improvements
- Additional security features

---

## 📞 Support

For questions or issues:
- 📧 Email: support@yourdomain.com
- 📚 Documentation: See `/docs` directory
- 🐛 Bug Reports: Open an issue on GitHub
- 💬 Community: Join our Discord/Slack channel

---

## 📄 License

This project is licensed under the MIT License - see the LICENSE file for details.

---

## 🙏 Acknowledgments

- **Baguio City Government** - For supporting disaster preparedness innovation
- **Barangay Officials** - For feedback and requirements gathering
- **Open Source Community** - For the amazing libraries we use
- **First Responders** - For their dedication to community safety

---

## 📊 Stats

![GitHub stars](https://img.shields.io/github/stars/your-username/cbantay-bayanihan)
![GitHub forks](https://img.shields.io/github/forks/your-username/cbantay-bayanihan)
![GitHub issues](https://img.shields.io/github/issues/your-username/cbantay-bayanihan)
![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-blue)
![License](https://img.shields.io/badge/License-MIT-green)

---

<div align="center">

**Made with ❤️ for Baguio City**

🛡️ **Bantay Bayanihan** - *Keeping Communities Safe*

</div>
