# Contributing to Dropblog

We welcome contributions to Dropblog! This guide will help you get started with contributing to the project.

## 🤝 How to Contribute

### Types of Contributions

We appreciate all kinds of contributions:

- **Bug Reports**: Help us identify and fix issues
- **Feature Requests**: Suggest new features or improvements
- **Code Contributions**: Submit bug fixes or new features
- **Documentation**: Improve or add to our documentation
- **Testing**: Help test new features and report issues
- **Translations**: Add support for new languages

## 🚀 Getting Started

### Fork and Clone

1. **Fork the repository** on GitHub
2. **Clone your fork** locally:
   ```bash
   git clone https://github.com/yourusername/dropblog.git
   cd dropblog
   ```
3. **Add the upstream remote**:
   ```bash
   git remote add upstream https://github.com/originalowner/dropblog.git
   ```

### Set Up Development Environment

#### Web Development Setup

```bash
cd web/src
composer install
cp Config.template.php Config.php
# Edit Config.php with your development settings
```

#### App Development Setup

```bash
cd app/Dropblog
dotnet restore
dotnet build
```

## 🔄 Contribution Workflow

### 1. Create a Feature Branch

```bash
git checkout -b feature/your-feature-name
# or
git checkout -b bugfix/issue-description
# or  
git checkout -b docs/documentation-update
```

### 2. Make Your Changes

- **Follow coding standards** (see below)
- **Write clear, descriptive commit messages**
- **Add tests** for new functionality
- **Update documentation** as needed

### 3. Test Your Changes

#### Web Testing
```bash
# Test web functionality
# Run your local blog instance
# Test API endpoints
curl -X POST -H "Authorization: Bearer YOUR_API_KEY" http://localhost/admin/update
```

#### App Testing
```bash
# Test app functionality
dotnet run --framework net9.0-windows10.0.19041.0
# Test on different platforms if possible
```

### 4. Commit Your Changes

```bash
git add .
git commit -m "Add feature: description of what you added"
```

#### Commit Message Guidelines

Use clear, descriptive commit messages:

```
Add feature: Smart Markdown editor with text selection

- Implement text selection aware formatting
- Add smart cursor positioning for empty selections
- Include list toggle functionality
- Add link template insertion with URL detection
```

**Format:**
- Use present tense ("Add feature" not "Added feature")
- Keep first line under 50 characters
- Add detailed description if needed
- Reference issues: "Fixes #123" or "Closes #456"

### 5. Push and Create Pull Request

```bash
git push origin feature/your-feature-name
```

Then create a Pull Request on GitHub with:
- **Clear title** describing the change
- **Detailed description** of what you changed and why
- **Link to related issues** (if any)
- **Screenshots** (for UI changes)
- **Testing instructions** for reviewers

## 📋 Coding Standards

### PHP Standards (Web)

- **PSR-4 autoloading** for class organization
- **Descriptive variable names**: `$postContent` not `$pc`
- **Type hints** where appropriate: `function createPost(string $content): array`
- **Error handling** with try-catch blocks
- **Security first**: Always sanitize input and escape output
- **Documentation**: PHPDoc comments for public methods

```php
/**
 * Creates a new blog post from markdown content
 *
 * @param string $content The markdown content of the post
 * @param string $postType The type of post (optional, defaults to 'note')
 * @return array Response array with success status and post details
 * @throws DatabaseException If database operation fails
 */
public function createPost(string $content, string $postType = 'note'): array
{
    // Implementation here
}
```

### C# Standards (App)

- **PascalCase** for public members: `CreatePostAsync()`
- **camelCase** for private fields: `_httpClient`
- **Async/await** for asynchronous operations
- **Proper disposal** of resources
- **XML documentation** for public APIs

```csharp
/// <summary>
/// Creates a new blog post via the API
/// </summary>
/// <param name="content">The markdown content of the post</param>
/// <param name="postType">The type of post (optional)</param>
/// <returns>API response with post details</returns>
public async Task<ApiResponse<PostData>> CreatePostAsync(string content, string postType = "note")
{
    // Implementation here
}
```

### Frontend Standards (CSS/JavaScript)

- **BEM methodology** for CSS class names: `.post-type-card--selected`
- **Mobile-first** responsive design
- **Semantic HTML** elements
- **Accessible** markup with proper ARIA labels
- **Modern JavaScript** (ES6+) features

## 🧪 Testing Guidelines

### Required Tests

- **API endpoint tests** for new endpoints
- **Unit tests** for business logic
- **Integration tests** for database operations
- **UI tests** for app components (when applicable)

### Test Structure

```php
// PHP Test Example
class PostControllerTest extends TestCase
{
    public function testCreatePostWithValidContent()
    {
        // Arrange
        $content = "# Test Post\n\nTest content";
        
        // Act  
        $response = $this->postController->create($content);
        
        // Assert
        $this->assertTrue($response['success']);
        $this->assertNotEmpty($response['post_hash']);
    }
}
```

