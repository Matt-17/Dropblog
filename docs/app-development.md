# App Development Guide

This guide covers developing and customizing the Dropblog cross-platform app built with .NET MAUI and Blazor.

## 📱 App Overview

The Dropblog app provides a native cross-platform experience for creating and publishing blog posts:

- **Platforms**: Windows, Android, iOS, macOS
- **Technology**: .NET MAUI with Blazor Hybrid
- **Features**: Smart Markdown editor, visual post type selection, direct publishing

## 🏗️ Architecture

### Technology Stack
- **.NET 9.0**: Core framework
- **.NET MAUI**: Cross-platform UI framework  
- **Blazor Hybrid**: Web-based UI components in native apps
- **JavaScript Interop**: Enhanced editor functionality

### Project Structure

```
app/Dropblog/
├── Components/
│   ├── Layout/
│   │   ├── MainLayout.razor      # Main app layout
│   │   └── NavMenu.razor         # Navigation menu
│   ├── Pages/
│   │   └── Home.razor            # Main post creation page
│   ├── MarkdownEditor.razor      # Enhanced markdown editor
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

## 🚀 Getting Started

### Prerequisites

- **.NET 9.0 SDK**
- **Platform-specific workloads** for your target platforms
- **IDE**: Visual Studio 2022, VS Code, or JetBrains Rider

### Install Workloads

```bash
# Install required workloads
dotnet workload install maui
dotnet workload install android
dotnet workload install ios
dotnet workload install maccatalyst
```

### Clone and Setup

```bash
# Navigate to app directory
cd app/Dropblog

# Restore dependencies
dotnet restore

# Build the app
dotnet build
```

## ⚙️ Configuration

### API Connection Setup

Edit `Services/BlogApiService.cs` to configure your blog connection:

```csharp
public class BlogApiService
{
    private const string BaseUrl = "https://your-blog.com";
    private const string ApiKey = "YOUR_API_KEY";
    
    private readonly HttpClient _httpClient;
    
    public BlogApiService()
    {
        _httpClient = new HttpClient();
        _httpClient.DefaultRequestHeaders.Authorization = 
            new System.Net.Http.Headers.AuthenticationHeaderValue("Bearer", ApiKey);
    }
    
    // ... rest of implementation
}
```

### Environment-Specific Configuration

[TODO] Add support for multiple environments:

```csharp
// Development configuration
#if DEBUG
    private const string BaseUrl = "http://localhost/dropblog";
    private const string ApiKey = "dev-api-key";
#else
    private const string BaseUrl = "https://your-blog.com";
    private const string ApiKey = "prod-api-key";
