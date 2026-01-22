# Catalog Module - Implementation Summary

## Overview
The Catalog module has been successfully created with Product and Category entities, following the same architecture pattern as AdminUser, Bot, and Config modules.

## Directory Structure
```
src/Catalog/
├── Entity/
│   ├── Product.php
│   └── Category.php
├── Repository/
│   ├── ProductRepository.php
│   └── CategoryRepository.php
├── State/
│   ├── ProductProcessor.php
│   └── CategoryProcessor.php
└── Doctrine/
    ├── ProductCollectionExtension.php
    └── CategoryCollectionExtension.php
```

## Entities

### Product Entity
**Fields:**
- `id` - Auto-generated integer ID
- `name` - String (max 10 symbols)
- `description` - Text field (supports emojis, bold text, line breaks)
- `image` - String (255 chars, nullable) - path to image file
- `bot_identifier` - String (10 chars, indexed) - automatically set from JWT
- `categories` - ManyToMany relationship (0-3 categories maximum)

**Validation:**
- Maximum 3 categories per product (validated with Assert\Count constraint)

**API Access:**
- Only accessible by users with `ROLE_ADMIN`
- Automatically filtered by user's bot_identifier from JWT token
- bot_identifier is read-only (set automatically from authenticated user)

### Category Entity
**Fields:**
- `id` - Auto-generated integer ID
- `name` - String (max 10 symbols)
- `bot_identifier` - String (10 chars, indexed) - automatically set from JWT
- `products` - ManyToMany relationship (inverse side)
- `parentCategories` - ManyToMany self-referencing (0-2 parent categories maximum)
- `childCategories` - ManyToMany self-referencing (inverse side)

**Validation:**
- Maximum 2 parent categories per category (validated with Assert\Count constraint)

**API Access:**
- Only accessible by users with `ROLE_ADMIN`
- Automatically filtered by user's bot_identifier from JWT token
- bot_identifier is read-only (set automatically from authenticated user)

## Key Features

### 1. Automatic bot_identifier Assignment
- Both Product and Category use custom processors (`ProductProcessor`, `CategoryProcessor`)
- The `bot_identifier` is automatically extracted from the JWT token of the authenticated user
- Users cannot manually set or modify the `bot_identifier` field via API

### 2. Data Isolation
- Each admin user can only see and manage products/categories for their own bot
- Implemented via Doctrine Collection Extensions (`ProductCollectionExtension`, `CategoryCollectionExtension`)
- Filters are applied automatically to all GetCollection operations

### 3. Relationships
- **Product ↔ Category**: ManyToMany (product can have 0-3 categories)
- **Category ↔ Category**: Self-referencing ManyToMany (category can have 0-2 parent categories)

### 4. Security
- All operations require `ROLE_ADMIN` (not accessible to `ROLE_SUPER_ADMIN`)
- Data is filtered by `bot_identifier` matching the authenticated user's bot

## API Endpoints

### Products
- `GET /api/products` - List all products (filtered by user's bot_identifier)
- `GET /api/products/{id}` - Get single product
- `POST /api/products` - Create new product
- `PUT /api/products/{id}` - Update product (full replacement)
- `PATCH /api/products/{id}` - Partial update product
- `DELETE /api/products/{id}` - Delete product

### Categories
- `GET /api/categories` - List all categories (filtered by user's bot_identifier)
- `GET /api/categories/{id}` - Get single category
- `POST /api/categories` - Create new category
- `PUT /api/categories/{id}` - Update category (full replacement)
- `PATCH /api/categories/{id}` - Partial update category
- `DELETE /api/categories/{id}` - Delete category

## Database Schema

### Tables Created
1. `product` - Main product table
2. `category` - Main category table
3. `product_category` - Join table for Product-Category relationship
4. `category_parent` - Join table for Category self-referencing relationship

### Indexes
- `idx_product_bot_identifier` on `product.bot_identifier`
- `idx_category_bot_identifier` on `category.bot_identifier`

## Configuration

All services are configured in `config/services.yaml`:
- State processors for automatic bot_identifier assignment
- Doctrine extensions for collection filtering

## Next Steps

To activate the Catalog module:

1. **Create migration:**
   ```bash
   php bin/console make:migration
   ```

2. **Run migration:**
   ```bash
   php bin/console doctrine:migrations:migrate
   ```

3. **Clear cache:**
   ```bash
   php bin/console cache:clear
   ```

## Usage Example

### Create a Product
```bash
POST /api/products
Content-Type: application/ld+json
Authorization: Bearer <JWT_TOKEN>

{
  "name": "Product 1",
  "description": "This is a **bold** description with emoji 🎉\nNew line here",
  "image": "/media/telegram/bot_folder/photos/product1.jpg",
  "categories": ["/api/categories/1", "/api/categories/2"]
}
```

The `bot_identifier` will be automatically set from the JWT token.

### Get Products by Category
Products are linked to categories via the ManyToMany relationship. To get all products of a category, access:
```bash
GET /api/categories/{id}
```
This will include the `products` collection in the response.

## Notes
- The `description` field supports rich text including emojis, bold formatting, and line breaks
- The `image` field stores the path to the image file (can be used with the media storage system)
- Products and categories are completely isolated per bot - admins can only access their own bot's data
- SUPER_ADMIN users do NOT have access to the Catalog API (only ROLE_ADMIN)

