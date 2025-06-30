# Customizable Post Types - Implementation Guide

This document describes the new customizable post types system that replaces the hardcoded post type system in Dropblog.

## 🎯 Overview

Previously, Dropblog had 14 hardcoded post types. Now you can:
- Create unlimited custom post types
- Use emoji or custom icons
- Manage post types via REST API
- Maintain referential integrity (can't delete types with posts)
- Control display order and active status

## 🗄️ Database Changes

### New Tables

1. **`post_types`** - Stores all post type definitions
   - `id` - Primary key
   - `slug` - Unique identifier (e.g., 'note', 'recipe')
   - `name` - Display name (e.g., 'Note', 'Recipe')
   - `description` - Optional description
   - `icon_filename` - Custom icon file (optional)
   - `emoji` - Emoji representation (recommended)
   - `is_active` - Enable/disable post types
   - `sort_order` - Display order
   - Timestamps

2. **Posts table updates**
   - Added `post_type_id` foreign key to `post_types.id`
   - Kept legacy `type` column for backward compatibility
   - Foreign key constraint prevents deleting used post types

### Migration Process

Run the database migration to update your schema:

```bash
curl -X POST -H "Authorization: Bearer YOUR_API_KEY" https://your-blog.com/admin/update
```

This will:
1. Create the `post_types` table
2. Insert all 14 default post types
3. Add `post_type_id` column to posts
4. Map existing posts to new post type system
5. Add foreign key constraints

## 📡 New API Endpoints

### Get All Post Types
```bash
GET /admin/post-types
GET /admin/post-types?include_inactive=true
```

### Create Post Type
```bash
POST /admin/post-types
Content-Type: application/json

{
  "slug": "recipe",
  "name": "Recipe",
  "description": "Cooking recipes and food content",
  "emoji": "🍳",
  "sort_order": 15
}
```

### Update Post Type
```bash
PUT /admin/post-types/{id}
Content-Type: application/json

{
  "name": "Cooking Recipe",
  "description": "Updated description",
  "sort_order": 20
}
```

### Delete Post Type
```bash
DELETE /admin/post-types/{id}
```

### Get Usage Statistics
```bash
GET /admin/post-types/stats
```

## 🎨 Icon System

### Option 1: Emoji Icons (Recommended)
- Set the `emoji` field when creating post types
- Cross-platform compatible
- No file management needed
- Automatically scales

### Option 2: Custom Icon Files
- Create directory: `web/src/wwwroot/assets/images/post-types/`
- Upload 64x64px PNG icons
- Set `icon_filename` field
- Fallback to emoji if file missing

### Icon Path Structure
```
web/src/wwwroot/assets/images/post-types/
├── icon-note.png
├── icon-recipe.png
├── icon-tutorial.png
└── icon-default.png (fallback)
```

## 🔧 Updated Code Components

### Backend Changes

1. **PostType Model** (`web/src/Models/PostType.php`)
   - Full CRUD operations for post types
   - Validation and caching
   - Usage statistics

2. **PostModel Updates** (`web/src/Models/PostModel.php`)
   - Uses database post types instead of hardcoded
   - Backward compatibility maintained
   - Enhanced queries with post type joins

3. **AdminController** (`web/src/Controller/AdminController.php`)
   - New post type management endpoints
   - Updated post creation/update validation
   - Support for both `post_type` and legacy `type` fields

### Frontend Changes

1. **PostTypeSelector** (`app/Dropblog/Components/PostTypeSelector.razor`)
   - Loads post types from API
   - Supports emoji and icon display
   - Fallback to hardcoded types if API fails
   - Loading states and error handling

2. **BlogApiService** (`app/Dropblog/Services/BlogApiService.cs`)
   - New post types endpoints
   - Updated request/response models
   - Support for post type management

## 🚀 Usage Examples

### Creating a Custom Post Type

```bash
# Create a recipe post type
curl -X POST \
  -H "Authorization: Bearer YOUR_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "slug": "recipe",
    "name": "Recipe",
    "description": "Cooking recipes with ingredients and instructions",
    "emoji": "🍳",
    "sort_order": 15
  }' \
  https://your-blog.com/admin/post-types
```

### Creating Posts with Custom Types

```bash
# Create a recipe post
curl -X POST \
  -H "Authorization: Bearer YOUR_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "content": "# Chocolate Chip Cookies\n\n## Ingredients\n- 2 cups flour\n- 1 cup sugar\n\n## Instructions\n1. Preheat oven...",
    "post_type": "recipe"
  }' \
  https://your-blog.com/admin/posts
```

### Managing Post Types

```bash
# Get all post types
curl -H "Authorization: Bearer YOUR_API_KEY" \
  https://your-blog.com/admin/post-types

# Get usage statistics
curl -H "Authorization: Bearer YOUR_API_KEY" \
  https://your-blog.com/admin/post-types/stats

# Update a post type
curl -X PUT \
  -H "Authorization: Bearer YOUR_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{"description": "Updated description"}' \
  https://your-blog.com/admin/post-types/15
```

## 🔒 Security & Validation

### Input Validation
- **Slug**: 2-50 characters, lowercase alphanumeric + hyphens/underscores
- **Name**: Required, reasonable length
- **Sort Order**: Integer values
- **Emoji**: Optional single emoji character

### Database Integrity
- Foreign key constraints prevent orphaned posts
- Cannot delete post types with existing posts
- Unique slug constraint prevents duplicates

### API Security
- All endpoints require Bearer token authentication
- Input sanitization and validation
- Proper HTTP status codes and error messages

## 📱 App Integration

The mobile app automatically adapts to your custom post types:

1. **Dynamic Loading**: Post types loaded from API on startup
2. **Visual Display**: Supports both emoji and icon display
3. **Fallback**: Graceful degradation if API unavailable
4. **Responsive**: Grid layout adapts to different screen sizes

## 🔄 Migration Strategy

### Phase 1: Deploy (Backward Compatible)
1. Deploy new code
2. Run database migration
3. Both old and new systems work side by side

### Phase 2: Verify (Optional)
1. Test post creation with new system
2. Verify app displays custom post types
3. Test post type management endpoints

### Phase 3: Cleanup (Future)
1. Run cleanup migration to remove legacy `type` column
2. Remove deprecated code paths
3. Update documentation

## 🚨 Troubleshooting

### Common Issues

1. **Migration Fails**
   - Check database permissions
   - Verify foreign key constraints
   - Check for existing conflicting data

2. **App Shows Old Post Types**
   - Check API connectivity
   - Verify authentication
   - Check fallback post types loading

3. **Custom Icons Not Displaying**
   - Verify directory exists: `web/src/wwwroot/assets/images/post-types/`
   - Check file permissions
   - Ensure correct filename in database

### Debug Steps

```bash
# Test API connectivity
curl -H "Authorization: Bearer YOUR_API_KEY" \
  https://your-blog.com/admin/post-types

# Check post type usage
curl -H "Authorization: Bearer YOUR_API_KEY" \
  https://your-blog.com/admin/post-types/stats

# Verify database migration
curl -X POST -H "Authorization: Bearer YOUR_API_KEY" \
  https://your-blog.com/admin/update
```

## 📈 Benefits

### For Users
- **Flexibility**: Create post types for specific needs
- **Organization**: Better content categorization
- **Visual**: Emoji/icon representation
- **Professional**: Custom branding opportunities

### For Developers
- **Extensible**: Easy to add new features
- **Maintainable**: Database-driven instead of hardcoded
- **API-First**: Full programmatic control
- **Modern**: Follows REST principles

### For Content
- **Structured**: Consistent post categorization
- **Discoverable**: Filter by post type
- **Metadata**: Extensible with custom fields
- **Analytics**: Usage statistics and insights

## 🔮 Future Enhancements

Potential future improvements:
- Post type templates with predefined content structures
- Custom metadata schemas per post type
- Advanced filtering and search by post type
- Post type-specific editing interfaces
- Import/export of post type configurations
- Post type permissions and access control

---

**Need Help?** Check the [API Reference](docs/api-reference.md) for detailed endpoint documentation or the [Troubleshooting Guide](docs/troubleshooting.md) for common issues. 