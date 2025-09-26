# Pages Banners Image System

## Overview
This document describes the Pages Banners Image system that allows managing one banner image per page in the Mariamly backend system.

## Features

### 1. Database Structure
- **Table**: `pages_banners_images`
- **Fields**:
  - `id` - Primary key
  - `title` - Optional banner title
  - `image_path` - Path to the stored image
  - `page_name` - Unique identifier for the page (e.g., 'home', 'about', 'contact')
  - `is_active` - Boolean flag to enable/disable the banner
  - `timestamps` - Created/updated timestamps

### 2. Model Features

#### PagesBannersImage Model
- **Fillable fields**: `title`, `image_path`, `page_name`, `is_active`
- **Casts**: `is_active` as boolean
- **Appends**: `image_url` (full URL to the image)
- **Scopes**:
  - `active()` - Filter active banners
  - `forPage($pageName)` - Filter by specific page

### 3. Controller Methods

#### Public Methods
- `getByPage($pageName)` - Get active banner for a specific page

#### Admin Methods
- `index()` - List all banner images
- `store()` - Create new banner image
- `show($banner)` - Show specific banner
- `update($banner)` - Update banner
- `destroy($banner)` - Delete banner
- `toggleStatus($banner)` - Toggle active status

## API Endpoints

### Public Routes
```http
GET /api/pages-banners/{pageName}
```
Get the active banner for a specific page.

**Response:**
```json
{
    "success": true,
    "banner": {
        "id": 1,
        "title": "Home Page Banner",
        "image_path": "banners/home-banner.jpg",
        "image_url": "http://domain.com/storage/banners/home-banner.jpg",
        "page_name": "home",
        "is_active": true,
        "created_at": "2025-09-26T12:00:00.000000Z",
        "updated_at": "2025-09-26T12:00:00.000000Z"
    }
}
```

### Admin Routes (Protected by auth:sanctum + is_admin middleware)

#### List All Banners
```http
GET /api/admin/pages-banners
```

#### Create Banner
```http
POST /api/admin/pages-banners
Content-Type: multipart/form-data

{
    "title": "Home Page Banner",
    "page_name": "home",
    "image": [file],
    "is_active": true
}
```

#### Get Specific Banner
```http
GET /api/admin/pages-banners/{banner}
```

#### Update Banner
```http
PUT /api/admin/pages-banners/{banner}
Content-Type: multipart/form-data

{
    "title": "Updated Banner Title",
    "page_name": "home",
    "image": [file], // optional
    "is_active": false
}
```

#### Delete Banner
```http
DELETE /api/admin/pages-banners/{banner}
```

#### Toggle Banner Status
```http
POST /api/admin/pages-banners/{banner}/toggle-status
```

## Usage Examples

### 1. Frontend - Get Banner for Home Page
```javascript
const getHomeBanner = async () => {
    try {
        const response = await fetch('/api/pages-banners/home');
        const data = await response.json();
        
        if (data.success) {
            return data.banner.image_url;
        }
        return null;
    } catch (error) {
        console.error('Error fetching banner:', error);
        return null;
    }
};
```

### 2. Admin - Create New Banner
```javascript
const createBanner = async (formData) => {
    try {
        const response = await fetch('/api/admin/pages-banners', {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`
            },
            body: formData
        });
        
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Error creating banner:', error);
        throw error;
    }
};
```

### 3. Admin - Update Banner
```javascript
const updateBanner = async (bannerId, formData) => {
    try {
        const response = await fetch(`/api/admin/pages-banners/${bannerId}`, {
            method: 'PUT',
            headers: {
                'Authorization': `Bearer ${token}`
            },
            body: formData
        });
        
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Error updating banner:', error);
        throw error;
    }
};
```

## Validation Rules

### Store/Update Validation
- `title`: nullable, string, max 255 characters
- `page_name`: required, string, max 100 characters, unique
- `image`: required for store, optional for update, image file, max 2MB
- `is_active`: optional, boolean

## File Storage

- **Storage Path**: `storage/app/public/banners/`
- **Public URL**: `http://domain.com/storage/banners/filename.jpg`
- **Automatic Cleanup**: Old images are deleted when updating or deleting banners

## Common Page Names

Suggested page names for consistency:
- `home` - Home page banner
- `about` - About page banner
- `contact` - Contact page banner
- `products` - Products listing page banner
- `cart` - Shopping cart page banner
- `checkout` - Checkout page banner
- `profile` - User profile page banner
- `login` - Login page banner
- `register` - Registration page banner

## Error Responses

### Banner Not Found
```json
{
    "success": false,
    "message": "Banner not found for this page"
}
```

### Validation Errors
```json
{
    "message": "The given data was invalid.",
    "errors": {
        "page_name": ["The page name field is required."],
        "image": ["The image field is required."]
    }
}
```

## Migration

To set up the database table, run:
```bash
php artisan migrate
```

## Benefits

1. **One Banner Per Page**: Ensures only one banner per page
2. **Easy Management**: Simple CRUD operations for admins
3. **Public API**: Easy access for frontend applications
4. **File Management**: Automatic image storage and cleanup
5. **Status Control**: Enable/disable banners without deletion
6. **Flexible**: Support for any page name
7. **Performance**: Efficient queries with proper indexing
