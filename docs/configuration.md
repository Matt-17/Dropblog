# Configuration Guide

This guide covers all configuration options available in Dropblog, from basic setup to advanced customization.

## 📁 Configuration File Location

The main configuration file is located at:
```
web/src/Config.php
```

Create it from the template:
```bash
cp web/src/Config.template.php web/src/Config.php
```

## 🔧 Basic Configuration

### Database Settings

```php
// Database connection settings
public const DB_HOST = 'localhost';        // Database host
public const DB_NAME = 'dropblog';         // Database name  
public const DB_USER = 'your_username';    // Database username
public const DB_PASS = 'your_password';    // Database password
public const DB_PORT = 3306;               // Database port (optional)
```

**Connection Options:**
- Use `localhost` for local development
- For remote databases, specify the full hostname or IP
- Port 3306 is MySQL default; adjust if using custom port
- Ensure database exists before running migrations

### Blog Identity

```php
// Blog metadata and branding
public const BLOG_TITLE = 'My Dropblog';           // Displayed in header/title
public const BLOG_DESCRIPTION = 'My thoughts';      // Meta description [TODO]
public const BLOG_AUTHOR = 'Your Name';            // Default author [TODO]
public const BLOG_URL = 'https://your-blog.com';   // Canonical URL [TODO]
```

### Security Settings

```php
// API authentication
public const ADMIN_API_KEY = 'your-secure-api-key-here';

// Security options [TODO]
public const REQUIRE_HTTPS = true;                  // Force HTTPS
public const ALLOW_CORS = false;                    // Enable CORS for API
public const RATE_LIMIT_ENABLED = true;             // Enable rate limiting
```

**API Key Best Practices:**
- Use a long, random string (32+ characters)
- Generate with: `openssl rand -hex 32`
- Never commit API keys to version control
- Use environment variables in production

## 🌍 Localization Configuration

### Basic Locale Setup

```php
// Primary localization setting
public const LOCALE = 'en-US';    // Format: language-region
```

**Supported Locales:**
- `en-US` - English (United States) - Default
- `de-DE` - German (Germany)
- `de-AT` - German (Austria)
- [TODO] Add more languages as they become available

### Date and Time Settings

```php
// Regional formatting
public const TIMEZONE = 'Europe/Berlin';           // PHP timezone identifier
public const DATE_FORMAT = 'd. F Y';               // PHP date format
public const TIME_FORMAT = 'H:i';                  // Time format [TODO]
```

