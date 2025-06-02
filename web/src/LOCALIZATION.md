# Localization System

## Overview

The blog uses a **config-based localization system** with smart fallback chains for regional language variants.

## Configuration

Set the locale in your `Config.php`:

```php
// Examples:
public const LOCALE = 'en-US';    // English (default)
public const LOCALE = 'de-DE';    // German (Germany)
public const LOCALE = 'de-AT';    // German (Austria)
```

If `LOCALE` is not defined, the system defaults to `en-US`.

## File Structure

```
web/src/resources/locales/
├── strings.json        # Default (English) - always loaded first
├── strings.de.json     # German base translations
├── strings.de-DE.json  # German (Germany) regional overrides
└── strings.de-AT.json  # German (Austria) regional overrides
```

## Fallback Chain

The system loads translations in this order, with later files overriding earlier ones:

### For `en-US` or `en`:
1. `strings.json` ✓

### For `de-DE`:
1. `strings.json` (default)
2. `strings.de.json` (German base)
3. `strings.de-DE.json` (German Germany overrides)

### For `de-AT`:
1. `strings.json` (default)
2. `strings.de.json` (German base)
3. `strings.de-AT.json` (Austrian German overrides)

## Usage in Code

```php
// Simple translation
Localization::t('common.search')

// With parameters
Localization::t('common.powered_by', ['title' => 'My Blog'])

// Nested keys using dot notation
Localization::t('months.january')  // "January" or "Januar" or "Jänner"
```

## Translation File Format

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

## Regional Variants Example

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

## Adding New Languages

1. Create `strings.{language}.json` (e.g., `strings.fr.json`)
2. Add your translations using the same structure as `strings.json`
3. For regional variants, create `strings.{language}-{region}.json`
4. Set `Config::LOCALE` to your desired locale

## Features

- ✅ **No sessions** - purely config-based
- ✅ **Smart fallbacks** - graceful degradation
- ✅ **Regional variants** - support for de-DE, de-AT, en-US, en-GB, etc.
- ✅ **Parameter substitution** - `{title}`, `{name}`, etc.
- ✅ **Dot notation** - easy nested key access
- ✅ **Missing key safety** - returns key name if translation not found 