```csharp
// C# Test Example
[Test]
public async Task CreatePostAsync_WithValidContent_ReturnsSuccess()
{
    // Arrange
    var content = "# Test Post\n\nTest content";
    
    // Act
    var result = await _apiService.CreatePostAsync(content);
    
    // Assert
    Assert.IsTrue(result.Success);
    Assert.IsNotNull(result.Data?.PostHash);
}
```

## 📚 Documentation Standards

### Code Documentation

- **Public APIs** must have documentation
- **Complex logic** should have inline comments
- **Configuration options** need clear descriptions
- **Examples** for usage patterns

### User Documentation

- **Clear, step-by-step** instructions
- **Code examples** with expected output
- **Screenshots** for UI features
- **Common issues** and solutions
- **Cross-references** to related documentation

## 🐛 Bug Reports

### Before Reporting

1. **Search existing issues** to avoid duplicates
2. **Test with latest version** to ensure bug still exists
3. **Gather necessary information** (see template below)

### Bug Report Template

```markdown
## Bug Description
A clear description of what the bug is.

## Steps to Reproduce
1. Go to '...'
2. Click on '...'
3. See error

## Expected Behavior
What you expected to happen.

## Actual Behavior
What actually happened.

## Environment
- OS: [e.g., Windows 10, macOS 12, Ubuntu 20.04]
- PHP Version: [e.g., 8.2.1]
- Database: [e.g., MySQL 8.0.28]
- Browser: [e.g., Chrome 98, Firefox 97]
- App Platform: [e.g., Windows, Android 12]

## Additional Context
Any other context about the problem, screenshots, error logs, etc.
```

## 💡 Feature Requests

### Before Requesting

1. **Check existing issues** and discussions
2. **Consider the project scope** - does it fit Dropblog's minimalist philosophy?
3. **Think about implementation** - is it feasible?

### Feature Request Template

```markdown
## Feature Description
A clear description of the feature you'd like to see.

## Use Case
Describe the problem this feature would solve.

## Proposed Solution
How you envision this feature working.

## Alternatives Considered
Other ways this could be implemented or solved.

## Additional Context
Any other context, mockups, or examples.
```

## 🔍 Code Review Process

### For Contributors

- **Self-review** your code before submitting
- **Respond promptly** to review feedback
- **Be open** to suggestions and changes
- **Test thoroughly** after making requested changes

### Review Criteria

Reviewers will check for:

- **Functionality**: Does the code work as intended?
- **Security**: Are there any security vulnerabilities?
- **Performance**: Is the code efficient?
- **Maintainability**: Is the code clean and well-organized?
- **Documentation**: Is the code properly documented?
- **Tests**: Are there adequate tests?
- **Consistency**: Does it follow project conventions?

## 🏷️ Versioning and Releases

### Version Numbers

We follow [Semantic Versioning](https://semver.org/):
- **MAJOR.MINOR.PATCH** (e.g., 1.2.3)
- **Major**: Breaking changes
- **Minor**: New features (backward compatible)
- **Patch**: Bug fixes (backward compatible)

### Release Process

1. **Feature freeze** for release candidates
2. **Testing period** with release candidates
3. **Documentation updates** for new features
4. **Release notes** preparation
5. **Tagged release** with changelog

## 🏆 Recognition

### Contributors

All contributors are recognized in:
- **GitHub contributors** list
- **Release notes** for significant contributions
- **Documentation credits** where appropriate

### Types of Recognition

- **Code contributors**: Listed in release notes
- **Documentation contributors**: Credited in improved docs
- **Bug reporters**: Mentioned in issue closures
- **Community helpers**: Recognized for support and assistance

## ❓ Getting Help

### Where to Ask

- **GitHub Issues**: For bugs and feature requests
- **GitHub Discussions**: For questions and community chat
- **Documentation**: Check existing docs first
- **Code Comments**: Look for inline documentation

### Response Times

We aim to:
- **Acknowledge** new issues within 48 hours
- **Review** pull requests within one week
- **Respond** to questions within a few days

## 📜 Code of Conduct

### Our Commitment

We are committed to providing a welcoming and inclusive environment for all contributors, regardless of:
- Experience level
- Gender identity and expression
- Sexual orientation
- Disability
- Personal appearance
- Body size
- Race or ethnicity
- Age
- Religion or nationality

### Expected Behavior

- **Be respectful** and considerate
- **Be collaborative** and helpful
- **Be patient** with beginners
- **Give constructive feedback**
- **Focus on what's best** for the community

### Unacceptable Behavior

- **Harassment** or discrimination
- **Trolling** or inflammatory comments
- **Personal attacks**
- **Spam** or off-topic content

### Enforcement

Issues will be addressed by project maintainers. Consequences may include warnings, temporary bans, or permanent removal from the project.

---

Thank you for contributing to Dropblog! Your efforts help make this project better for everyone. 🙏 