**Common Timezones:**
- `UTC` - Coordinated Universal Time
- `America/New_York` - Eastern Time
- `Europe/London` - Greenwich Mean Time
- `Asia/Tokyo` - Japan Standard Time
- See [PHP Timezone List](https://www.php.net/manual/en/timezones.php) for all options

**Date Format Examples:**
- `d. F Y` → "15. March 2024" (German style)
- `F j, Y` → "March 15, 2024" (US style)
- `Y-m-d` → "2024-03-15" (ISO format)

## 🎨 Display and UI Settings

### Content Display

```php
// Post and content options
public const POSTS_PER_PAGE = 10;                  // Homepage pagination [TODO]
public const EXCERPT_LENGTH = 150;                 // Auto-excerpt length [TODO]
public const MARKDOWN_EXTENSIONS = true;           // Enable Markdown extras [TODO]
```

### Theme and Styling

```php
// Appearance settings [TODO]
public const THEME = 'default';                    // Theme directory name
public const CUSTOM_CSS = '';                      // Additional CSS file
public const DARK_MODE = false;                    // Enable dark mode toggle
```

## 🔧 Advanced Configuration

### Performance Settings

```php
// Caching and optimization [TODO]
public const CACHE_ENABLED = true;                 // Enable output caching
public const CACHE_DURATION = 3600;                // Cache duration in seconds
public const MINIFY_HTML = false;                  // Minify HTML output
public const COMPRESS_IMAGES = true;               // Auto-compress uploads
```

### Development Settings

```php
// Debug and development
public const DEBUG = false;                        // Enable debug mode
public const LOG_LEVEL = 'ERROR';                  // Logging level [TODO]
public const SHOW_ERRORS = false;                  // Display PHP errors
```

**Debug Mode Features:**
- Detailed error messages
- SQL query logging
- Performance metrics
- **Never enable in production!**

### API Configuration

```php
// API behavior and limits [TODO]
public const API_RATE_LIMIT = 60;                  // Requests per minute
public const API_CORS_ORIGINS = ['*'];             // Allowed CORS origins
public const API_VERSION = 'v1';                   // API version prefix
```

## 📧 Email and Notifications

```php
// Email settings [TODO - Future feature]
public const SMTP_HOST = 'smtp.gmail.com';
public const SMTP_PORT = 587;
public const SMTP_USER = 'your-email@gmail.com';
public const SMTP_PASS = 'your-app-password';
public const FROM_EMAIL = 'noreply@your-blog.com';
public const FROM_NAME = 'Your Blog';
```

## 🗄️ Database Configuration

### Connection Pool Settings

```php
// Advanced database options [TODO]
public const DB_CHARSET = 'utf8mb4';               // Character set
public const DB_COLLATION = 'utf8mb4_unicode_ci';  // Collation
public const DB_PERSISTENT = false;                // Persistent connections
public const DB_TIMEOUT = 30;                      // Connection timeout
```

### Migration Settings

```php
// Migration system configuration [TODO]
public const MIGRATION_TABLE = 'migrations';       // Migration tracking table
public const AUTO_MIGRATE = false;                 // Auto-run on deployment
public const BACKUP_ON_MIGRATE = true;             // Backup before migrations
```

## 🔒 Security Configuration

### Authentication

```php
// Security hardening [TODO]
public const SESSION_SECURE = true;                // Secure cookies only
public const CSRF_PROTECTION = true;               // Enable CSRF protection
public const XSS_PROTECTION = true;                // XSS filtering
public const CONTENT_SECURITY_POLICY = true;       // Enable CSP headers
```

### File Upload Security

```php
// Upload restrictions [TODO]
public const UPLOAD_MAX_SIZE = '5M';               // Max file size
public const ALLOWED_EXTENSIONS = ['jpg', 'png', 'gif', 'md']; // File types
public const SCAN_UPLOADS = true;                  // Virus scanning
```

## 🌐 Environment-Specific Configuration

### Development Environment

```php
// Development settings
public const DEBUG = true;
public const SHOW_ERRORS = true;
public const LOG_LEVEL = 'DEBUG';
public const CACHE_ENABLED = false;
public const REQUIRE_HTTPS = false;
```

### Production Environment

```php
// Production settings
public const DEBUG = false;
public const SHOW_ERRORS = false;
public const LOG_LEVEL = 'ERROR';
public const CACHE_ENABLED = true;
public const REQUIRE_HTTPS = true;
```

### Using Environment Variables

For sensitive data, use environment variables:

```php
// Example: Reading from environment
public const DB_PASS = $_ENV['DB_PASSWORD'] ?? 'fallback_password';
public const ADMIN_API_KEY = $_ENV['ADMIN_API_KEY'] ?? 'default_key';
```

Set environment variables in your system:
```bash
export DB_PASSWORD="your_secure_password"
export ADMIN_API_KEY="your_secure_api_key"
```

## 🔄 Configuration Validation

Dropblog validates configuration on startup:

### Required Settings
- `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` - Database connection
- `ADMIN_API_KEY` - API authentication
- `LOCALE` - Localization (will throw error if missing)

### Optional Settings
- Most other settings have sensible defaults
- Missing optional settings will use built-in defaults
- Check error logs for configuration warnings

## 🛠️ Configuration Management

### Best Practices

1. **Version Control**: Never commit `Config.php` with real credentials
2. **Environment Variables**: Use for sensitive data in production
3. **Documentation**: Comment your custom settings
4. **Backups**: Keep backups of working configurations
5. **Testing**: Test configuration changes on staging first

### Configuration Templates

Create environment-specific templates:

```bash
# Different configs for different environments
Config.development.php.template
Config.staging.php.template  
Config.production.php.template
```

### Automated Configuration

[TODO] Script to generate configuration from templates and environment variables.

## 🚨 Common Configuration Issues

### Database Connection Problems
- Check host, port, username, password
- Verify database exists and user has permissions
- Test connection manually with MySQL client

### API Authentication Issues
- Ensure API key is properly formatted
- Check Bearer token format in requests
- Verify API key matches between web and app

### Localization Problems
- Confirm locale format (language-region)
- Check that locale files exist
- Verify LOCALE constant is set

### Performance Issues
- Enable caching in production
- Set appropriate cache duration
- Monitor debug settings (disable in production)

---

Need help with a specific configuration? Check [Troubleshooting](troubleshooting.md) or open an issue on GitHub. 