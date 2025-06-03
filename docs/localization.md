# Localization Guide

Dropblog uses a config-based localization system with smart fallback chains for regional language variants. This guide covers how to configure, use, and extend the localization system.

## 🌍 Overview

The localization system supports:
- **Multiple languages** with regional variants
- **Smart fallback chains** for missing translations
- **Easy configuration** through a single config constant
- **Extensible structure** for adding new languages

## ⚙️ Configuration

### Basic Setup

Set the locale in your `Config.php`:

```php
namespace Dropblog;

class Config
{
    // Set your desired locale
    public const LOCALE = 'en-US';    // Format: language-region
    
    // ... other configuration
}
```

**Important**: The `LOCALE` constant must be set - the system will throw an error if not configured.

### Supported Locales

Currently supported locales:

- **`en-US`** - English (United States) - Default
- **`en`** - English (Generic)
- **`de-DE`** - German (Germany)
- **`de-AT`** - German (Austria)
- **`de`** - German (Generic)

Additional languages can be added by creating the appropriate translation files.

## 📁 File Structure

Translation files are stored in the locales directory:

```
web/src/resources/locales/
├── strings.json        # Default (English) - always loaded first
├── strings.de.json     # German base translations
├── strings.de-DE.json  # German (Germany) regional overrides
└── strings.de-AT.json  # German (Austria) regional overrides
```

### File Naming Convention

- **Base language**: `strings.{language}.json` (e.g., `strings.de.json`)
- **Regional variant**: `strings.{language}-{region}.json` (e.g., `strings.de-AT.json`)
- **Default**: `strings.json` (English fallback)

## 🔄 Fallback Chain

The system loads translations in a specific order, with later files overriding earlier ones:

### For English (`en-US` or `en`)
1. `strings.json` ✓ (complete)

### For German Germany (`de-DE`)
1. `strings.json` (default English)
2. `strings.de.json` (German base)
3. `strings.de-DE.json` (German Germany overrides)

### For Austrian German (`de-AT`)
1. `strings.json` (default English)
2. `strings.de.json` (German base)
3. `strings.de-AT.json` (Austrian German overrides)

This ensures that:
- **All text is always translated** (fallback to English)
- **Regional differences are supported** (Austria vs Germany)
- **Missing regional translations** fall back to base language
- **New languages only need to translate differences** from English

## 💬 Translation File Format

Translation files use JSON with nested structure:

```json
{
  "common": {
    "search": "Search",
    "home": "Home",
    "powered_by": "Powered by {title}",
    "loading": "Loading..."
  },
  "months": {
    "january": "January",
    "february": "February",
    "march": "March"
  },
  "navigation": {
    "archive": "Archive",
    "about": "About"
  },
  "posts": {
    "read_more": "Read more",
    "published_on": "Published on",
    "no_posts": "No posts found"
  }
}
```

### Key Features

- **Nested structure** for organization
- **Parameter support** with `{parameter}` syntax
- **Consistent naming** using snake_case
- **Logical grouping** by functionality

## 🔧 Usage in Code

### Basic Translation

```php
// Simple translation
echo Localization::t('common.search');
// Output: "Search" (en) or "Suchen" (de)

// Nested keys using dot notation
echo Localization::t('months.january');
// Output: "January" (en) or "Januar" (de) or "Jänner" (de-AT)
```

### Translation with Parameters

```php
// With single parameter
echo Localization::t('common.powered_by', ['title' => 'My Blog']);
// Output: "Powered by My Blog"

// With multiple parameters
echo Localization::t('posts.published_on_by', [
    'date' => '15. März 2024',
    'author' => 'John Doe'
]);
// Output: "Published on 15. März 2024 by John Doe"
```

### Handling Missing Translations

```php
// If translation key doesn't exist, returns the key
echo Localization::t('nonexistent.key');
// Output: "nonexistent.key"

// With default value
echo Localization::t('missing.key', [], 'Default Text');
// Output: "Default Text"
```

## 🌐 Regional Variants Example

### German Base (`strings.de.json`)
```json
{
  "months": {
    "january": "Januar",
    "february": "Februar"
  },
  "common": {
    "search": "Suchen",
    "home": "Startseite"
  }
}
```

