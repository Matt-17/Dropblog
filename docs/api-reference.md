# API Reference

This document provides a complete reference for Dropblog's RESTful API, including all endpoints, request/response formats, and authentication details.

## 🔐 Authentication

All admin API endpoints require Bearer token authentication using your configured API key.

### Authentication Header
```http
Authorization: Bearer YOUR_API_KEY
```

### API Key Configuration
Set your API key in `web/src/Config.php`:
```php
public const ADMIN_API_KEY = 'your-secure-api-key-here';
```

## 📋 Base URL

All API endpoints are relative to your blog's base URL:
```
https://your-blog.com/admin/
```

## 🔄 Admin API Endpoints

### Database Update

Updates the database schema using migration files.

**Endpoint:** `POST /admin/update`

**Authentication:** Required

**Request:**
```http
POST /admin/update HTTP/1.1
Host: your-blog.com
Authorization: Bearer YOUR_API_KEY
```

**Response (Success):**
```json
{
  "success": true,
  "message": "Database updated successfully",
  "migrations_run": [
    "001_create_posts_table",
    "002_add_post_types",
    "005_create_post_types_table"
  ],
  "total_migrations": 3
}
```

**Response (No Updates):**
```json
{
  "success": true,
  "message": "Database is up to date",
  "migrations_run": [],
  "total_migrations": 0
}
```

**Response (Error):**
```json
{
  "success": false,
  "message": "Migration failed: Table 'posts' already exists",
  "error_details": "SQL Error details here"
}
```

### Create Post

Creates a new blog post from Markdown content.

**Endpoint:** `POST /admin/posts`

**Authentication:** Required

**Request:**
```http
POST /admin/posts HTTP/1.1
Host: your-blog.com
Authorization: Bearer YOUR_API_KEY
Content-Type: application/json

{
  "content": "# My Blog Post Title\n\nHere is some content in *Markdown* format.\n\n## Subheading\n\n- List item 1\n- List item 2\n\n**Bold text** and [link](https://example.com).",
  "post_type": "note",
  "metadata": {
    "custom_field": "value"
  }
}
```

**Request Parameters:**
- `content` (required): Markdown content of the post
- `post_type` (optional): Post type slug (defaults to "note")
- `type` (optional): Legacy field name, use `post_type` instead
- `metadata` (optional): JSON object with additional post metadata

**Response (Success):**
```json
{
  "success": true,
  "message": "Post created successfully",
  "post_id": 123,
  "post_hash": "a1b2c3d4",
  "post_url": "/post/a1b2c3d4",
  "post_type": {
    "slug": "note",
    "name": "Note",
    "emoji": "📝",
    "icon_path": "/assets/images/post-types/icon-note.png"
  }
}
```

**Response (Validation Error):**
```json
{
  "success": false,
  "message": "Invalid post type",
  "provided_type": "invalid-type",
  "valid_types": ["note", "link", "comment", "quote", "photo", "code"]
}
```

### Update Post

Updates an existing blog post.

**Endpoint:** `PUT /admin/posts/{hash}`

**Authentication:** Required

**Path Parameters:**
- `hash`: 8-character alphanumeric post identifier

**Request:**
```http
PUT /admin/posts/a1b2c3d4 HTTP/1.1
Host: your-blog.com
Authorization: Bearer YOUR_API_KEY
Content-Type: application/json

{
  "content": "# Updated Post Title\n\nUpdated content in *Markdown* format.\n\nThis post has been modified.",
  "post_type": "article"
}
```

**Request Parameters:**
- `content` (optional): Updated Markdown content
- `post_type` (optional): New post type slug
- `metadata` (optional): Updated metadata object

**Response (Success):**
```json
{
  "success": true,
  "message": "Post updated successfully", 
  "post_id": 123,
  "post_hash": "a1b2c3d4",
  "post_type": {
    "slug": "article",
    "name": "Article",
    "emoji": "📄",
    "icon_path": "/assets/images/post-types/icon-article.png"
  }
}
```

**Response (Not Found):**
```json
{
  "success": false,
  "message": "Post not found",
  "error_code": "POST_NOT_FOUND"
}
```

### Get Post

[TODO] Retrieve a specific post by hash.

**Endpoint:** `GET /admin/posts/{hash}`

**Authentication:** Required

**Response (Success):**
```json
{
  "success": true,
  "post": {
    "id": 123,
    "hash": "a1b2c3d4",
    "title": "My Blog Post Title",
    "content": "# My Blog Post Title\n\nContent here...",
    "post_type": "note",
    "created_at": "2024-03-15T10:30:00Z",
    "updated_at": "2024-03-15T14:45:00Z",
    "url": "/post/a1b2c3d4"
  }
}
```

