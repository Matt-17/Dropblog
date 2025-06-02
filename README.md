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
    
    // Localization
    public const LOCALE = 'en-US'; // e.g., 'en-US', 'de-DE', 'de-AT'
    
    // Other settings
    public const TIMEZONE = 'Europe/Berlin';
    public const DATE_FORMAT = 'd. F Y';
    public const DEBUG = false;
}
```

### Localization

The blog uses a **config-based localization system** with smart fallback chains for regional language variants.

#### Configuration

Set the locale in your `Config.php`:

```php
// Examples:
public const LOCALE = 'en-US';    // English (default)
public const LOCALE = 'de-DE';    // German (Germany)
public const LOCALE = 'de-AT';    // German (Austria)
```

The `LOCALE` constant must be set - the system will throw an error if not configured.

#### File Structure

```
web/src/resources/locales/
├── strings.json        # Default (English) - always loaded first
├── strings.de.json     # German base translations
├── strings.de-DE.json  # German (Germany) regional overrides
└── strings.de-AT.json  # German (Austria) regional overrides
```

#### Fallback Chain

The system loads translations in this order, with later files overriding earlier ones:

**For `en-US` or `en`:**
1. `strings.json` ✓

**For `de-DE`:**
1. `strings.json` (default)
2. `strings.de.json` (German base)
3. `strings.de-DE.json` (German Germany overrides)

**For `de-AT`:**
1. `strings.json` (default)
2. `strings.de.json` (German base)
3. `strings.de-AT.json` (Austrian German overrides)

#### Usage in Code

```php
// Simple translation
Localization::t('common.search')

// With parameters
Localization::t('common.powered_by', ['title' => 'My Blog'])

// Nested keys using dot notation
Localization::t('months.january')  // "January" or "Januar" or "Jänner"
```

#### Translation File Format

JSON with nested structure:

```json
{
  "common": {
    "search": "Search",
    "home": "Home"
  },
  "months": {
    "january": "January"
  }
}
```

#### Regional Variants Example

**strings.de.json** (German base):
```json
{
  "months": {
    "january": "Januar"
  }
}
```

**strings.de-AT.json** (Austrian override):
```json
{
  "months": {
    "january": "Jänner"
  }
}
```

When `LOCALE = 'de-AT'`, January will be "Jänner" instead of "Januar".

#### Adding New Languages

1. Create `strings.{language}.json` (e.g., `strings.fr.json`)
2. Add your translations using the same structure as `strings.json`
3. For regional variants, create `strings.{language}-{region}.json`
4. Set `Config::LOCALE` to your desired locale

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

# MAUI Mobile App

A cross-platform mobile and desktop app for creating blog posts using .NET MAUI and Blazor.

## App Features

- ✍️ **Enhanced Markdown Editor** with intelligent text handling:
  - **Bold** (`**text**`) - Wraps selected text or positions cursor inside `**|**`
  - *Italic* (`*text*`) - Wraps selected text or positions cursor inside `*|*`
  - <u>Underline</u> (`<u>text</u>`) - Wraps selected text or positions cursor inside tags
  - **Smart Lists** - Toggle list formatting (adds/removes `- ` on current line)
  - **Smart Links** - Inserts `[link text](url)` with "link text" pre-selected
  - **Code blocks** - Supports both inline `` `code` `` and fenced ```code blocks```
  - Real-time cursor positioning and text selection

- 🎨 **Visual Post Type Selector** - Beautiful horizontal grid with icons:
  - 📝 Note, 🔗 Link, 💬 Comment, 💭 Quote, 📷 Photo, 💻 Code, ❓ Question
  - 🛒 Shopping, 😤 Rant, 📊 Poll, 🎵 Media, 📚 Book, 📢 Announcement, 📅 Calendar
  - **Original blog icons** shared from the web version
  - Hover effects and visual feedback
  - Responsive grid layout for all screen sizes

- 🌐 **Direct Publishing** to your blog via API
- ✅ **Success Feedback** with link to view published post
- 📱 **Cross-platform** support (Windows, Android, iOS, macOS)

## Recent App Enhancements

### 🎯 Smart Markdown Editor
- **Text Selection Aware**: If you select text and click Bold, it becomes `**selected text**`
- **Cursor Positioning**: If nothing is selected, cursor is positioned optimally (e.g., inside `**|**`)
- **Smart Lists**: Toggle list formatting intelligently - adds/removes dashes as needed
- **Link Templates**: Inserts link template with "link text" automatically selected for easy editing
- **Smart URL Detection**: If selected text is a URL, automatically places it in the URL field

### 🎨 Visual Post Type Selection
- **Icon Grid Layout**: Replaced dropdown with visual grid showing actual blog icons
- **Better UX**: Large, clickable cards with icons and labels
- **Shared Assets**: References the same icons as the web version (no duplication)
- **Positioning**: Post type selection moved below content editor for better workflow

### 🔧 Technical Improvements
- **JSInterop Integration**: Enhanced editor with JavaScript for proper text manipulation
- **Responsive Design**: Optimized for both desktop and mobile screens
- **Error Handling**: Graceful fallbacks + preserves content on errors
- **Real-time Updates**: Proper content synchronization between editor and form

## App Configuration

The app is currently hardcoded to work with:
- **Domain**: `numbertools.de`
- **API Key**: `ADMIN_API_KEY`
- **Endpoint**: `POST /admin/posts`

To change these settings, edit `app/Dropblog/Services/BlogApiService.cs`:

```csharp
private const string BaseUrl = "https://your-domain.com";
private const string ApiKey = "YOUR_API_KEY";
```

## Building and Running the App

### Prerequisites
- .NET 9.0 SDK
- Platform-specific workloads for your target platform

### Build
```bash
cd app
dotnet build
```

### Run on Windows
```bash
dotnet run --framework net9.0-windows10.0.19041.0
```

### Run on Android (with emulator/device)
```bash
dotnet run --framework net9.0-android
```

### Run on iOS (with simulator/device)
```bash
dotnet run --framework net9.0-ios
```

### Run on macOS
```bash
dotnet run --framework net9.0-maccatalyst
```

## App User Experience

The app provides an intuitive experience:

1. **Write Content**: Use the enhanced markdown editor with smart formatting
2. **Format Text**: Select text and use toolbar buttons for instant formatting
3. **Choose Type**: Visually select post type from the icon grid below the editor
4. **Publish**: Click the Post button to publish directly to your blog
5. **Success**: Get immediate feedback with a link to view your published post

## App Project Structure

```
app/Dropblog/
├── Components/
│   ├── Layout/
│   │   ├── MainLayout.razor      # Main app layout
│   │   └── NavMenu.razor         # Navigation menu
│   ├── Pages/
│   │   └── Home.razor            # Main post creation page
│   ├── MarkdownEditor.razor      # Enhanced markdown editor with JSInterop
│   ├── PostTypeSelector.razor    # Visual post type selector
│   └── _Imports.razor            # Global imports
├── Services/
│   └── BlogApiService.cs         # API communication service
├── wwwroot/
│   ├── css/
│   │   └── app.css              # Global styles + responsive CSS
│   ├── js/
│   │   └── markdownEditor.js    # JavaScript for enhanced editor
│   └── index.html               # Main HTML template
├── MauiProgram.cs               # App configuration
└── Dropblog.csproj             # Project file
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