### Austrian German Override (`strings.de-AT.json`)
```json
{
  "months": {
    "january": "Jänner"
  }
}
```

**Result for `de-AT` locale:**
- `months.january` → "Jänner" (Austrian override)
- `months.february` → "Februar" (German base)
- `common.search` → "Suchen" (German base)
- `common.home` → "Startseite" (German base)

## ➕ Adding New Languages

### 1. Create Base Language File

Create `strings.{language}.json`:

```json
// strings.fr.json (French)
{
  "common": {
    "search": "Rechercher",
    "home": "Accueil",
    "powered_by": "Propulsé par {title}"
  },
  "months": {
    "january": "Janvier",
    "february": "Février"
  }
}
```

### 2. Add Regional Variants (Optional)

Create `strings.{language}-{region}.json`:

```json
// strings.fr-CA.json (Canadian French)
{
  "common": {
    "search": "Chercher"
  }
}
```

### 3. Update Configuration

```php
public const LOCALE = 'fr-FR';  // or 'fr-CA'
```

### 4. Test Translation

```php
echo Localization::t('common.search');
// Output: "Rechercher" (fr-FR) or "Chercher" (fr-CA)
```

## 📋 Translation Guidelines

### Key Naming

- Use **snake_case** for keys: `read_more`, not `readMore`
- Use **descriptive names**: `navigation.archive`, not `nav.arch`
- **Group related keys**: `posts.read_more`, `posts.published_on`

### Content Guidelines

- Keep translations **contextually appropriate**
- Consider **cultural differences** for regional variants
- Use **formal/informal tone** consistently within a language
- **Test with longer text** (some languages are much longer)

### Parameter Usage

```json
{
  "posts": {
    "count_singular": "1 post",
    "count_plural": "{count} posts",
    "published_by": "Published by {author} on {date}"
  }
}
```

## 🔧 Advanced Features

### Conditional Translations

[TODO] Support for pluralization and conditionals:

```json
{
  "posts": {
    "count": {
      "zero": "No posts",
      "one": "1 post", 
      "other": "{count} posts"
    }
  }
}
```

### Date and Number Formatting

[TODO] Locale-aware formatting:

```php
// Date formatting based on locale
$formattedDate = Localization::formatDate($date);

// Number formatting
$formattedNumber = Localization::formatNumber(1234.56);
```

### Currency Support

[TODO] Currency formatting for e-commerce features:

```php
$price = Localization::formatCurrency(29.99, 'EUR');
// Output: "29,99 €" (de) or "$29.99" (en-US)
```

## 🧪 Testing Localization

### Test Different Locales

```php
// Temporarily change locale for testing
$originalLocale = Config::LOCALE;
Config::LOCALE = 'de-AT';

// Test translations
$translation = Localization::t('months.january');
assert($translation === 'Jänner');

// Restore original locale
Config::LOCALE = $originalLocale;
```

### Validate Translation Files

[TODO] Script to validate translation files:

```bash
# Check for missing keys
php scripts/validate-translations.php

# Find unused translation keys
php scripts/find-unused-translations.php
```

## 🚨 Common Issues

### Missing Locale Configuration

**Error**: `Locale not configured`
**Solution**: Set `Config::LOCALE` in your configuration file

### Translation File Not Found

**Error**: Translation keys return the key itself
**Solution**: Ensure translation files exist and are properly named

### Invalid JSON Format

**Error**: Translations not loading
**Solution**: Validate JSON syntax in translation files

### Parameter Not Replaced

**Issue**: `{parameter}` appears literally in output
**Solution**: Ensure parameter array keys match placeholder names

## 🔮 Future Enhancements

### Planned Features

- [TODO] **Pluralization support** for complex plural rules
- [TODO] **Context-based translations** for ambiguous terms
- [TODO] **Translation management UI** for non-technical users
- [TODO] **Automatic translation validation** in CI/CD
- [TODO] **RTL language support** for Arabic, Hebrew, etc.

### Community Translations

- [TODO] **Translation submission system** for community contributors
- [TODO] **Translation review process** for quality assurance
- [TODO] **Translation statistics** and completion tracking

---

Need help with localization? Check [Troubleshooting](troubleshooting.md) or open an issue on GitHub. 