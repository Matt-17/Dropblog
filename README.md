# Dropblog

A minimalist, no-frills blogging platform that focuses on simplicity and performance. Dropblog is designed for those who want to share their thoughts without the complexity of traditional blogging platforms.

## Features

- **Simple Post System**: Just write and post - no categories, tags, or complex metadata
- **Clean URLs**: Short, readable URLs using HashIds
- **Markdown Support**: Write your posts in Markdown for better formatting
- **Monthly Archives**: Posts are automatically organized by month
- **Responsive Design**: Clean, mobile-friendly interface
- **RESTful Admin API**: Simple API for managing your blog
- **Database Migrations**: Easy database updates through migration system
- **Cross-Platform App**: Native apps for Android, iOS, Windows, and MacCatalyst
- **Automated Deployment**: GitHub Actions workflows for web and app deployment
- **Verified Installer**: Tested and packaged installer for easy setup

## Requirements

- PHP 8.2 or higher
- MySQL/MariaDB
- Apache with mod_rewrite enabled
- Composer for dependency management
- For app development: .NET 9.0 SDK

## Installation

### Web Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/yourusername/dropblog.git
   cd dropblog
   ```

2. Install dependencies:
   ```bash
   cd web/src
   composer install
   ```

3. Create your configuration:
   ```bash
   cp Config.template.php Config.php
   ```
   Edit `Config.php` with your database and blog settings.

4. Set up your web server:
   - Point your document root to the `web/src/wwwroot` directory
   - Ensure mod_rewrite is enabled
   - Make sure the `web/src` directory is outside the web root

5. Run the initial setup:
   ```bash
   curl -X POST -H "Authorization: Bearer YOUR_API_KEY" https://your-blog.com/admin/update
   ```

### App Development

1. Navigate to the app directory:
   ```bash
   cd app/Dropblog
   ```

2. Restore dependencies:
   ```bash
   dotnet restore
   ```

3. Build for your target platform:
   ```bash
   # For Android
   dotnet build -c Release -f net9.0-android
   
   # For iOS
   dotnet build -c Release -f net9.0-ios
   
   # For Windows
   dotnet build -c Release -f net9.0-windows10.0.19041.0
   
   # For MacCatalyst
   dotnet build -c Release -f net9.0-maccatalyst
   ```

## Configuration

### Web Configuration

Edit `web/src/Config.php` to set up your blog:

```php
namespace Dropblog;

class Config
{
    // Database settings
    public const DB_HOST = 'your_host';
    public const DB_NAME = 'your_database';
    public const DB_USER = 'your_username';
    public const DB_PASS = 'your_password';
    
    // Blog settings
    public const BLOG_TITLE = 'Your Blog Title';
    public const ADMIN_API_KEY = 'your_api_key';
    
    // Other settings
    public const TIMEZONE = 'Europe/Berlin';
    public const DATE_FORMAT = 'd. F Y';
    public const DEBUG = false;
}
```

### App Configuration

The app uses the same configuration as the web version, accessed through the REST API.

## Directory Structure

```
├── app/                # .NET MAUI application
│   └── Dropblog/      # App source code
├── web/               # Web application
│   ├── installer/     # Installation scripts
│   └── src/          # Web source code
│       ├── Config.php # Configuration file
│       ├── Controller/# Controller classes
│       ├── Migrations/# Database migration files
│       ├── Models/    # Data models
│       ├── Utils/     # Utility classes
│       ├── Views/     # View templates
│       └── wwwroot/   # Public web root
└── .github/          # GitHub Actions workflows
    └── workflows/    # CI/CD configuration
```

## API Endpoints

### Admin API

- **POST /admin/update**
  - Updates database schema using migration files
  - Requires Bearer token authentication
  - Returns JSON response with migration results

- **POST /admin/posts**
  - Creates a new blog post
  - Requires Bearer token authentication
  - Request body should be JSON with a `content` field containing markdown text
  - Example:
    ```json
    {
      "content": "# My Blog Post Title\n\nHere is some content in *Markdown* format."
    }
    ```
  - Returns JSON response with post details including URL
  - Example response:
    ```json
    {
      "success": true,
      "message": "Post created successfully",
      "post_id": 123,
      "post_hash": "a1b2c3d4",
      "post_url": "/post/a1b2c3d4"
    }
    ```

- **PUT /admin/posts/{hash}**
  - Updates an existing blog post
  - Requires Bearer token authentication
  - Hash must be 8 alphanumeric characters
  - Request body should be JSON with a `content` field containing markdown text
  - Example:
    ```json
    {
      "content": "# Updated Post Title\n\nUpdated content in *Markdown* format."
    }
    ```
  - Returns JSON response with update status
  - Example response:
    ```json
    {
      "success": true,
      "message": "Post updated successfully",
      "post_id": 123,
      "post_hash": "a1b2c3d4"
    }
    ```

## Development

### Web Development

1. Create database migrations in `web/src/Migrations/`
2. Add models in `web/src/Models/`
3. Create controllers in `web/src/Controller/`
4. Add views in `web/src/Views/`

### App Development

1. Add new pages in `app/Dropblog/Views/`
2. Create view models in `app/Dropblog/ViewModels/`
3. Add services in `app/Dropblog/Services/`

### Running Migrations

Migrations are automatically run when calling the update endpoint:
```bash
curl -X POST -H "Authorization: Bearer YOUR_API_KEY" https://your-blog.com/admin/update
```

## Deployment

### Web Deployment

The web application is automatically deployed when changes are pushed to the `main` branch that affect files in `web/src/`.

### App Deployment

App builds are available through GitHub Actions workflows:
- Android: `deploy-android.yml`
- iOS: `deploy-ios.yml`
- Windows: `deploy-windows.yml`
- MacCatalyst: `deploy-maccatalyst.yml`

Each workflow can be triggered manually through the GitHub Actions UI.

## Security

- All admin operations require API key authentication
- SQL injection prevention through PDO
- XSS protection through proper escaping
- CSRF protection through API key validation
- HashId validation for all post operations
- Secure password handling in database

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Contributing

1. Fork the repository
2. Create your feature branch
3. Commit your changes
4. Push to the branch
5. Create a new Pull Request

## Credits

- [Parsedown](https://github.com/erusev/parsedown) for Markdown parsing
- [Hashids](https://github.com/vinkla/hashids) for URL shortening
- [.NET MAUI](https://github.com/dotnet/maui) for cross-platform app development 