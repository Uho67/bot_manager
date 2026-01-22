# Product Image Upload Implementation

## Overview
Implemented complete image upload functionality for products, using the bot identifier from the authenticated admin user to organize images in the correct media folder structure.

## Backend Implementation

### 1. ProductImageService
**Location:** `src/Catalog/Service/ProductImageService.php`

**Features:**
- Uploads product images to the bot's media folder
- Uses `BotMediaService` to leverage existing folder structure
- Images are stored in: `/public/media/telegram/{first_20_chars_of_api_key}/photos/`
- Generates unique filenames using `uniqid()`
- Returns public path for storage in database

**Flow:**
1. Receives uploaded file and bot_identifier
2. Looks up bot by identifier to get API key
3. Uses API key to determine storage folder (first 20 characters)
4. Saves image using BotMediaService
5. Returns public path: `/media/telegram/{botFolder}/photos/{filename}`

### 2. ProductImageController
**Location:** `src/Catalog/Controller/ProductImageController.php`

**Endpoint:** `POST /api/product/upload-image`
**Security:** Requires `ROLE_ADMIN`

**Request:**
- Content-Type: `multipart/form-data`
- Field: `image` (file upload)

**Response:**
```json
{
  "success": true,
  "path": "/media/telegram/1234567890ABCDEFGH/photos/product_63b1234567890.jpg"
}
```

**Process:**
1. Validates file upload
2. Gets current user's bot_identifier from JWT
3. Calls ProductImageService to upload
4. Returns the public path

## Frontend Implementation

### 1. ProductEditPage.vue Updates

**Image Upload on Change:**
- When user selects an image, it's immediately uploaded to the backend
- Preview is shown using FileReader (client-side)
- Backend returns the actual path which is saved in `form.value.image`
- Path is then sent with the product create/update request

**Flow:**
1. User selects image file
2. Show preview immediately (client-side)
3. Upload file to `/api/product/upload-image`
4. Store returned path in form
5. Submit form with image path

### 2. ProductListPage.vue Updates

**Image Display:**
- Shows thumbnail (64x64px) in the product grid
- Uses `getImageUrl()` helper to construct full URL
- Handles both relative paths and absolute URLs
- Shows "No image" placeholder if no image exists

## File Organization

### Storage Structure
```
public/
└── media/
    └── telegram/
        └── {first_20_chars_of_bot_api_key}/
            └── photos/
                ├── product_63b1234567890a.jpg
                ├── product_63b1234567891b.jpg
                └── product_63b1234567892c.jpg
```

### Example Flow
1. Admin user logs in (bot_identifier: "test-2-34")
2. System looks up bot with identifier "test-2-34"
3. Bot has api_key: "1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ"
4. Images stored in: `/public/media/telegram/1234567890ABCDEFGH/photos/`
5. Database stores: `/media/telegram/1234567890ABCDEFGH/photos/product_xxx.jpg`
6. Frontend displays: `https://bot_api.test/media/telegram/1234567890ABCDEFGH/photos/product_xxx.jpg`

## Security

- Only `ROLE_ADMIN` users can upload images
- Images are automatically isolated by bot (via bot_identifier → api_key → folder)
- No cross-bot access (each admin only uploads to their bot's folder)
- Unique filenames prevent overwrites

## Benefits

1. **Automatic Organization:** Images are automatically organized by bot
2. **Isolation:** Each bot has its own folder
3. **Consistent:** Uses existing BotMediaService infrastructure
4. **Scalable:** Easy to add more media types (documents, videos, etc.)
5. **Clean URLs:** Public URLs are consistent and predictable

## Usage

### Upload Image (Frontend)
```typescript
// User selects file → automatically uploads
const onImageChange = async (event: Event) => {
  const file = target.files[0];

  // Upload to backend
  const formData = new FormData();
  formData.append('image', file);

  const response = await api.post('/api/product/upload-image', formData, {
    headers: { 'Content-Type': 'multipart/form-data' }
  });

  // Store path
  form.value.image = response.data.path;
};
```

### Create Product with Image
```json
POST /api/products
{
  "name": "Product 1",
  "description": "Description",
  "image": "/media/telegram/1234567890ABCDEFGH/photos/product_xxx.jpg",
  "categories": []
}
```

## Notes

- Images are stored in the same folder structure as bot media
- When bot API key changes, bot media folders are moved (including product images)
- When bot is deleted, all images are deleted with the folder
- Frontend automatically handles SSL certificate issues with proper error messages
- Image paths are relative (start with `/media/`) for flexibility

