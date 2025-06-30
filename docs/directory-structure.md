# Directory Structure

This document provides a detailed overview of Dropblog's directory structure and explains the purpose of each component.

## 📁 Project Overview

```
dropblog/
├── app/                # .NET MAUI cross-platform application
├── web/                # Web application and installer
├── docs/               # Documentation (this folder)
├── .github/            # GitHub Actions workflows and templates
├── .git/               # Git repository data
├── .gitignore          # Git ignore rules
├── LICENSE             # MIT license file
└── README.md           # Project overview and quick start
```

## 🌐 Web Application Structure

### `/web/` - Web Application Root

```
web/
├── installer/          # Installation scripts and utilities
└── src/               # Main web application source code
```

### `/web/src/` - Source Code

```
web/src/
├── Config.php          # Main configuration file
├── Config.template.php # Configuration template
├── composer.json       # PHP dependencies
├── composer.lock       # Dependency lock file
├── vendor/            # Composer dependencies (auto-generated)
├── Controller/        # MVC Controllers
├── Models/           # Data models and entities  
├── Views/            # View templates
├── Utils/            # Utility classes and helpers
├── Migrations/       # Database migration files
├── resources/        # Static resources and assets
└── wwwroot/          # Public web root (document root)
```

#### Controllers (`/web/src/Controller/`)

```
Controller/
├── BaseController.php     # Base controller with common functionality
├── HomeController.php     # Homepage and post display
├── AdminController.php    # Admin API endpoints
├── PostController.php     # Post-related operations
└── ArchiveController.php  # Monthly archive functionality
```

**Purpose**: Handle HTTP requests, business logic, and coordinate between models and views.

#### Models (`/web/src/Models/`)

```
Models/
├── Post.php              # Post entity and database operations
├── Database.php          # Database connection and utilities
├── Migration.php         # Migration system handling
└── PostType.php          # Post type definitions and icons
```

**Purpose**: Data layer handling database operations, entity definitions, and business logic.

#### Views (`/web/src/Views/`)

```
Views/
├── layout.php           # Main layout template
├── home.php            # Homepage template
├── post.php            # Individual post display
├── archive.php         # Monthly archive page
├── error.php           # Error page template
└── partials/           # Reusable template components
    ├── header.php      # Page header
    ├── footer.php      # Page footer
    └── navigation.php  # Navigation menu
```

**Purpose**: Presentation layer with HTML templates and UI components.

#### Utilities (`/web/src/Utils/`)

```
Utils/
├── Router.php           # URL routing and request handling
├── Localization.php     # Multi-language support
├── HashIds.php          # URL shortening utility
├── MarkdownParser.php   # Markdown to HTML conversion
└── SecurityHelper.php   # Security utilities and validation
```

**Purpose**: Helper classes and utilities used throughout the application.

#### Migrations (`/web/src/Migrations/`)

```
Migrations/
├── 001_create_posts_table.sql      # Initial posts table
├── 002_add_post_types.sql          # Post type support
├── 003_add_indexes.sql             # Performance indexes
└── migration_tracker.php          # Migration execution tracking
```

**Purpose**: Version-controlled database schema changes and updates.

#### Resources (`/web/src/resources/`)

```
resources/
├── locales/             # Translation files
│   ├── strings.json     # Default English translations
│   ├── strings.de.json  # German translations
│   └── strings.de-AT.json # Austrian German overrides
├── icons/              # Post type icons and UI icons
└── templates/          # Email and other templates [TODO]
```

**Purpose**: Static resources, translations, and template files.

### `/web/src/wwwroot/` - Public Web Root

```
wwwroot/
├── index.php           # Main entry point
├── .htaccess          # Apache URL rewriting rules
├── favicon.ico        # Website favicon
├── robots.txt         # Search engine directives
├── css/              # Stylesheets
│   ├── main.css      # Main stylesheet
│   └── responsive.css # Mobile responsive styles
├── js/               # JavaScript files
│   ├── main.js       # Core JavaScript functionality
│   └── utils.js      # Utility functions
├── images/           # Image assets
│   ├── icons/        # UI icons and post type icons
│   └── backgrounds/  # Background images
└── uploads/          # User uploaded files [TODO]
```

**Purpose**: Publicly accessible files served directly by the web server.

**Security Note**: This is the only directory that should be accessible via web browser. All source code should be outside this directory.

## 📱 App Structure

### `/app/` - Cross-Platform App

```
app/
└── Dropblog/          # .NET MAUI application
```

### `/app/Dropblog/` - App Source

```
app/Dropblog/
├── Dropblog.csproj    # Project file with dependencies and build config
├── MauiProgram.cs     # App startup and configuration
├── Components/        # Blazor components and UI elements
├── Services/         # Business logic and API communication
├── wwwroot/          # Static web assets for Blazor
└── obj/              # Build outputs (auto-generated)
└── bin/              # Compiled binaries (auto-generated)
```

#### Components (`/app/Dropblog/Components/`)

