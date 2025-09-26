# Offer Section API Documentation

## Overview

The Offer Section feature provides a backend API for managing promotional offer sections that can be displayed on the frontend. This system allows administrators to create, update, and manage offer sections with images, text content, and call-to-action buttons.

## Database Schema

### Table: `offer_sections`

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint | Primary key |
| `image_path` | varchar(255) | Path to the uploaded image file |
| `alt_text` | varchar(255) | Alt text for the image (default: 'Offer Image') |
| `discount_percentage` | varchar(50) | Discount text (e.g., "60% Off") |
| `title` | varchar(255) | Main heading text |
| `description` | text | Detailed description text |
| `button_text` | varchar(100) | Button label text (default: 'Customize now') |
| `button_link` | varchar(255) | Button destination URL (default: '/shop') |
| `is_active` | boolean | Whether the offer section is active (default: true) |
| `sort_order` | integer | Order for displaying multiple sections (default: 0) |
| `created_at` | timestamp | Record creation timestamp |
| `updated_at` | timestamp | Record last update timestamp |

## API Endpoints

### Public Endpoints

#### Get Active Offer Sections
```http
GET /api/offer-sections
```

**Description**: Retrieves all active offer sections ordered by sort_order.

**Response**:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "image_path": "offer-sections/valentine-offer.jpg",
      "alt_text": "Valentine Offer",
      "discount_percentage": "60% Off",
      "title": "Celebrate love & beauty this Valentine's!",
      "description": "Get our exclusive cosmetic Valentine gifts at a special discounted price – the perfect way to surprise someone you love.",
      "button_text": "Customize now",
      "button_link": "/shop",
      "is_active": true,
      "sort_order": 0,
      "image_url": "http://your-domain.com/storage/offer-sections/valentine-offer.jpg",
      "created_at": "2025-09-25T16:54:05.000000Z",
      "updated_at": "2025-09-25T16:54:05.000000Z"
    }
  ]
}
```

### Admin Endpoints

All admin endpoints require authentication with `auth:sanctum` middleware and admin privileges.

#### List All Offer Sections (Admin)
```http
GET /api/admin/offer-sections
```

**Query Parameters**:
- `active` (boolean, optional): Filter by active status
- `per_page` (integer, optional): Number of items per page (default: 20)

**Response**:
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "image_path": "offer-sections/valentine-offer.jpg",
        "alt_text": "Valentine Offer",
        "discount_percentage": "60% Off",
        "title": "Celebrate love & beauty this Valentine's!",
        "description": "Get our exclusive cosmetic Valentine gifts...",
        "button_text": "Customize now",
        "button_link": "/shop",
        "is_active": true,
        "sort_order": 0,
        "created_at": "2025-09-25T16:54:05.000000Z",
        "updated_at": "2025-09-25T16:54:05.000000Z"
      }
    ],
    "first_page_url": "http://your-domain.com/api/admin/offer-sections?page=1",
    "from": 1,
    "last_page": 1,
    "last_page_url": "http://your-domain.com/api/admin/offer-sections?page=1",
    "links": [...],
    "next_page_url": null,
    "path": "http://your-domain.com/api/admin/offer-sections",
    "per_page": 20,
    "prev_page_url": null,
    "to": 1,
    "total": 1
  }
}
```

#### Create Offer Section
```http
POST /api/admin/offer-sections
Content-Type: multipart/form-data
```

**Request Body**:
- `image` (file, optional): Image file (jpeg, png, jpg, gif, max 2MB)
- `alt_text` (string, optional): Alt text for image
- `discount_percentage` (string, optional): Discount text
- `title` (string, optional): Main heading
- `description` (string, optional): Description text
- `button_text` (string, optional): Button label
- `button_link` (string, optional): Button URL
- `is_active` (boolean, optional): Active status
- `sort_order` (integer, optional): Sort order

**Response**:
```json
{
  "success": true,
  "message": "Offer section created",
  "data": {
    "id": 2,
    "image_path": "offer-sections/new-offer.jpg",
    "alt_text": "New Offer",
    "discount_percentage": "50% Off",
    "title": "Summer Sale!",
    "description": "Get amazing discounts on summer products",
    "button_text": "Shop Now",
    "button_link": "/summer-sale",
    "is_active": true,
    "sort_order": 1,
    "created_at": "2025-09-25T17:00:00.000000Z",
    "updated_at": "2025-09-25T17:00:00.000000Z"
  }
}
```

#### Get Specific Offer Section
```http
GET /api/admin/offer-sections/{id}
```

**Response**:
```json
{
  "success": true,
  "data": {
    "id": 1,
    "image_path": "offer-sections/valentine-offer.jpg",
    "alt_text": "Valentine Offer",
    "discount_percentage": "60% Off",
    "title": "Celebrate love & beauty this Valentine's!",
    "description": "Get our exclusive cosmetic Valentine gifts...",
    "button_text": "Customize now",
    "button_link": "/shop",
    "is_active": true,
    "sort_order": 0,
    "image_url": "http://your-domain.com/storage/offer-sections/valentine-offer.jpg",
    "created_at": "2025-09-25T16:54:05.000000Z",
    "updated_at": "2025-09-25T16:54:05.000000Z"
  }
}
```

#### Update Offer Section
```http
PUT /api/admin/offer-sections/{id}
Content-Type: multipart/form-data
```

**Request Body**: Same as create endpoint

**Response**:
```json
{
  "success": true,
  "message": "Offer section updated",
  "data": {
    "id": 1,
    "image_path": "offer-sections/updated-offer.jpg",
    "alt_text": "Updated Offer",
    "discount_percentage": "70% Off",
    "title": "Updated Title",
    "description": "Updated description",
    "button_text": "Updated Button",
    "button_link": "/updated-link",
    "is_active": false,
    "sort_order": 2,
    "created_at": "2025-09-25T16:54:05.000000Z",
    "updated_at": "2025-09-25T17:05:00.000000Z"
  }
}
```

