# Category Relationship Simplification

## Problem
The original Category entity had both `parentCategories` and `childCategories` relationships, creating unnecessary complexity:
- `parentCategories` (owning side) - a category could have multiple parents
- `childCategories` (inverse side) - a category could have multiple children

This bidirectional self-referencing relationship was confusing and required complex processor logic to manage.

## Solution
Simplified to a **unidirectional tree structure** with only `childCategories`:
- Each category can have 0-10 child categories
- This creates a parent → children tree structure
- Much simpler and more intuitive

## Changes Made

### 1. Category Entity
**Removed:**
- `parentCategories` property
- `addParentCategory()` method
- `removeParentCategory()` method
- `getParentCategories()` method

**Kept:**
- `childCategories` property (now the owning side)
- `addChildCategory()` method
- `removeChildCategory()` method
- `getChildCategories()` method

**Updated:**
- `childCategories` is now a simple ManyToMany (unidirectional)
- Uses `category_children` join table
- Writable via `category:write` serialization group

### 2. CategoryProcessor
**Simplified:**
- Removed complex relationship management logic
- No longer needs EntityManager
- childCategories is now the owning side, so API Platform handles persistence automatically
- Processor only sets `bot_identifier` from JWT

### 3. Database Schema
**Migration created to:**
- Drop old `category_parent` table
- Keep/create `category_children` table

## How It Works Now

### Frontend sends:
```json
{
  "name": "Main Category",
  "childCategories": ["/api/categories/2", "/api/categories/3"]
}
```

### Backend:
1. API Platform deserializes IRIs to Category entities
2. CategoryProcessor sets `bot_identifier` from JWT
3. API Platform's default persist processor saves the category with its children
4. Database stores the relationships in `category_children` table

### Result:
- Category 1 has children: Category 2, Category 3
- Simple, intuitive tree structure
- No inverse relationship complexity

## Benefits

1. **Simpler Code:** No complex relationship management needed
2. **Easier to Understand:** Clear parent → children hierarchy
3. **Less Error-Prone:** Owning side is directly writable
4. **Better Performance:** No need for extra queries or flushes
5. **Standard Pattern:** Common tree structure pattern
6. **Prevents Circular References:** Custom validation prevents invalid tree structures

## Validation Rules

The system now validates childCategories to prevent:
1. **Self-reference:** A category cannot have itself as a child
2. **Circular reference:** A category cannot have its ancestor as a child
   - Example: If A → B → C, then C cannot have A or B as children
3. **Maximum children:** Limited to 10 children per category

### Validation Examples

**Invalid (self-reference):**
```json
// Category ID 1 trying to add itself as a child
{
  "name": "Category 1",
  "childCategories": ["/api/categories/1"]  // ❌ Error: Cannot have itself
}
```

**Invalid (circular reference):**
```json
// If Category 1 has child Category 2
// Category 2 cannot have Category 1 as a child
{
  "name": "Category 2",
  "childCategories": ["/api/categories/1"]  // ❌ Error: Circular reference
}
```

**Valid:**
```json
{
  "name": "Electronics",
  "childCategories": ["/api/categories/5", "/api/categories/6"]  // ✅ OK
}
```

## Usage Example

```php
// Create main category with children
POST /api/categories
{
  "name": "Electronics",
  "childCategories": [
    "/api/categories/5",  // Phones
    "/api/categories/6"   // Laptops
  ]
}

// Result:
Electronics
├── Phones
└── Laptops
```

## Frontend
No changes needed in CategoryEditPage.vue - it already sends `childCategories` as IRIs.

## Next Steps
Run the migration:
```bash
php bin/console doctrine:migrations:migrate
```

This will drop the old `category_parent` table and ensure the `category_children` table is correct.