### List Posts

[TODO] Retrieve a paginated list of posts.

**Endpoint:** `GET /admin/posts`

**Authentication:** Required

**Query Parameters:**
- `page` (optional): Page number (default: 1)
- `limit` (optional): Posts per page (default: 10, max: 100)
- `type` (optional): Filter by post type
- `from_date` (optional): Filter posts after date (ISO 8601)
- `to_date` (optional): Filter posts before date (ISO 8601)

**Response (Success):**
```json
{
  "success": true,
  "posts": [
    {
      "id": 123,
      "hash": "a1b2c3d4", 
      "title": "My Blog Post Title",
      "excerpt": "Here is some content in Markdown format...",
      "post_type": "note",
      "created_at": "2024-03-15T10:30:00Z",
      "url": "/post/a1b2c3d4"
    }
  ],
  "pagination": {
    "current_page": 1,
    "total_pages": 5,
    "total_posts": 47,
    "posts_per_page": 10
  }
}
```

### Delete Post

[TODO] Delete a post by hash.

**Endpoint:** `DELETE /admin/posts/{hash}`

**Authentication:** Required

**Response (Success):**
```json
{
  "success": true,
  "message": "Post deleted successfully",
  "post_hash": "a1b2c3d4"
}
```

## 🏷️ Post Types Management

### Get Post Types

Retrieves all available post types.

**Endpoint:** `GET /admin/post-types`

**Authentication:** Required

**Query Parameters:**
- `include_inactive` (optional): Set to "true" to include inactive post types

**Request:**
```http
GET /admin/post-types HTTP/1.1
Host: your-blog.com
Authorization: Bearer YOUR_API_KEY
```

**Response (Success):**
```json
{
  "success": true,
  "post_types": [
    {
      "id": 1,
      "slug": "note",
      "name": "Note",
      "description": "Default text post for thoughts and updates",
      "icon_filename": "icon-note.png",
      "emoji": "📝",
      "icon_path": "/assets/images/post-types/icon-note.png",
      "is_active": true,
      "sort_order": 1
    },
    {
      "id": 2,
      "slug": "link",
      "name": "Link",
      "description": "Share interesting links with context",
      "icon_filename": "icon-link.png",
      "emoji": "🔗",
      "icon_path": "/assets/images/post-types/icon-link.png",
      "is_active": true,
      "sort_order": 2
    }
  ],
  "total_count": 2
}
```

### Create Post Type

Creates a new custom post type.

**Endpoint:** `POST /admin/post-types`

**Authentication:** Required

**Request:**
```http
POST /admin/post-types HTTP/1.1
Host: your-blog.com
Authorization: Bearer YOUR_API_KEY
Content-Type: application/json

{
  "slug": "recipe",
  "name": "Recipe",
  "description": "Cooking recipes and food content",
  "emoji": "🍳",
  "icon_filename": "icon-recipe.png",
  "sort_order": 15
}
```

**Request Parameters:**
- `slug` (required): Unique identifier (2-50 chars, lowercase, alphanumeric + hyphens/underscores)
- `name` (required): Display name for the post type
- `description` (optional): Description of when to use this post type
- `emoji` (optional): Emoji representation (recommended)
- `icon_filename` (optional): Custom icon filename in post-types directory
- `is_active` (optional): Whether the post type is active (default: true)
- `sort_order` (optional): Display order (default: 0)

**Response (Success):**
```json
{
  "success": true,
  "message": "Post type created successfully",
  "post_type": {
    "id": 15,
    "slug": "recipe",
    "name": "Recipe",
    "description": "Cooking recipes and food content",
    "emoji": "🍳",
    "icon_filename": "icon-recipe.png",
    "icon_path": "/assets/images/post-types/icon-recipe.png",
    "is_active": true,
    "sort_order": 15
  }
}
```

**Response (Validation Error):**
```json
{
  "success": false,
  "message": "Invalid slug format. Use 2-50 lowercase letters, numbers, hyphens, and underscores only."
}
```

### Update Post Type

Updates an existing post type.

**Endpoint:** `PUT /admin/post-types/{id}`

**Authentication:** Required

**Path Parameters:**
- `id`: Post type ID

**Request:**
```http
PUT /admin/post-types/15 HTTP/1.1
Host: your-blog.com
Authorization: Bearer YOUR_API_KEY
Content-Type: application/json

{
  "name": "Cooking Recipe",
  "description": "Detailed cooking recipes with ingredients and instructions",
  "sort_order": 20
}
```