#### Delete Offer Section
```http
DELETE /api/admin/offer-sections/{id}
```

**Response**:
```json
{
  "success": true,
  "message": "Offer section deleted"
}
```

#### Reorder Offer Sections
```http
POST /api/admin/offer-sections/reorder
Content-Type: application/json
```

**Request Body**:
```json
{
  "ids": [3, 1, 2]
}
```

**Response**:
```json
{
  "success": true,
  "message": "Reordered"
}
```

## Error Responses

All endpoints return consistent error responses:

```json
{
  "success": false,
  "message": "Error description",
  "error": "Detailed error message"
}
```

**Common HTTP Status Codes**:
- `200` - Success
- `201` - Created
- `400` - Bad Request (validation errors)
- `401` - Unauthorized
- `403` - Forbidden (admin access required)
- `404` - Not Found
- `422` - Validation Error
- `500` - Internal Server Error

## Validation Rules

### Create/Update Validation

| Field | Rules |
|-------|-------|
| `image` | nullable, image, mimes:jpeg,png,jpg,gif, max:2048 |
| `alt_text` | nullable, string, max:255 |
| `discount_percentage` | nullable, string, max:50 |
| `title` | nullable, string, max:255 |
| `description` | nullable, string |
| `button_text` | nullable, string, max:100 |
| `button_link` | nullable, string, max:255 |
| `is_active` | boolean |
| `sort_order` | integer, min:0 |

### Reorder Validation

| Field | Rules |
|-------|-------|
| `ids` | required, array, min:1 |
| `ids.*` | integer, exists:offer_sections,id |

## Model Features

### OfferSection Model

The `OfferSection` model includes:

- **Fillable Fields**: All database columns except `id`, `created_at`, `updated_at`
- **Casts**: `is_active` as boolean, `sort_order` as integer
- **Default Attributes**: Sensible defaults for common fields
- **Accessor**: `image_url` - Returns full URL for the image
- **Scope**: `active()` - Filters active offer sections

### Usage Examples

```php
// Get active offer sections
$activeOffers = OfferSection::active()->get();

// Get offer sections ordered by sort_order
$orderedOffers = OfferSection::active()
    ->orderBy('sort_order')
    ->orderByDesc('id')
    ->get();

// Access image URL
$offer = OfferSection::find(1);
echo $offer->image_url; // http://domain.com/storage/offer-sections/image.jpg
```

## File Storage

Images are stored in the `storage/app/public/offer-sections/` directory. The system automatically:

- Creates the directory if it doesn't exist
- Generates unique filenames
- Deletes old images when updating
- Provides public access via the `image_url` accessor

## Frontend Integration

### React Component Example

```jsx
import { useState, useEffect } from 'react';

function OfferSection() {
  const [offerSections, setOfferSections] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetch('/api/offer-sections')
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          setOfferSections(data.data);
        }
        setLoading(false);
      })
      .catch(error => {
        console.error('Error fetching offer sections:', error);
        setLoading(false);
      });
  }, []);

  if (loading) return <div>Loading...</div>;

  return (
    <div className="offer-sections">
      {offerSections.map(section => (
        <section key={section.id} className="offer-section">
          <div className="offer-image">
            <img src={section.image_url} alt={section.alt_text} />
          </div>
          <div className="offer-text">
            <h2>{section.discount_percentage}</h2>
            <p className="offer-title">{section.title}</p>
            <p className="offer-desc">{section.description}</p>
            <a href={section.button_link} className="offer-btn">
              {section.button_text}
            </a>
          </div>
        </section>
      ))}
    </div>
  );
}
```

## Database Seeding

A seeder is provided to create sample data:

```bash
php artisan db:seed --class=OfferSectionSeeder
```

This creates a sample offer section with Valentine's Day content matching your original frontend component.

## Migration

To create the database table:

```bash
php artisan migrate
```

## Security Considerations

1. **Authentication**: Admin endpoints require valid Sanctum tokens
2. **Authorization**: Admin endpoints require admin privileges
3. **File Upload**: Images are validated for type and size
4. **SQL Injection**: Uses Eloquent ORM for protection
5. **XSS**: Input validation prevents malicious content

## Performance Notes

- Public endpoint uses `active()` scope for efficient filtering
- Images are served via Laravel's storage system
- Pagination is available for admin list endpoint
- Database indexes on `is_active` and `sort_order` recommended for large datasets

## Troubleshooting

### Common Issues

1. **Image Upload Fails**
   - Check file permissions on `storage/app/public/offer-sections/`
   - Ensure `php artisan storage:link` has been run
   - Verify file size is under 2MB

2. **Image URLs Not Working**
   - Run `php artisan storage:link` to create symbolic link
   - Check `APP_URL` in `.env` file
   - Verify web server can serve files from `public/storage`

3. **Admin Access Denied**
   - Ensure user has admin role
   - Check Sanctum token is valid
   - Verify `is_admin` middleware is working

### Debug Commands

```bash
# Check storage link
ls -la public/storage

# Clear cache
php artisan cache:clear
php artisan config:clear

# Check routes
php artisan route:list --name=offer-sections
```

## Future Enhancements

Potential improvements for future versions:

1. **Multiple Languages**: Add translation support
2. **Scheduling**: Add start/end dates for offers
3. **Analytics**: Track click-through rates
4. **Templates**: Predefined offer section templates
5. **A/B Testing**: Multiple versions of offer sections
6. **Rich Text**: WYSIWYG editor for descriptions
7. **Video Support**: Allow video backgrounds
8. **Mobile Optimization**: Responsive image handling
