# Deployment Guide

This guide covers deploying Dropblog to production environments, including web hosting and app store distribution.

## 🌐 Web Deployment

### Prerequisites

- **Web server**: Apache or Nginx
- **PHP 8.2+** with required extensions
- **MySQL/MariaDB** database
- **SSL certificate** (recommended)
- **Domain name** configured

### Production Configuration

#### Environment Setup

```php
// Config.php - Production settings
namespace Dropblog;

class Config
{
    // Database settings
    public const DB_HOST = 'your-production-host';
    public const DB_NAME = 'your-production-db';
    public const DB_USER = 'your-production-user';
    public const DB_PASS = 'your-secure-password';
    
    // Blog settings
    public const BLOG_TITLE = 'Your Blog';
    public const ADMIN_API_KEY = 'your-very-secure-api-key';
    
    // Production settings
    public const DEBUG = false;
    public const REQUIRE_HTTPS = true;
    public const CACHE_ENABLED = true;
    
    // ... other settings
}
```

#### Security Checklist

- ✅ **Strong API key** (32+ characters)
- ✅ **Database password** is secure
- ✅ **DEBUG = false** in production
- ✅ **HTTPS enabled** with valid SSL certificate
- ✅ **File permissions** properly set
- ✅ **Database access** restricted
- ✅ **Backups** configured

### Shared Hosting Deployment

#### File Upload

1. **Upload files** via FTP/SFTP:
   ```
   /public_html/dropblog/    # Your domain directory
   └── web/src/wwwroot/      # Point domain here
   ```

2. **Set document root** to `web/src/wwwroot`

3. **Upload source files** outside web root:
   ```
   /home/username/dropblog/web/src/  # Source files (secure)
   ```

#### Database Setup

```sql
-- Create database through hosting control panel
CREATE DATABASE username_dropblog;

-- Import schema (if needed)
-- Usually done through phpMyAdmin or similar
```

#### File Permissions

```bash
# Set appropriate permissions
chmod -R 644 web/src/
chmod -R 755 web/src/wwwroot/
chmod 600 web/src/Config.php
```

### VPS/Dedicated Server Deployment

#### Apache Configuration

```apache
<VirtualHost *:443>
    ServerName your-blog.com
    DocumentRoot /var/www/dropblog/web/src/wwwroot
    
    # SSL Configuration
    SSLEngine on
    SSLCertificateFile /path/to/your/certificate.crt
    SSLCertificateKeyFile /path/to/your/private.key
    
    # Security headers
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
    
    # Directory protection
    <Directory /var/www/dropblog/web/src/wwwroot>
        AllowOverride All
        Require all granted
        
        # Cache static files
        <FilesMatch "\.(css|js|png|jpg|jpeg|gif|ico|svg)$">
            ExpiresActive on
            ExpiresDefault "access plus 1 month"
        </FilesMatch>
    </Directory>
    
    # Deny access to source directory
    <Directory /var/www/dropblog/web/src>
        Require all denied
    </Directory>
    <Directory /var/www/dropblog/web/src/wwwroot>
        Require all granted
    </Directory>
</VirtualHost>

# Redirect HTTP to HTTPS
<VirtualHost *:80>
    ServerName your-blog.com
    Redirect permanent / https://your-blog.com/
</VirtualHost>
```

#### Nginx Configuration

```nginx
server {
    listen 443 ssl http2;
    server_name your-blog.com;
    root /var/www/dropblog/web/src/wwwroot;
    index index.php;
    
    # SSL Configuration
    ssl_certificate /path/to/your/certificate.crt;
    ssl_certificate_key /path/to/your/private.key;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES128-GCM-SHA256:ECDHE-RSA-AES256-GCM-SHA384;
    
    # Security headers
    add_header X-Content-Type-Options nosniff;
    add_header X-Frame-Options DENY;
    add_header X-XSS-Protection "1; mode=block";
    
    # PHP handling
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    # URL rewriting for clean URLs
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    # Deny access to sensitive files
    location ~ /\. {
        deny all;
    }
    
    location ~ /(Config\.php|composer\.|\.git) {
        deny all;
    }
    
    # Cache static files
    location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg)$ {
        expires 1M;
        add_header Cache-Control "public, immutable";
    }
}

# Redirect HTTP to HTTPS
server {
    listen 80;
    server_name your-blog.com;
    return 301 https://$server_name$request_uri;
}
```

### Cloud Deployment

#### GitHub Actions Deployment

