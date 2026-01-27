# PHP Code Standards

## Quick Start

```bash
# Install dependencies
composer install

# Check code
composer phpcs

# Auto-fix issues
composer phpcbf
```

## What's Enforced

- ✅ Strict types: `declare(strict_types=1);`
- ✅ Type hints on all parameters/returns/properties
- ✅ Strict comparison (`===` not `==`)
- ✅ Trailing commas in arrays/functions
- ✅ Constructor property promotion
- ✅ Complexity limits (max 10)
- ✅ Modern PHP 8.4+ features
- ❌ No `var_dump()`, `dd()`, `dump()`, `print_r()`
- ❌ No superglobals (`$_GET`, `$_POST`, etc.)

## Common Fixes

### Add strict types (all files)
```php
<?php
declare(strict_types=1);
```

### Add type hints
```php
// Before
public function process($data) { }

// After
public function process(array $data): void { }
```

### Use strict comparison
```php
// Before: if ($value == null)
// After:  if ($value === null)
```

### Use null coalesce
```php
// Before: $x = isset($data['key']) ? $data['key'] : 'default';
// After:  $x = $data['key'] ?? 'default';
```

### Constructor property promotion
```php
// Before
private Service $service;
public function __construct(Service $service) {
    $this->service = $service;
}

// After
public function __construct(
    private readonly Service $service,
) {
}
```

## Files to Commit

- ✅ `phpcs.xml` - Configuration (share with team)
- ✅ `composer.json` - Dependencies
- ❌ `.phpcs-cache` - Auto-generated (in .gitignore)