#endif
```

## 🛠️ Building and Running

### Development Builds

```bash
# Run on specific platform
dotnet run --framework net9.0-windows10.0.19041.0    # Windows
dotnet run --framework net9.0-android                # Android
dotnet run --framework net9.0-ios                    # iOS
dotnet run --framework net9.0-maccatalyst            # macOS
```

### Release Builds

```bash
# Build release version
dotnet build -c Release -f net9.0-android
dotnet build -c Release -f net9.0-ios
dotnet build -c Release -f net9.0-windows10.0.19041.0
dotnet build -c Release -f net9.0-maccatalyst
```

### Publishing

```bash
# Publish for distribution
dotnet publish -c Release -f net9.0-android
dotnet publish -c Release -f net9.0-ios
dotnet publish -c Release -f net9.0-windows10.0.19041.0
dotnet publish -c Release -f net9.0-maccatalyst
```

## ✨ App Features

### Smart Markdown Editor

The enhanced markdown editor provides intelligent text handling:

#### Text Selection Aware Formatting
- **Bold**: Select text → Click Bold → `**selected text**`
- **Italic**: Select text → Click Italic → `*selected text*`
- **Underline**: Select text → Click Underline → `<u>selected text</u>`

#### Smart Cursor Positioning
- **No Selection**: Click Bold → Cursor positioned inside `**|**`
- **Empty Selection**: Optimal cursor placement for immediate typing

#### List Management
- **Toggle Lists**: Intelligently add/remove `- ` prefixes
- **Multi-line Support**: Handle multiple selected lines
- **Smart Detection**: Recognize existing list formatting

#### Link Creation
- **Template Insertion**: Insert `[link text](url)` with "link text" selected
- **URL Detection**: If selected text is URL, automatically place in URL field
- **Easy Editing**: Pre-select text for immediate replacement

### Visual Post Type Selection

Beautiful visual selector with actual blog icons:

#### Post Types Available
- 📝 **Note**: Default text post
- 🔗 **Link**: Link sharing
- 💬 **Comment**: Comment or response
- 💭 **Quote**: Quote or citation
- 📷 **Photo**: Photo post
- 💻 **Code**: Code snippet
- ❓ **Question**: Question post
- 🛒 **Shopping**: Shopping/product post
- 😤 **Rant**: Rant or opinion
- 📊 **Poll**: Poll or survey
- 🎵 **Media**: Media content
- 📚 **Book**: Book review/recommendation
- 📢 **Announcement**: Important announcement
- 📅 **Calendar**: Event or date-related

#### Visual Features
- **Icon Grid Layout**: Responsive grid with actual blog icons
- **Hover Effects**: Visual feedback on interaction
- **Shared Assets**: Same icons as web version (no duplication)
- **Responsive Design**: Adapts to screen size

### Direct Publishing

Seamless integration with your blog's API:

#### Publishing Flow
1. **Write Content**: Use enhanced markdown editor
2. **Format Text**: Apply formatting with toolbar
3. **Select Type**: Choose post type from visual grid
4. **Publish**: Click Post button
5. **Success Feedback**: Get confirmation with link to published post

#### Error Handling
- **Network Issues**: Graceful error handling with retry options
- **Validation Errors**: Clear feedback on content issues
- **Content Preservation**: Never lose content on errors

## 🔧 Customization

### Adding New Features

#### Create New Components

```razor
@* Components/MyCustomComponent.razor *@
<div class="custom-component">
    <h3>@Title</h3>
    <p>@Content</p>
    <button @onclick="HandleClick">Click Me</button>
</div>

@code {
    [Parameter] public string Title { get; set; } = "";
    [Parameter] public string Content { get; set; } = "";
    
    private void HandleClick()
    {
        // Custom logic here
    }
}
```

#### Add New Pages

```razor
@* Components/Pages/Settings.razor *@
@page "/settings"

<PageTitle>Settings - Dropblog</PageTitle>

<h1>Settings</h1>

<div class="settings-form">
    <!-- Settings UI here -->
</div>

@code {
    // Settings logic here
}
```

### Extending the API Service

```csharp
public partial class BlogApiService
{
    public async Task<ApiResponse<List<Post>>> GetPostsAsync(int page = 1, int limit = 10)
    {
        try
        {
            var response = await _httpClient.GetAsync($"/admin/posts?page={page}&limit={limit}");
            
            if (response.IsSuccessStatusCode)
            {
                var json = await response.Content.ReadAsStringAsync();
                var result = JsonSerializer.Deserialize<ApiResponse<List<Post>>>(json);
                return result;
            }
            
            return new ApiResponse<List<Post>>
            {
                Success = false,
                Message = $"HTTP {response.StatusCode}: {response.ReasonPhrase}"
            };
        }
        catch (Exception ex)
        {
            return new ApiResponse<List<Post>>
            {
                Success = false,
                Message = $"Network error: {ex.Message}"
            };
        }
    }
}
```

### Custom Styling

#### Global Styles

Edit `wwwroot/css/app.css`:

```css
/* Custom app styling */
.custom-editor {
    border: 2px solid #007ACC;
    border-radius: 8px;
    padding: 1rem;
}

.post-type-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 1rem;
    margin: 1rem 0;
}

.post-type-card {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 1rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s ease;
}

.post-type-card:hover {
    background: #e9ecef;
    border-color: #007ACC;
    transform: translateY(-2px);
}