```
Components/
├── Layout/                    # Layout components
│   ├── MainLayout.razor       # Main app layout
│   └── NavMenu.razor          # Navigation menu
├── Pages/                     # Page components
│   └── Home.razor             # Main post creation page
├── MarkdownEditor.razor       # Enhanced markdown editor
├── PostTypeSelector.razor     # Visual post type selector
└── _Imports.razor             # Global component imports
```

**Purpose**: Blazor components providing the user interface and interaction logic.

#### Services (`/app/Dropblog/Services/`)

```
Services/
├── BlogApiService.cs          # API communication with blog
├── StorageService.cs          # Local data storage [TODO]
└── ConfigService.cs           # App configuration management [TODO]
```

**Purpose**: Business logic, data services, and external API integration.

#### Web Assets (`/app/Dropblog/wwwroot/`)

```
wwwroot/
├── index.html         # Main HTML template for Blazor
├── css/              # App-specific stylesheets
│   └── app.css       # Main app styles with responsive design
├── js/               # JavaScript for enhanced functionality
│   └── markdownEditor.js # Editor enhancements and text manipulation
└── icons/            # App icons and UI elements
```

**Purpose**: Static assets served within the app's Blazor WebView.

## 🔧 Development and CI/CD

### `/.github/` - GitHub Integration

```
.github/
├── workflows/         # GitHub Actions CI/CD workflows
│   ├── deploy-web.yml        # Web application deployment
│   ├── deploy-android.yml    # Android app deployment
│   ├── deploy-ios.yml        # iOS app deployment
│   ├── deploy-windows.yml    # Windows app deployment
│   └── deploy-maccatalyst.yml # macOS app deployment
├── ISSUE_TEMPLATE/    # Issue templates for bug reports
└── pull_request_template.md # PR template
```

**Purpose**: Automated testing, building, and deployment workflows.

### `/.git/` - Version Control

Standard Git repository structure containing:
- **Commit history** and branches
- **Remote repository** configuration
- **Git hooks** and configuration
- **Tracking information** for files

## 📚 Documentation

### `/docs/` - Comprehensive Documentation

```
docs/
├── readme.md              # Documentation overview and navigation
├── introduction.md        # Detailed project introduction
├── getting-started.md     # Complete setup guide
├── installation.md        # Step-by-step installation [TODO]
├── configuration.md       # Configuration options and settings
├── localization.md        # Multi-language support guide
├── api-reference.md       # Complete API documentation
├── app-development.md     # Cross-platform app development
├── development.md         # Development workflows [TODO]
├── deployment.md          # Production deployment guide
├── database.md           # Database schema and migrations [TODO]
├── security.md           # Security features and best practices [TODO]
├── performance.md        # Performance optimization [TODO]
├── troubleshooting.md    # Common issues and solutions
├── contributing.md       # Contribution guidelines
└── changelog.md          # Version history [TODO]
```

**Purpose**: Comprehensive documentation covering all aspects of Dropblog.

## 🔍 File Type Breakdown

### Configuration Files

- **`Config.php`**: Main application configuration
- **`composer.json`**: PHP dependency management
- **`Dropblog.csproj`**: .NET project configuration
- **`.htaccess`**: Apache web server configuration
- **`.gitignore`**: Git exclusion rules

### Source Code Files

- **`.php`**: Server-side PHP code (web application)
- **`.cs`**: C# code (cross-platform app)
- **`.razor`**: Blazor component files (app UI)
- **`.sql`**: Database migration files

### Asset Files

- **`.css`**: Stylesheets for web and app
- **`.js`**: JavaScript for enhanced functionality
- **`.json`**: Translation files and configuration
- **`.html`**: HTML templates

### Build and Output

- **`vendor/`**: PHP dependencies (generated by Composer)
- **`obj/` and `bin/`**: .NET build outputs
- **Auto-generated files**: Should not be edited manually

## 🔒 Security Considerations

### Protected Directories

These directories should **NOT** be web-accessible:
- `/web/src/` (except `/wwwroot/`)
- `/app/`
- `/docs/`
- `/.git/`
- `/web/src/vendor/`

### Public Directories

Only these should be web-accessible:
- `/web/src/wwwroot/` (document root)

### Sensitive Files

Special attention required:
- **`Config.php`**: Contains database credentials and API keys
- **Migration files**: May contain sensitive schema information
- **`.env` files**: Environment-specific secrets [TODO]

## 📊 File Size Considerations

### Large Directories

- **`vendor/`**: PHP dependencies (can be large)
- **`node_modules/`**: If using Node.js tools [TODO]
- **`bin/` and `obj/`**: Build outputs

### Exclusions

These are typically excluded from version control:
- Build outputs (`bin/`, `obj/`)
- Dependencies (`vendor/`, `node_modules/`)
- User uploads (`wwwroot/uploads/`)
- Environment-specific configs

---

This structure promotes:
- **Separation of concerns** between web and app
- **Security** by keeping source code outside web root
- **Maintainability** through organized code structure
- **Scalability** with modular architecture
- **Cross-platform** development support 