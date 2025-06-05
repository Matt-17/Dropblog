# Testing Guide

This guide covers unit testing for the Dropblog project, including setup, running tests, and writing new tests.

## Overview

Dropblog uses comprehensive unit testing to ensure code quality and prevent regressions. The testing setup includes:

- **PHP Backend Tests**: Using PHPUnit 10 with SQLite in-memory database
- **Future MAUI App Tests**: Planned for .NET testing framework
- **Test Coverage**: HTML and console coverage reports
- **CI/CD Integration**: Ready for GitHub Actions

## Setup (One Time Only)

### Option 1: Install PHP + PHPUnit in WSL

```bash
# Update package list
sudo apt update

# Install PHP and required extensions
sudo apt install php8.2 php8.2-cli php8.2-sqlite3 php8.2-mbstring php8.2-xml

# Install PHPUnit globally (better for production - not in vendor)
wget -O phpunit https://phar.phpunit.de/phpunit-10.phar
chmod +x phpunit
sudo mv phpunit /usr/local/bin/phpunit

# Verify installation
php --version
phpunit --version
```

### Option 2: Use DDEV (Recommended)

DDEV includes PHP and all necessary tools:

```bash
cd web/site
ddev start
```

### Why Global PHPUnit?

✅ **Production Safe**: PHPUnit doesn't get deployed to web server  
✅ **Faster Deploys**: Smaller vendor directory  
✅ **Version Control**: Same PHPUnit version across all projects  
✅ **CI/CD Friendly**: Most CI systems have PHPUnit pre-installed

## Test Structure

```
tests/
├── site/                   # PHP Backend Tests
│   ├── Models/             # Model layer tests
│   │   └── PostTypeTest.php
│   ├── Controller/         # API controller tests
│   ├── Utils/              # Utility class tests
│   ├── bootstrap.php       # Test environment setup
│   ├── phpunit.xml         # PHPUnit configuration
│   └── README.md           # Quick commands
├── app/                    # Future: MAUI app tests
└── README.md               # This gets removed, docs/ is better
```

## Running Tests

### Method 1: From tests/site Directory (Recommended)

```bash
cd tests/site

# All tests
phpunit

# With coverage report
phpunit --coverage-html coverage

# Verbose output with test descriptions
phpunit --testdox --verbose

# Single test file
phpunit Models/PostTypeTest.php

# Specific test method
phpunit --filter testCreateWithValidDataReturnsId
```

### Method 2: Using Composer Scripts

From project root or `web/site/`:

```bash
cd web/site
composer test                # Runs all tests
composer test-coverage       # Runs tests with coverage
```

### Method 3: Using DDEV

```bash
# From anywhere in project
ddev exec phpunit --configuration tests/site/phpunit.xml

# Or start a shell and run normally  
ddev ssh
cd tests/site
phpunit
```

### Method 4: CI/CD Integration

For GitHub Actions, Azure DevOps, etc:

```yaml
- name: Setup PHP
  uses: shivammathur/setup-php@v2
  with:
    php-version: 8.2
    tools: phpunit

- name: Install dependencies
  run: composer install --no-dev --optimize-autoloader
  working-directory: web/site

- name: Run tests
  run: phpunit --configuration tests/site/phpunit.xml
```

## Test Database

Tests use an **SQLite in-memory database** that's automatically created and destroyed for each test run. This provides:

- ✅ **Isolation**: Each test runs with a fresh database
- ✅ **Speed**: In-memory operations are extremely fast
- ✅ **Portability**: No external database setup required
- ✅ **Consistency**: Same environment across all machines

### Database Schema

The test database includes all production tables:

- `posts` - Blog posts with content and metadata
- `post_types` - Post type definitions (note, link, photo, etc.)
- `migrations` - Migration tracking

### Default Test Data

Each test starts with these default post types:
- **note**: Quick thoughts and updates (`note.svg`)
- **link**: Shared links with commentary (`link.svg`) 
- **photo**: Photo posts with captions (`photo.svg`)

## Writing Tests

### Test File Structure

1. **Location**: Place tests in appropriate subdirectory (`Models/`, `Controller/`, `Utils/`)
2. **Naming**: Use `ClassNameTest.php` convention
3. **Namespace**: Follow `Dropblog\Tests\[Subdirectory]` pattern

### Basic Test Template