.post-type-card.selected {
    background: #007ACC;
    color: white;
    border-color: #0056b3;
}
```

#### Component-Specific Styles

```razor
<style>
    .my-component {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 2rem;
        border-radius: 12px;
    }
</style>

<div class="my-component">
    <!-- Component content -->
</div>
```

## 🧪 Testing

### Unit Testing

[TODO] Add unit testing setup:

```csharp
[Test]
public async Task BlogApiService_CreatePost_ReturnsSuccess()
{
    // Arrange
    var service = new BlogApiService();
    var content = "# Test Post\n\nThis is a test.";
    
    // Act
    var result = await service.CreatePostAsync(content);
    
    // Assert
    Assert.IsTrue(result.Success);
    Assert.IsNotNull(result.Data?.PostUrl);
}
```

### UI Testing

[TODO] Add UI testing with Appium or similar:

```csharp
[Test]
public void MarkdownEditor_BoldButton_FormatsSelectedText()
{
    // UI test implementation
}
```

## 📦 Deployment

### Android Deployment

#### Debug APK
```bash
dotnet build -c Debug -f net9.0-android
# APK location: bin/Debug/net9.0-android/
```

#### Release APK
```bash
dotnet publish -c Release -f net9.0-android
# APK location: bin/Release/net9.0-android/publish/
```

#### Google Play Store
[TODO] Add signing configuration and Play Store deployment guide

### iOS Deployment

#### Development
```bash
dotnet build -c Debug -f net9.0-ios
```

#### App Store
[TODO] Add provisioning profile and App Store deployment guide

### Windows Deployment

#### MSIX Package
```bash
dotnet publish -c Release -f net9.0-windows10.0.19041.0
```

#### Microsoft Store
[TODO] Add Microsoft Store deployment guide

### macOS Deployment

#### Development
```bash
dotnet build -c Debug -f net9.0-maccatalyst
```

#### Mac App Store
[TODO] Add Mac App Store deployment guide

## 🚨 Troubleshooting

### Common Build Issues

#### Workload Issues
```bash
# Check installed workloads
dotnet workload list

# Repair workloads
dotnet workload repair
```

#### Android Issues
- **SDK Not Found**: Install Android SDK via Visual Studio or Android Studio
- **Emulator Issues**: Ensure Android emulator is running and accessible
- **Signing Issues**: Check debug keystore configuration

#### iOS Issues
- **Provisioning**: Ensure proper provisioning profiles are installed
- **Simulator**: Check that iOS simulator is available
- **Xcode**: Ensure Xcode command line tools are installed

### Runtime Issues

#### API Connection
- **Network Errors**: Check internet connectivity and API endpoint
- **Authentication**: Verify API key is correct and properly configured
- **CORS Issues**: [TODO] Configure CORS on server if needed

#### UI Issues
- **Layout Problems**: Check CSS and responsive design
- **JavaScript Errors**: Monitor browser dev tools in Blazor debug mode
- **Performance**: Monitor memory usage and optimize as needed

## 🔮 Advanced Topics

### Performance Optimization

#### Memory Management
```csharp
// Proper disposal of resources
public void Dispose()
{
    _httpClient?.Dispose();
    // Dispose other resources
}
```

#### Lazy Loading
[TODO] Implement lazy loading for large lists and images

### Platform-Specific Features

#### Android Notifications
[TODO] Add support for Android notifications

#### iOS Shortcuts
[TODO] Add support for iOS Siri Shortcuts

#### Windows Live Tiles
[TODO] Add support for Windows Live Tiles

### Security

#### API Key Protection
[TODO] Implement secure storage for API keys:

```csharp
// Use secure storage for sensitive data
await SecureStorage.SetAsync("api_key", apiKey);
var storedKey = await SecureStorage.GetAsync("api_key");
```

#### Certificate Pinning
[TODO] Implement certificate pinning for enhanced security

---

Need help with app development? Check [Troubleshooting](troubleshooting.md) or open an issue on GitHub. 