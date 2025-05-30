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

## Requirements

- PHP 8.2 or higher
- MySQL/MariaDB
- Apache with mod_rewrite enabled
- Composer for dependency management

## Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/yourusername/dropblog.git
   cd dropblog
   ```

2. Install dependencies:
   ```bash
   cd src
   composer install
   ```

3. Create your configuration:
   ```bash
   cp Config.template.php Config.php
   ```
   Edit `Config.php` with your database and blog settings.

4. Set up your web server:
   - Point your document root to the `src/wwwroot` directory
   - Ensure mod_rewrite is enabled
   - Make sure the `src` directory is outside the web root

5. Run the initial setup:
   ```bash
   curl -X POST -H "Authorization: Bearer YOUR_API_KEY" https://your-blog.com/admin/update
   ```

## Configuration

Edit `src/Config.php` to set up your blog:

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
}
```

## Directory Structure

```
src/
├── Config.php           # Configuration file
├── Controller/         # Controller classes
├── Migrations/         # Database migration files
├── Models/            # Data models
├── Utils/             # Utility classes
├── Views/             # View templates
│   ├── Admin/         # Admin views
│   ├── Components/    # Reusable components
│   └── Layouts/       # Layout templates
└── wwwroot/           # Public web root
    ├── index.php      # Front controller
    └── .htaccess      # URL rewriting rules
```

## API Endpoints

### Admin API

- **POST /admin/update**
  - Updates database schema using migration files
  - Requires Bearer token authentication
  - Returns JSON response with migration results

## Development

### Adding New Features

1. Create database migrations in `src/Migrations/`
2. Add models in `src/Models/`
3. Create controllers in `src/Controller/`
4. Add views in `src/Views/`

### Running Migrations

Migrations are automatically run when calling the update endpoint:
```bash
curl -X POST -H "Authorization: Bearer YOUR_API_KEY" https://your-blog.com/admin/update
```

## Security

- All admin operations require API key authentication
- SQL injection prevention through PDO
- XSS protection through proper escaping
- CSRF protection through API key validation

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