```yaml
# .github/workflows/deploy-web.yml
name: Deploy Web Application

on:
  push:
    branches: [main]
    paths: ['web/src/**']

jobs:
  deploy:
    runs-on: ubuntu-latest
    
    steps:
    - uses: actions/checkout@v3
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.2'
        extensions: pdo, pdo_mysql, json
        
    - name: Install dependencies
      run: |
        cd web/src
        composer install --no-dev --optimize-autoloader
        
    - name: Deploy to server
      uses: easingthemes/ssh-deploy@v2.1.5
      env:
        SSH_PRIVATE_KEY: ${{ secrets.SSH_PRIVATE_KEY }}
        REMOTE_HOST: ${{ secrets.REMOTE_HOST }}
        REMOTE_USER: ${{ secrets.REMOTE_USER }}
        SOURCE: "web/src/"
        TARGET: "/var/www/dropblog/web/src/"
        EXCLUDE: "/Config.php"
        
    - name: Run migrations
      run: |
        curl -X POST \
          -H "Authorization: Bearer ${{ secrets.ADMIN_API_KEY }}" \
          https://your-blog.com/admin/update
```

#### Docker Deployment

```dockerfile
# Dockerfile
FROM php:8.2-apache

# Install extensions
RUN docker-php-ext-install pdo pdo_mysql

# Enable Apache modules
RUN a2enmod rewrite ssl headers

# Copy application
COPY web/src/ /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 755 /var/www/html
RUN chmod 600 /var/www/html/Config.php

# Apache configuration
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf

EXPOSE 80 443
```

```yaml
# docker-compose.yml
version: '3.8'

services:
  web:
    build: .
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./web/src:/var/www/html
      - ./ssl:/etc/ssl/certs
    environment:
      - APACHE_DOCUMENT_ROOT=/var/www/html/wwwroot
    depends_on:
      - database
      
  database:
    image: mysql:8.0
    environment:
      MYSQL_ROOT_PASSWORD: rootpassword
      MYSQL_DATABASE: dropblog
      MYSQL_USER: dropblog
      MYSQL_PASSWORD: password
    volumes:
      - mysql_data:/var/lib/mysql
      
volumes:
  mysql_data:
```

## 📱 App Deployment

### Android Deployment

#### Google Play Console Setup

1. **Create developer account** at Google Play Console
2. **Create new app** in console
3. **Configure app details** (name, description, screenshots)
4. **Set up app signing** (Google Play App Signing recommended)

#### Build Release APK

```bash
cd app/Dropblog

# Build release version
dotnet publish -c Release -f net9.0-android

# APK location: bin/Release/net9.0-android/publish/
```

#### App Signing

```xml
<!-- In Dropblog.csproj -->
<PropertyGroup Condition="$(TargetFramework.Contains('-android')) and '$(Configuration)' == 'Release'">
    <AndroidKeyStore>true</AndroidKeyStore>
    <AndroidSigningKeyStore>../myapp.keystore</AndroidSigningKeyStore>
    <AndroidSigningKeyAlias>myappalias</AndroidSigningKeyAlias>
    <AndroidSigningKeyPass>$(AndroidSigningKeyPass)</AndroidSigningKeyPass>
    <AndroidSigningStorePass>$(AndroidSigningStorePass)</AndroidSigningStorePass>
</PropertyGroup>
```

#### GitHub Actions for Android

```yaml
# .github/workflows/deploy-android.yml
name: Deploy Android App

on:
  workflow_dispatch:
  push:
    tags: ['v*']

jobs:
  build:
    runs-on: ubuntu-latest
    
    steps:
    - uses: actions/checkout@v3
    
    - name: Setup .NET
      uses: actions/setup-dotnet@v3
      with:
        dotnet-version: '9.0.x'
        
    - name: Install MAUI workload
      run: dotnet workload install maui
      
    - name: Build Android
      run: |
        cd app/Dropblog
        dotnet publish -c Release -f net9.0-android
        
    - name: Upload to Play Store
      uses: r0adkll/upload-google-play@v1
      with:
        serviceAccountJsonPlainText: ${{ secrets.PLAY_STORE_SERVICE_ACCOUNT }}
        packageName: com.yourcompany.dropblog
        releaseFiles: app/Dropblog/bin/Release/net9.0-android/publish/*.aab
        track: internal
```

### iOS Deployment

#### App Store Connect Setup

1. **Apple Developer Program** membership required
2. **Create app** in App Store Connect
3. **Configure app metadata** and screenshots
4. **Set up certificates** and provisioning profiles

#### Build for iOS

```bash
cd app/Dropblog

# Build for iOS
dotnet build -c Release -f net9.0-ios

# For distribution (requires Mac with Xcode)
dotnet publish -c Release -f net9.0-ios
```

