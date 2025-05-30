# Configuration Guide

This guide covers all configuration options available in Dropblog, from basic setup to advanced customization.

## 📁 Configuration System

Dropblog uses **environment variables** for configuration, providing a secure and flexible approach that separates configuration from code.

### Configuration Files

**Main Config Class:**
```
web/src/Config.php
```

**Environment Configuration:**
```
web/src/.env          # Your actual configuration (not in git)
web/src/env.example   # Template with all available options
```

### Setup Process

1. **Copy the template:**
```bash
cp web/src/env.example web/src/.env
```

2. **Edit your values:**
```bash
# Edit with your preferred editor
nano web/src/.env
```

3. **Configure your application:**
The Config class automatically loads and provides access to your environment variables.

## 🔧 Basic Configuration

### Database Settings

Add these to your `.env` file:

```bash
# Database connection settings
DB_HOST=localhost                 # Database host
DB_NAME=dropblog                  # Database name  
DB_USER=your_username             # Database username
DB_PASS=your_password             # Database password
DB_CHARSET=utf8mb4                # Character set (optional, defaults to utf8mb4)
```

**Connection Options:**
- Use `localhost` for local development
- For remote databases, specify the full hostname or IP
- Ensure database exists before running migrations
- The charset defaults to `utf8mb4` if not specified

### Blog Identity

```bash
# Blog metadata and branding
BLOG_TITLE=My Dropblog            # Displayed in header/title
```

### Security Settings

```bash
# API authentication
ADMIN_API_KEY=your-secure-api-key-here
```

**API Key Best Practices:**
- Use a long, random string (32+ characters)
- Generate with: `openssl rand -hex 32`
- Never commit API keys to version control
- The `.env` file is automatically excluded from git

## 🌍 Localization Configuration

### Basic Locale Setup

```bash
# Primary localization setting
LOCALE=en-US                      # Format: language-region
```

**Supported Locales:**
- `en-US` - English (United States) - Default
- `de-DE` - German (Germany)
- `de-AT` - German (Austria)

### Date and Time Settings

```bash
# Regional formatting
TIMEZONE=Europe/Berlin            # PHP timezone identifier
DATE_FORMAT=d. F Y                # PHP date format
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

## 🔧 Development Settings

### Debug Mode

```bash
# Debug and development
DEBUG=false                       # Enable debug mode (true/false)
```

**Debug Mode Features:**
- Detailed error messages
- Additional API response information
- **Never enable in production!**

## 📖 Using Configuration in Code

### Accessing Configuration Values

The Config class provides methods to access your environment variables:

```php
use Dropblog\Config;

// Database settings
$host = Config::dbHost();
$name = Config::dbName();
$user = Config::dbUser();
$pass = Config::dbPass();
$charset = Config::dbCharset();

// Security
$apiKey = Config::apiKey();
$debug = Config::debug();

// Blog settings
$title = Config::blogTitle();

// Localization
$locale = Config::locale();
$timezone = Config::timezone();
$dateFormat = Config::dateFormat();

// Internal settings (not configurable via .env)
$urlLength = Config::urlLength();          // Returns 8
$hashSalt = Config::hashidsSalt();         // Returns 'dropblog'
```

### Fallback Values

If an environment variable is not set, the Config class provides sensible defaults using placeholder templates. For production deployment, you can use deployment tools to replace these placeholders:

- `{{DB_HOST}}` - Database host placeholder
- `{{DB_NAME}}` - Database name placeholder
- `{{ADMIN_API_KEY}}` - API key placeholder
- etc.

## 🚀 Deployment Configuration

### Environment-Specific Settings

**Development (.env):**
```bash
DEBUG=true
DB_HOST=localhost
ADMIN_API_KEY=dev-key-not-secure
```

**Production (environment variables):**
```bash
export DEBUG=false
export DB_HOST=prod-db-server
export ADMIN_API_KEY=$(openssl rand -hex 32)
```

### Docker Environment

```dockerfile
# In your Dockerfile or docker-compose.yml
ENV DEBUG=false
ENV DB_HOST=database
ENV BLOG_TITLE="My Production Blog"
```

### Cloud Platform Configuration

Most cloud platforms allow you to set environment variables through their web interface or CLI tools.

## 🔒 Security Best Practices

### Environment Variables

- **Never commit `.env` files** (already in `.gitignore`)
- Use **strong, unique API keys**
- Set **DEBUG=false** in production
- Use **environment-specific configurations**

### Database Security

- Use **dedicated database users** with minimal privileges
- Enable **SSL connections** for remote databases
- **Regularly rotate** database passwords

## 🔧 Advanced Configuration

### Custom Environment Loading

The Config class automatically loads `.env` files from the `web/src/` directory. The loading logic:

1. Looks for `.env` file in the Config class directory
2. Ignores empty lines and comments (starting with `#`)
3. Parses `KEY=value` pairs
4. Sets them as environment variables with `putenv()`

### Adding New Configuration Options

To add new configuration options:

1. **Add to `env.example`:**
```bash
# New feature setting
NEW_FEATURE_ENABLED=true
```

2. **Add method to Config.php:**
```php
public static function newFeatureEnabled(): bool
{
    return self::env('NEW_FEATURE_ENABLED', 'false') === 'true';
}
```

3. **Use in your code:**
```php
if (Config::newFeatureEnabled()) {
    // New feature logic
}
```

---

Need help with configuration? Check the [Troubleshooting Guide](troubleshooting.md) or review the complete [env.example](../web/src/env.example) file for all available options. 