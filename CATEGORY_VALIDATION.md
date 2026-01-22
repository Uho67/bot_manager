# Category Validation Implementation

## Overview
Added custom validation to prevent invalid category tree structures by preventing circular references and self-references.

## Files Created

### 1. ValidCategoryChildren.php (Constraint)
**Location:** `src/Catalog/Validator/ValidCategoryChildren.php`

Custom validation constraint that can be applied to the `childCategories` property.

**Messages:**
- `message`: "A category cannot have itself or its parent as a child."
- `circularMessage`: "Circular reference detected: Category cannot be its own ancestor."

### 2. ValidCategoryChildrenValidator.php (Validator)
**Location:** `src/Catalog/Validator/ValidCategoryChildrenValidator.php`

The validator logic that checks:
1. **Self-reference check:** Prevents category from having itself as a child
2. **Circular reference check:** Prevents category from having its ancestors as children
3. **Recursive depth limit:** Prevents infinite loops (max depth: 10 levels)

**How it works:**
- Gets the parent category being validated
- Iterates through each child in the childCategories collection
- Checks if child ID matches parent ID (self-reference)
- Checks if child is an ancestor of parent (circular reference)
- Uses recursive checking with depth limit for safety

## Usage in Category Entity

```php
#[ORM\ManyToMany(targetEntity: Category::class)]
#[ORM\JoinTable(name: 'category_children')]
#[Groups(['category:read', 'category:write'])]
#[Assert\Count(max: 10, maxMessage: 'A category cannot have more than {{ limit }} children.')]
#[ValidCategoryChildren]  // ← Custom validation
private Collection $childCategories;
```

## Validation Scenarios

### ✅ Valid Cases

1. **Empty children:**
```json
{"name": "Category A", "childCategories": []}
```

2. **Normal hierarchy:**
```json
// Category 1
{"name": "Electronics", "childCategories": ["/api/categories/2", "/api/categories/3"]}
// Category 2 and 3 can have their own children (not 1)
```

3. **Multi-level tree:**
```
Electronics (1)
├── Phones (2)
│   ├── iPhone (4)
│   └── Android (5)
└── Laptops (3)
    └── Gaming (6)
```

### ❌ Invalid Cases

1. **Self-reference:**
```json
// Category 1 trying to add itself
{"name": "Category A", "childCategories": ["/api/categories/1"]}
// Error: "A category cannot have itself or its parent as a child."
```

2. **Direct circular reference:**
```json
// Category 1 has child 2
{"name": "Category 1", "childCategories": ["/api/categories/2"]}

// Category 2 tries to have 1 as child (circular!)
{"name": "Category 2", "childCategories": ["/api/categories/1"]}
// Error: "Circular reference detected: Category cannot be its own ancestor."
```

3. **Indirect circular reference:**
```json
// Category 1 → 2 → 3
{"name": "Category 1", "childCategories": ["/api/categories/2"]}
{"name": "Category 2", "childCategories": ["/api/categories/3"]}

// Category 3 tries to have 1 as child (1 is ancestor of 3!)
{"name": "Category 3", "childCategories": ["/api/categories/1"]}
// Error: "Circular reference detected: Category cannot be its own ancestor."
```

## API Response on Validation Error

When validation fails, API Platform returns a 422 Unprocessable Entity response:

```json
{
  "@context": "/api/contexts/ConstraintViolationList",
  "@type": "ConstraintViolationList",
  "title": "An error occurred",
  "violations": [
    {
      "propertyPath": "childCategories",
      "message": "Circular reference detected: Category cannot be its own ancestor.",
      "code": null
    }
  ]
}
```

## How Validation Works

1. **Trigger:** Validation runs when creating or updating a category via API
2. **Check:** ValidCategoryChildrenValidator checks each child
3. **Database Query:** Fetches fresh data from DB to check actual relationships
4. **Recursive Check:** Traverses tree up to 10 levels deep
5. **Result:** Either allows the operation or returns validation error

## Performance Considerations

- **Database queries:** Validator may query DB to get fresh category data
- **Recursion limit:** Max depth of 10 prevents infinite loops and excessive queries
- **Caching:** EntityManager caches entities to minimize queries
- **Validation only on write:** Read operations (GET) are not validated

## Testing

### Test Self-Reference
```bash
curl -X PUT https://bot_api.test/api/categories/1 \
  -H 'Content-Type: application/ld+json' \
  -H 'Authorization: Bearer YOUR_TOKEN' \
  -d '{"name":"Test","childCategories":["/api/categories/1"]}'
```
**Expected:** 422 error with validation message

### Test Circular Reference
```bash
# Create Category 1 with child 2
curl -X PUT https://bot_api.test/api/categories/1 \
  -d '{"name":"Parent","childCategories":["/api/categories/2"]}'

# Try to make Category 2 have Category 1 as child
curl -X PUT https://bot_api.test/api/categories/2 \
  -d '{"name":"Child","childCategories":["/api/categories/1"]}'
```
**Expected:** 422 error for second request

## Benefits

1. **Data Integrity:** Prevents invalid tree structures in database
2. **No Circular Loops:** Impossible to create infinite loops
3. **Clear Errors:** User-friendly error messages
4. **Automatic:** Works without additional code in controllers
5. **Consistent:** Validation applied on all API operations (POST, PUT, PATCH)

## Notes

- Validation only applies to existing categories (ID must exist)
- New categories (without ID) skip validation
- Validation uses fresh DB data to avoid stale collection issues
- Maximum 10 children per category (existing Count constraint)
- Works seamlessly with API Platform serialization/deserialization