```php
<?php

namespace Dropblog\Tests\Models;

use PHPUnit\Framework\TestCase;
use Dropblog\Models\YourModel;

class YourModelTest extends TestCase
{
    private YourModel $model;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new YourModel();
        TestDatabase::resetTestDatabase(); // Fresh database
    }
    
    protected function tearDown(): void
    {
        // Clean up if needed
        parent::tearDown();
    }
    
    public function testSomething(): void
    {
        // Arrange
        $expectedValue = 'expected';
        
        // Act
        $result = $this->model->doSomething();
        
        // Assert
        $this->assertEquals($expectedValue, $result);
    }
}
```

### Test Categories

#### Model Tests
Test business logic, database operations, validation:

```php
public function testCreateWithValidDataReturnsId(): void
{
    $data = ['slug' => 'test', 'name' => 'Test'];
    $id = $this->model->create($data);
    $this->assertIsInt($id);
    $this->assertGreaterThan(0, $id);
}

public function testCreateThrowsExceptionForInvalidData(): void
{
    $this->expectException(InvalidArgumentException::class);
    $this->model->create([]); // Missing required fields
}
```

#### Controller Tests
Test API endpoints, authentication, request/response:

```php
public function testCreatePostWithValidAuthReturnsSuccess(): void
{
    // Mock authentication, test endpoint
}

public function testCreatePostWithoutAuthReturns401(): void
{
    // Test unauthorized access
}
```

#### Utility Tests
Test helper functions, formatting, validation:

```php
public function testHashIdEncodeGeneratesValidHash(): void
{
    $hash = HashIdHelper::encode(123);
    $this->assertIsString($hash);
    $this->assertEquals(8, strlen($hash));
}
```

## Test Coverage

### Generating Coverage Reports

```bash
cd tests/site
phpunit --coverage-html coverage
```

This creates an HTML coverage report in `tests/site/coverage/index.html`.

### Coverage Goals

- **Models**: 95%+ coverage (critical business logic)
- **Controllers**: 90%+ coverage (API endpoints)
- **Utils**: 90%+ coverage (helper functions)
- **Overall**: 85%+ coverage

### Viewing Coverage

Open `tests/site/coverage/index.html` in your browser to see:
- Line-by-line coverage
- Method coverage statistics
- Uncovered code highlights
- Coverage trends

## Current Test Status

### ✅ Completed (30+ tests)
- **PostType Model**: Complete CRUD operations, validation, caching, edge cases

### 🚧 In Progress
- **PostModel**: Post creation, content handling, metadata
- **AdminController**: API endpoints, authentication, error handling
- **HashIdHelper**: URL shortening utilities

### 📋 Planned
- **Database utilities**: Connection handling, migrations
- **Search functionality**: Full-text search, filtering
- **Integration tests**: End-to-end API testing
- **.NET MAUI tests**: Mobile app testing

## Production Deployment

### Clean Vendor Directory

For production deployments, use:

```bash
composer install --no-dev --optimize-autoloader
```

This excludes all dev dependencies and optimizes the autoloader. Your `vendor/` directory will be much smaller and won't include testing tools.

### Testing Before Deploy

Always run tests before deploying:

```bash
cd tests/site && phpunit
cd web/site && composer install --no-dev --optimize-autoloader
```

## Troubleshooting

### Common Issues

**"php: command not found" in WSL:**
```bash
sudo apt install php8.2-cli php8.2-sqlite3 php8.2-mbstring php8.2-xml
```

**"phpunit: command not found":**
```bash
# Install globally
wget -O phpunit https://phar.phpunit.de/phpunit-10.phar
chmod +x phpunit
sudo mv phpunit /usr/local/bin/phpunit
```

**"Class not found" errors:**
- Ensure autoload paths are correct in `tests/site/bootstrap.php`
- Check that Composer dependencies are installed in `web/site/`

**Database connection errors:**
- Tests should use in-memory SQLite, not production database
- Verify `TestDatabase` class is being used

### Debug Mode

Enable verbose output:

```bash
phpunit --verbose --debug --testdox
```

## Best Practices

1. **Test Isolation**: Each test should be independent
2. **Clear Naming**: Test names should describe what they test
3. **AAA Pattern**: Arrange, Act, Assert structure
4. **Edge Cases**: Test boundary conditions and error cases
5. **Fast Tests**: Keep tests quick (< 1 second each)
6. **Data Providers**: Use for testing multiple scenarios
7. **Global PHPUnit**: Don't include PHPUnit in vendor for production

## Resources

- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [Testing Best Practices](https://phpunit.de/best-practices.html)
- [Mockery Documentation](http://docs.mockery.io/) (for mocking) 