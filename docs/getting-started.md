# Getting Started with Dropblog

This guide will walk you through setting up Dropblog from scratch, covering both the web application and cross-platform app.

## 📋 Prerequisites

Before you begin, ensure you have:

### For Web Application
- **PHP 8.2 or higher** with the following extensions:
  - PDO and PDO_MySQL
  - JSON support
  - mod_rewrite enabled on Apache
- **MySQL 5.7+ or MariaDB 10.2+**
- **Composer** for dependency management
- **Apache web server** (or nginx with proper configuration)

### For App Development (Optional)
- **.NET 9.0 SDK**
- **Platform-specific workloads** for your target platforms
- **Code editor** (Visual Studio, VS Code, or similar)

## 🚀 Quick Start (5 Minutes)

Follow these steps to get Dropblog running quickly:

### 1. Clone and Install

```bash
# Clone the repository
git clone https://github.com/yourusername/dropblog.git
cd dropblog

# Navigate to web source
cd web/src

# Install PHP dependencies
composer install
```

### 2. Configure Database

```bash
# Copy configuration template
cp Config.template.php Config.php
```

Edit `Config.php` with your settings:

```php
namespace Dropblog;

class Config
{
    // Database settings
    public const DB_HOST = 'localhost';
    public const DB_NAME = 'dropblog';
    public const DB_USER = 'your_username';
    public const DB_PASS = 'your_password';
    
    // Blog settings
    public const BLOG_TITLE = 'My Dropblog';
    public const ADMIN_API_KEY = 'your-secure-api-key-here';
    
    // Localization
    public const LOCALE = 'en-US';
    
    // Other settings
    public const TIMEZONE = 'Europe/Berlin';
    public const DATE_FORMAT = 'd. F Y';
    public const DEBUG = false;
}
```

### 3. Web Server Setup

**Apache Configuration:**

Point your document root to `web/src/wwwroot` directory:

```apache
<VirtualHost *:80>
    DocumentRoot /path/to/dropblog/web/src/wwwroot
    ServerName your-blog.local
    
    <Directory /path/to/dropblog/web/src/wwwroot>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

**Important Security Note**: The `web/src` directory should be **outside** your web root for security!

### 4. Initialize Database

Run the database migration to create tables:

```bash
curl -X POST -H "Authorization: Bearer YOUR_API_KEY" http://your-blog.local/admin/update
```

### 5. Create Your First Post

You can now create posts via:
- Web interface: Visit your blog URL
- API endpoint: `POST /admin/posts` with Bearer token
- Mobile app: Set up the cross-platform app (see below)

## 📱 Setting Up the Cross-Platform App

### 1. Navigate to App Directory

```bash
cd app/Dropblog
```

### 2. Restore Dependencies

```bash
dotnet restore
```

### 3. Configure API Connection

Edit `Services/BlogApiService.cs` to point to your blog:

```csharp
private const string BaseUrl = "https://your-blog.com";
private const string ApiKey = "YOUR_API_KEY";
```

### 4. Build and Run

For your platform:

```bash
# Windows
dotnet run --framework net9.0-windows10.0.19041.0

# Android (requires emulator/device)
dotnet run --framework net9.0-android

# iOS (requires simulator/device)
dotnet run --framework net9.0-ios

# macOS
dotnet run --framework net9.0-maccatalyst
```

## 🔧 Detailed Setup Instructions

### Web Server Configuration Options

#### Apache with .htaccess

Ensure `mod_rewrite` is enabled. The included `.htaccess` files should handle URL rewriting.

#### Nginx Configuration

[TODO] Add nginx configuration example

```nginx
# Nginx configuration for Dropblog
server {
    listen 80;
    server_name your-blog.com;
    root /path/to/dropblog/web/src/wwwroot;
    index index.php;
    
    # [TODO] Complete nginx configuration
}
```

### Database Setup Details

#### Manual Database Creation

```sql
CREATE DATABASE dropblog 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

CREATE USER 'dropblog_user'@'localhost' 
IDENTIFIED BY 'secure_password';

GRANT ALL PRIVILEGES ON dropblog.* 
TO 'dropblog_user'@'localhost';

FLUSH PRIVILEGES;
```

#### Migration System

Dropblog uses a migration system for database schema updates:

- Migrations are stored in `web/src/Migrations/`
- Run migrations via the `/admin/update` endpoint
- Each migration runs only once
- Migration status is tracked in the database

### Security Configuration

#### API Key Generation

Generate a secure API key:

```bash
# Generate random 32-character key
openssl rand -hex 32
```

#### File Permissions

Set appropriate permissions:

```bash
# Make sure web server can read files
chmod -R 644 web/src/
chmod -R 755 web/src/wwwroot/

# Protect sensitive files
chmod 600 web/src/Config.php
```

## 🧪 Testing Your Installation

### 1. Web Interface Test

Visit your blog URL - you should see:
- Clean, responsive homepage
- Working navigation
- Proper styling and layout

### 2. API Test

Test the admin API:

```bash
# Test database update
curl -X POST \
  -H "Authorization: Bearer YOUR_API_KEY" \
  http://your-blog.local/admin/update

# Test post creation
curl -X POST \
  -H "Authorization: Bearer YOUR_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{"content":"# Test Post\n\nThis is a test post."}' \
  http://your-blog.local/admin/posts
```

### 3. App Test

If you set up the app:
- Launch the app
- Write a test post in the editor
- Use the formatting toolbar
- Select a post type
- Publish to your blog

## 🎯 Next Steps

After getting Dropblog running:

1. **Customize Configuration**: See [Configuration](configuration.md) for detailed options
2. **Set Up Localization**: Check [Localization](localization.md) for multi-language support
3. **Explore App Features**: Read [App Development](app-development.md) for advanced app usage
4. **Plan Deployment**: Review [Deployment](deployment.md) for production setup
5. **Learn the API**: Study [API Reference](api-reference.md) for integration possibilities

## 🚨 Common Issues

### Permission Issues
- Ensure web server can read/write necessary files
- Check that the `web/src` directory is outside web root

### Database Connection
- Verify database credentials in `Config.php`
- Ensure MySQL/MariaDB is running
- Check that the database exists

### API Authentication
- Verify API key is set correctly in `Config.php`
- Ensure Bearer token format: `Authorization: Bearer YOUR_API_KEY`

### App Connection Issues
- Confirm API URL is accessible from app
- Check that CORS is configured if needed [TODO]
- Verify API key matches between app and web config

## 💡 Tips for Success

1. **Start Simple**: Get the basic web version working first
2. **Test Each Step**: Verify each component before moving to the next
3. **Keep Backups**: Backup your database and configuration files
4. **Use HTTPS**: Always use HTTPS in production environments
5. **Monitor Logs**: Check web server and PHP error logs for issues

---

Need help? Check the [Troubleshooting](troubleshooting.md) guide or open an issue on GitHub. 