# Bantay Bayanihan - Dependencies and Requirements

## System Requirements

- **PHP Version**: 7.4 or higher (PHP 8.0+ recommended)
- **Database**: MariaDB 10.3+ or MySQL 5.7+
- **Web Server**: Apache 2.4+ (via XAMPP) or Nginx
- **Required PHP Extensions**:
  - `pdo_mysql` - Database connectivity
  - `curl` - API requests (Gemini AI)
  - `json` - JSON encoding/decoding
  - `mbstring` - String handling
  - `openssl` - Password hashing and encryption
  - `fileinfo` - File upload validation
  - `session` - Session management

## PHP Dependencies (Composer)

Install via Composer:
```bash
composer install
```

### Required Packages:
- **PHPMailer 7.x** - Email sending functionality
  - Used for: Drill registration confirmation emails
  - Location: `/vendor/`

## External JavaScript Libraries (CDN)

These are loaded via CDN and do not need to be installed:

- **Leaflet.js 1.9.4** - Interactive maps
  - URL: https://unpkg.com/leaflet@1.9.4/
  - Used in: Map views and evacuation center displays

- **Font Awesome 6.5.0** - Icon library
  - URL: https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/
  - Used in: All pages for icons

- **Canvas Confetti 1.6.0** - Celebration animations
  - URL: https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/
  - Used in: Gamification features

## CSS Frameworks

- Custom design system using CSS custom properties (variables)
- Location: `/assets/css/design-system.css`
- Additional stylesheets:
  - `/assets/css/style.css`
  - `/assets/css/admin.css`
  - `/assets/css/responder.css`

## External API Services

### Required:
- **Google Gemini AI API** - Chatbot functionality
  - Requires: API key (set in `.env` as `GEMINI_API_KEY`)
  - Model: `gemini-2.5-flash`
  - Documentation: https://ai.google.dev/docs

### Optional:
- **Gmail SMTP** - Email notifications
  - Requires: Gmail App Password (set in `.env`)
  - Configuration: `SMTP_ENABLED`, `SMTP_USERNAME`, `SMTP_PASSWORD`
  - Note: Can be disabled if email notifications are not needed

## Database Setup

1. Create a MariaDB/MySQL database
2. Import the initial schema:
   ```bash
   mysql -u username -p database_name < db/bantay_bayanihan.sql
   ```

## Environment Configuration

1. Copy `.env.example` to `.env`:
   ```bash
   cp .env.example .env
   ```

2. Configure the following required values:
   - `DB_HOST`, `DB_NAME`, `DB_USERNAME`, `DB_PASSWORD` - Database credentials
   - `APP_SECRET` - Random string for security (generate with: `openssl rand -base64 32`)
   - `GEMINI_API_KEY` - Google Gemini API key (for chatbot)

3. Optional configuration:
   - `SMTP_ENABLED`, `SMTP_USERNAME`, `SMTP_PASSWORD` - Email notifications
   - `APP_ENV` - Set to "production" or "development"
   - `APP_DEBUG` - Set to `true` for development, `false` for production

## File Permissions

Ensure the following directories are writable by the web server:

```
uploads/              - Main upload directory
uploads/emergency-photos/  - Emergency report photos
uploads/certifications/    - Generated certifications
vendor/                    - Composer dependencies (during deployment)
```

## Security Requirements

- **HTTPS**: Required for production
- **Password Hashing**: Using PHP's `password_hash()` with `PASSWORD_DEFAULT`
- **CSRF Protection**: Token-based protection for forms
- **SQL Injection Prevention**: PDO prepared statements
- **XSS Prevention**: `htmlspecialchars()` on all outputs
- **Session Security**: Session regeneration after login

## Deployment Checklist

- [ ] Install PHP 7.4+ (8.0+ recommended)
- [ ] Install and configure Apache/Nginx
- [ ] Install and configure MariaDB/MySQL
- [ ] Install Composer dependencies: `composer install`
- [ ] Create `.env` file from `.env.example`
- [ ] Configure database credentials in `.env`
- [ ] Import database schema
- [ ] Set file permissions for uploads directory
- [ ] Configure SSL/HTTPS
- [ ] Set `APP_ENV=production` and `APP_DEBUG=false`
- [ ] Generate and set `APP_SECRET`
- [ ] Add `GEMINI_API_KEY` (if using chatbot)
- [ ] Configure SMTP settings (if using email notifications)
- [ ] Test login and registration
- [ ] Verify all API endpoints work
- [ ] Remove test files from production

## Development Tools (Optional)

- **Git** - Version control
- **Composer** - PHP dependency manager
- **phpMyAdmin** - Database management (via XAMPP)
- **Postman** - API testing