**Response (Success):**
```json
{
  "success": true,
  "message": "Post type updated successfully",
  "post_type": {
    "id": 15,
    "slug": "recipe",
    "name": "Cooking Recipe",
    "description": "Detailed cooking recipes with ingredients and instructions",
    "emoji": "🍳",
    "icon_path": "/assets/images/post-types/icon-recipe.png",
    "is_active": true,
    "sort_order": 20
  }
}
```

### Delete Post Type

Deletes a post type (only if no posts are using it).

**Endpoint:** `DELETE /admin/post-types/{id}`

**Authentication:** Required

**Path Parameters:**
- `id`: Post type ID

**Response (Success):**
```json
{
  "success": true,
  "message": "Post type deleted successfully"
}
```

**Response (In Use):**
```json
{
  "success": false,
  "message": "Cannot delete post type: 5 posts are using this type"
}
```

### Get Post Type Usage Statistics

Retrieves usage statistics for all post types.

**Endpoint:** `GET /admin/post-types/stats`

**Authentication:** Required

**Response (Success):**
```json
{
  "success": true,
  "post_type_stats": [
    {
      "id": 1,
      "slug": "note",
      "name": "Note",
      "emoji": "📝",
      "post_count": 42
    },
    {
      "id": 2,
      "slug": "link",
      "name": "Link", 
      "emoji": "🔗",
      "post_count": 15
    },
    {
      "id": 15,
      "slug": "recipe",
      "name": "Recipe",
      "emoji": "🍳",
      "post_count": 0
    }
  ],
  "total_types": 3
}
```

## 📊 Public API Endpoints

### Get Public Post

[TODO] Retrieve a public post without authentication.

**Endpoint:** `GET /api/posts/{hash}`

**Authentication:** Not required

**Response:**
```json
{
  "success": true,
  "post": {
    "hash": "a1b2c3d4",
    "title": "My Blog Post Title", 
    "content_html": "<h1>My Blog Post Title</h1><p>Content here...</p>",
    "post_type": {
      "slug": "note",
      "name": "Note",
      "emoji": "📝"
    },
    "created_at": "2024-03-15T10:30:00Z",
    "url": "/post/a1b2c3d4"
  }
}
```

### List Public Posts

[TODO] Get paginated list of public posts.

**Endpoint:** `GET /api/posts`

**Query Parameters:**
- `page` (optional): Page number
- `limit` (optional): Posts per page (max: 50)
- `type` (optional): Filter by post type slug

## 🎨 Post Type Icons and Images

### Icon Storage Options

Dropblog supports multiple approaches for post type icons:

#### 1. Emoji Icons (Recommended)
- Set the `emoji` field when creating/updating post types
- Cross-platform compatible
- No file management required
- Automatically scales for different screen sizes

#### 2. Custom Icon Files
- Upload PNG icons to `/web/src/wwwroot/assets/images/post-types/`
- Set `icon_filename` field (e.g., "icon-recipe.png")
- Recommended size: 64x64px for optimal display
- Fallback to default icon if file not found

#### 3. Icon Management
- Icons are served from `/assets/images/post-types/{filename}`
- Create the directory: `mkdir -p web/src/wwwroot/assets/images/post-types`
- Default fallback icon: `icon-default.png`

### Icon Guidelines

- **Size**: 64x64px PNG files work best
- **Style**: Simple, recognizable icons with good contrast
- **Naming**: Use descriptive filenames like `icon-recipe.png`
- **Fallback**: Always provide an emoji as fallback
- **Optimization**: Compress images for web performance

## 🏷️ Post Type System

### Built-in Post Types

Dropblog comes with 14 default post types:

| Slug | Name | Emoji | Description |
|------|------|-------|-------------|
| `note` | Note | 📝 | Default text post for thoughts and updates |
| `link` | Link | 🔗 | Share interesting links with context |
| `comment` | Comment | 💬 | Comments or responses to other content |
| `quote` | Quote | 💭 | Share quotes with proper attribution |
| `photo` | Photo | 📷 | Photo posts with descriptions |
| `code` | Code | 💻 | Code snippets and programming content |
| `question` | Question | ❓ | Ask questions to your audience |
| `shopping` | Shopping | 🛒 | Product recommendations and shopping lists |
| `rant` | Rant | 😤 | Express strong opinions and frustrations |
| `poll` | Poll | 📊 | Create polls and surveys |
| `media` | Media | 🎵 | Share music, videos, and media content |
| `book` | Book | 📚 | Book reviews and reading recommendations |
| `announcement` | Announcement | 📢 | Important announcements and news |
| `calendar` | Calendar | 📅 | Events and date-related content |

### Custom Post Types

You can create unlimited custom post types for your specific needs:

