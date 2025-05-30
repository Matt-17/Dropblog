# Troubleshooting Guide

This guide helps you diagnose and resolve common issues with Dropblog. Issues are organized by category for easy navigation.

## 🔍 Quick Diagnosis

### Check System Status

```bash
# Test web connectivity
curl -I https://your-blog.com

# Test API endpoint
curl -H "Authorization: Bearer YOUR_API_KEY" https://your-blog.com/admin/update

# Check PHP version
php -v

# Check database connectivity
mysql -u username -p -e "SELECT 1;"
```

### Log Locations

- **Apache**: `/var/log/apache2/error.log`
- **Nginx**: `/var/log/nginx/error.log`
- **PHP**: Check `php.ini` for `error_log` location
- **MySQL**: `/var/log/mysql/error.log`

## 🌐 Web Application Issues

### Installation Problems

#### "Config.php not found"

**Symptoms**: Error message about missing configuration file

**Solutions**:
```bash
# Copy template to Config.php
cp web/src/Config.template.php web/src/Config.php

# Verify file exists
ls -la web/src/Config.php

# Check permissions
chmod 644 web/src/Config.php
```

#### "Composer dependencies not installed"

**Symptoms**: "Class not found" errors, autoloader issues

**Solutions**:
```bash
# Navigate to web source
cd web/src

# Install dependencies
composer install

# Clear autoloader cache
composer dump-autoload

# Check vendor directory exists
ls -la vendor/
```

### Database Connection Issues

#### "Connection refused" or "Access denied"

**Symptoms**: Database connection errors

**Diagnosis**:
```bash
# Test database connection manually
mysql -h DB_HOST -u DB_USER -p DB_NAME

# Check if MySQL is running
sudo systemctl status mysql
# or
sudo systemctl status mariadb
```

**Solutions**:
1. **Verify credentials** in `Config.php`
2. **Check database server** is running
3. **Ensure database exists**:
   ```sql
   CREATE DATABASE IF NOT EXISTS your_database_name;
   ```
4. **Grant proper permissions**:
   ```sql
   GRANT ALL PRIVILEGES ON database_name.* TO 'username'@'host';
   FLUSH PRIVILEGES;
   ```

#### "Table doesn't exist"

**Symptoms**: SQL errors about missing tables

**Solutions**:
```bash
# Run database migrations
curl -X POST -H "Authorization: Bearer YOUR_API_KEY" https://your-blog.com/admin/update

# Check migration files exist
ls -la web/src/Migrations/

# Manually check database tables
mysql -u username -p -e "SHOW TABLES;" database_name
```

### Web Server Issues

#### "404 Not Found" for all pages

**Symptoms**: Only homepage works, all other URLs return 404

**Apache Solutions**:
```bash
# Enable mod_rewrite
sudo a2enmod rewrite
sudo systemctl restart apache2

# Check .htaccess exists
ls -la web/src/wwwroot/.htaccess

# Verify AllowOverride in Apache config
# Should be: AllowOverride All
```

**Nginx Solutions**:
```nginx
# Add to nginx config
location / {
    try_files $uri $uri/ /index.php?$query_string;
}

# Restart nginx
sudo systemctl restart nginx
```

#### "500 Internal Server Error"

**Symptoms**: Server error on any request

**Diagnosis**:
```bash
# Check error logs
tail -f /var/log/apache2/error.log
# or
tail -f /var/log/nginx/error.log

# Check PHP error logs
tail -f /var/log/php_errors.log
```

**Common Solutions**:
1. **File permissions**:
   ```bash
   chmod -R 644 web/src/
   chmod -R 755 web/src/wwwroot/
   chown -R www-data:www-data web/src/
   ```

2. **PHP syntax errors**:
   ```bash
   php -l web/src/Config.php
   php -l web/src/index.php
   ```

3. **Missing PHP extensions**:
   ```bash
   php -m | grep -E "(pdo|mysql|json)"
   ```

### API Issues

#### "401 Unauthorized"

**Symptoms**: API calls return authentication errors

**Solutions**:
1. **Check API key** in `Config.php`
2. **Verify Bearer token format**:
   ```bash
   curl -H "Authorization: Bearer YOUR_ACTUAL_API_KEY" \
        https://your-blog.com/admin/update
   ```
