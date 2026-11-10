# Dropblog

A minimalist, no-frills blogging platform that focuses on simplicity and performance. Dropblog is designed for those who want to share their thoughts without the complexity of traditional blogging platforms.

## Features

- **Simple Post System**: Just write and post - no categories, tags, or complex metadata
- **Clean URLs**: Short, readable URLs using HashIds
- **Markdown Support**: Write your posts in Markdown for better formatting
- **Monthly Archives**: Posts are automatically organized by month
- **Responsive Design**: Clean, mobile-friendly interface
- **Cross-Platform App**: Native apps for Android, iOS, Windows, and MacCatalyst
- **RESTful Admin API**: Simple API for managing your blog
- **Automated Deployment**: GitHub Actions workflows for web and app deployment

## Quick Start

### Requirements
- PHP 8.2 or higher
- MySQL/MariaDB
- Apache with mod_rewrite enabled
- Composer for dependency management

### Installation
```bash
git clone https://github.com/Matt-17/Dropblog.git
cd Dropblog/web/site
composer install
cp config.local.example.php config.local.php
# Edit config.local.php with your settings
```

Point your web server to `web/site/wwwroot` and run the initial setup:
```bash
curl -X POST -H "Authorization: Bearer YOUR_API_KEY" https://your-blog.com/admin/update
```

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Credits

- [Parsedown](https://github.com/erusev/parsedown) for Markdown parsing
- [Hashids](https://github.com/vinkla/hashids) for URL shortening
- [.NET MAUI](https://github.com/dotnet/maui) for cross-platform app development 