#### Provisioning Profile

```xml
<!-- In Dropblog.csproj -->
<PropertyGroup Condition="$(TargetFramework.Contains('-ios')) and '$(Configuration)' == 'Release'">
    <CodesignKey>iPhone Distribution</CodesignKey>
    <CodesignProvision>YourDistributionProvisioningProfile</CodesignProvision>
</PropertyGroup>
```

### Windows Deployment

#### Microsoft Store

1. **Partner Center** account required
2. **Create app reservation** in Partner Center
3. **Package and upload** MSIX package

#### Build MSIX Package

```bash
cd app/Dropblog

# Build Windows package
dotnet publish -c Release -f net9.0-windows10.0.19041.0 -p:PublishProfile=win10-x64

# Package will be in bin/Release/net9.0-windows10.0.19041.0/win10-x64/
```

#### Store Configuration

```xml
<!-- In Dropblog.csproj -->
<PropertyGroup Condition="$(TargetFramework.Contains('-windows')) and '$(Configuration)' == 'Release'">
    <UseWinUI>true</UseWinUI>
    <WindowsPackageType>MSIX</WindowsPackageType>
    <WindowsAppSDKSelfContained>true</WindowsAppSDKSelfContained>
    <PublishProfile>win10-$(Platform).pubxml</PublishProfile>
</PropertyGroup>
```

### macOS Deployment

#### Mac App Store

1. **Apple Developer Program** membership
2. **Create app** in App Store Connect
3. **Configure certificates** for Mac distribution

#### Build for macOS

```bash
cd app/Dropblog

# Build for macOS
dotnet publish -c Release -f net9.0-maccatalyst
```

## 🔧 Post-Deployment

### Initial Setup

1. **Run database migrations**:
   ```bash
   curl -X POST -H "Authorization: Bearer YOUR_API_KEY" https://your-blog.com/admin/update
   ```

2. **Test API endpoints**:
   ```bash
   curl -X POST \
     -H "Authorization: Bearer YOUR_API_KEY" \
     -H "Content-Type: application/json" \
     -d '{"content":"# Welcome\n\nFirst post!"}' \
     https://your-blog.com/admin/posts
   ```

3. **Verify SSL certificate**:
   ```bash
   curl -I https://your-blog.com
   ```

### Monitoring and Maintenance

#### Health Checks

```bash
# Check website accessibility
curl -f https://your-blog.com

# Check API health
curl -f -H "Authorization: Bearer YOUR_API_KEY" https://your-blog.com/admin/update
```

#### Log Monitoring

- **Web server logs**: `/var/log/apache2/` or `/var/log/nginx/`
- **PHP error logs**: Check PHP error_log configuration
- **Application logs**: [TODO] Implement application-level logging

#### Backup Strategy

```bash
# Database backup
mysqldump -u username -p dropblog > backup_$(date +%Y%m%d).sql

# File backup
tar -czf backup_files_$(date +%Y%m%d).tar.gz /var/www/dropblog/

# Automated backups (crontab)
0 2 * * * /home/user/scripts/backup.sh
```

#### Updates and Maintenance

```bash
# Update dependencies
cd web/src
composer update

# Apply new migrations
curl -X POST -H "Authorization: Bearer YOUR_API_KEY" https://your-blog.com/admin/update

# Restart services (if needed)
sudo systemctl restart apache2
sudo systemctl restart mysql
```

## 🚨 Troubleshooting

### Common Issues

#### Permission Errors
- Check file permissions (644 for files, 755 for directories)
- Ensure web server user can read files
- Verify `Config.php` is readable but not public

#### Database Connection
- Test database credentials manually
- Check if database server is running
- Verify network connectivity

#### SSL Certificate Issues
- Ensure certificate is valid and not expired
- Check certificate chain completeness
- Verify domain name matches certificate

#### API Authentication
- Confirm API key is correctly set
- Check Bearer token format in requests
- Verify HTTPS is working for API calls

### Performance Optimization

#### Caching
- [TODO] Implement output caching
- Enable browser caching for static files
- Consider CDN for global distribution

#### Database Optimization
- Regular database maintenance
- Index optimization
- Query performance monitoring

## 📊 Scaling

### Load Balancing

[TODO] Configuration for multiple web servers:
- Load balancer configuration
- Session sharing
- Database clustering

### CDN Integration

[TODO] Content Delivery Network setup:
- Static file delivery
- Global distribution
- Cache invalidation

---

Need deployment help? Check [Troubleshooting](troubleshooting.md) or open an issue on GitHub. 