3. **Ensure HTTPS** for secure token transmission

#### "CORS errors" (browser console)

**Symptoms**: Browser blocks API requests from app

**Solutions**:
```php
// Add CORS headers in your API endpoints [TODO]
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Authorization, Content-Type');
```

## 📱 App Issues

### Build Problems

#### ".NET workload not installed"

**Symptoms**: Build fails with workload errors

**Solutions**:
```bash
# Check installed workloads
dotnet workload list

# Install required workloads
dotnet workload install maui
dotnet workload install android
dotnet workload install ios
dotnet workload install maccatalyst

# Repair if corrupted
dotnet workload repair
```

#### "Target framework not supported"

**Symptoms**: Framework version errors during build

**Solutions**:
```bash
# Check .NET version
dotnet --version

# Install .NET 9.0 if needed
# Download from: https://dotnet.microsoft.com/download

# Check project file targets
cat app/Dropblog/Dropblog.csproj | grep TargetFrameworks
```

### Runtime Issues

#### "Network connection failed"

**Symptoms**: App cannot connect to blog API

**Diagnosis**:
1. **Check API URL** in `BlogApiService.cs`
2. **Test API manually**:
   ```bash
   curl -H "Authorization: Bearer YOUR_API_KEY" https://your-blog.com/admin/update
   ```
3. **Verify network connectivity**

**Solutions**:
1. **Update API configuration**:
   ```csharp
   private const string BaseUrl = "https://your-correct-domain.com";
   private const string ApiKey = "your-correct-api-key";
   ```

2. **Check firewall settings**
3. **Test with HTTP instead of HTTPS** (development only)

#### "SSL certificate errors"

**Symptoms**: HTTPS connection fails from app

**Solutions**:
1. **Use valid SSL certificate** on server
2. **For development**, disable SSL validation:
   ```csharp
   // WARNING: Development only!
   HttpClientHandler handler = new HttpClientHandler()
   {
       ServerCertificateCustomValidationCallback = 
           (message, cert, chain, errors) => true
   };
   ```

### Platform-Specific Issues

#### Android Deployment

**"Android SDK not found"**:
```bash
# Install Android SDK via Visual Studio or Android Studio
# Set ANDROID_HOME environment variable
export ANDROID_HOME="/path/to/android/sdk"
```

**"Emulator not starting"**:
```bash
# Check available emulators
emulator -list-avds

# Start specific emulator
emulator -avd YourEmulatorName
```

#### iOS Deployment

**"Provisioning profile not found"**:
1. **Create provisioning profile** in Apple Developer Portal
2. **Download and install** in Xcode
3. **Update project settings** with correct profile

**"Xcode command line tools missing"**:
```bash
# Install Xcode command line tools
xcode-select --install
```

#### Windows Deployment

**"WinUI package missing"**:
```bash
# Install Windows App SDK
dotnet workload install microsoft-windowsappsdk-projecttemplates
```

## 🔒 Security Issues

### API Key Exposure

**Symptoms**: API key visible in logs or client code

**Solutions**:
1. **Regenerate API key** immediately
2. **Use environment variables**:
   ```php
   public const ADMIN_API_KEY = $_ENV['ADMIN_API_KEY'] ?? '';
   ```
3. **Review logs** for exposed keys
4. **Update client configurations**

### File Permission Issues

**Symptoms**: "Permission denied" errors

**Solutions**:
```bash
# Standard web permissions
find web/src -type f -exec chmod 644 {} \;
find web/src -type d -exec chmod 755 {} \;

# Protect sensitive files
chmod 600 web/src/Config.php

# Set correct ownership
chown -R www-data:www-data web/src/
```

## 🚀 Performance Issues

### Slow Loading Times

**Diagnosis**:
```bash
# Test page load times
curl -w "@curl-format.txt" -o /dev/null -s https://your-blog.com

# Check database performance
mysql -u username -p -e "SHOW PROCESSLIST;" database_name
```

**Solutions**:
1. **Enable caching** in `Config.php`:
   ```php
   public const CACHE_ENABLED = true;
   public const CACHE_DURATION = 3600;
   ```