```bash
# Example: Create a recipe post type
curl -X POST \
  -H "Authorization: Bearer YOUR_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "slug": "recipe",
    "name": "Recipe", 
    "description": "Cooking recipes and food content",
    "emoji": "🍳",
    "sort_order": 15
  }' \
  https://your-blog.com/admin/post-types
```

### Post Type Features

- **Referential Integrity**: Cannot delete post types with existing posts
- **Flexible Icons**: Support for emoji or custom image files
- **Sorting**: Control display order with `sort_order` field
- **Active/Inactive**: Temporarily disable post types without deletion
- **Metadata Support**: Each post type can have custom metadata fields
- **API Driven**: Fully manageable via REST API

## 📝 Content Processing

### Markdown Support

All content is processed as Markdown with these features:

- **Headers**: `# H1`, `## H2`, `### H3`, etc.
- **Emphasis**: `*italic*`, `**bold**`, `***bold italic***`
- **Links**: `[text](url)` and `<url>`
- **Lists**: Bulleted (`-`, `*`) and numbered (`1.`)
- **Code**: Inline `` `code` `` and fenced ```code blocks```
- **Blockquotes**: `> quoted text`
- **Line breaks**: Double newline for paragraphs

### Content Extraction

The system automatically extracts:
- **Title**: First H1 heading (`# Title`) or first line
- **Excerpt**: First 150 characters (configurable)
- **Word Count**: Total words in content [TODO]
- **Reading Time**: Estimated reading time [TODO]

## ⚠️ Error Responses

### HTTP Status Codes

- `200 OK` - Request successful
- `400 Bad Request` - Invalid request data
- `401 Unauthorized` - Missing or invalid API key
- `404 Not Found` - Resource not found
- `422 Unprocessable Entity` - Validation errors
- `429 Too Many Requests` - Rate limit exceeded [TODO]
- `500 Internal Server Error` - Server error

### Error Response Format

```json
{
  "success": false,
  "message": "Human-readable error message",
  "error_code": "MACHINE_READABLE_CODE",
  "errors": {
    "field_name": "Specific field error message"
  },
  "timestamp": "2024-03-15T10:30:00Z"
}
```

### Common Error Codes

- `INVALID_API_KEY` - API key is invalid or missing
- `POST_NOT_FOUND` - Requested post doesn't exist
- `VALIDATION_FAILED` - Request data validation failed
- `RATE_LIMIT_EXCEEDED` - Too many requests [TODO]
- `DATABASE_ERROR` - Internal database error
- `MIGRATION_FAILED` - Database migration error

## 🔄 Rate Limiting

[TODO] API requests are rate-limited to prevent abuse:

- **Admin endpoints**: 60 requests per minute per API key
- **Public endpoints**: 100 requests per minute per IP
- **Burst allowance**: 10 additional requests
- **Headers included**: `X-RateLimit-Limit`, `X-RateLimit-Remaining`, `X-RateLimit-Reset`

## 📱 Client SDKs

### JavaScript/Node.js

```javascript
// Example API client usage
const DropblogAPI = require('@dropblog/api-client');

const client = new DropblogAPI({
  baseUrl: 'https://your-blog.com',
  apiKey: 'your-api-key'
});

// Create a post
const post = await client.posts.create({
  content: '# Hello World\n\nMy first post!',
  post_type: 'note'
});

console.log(`Post created: ${post.post_url}`);
```

### Python

```python
# Example Python client [TODO]
import dropblog

client = dropblog.Client(
    base_url='https://your-blog.com',
    api_key='your-api-key'
)

post = client.posts.create(
    content='# Hello World\n\nMy first post!',
    post_type='note'
)

print(f"Post created: {post['post_url']}")
```

## 🧪 Testing the API

### Using curl

```bash
# Test database update
curl -X POST \
  -H "Authorization: Bearer YOUR_API_KEY" \
  https://your-blog.com/admin/update

# Create a test post
curl -X POST \
  -H "Authorization: Bearer YOUR_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{"content":"# Test Post\n\nThis is a test.","post_type":"note"}' \
  https://your-blog.com/admin/posts

# Update a post
curl -X PUT \
  -H "Authorization: Bearer YOUR_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{"content":"# Updated Test Post\n\nThis has been updated."}' \
  https://your-blog.com/admin/posts/a1b2c3d4
```

### Using Postman

Import this collection: [TODO] Provide Postman collection download

### API Testing Tools

Recommended tools for API testing:
- **Postman**: GUI-based API testing
- **Insomnia**: Alternative to Postman
- **HTTPie**: Command-line HTTP client
- **curl**: Universal command-line tool

---

Need help with the API? Check [Troubleshooting](troubleshooting.md) or open an issue on GitHub. 