2. **Optimize database**:
   ```sql
   OPTIMIZE TABLE posts;
   ANALYZE TABLE posts;
   ```

3. **Compress static files**:
   ```apache
   # In .htaccess
   <IfModule mod_deflate.c>
       AddOutputFilterByType DEFLATE text/css text/javascript application/javascript
   </IfModule>
   ```

### High Memory Usage

**Solutions**:
1. **Increase PHP memory limit**:
   ```ini
   ; In php.ini
   memory_limit = 256M
   ```

2. **Optimize queries** to fetch only needed data
3. **Enable opcode caching**:
   ```ini
   ; In php.ini
   opcache.enable=1
   opcache.memory_consumption=128
   ```

## 🌍 Localization Issues

### Missing Translations

**Symptoms**: Text displays as translation keys (e.g., "common.search")

**Solutions**:
1. **Check LOCALE setting** in `Config.php`:
   ```php
   public const LOCALE = 'en-US'; // Must be set
   ```

2. **Verify translation files** exist:
   ```bash
   ls -la web/src/resources/locales/strings*.json
   ```

3. **Validate JSON syntax**:
   ```bash
   php -r "json_decode(file_get_contents('web/src/resources/locales/strings.json'));"
   ```

### Incorrect Regional Formatting

**Solutions**:
1. **Check timezone setting**:
   ```php
   public const TIMEZONE = 'Europe/Berlin';
   ```

2. **Verify date format**:
   ```php
   public const DATE_FORMAT = 'd. F Y';
   ```

## 🔧 Migration Issues

### "Migration failed"

**Symptoms**: Database update endpoint returns migration errors

**Diagnosis**:
```bash
# Check migration files
ls -la web/src/Migrations/

# Test database permissions
mysql -u username -p -e "CREATE TABLE test_table (id INT);" database_name
mysql -u username -p -e "DROP TABLE test_table;" database_name
```

**Solutions**:
1. **Check file permissions** on migration directory
2. **Verify database user privileges**:
   ```sql
   SHOW GRANTS FOR 'username'@'host';
   ```
3. **Manual migration** if needed:
   ```bash
   mysql -u username -p database_name < web/src/Migrations/001_create_posts_table.sql
   ```

## 🧰 Debug Mode

### Enable Debug Information

**For development only**:
```php
// In Config.php
public const DEBUG = true;
public const SHOW_ERRORS = true;
```

**Disable in production**:
```php
public const DEBUG = false;
public const SHOW_ERRORS = false;
```

### Logging Configuration

```php
// Enable error logging
ini_set('log_errors', 1);
ini_set('error_log', '/path/to/php_errors.log');

// Log level configuration [TODO]
public const LOG_LEVEL = 'DEBUG'; // DEBUG, INFO, WARN, ERROR
```

## 📞 Getting Help

### Before Asking for Help

1. **Check this troubleshooting guide**
2. **Search existing GitHub issues**
3. **Test with debug mode enabled**
4. **Gather error logs and system information**

### Information to Include

When reporting issues, include:
- **Operating system** and version
- **PHP version**: `php -v`
- **Database version**: `mysql --version`
- **Web server**: Apache/Nginx version
- **Error messages** (full text)
- **Log excerpts** (relevant portions)
- **Steps to reproduce** the issue

### Where to Get Help

- **GitHub Issues**: For bugs and technical problems
- **GitHub Discussions**: For questions and community help
- **Documentation**: Check all relevant docs first

## 🔄 Recovery Procedures

### Restore from Backup

```bash
# Database restoration
mysql -u username -p database_name < backup_file.sql

# File restoration
tar -xzf backup_files.tar.gz -C /var/www/dropblog/
```

### Reset to Clean State

```bash
# Backup current state first!
mysqldump -u username -p database_name > backup_before_reset.sql

# Drop and recreate database
mysql -u username -p -e "DROP DATABASE database_name; CREATE DATABASE database_name;"

# Run fresh migrations
curl -X POST -H "Authorization: Bearer YOUR_API_KEY" https://your-blog.com/admin/update
```

---

**Still having issues?** Open an issue on GitHub with detailed information about your problem, including error messages, system information, and steps to reproduce. 