# Review System Documentation

## Overview
The Review System allows users to submit product reviews with ratings and comments. Reviews require admin approval before being displayed publicly. Each user can only review a product once.

## Database Schema

### Reviews Table
```sql
CREATE TABLE reviews (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    product_id BIGINT NOT NULL,
    comment TEXT NOT NULL,
    stars_count INT UNSIGNED NOT NULL CHECK (stars_count >= 1 AND stars_count <= 5),
    status BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);
```

### Fields Description
- `id`: Unique review identifier
- `user_id`: Foreign key to users table
- `product_id`: Foreign key to products table
- `comment`: Review text (max 1000 characters)
- `stars_count`: Rating from 1 to 5 stars
- `status`: Approval status (false = pending, true = approved)
- `created_at`: Review submission timestamp
- `updated_at`: Last modification timestamp

## Models

### Review Model
```php
class Review extends Model
{
    protected $fillable = [
        'user_id',
        'product_id', 
        'comment',
        'stars_count',
        'status'
    ];

    protected $casts = [
        'status' => 'boolean',
        'stars_count' => 'integer'
    ];

    protected $appends = ['user_name'];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Accessor for dynamic user name
    public function getUserNameAttribute()
    {
        return $this->user ? $this->user->name : null;
    }
}
```

### User Model (Updated)
```php
// Added to User model
public function reviews()
{
    return $this->hasMany(Review::class);
}

public function getNameAttribute()
{
    return $this->fname . ' ' . $this->lname;
}
```

### Product Model (Updated)
```php
// Added to Product model
public function reviews()
{
    return $this->hasMany(Review::class);
}

public function approvedReviews()
{
    return $this->hasMany(Review::class)->where('status', true);
}
```

## API Endpoints

### Public Endpoints (No Authentication Required)

#### Get Reviews
```http
GET /api/reviews
GET /api/reviews?product_id=123
GET /api/products/{product}/reviews
```

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "user_id": 5,
            "product_id": 123,
            "user_name": "John Doe",
            "comment": "Great product!",
            "stars_count": 5,
            "status": true,
            "created_at": "2025-01-24T16:55:34.000000Z",
            "updated_at": "2025-01-24T16:55:34.000000Z",
            "user": {
                "id": 5,
                "fname": "John",
                "lname": "Doe"
            },
            "product": {
                "id": 123,
                "name": "Amazing Product"
            }
        }
    ]
}
```

### User Endpoints (Authentication Required)

#### Submit Review
```http
POST /api/reviews
Authorization: Bearer {token}
Content-Type: application/json

{
    "product_id": 123,
    "comment": "Great product! Very satisfied.",
    "stars_count": 5
}
```

**Success Response (201):**
```json
{
    "success": true,
    "message": "Review submitted successfully. It will be reviewed by admin.",
    "data": {
        "id": 1,
        "user_id": 5,
        "product_id": 123,
        "user_name": "John Doe",
        "comment": "Great product! Very satisfied.",
        "stars_count": 5,
        "status": false,
        "created_at": "2025-01-24T16:55:34.000000Z",
        "updated_at": "2025-01-24T16:55:34.000000Z",
        "user": {
            "id": 5,
            "fname": "John",
            "lname": "Doe"
        },
        "product": {
            "id": 123,
            "name": "Amazing Product"
        }
    }
}
```

**Error Responses:**
```json
// Validation Error (422)
{
    "success": false,
    "message": "Validation errors",
    "errors": {
        "product_id": ["The product id field is required."],
        "stars_count": ["The stars count must be between 1 and 5."]
    }
}

// Duplicate Review (409)
{
    "success": false,
    "message": "You have already reviewed this product"
}
```

#### Get Specific Review
```http
GET /api/reviews/{id}
Authorization: Bearer {token}
```

### Admin Endpoints (Authentication + Admin Role Required)

#### Get All Reviews (Including Pending)
```http
GET /api/admin/reviews
GET /api/admin/reviews?product_id=123
GET /api/admin/products/{product}/reviews
Authorization: Bearer {admin_token}
```

#### Update Review
```http
PUT /api/admin/reviews/{id}
Authorization: Bearer {admin_token}
Content-Type: application/json

{
    "status": true,
    "comment": "Updated comment if needed",
    "stars_count": 4
}
```

**Success Response:**
```json
{
    "success": true,
    "message": "Review updated successfully",
    "data": {
        "id": 1,
        "user_id": 5,
        "product_id": 123,
        "user_name": "John Doe",
        "comment": "Updated comment if needed",
        "stars_count": 4,
        "status": true,
        "created_at": "2025-01-24T16:55:34.000000Z",
        "updated_at": "2025-01-24T17:30:15.000000Z"
    }
}
```

#### Delete Review
```http
DELETE /api/admin/reviews/{id}
Authorization: Bearer {admin_token}
```

**Success Response:**
```json
{
    "success": true,
    "message": "Review deleted successfully"
}
```

## Validation Rules

### Submit Review
- `product_id`: Required, must exist in products table
- `comment`: Required, string, max 1000 characters
- `stars_count`: Required, integer, between 1 and 5

### Update Review (Admin)
- `status`: Optional, boolean
- `comment`: Optional, string, max 1000 characters
- `stars_count`: Optional, integer, between 1 and 5
- `product_id`: Optional, must exist in products table

## Business Logic

### Review Submission
1. User must be authenticated
2. User can only review each product once
3. Review is created with `status = false` (pending approval)
4. User name is automatically retrieved from user relationship

### Review Approval Process
1. Admin can view all reviews (approved + pending)
2. Admin can change `status` from `false` to `true` to approve
3. Admin can edit review content if needed
4. Admin can delete inappropriate reviews

### Public Display
1. Only approved reviews (`status = true`) are shown publicly
2. Reviews include user name, comment, star rating, and timestamps
3. Reviews can be filtered by specific product

## Error Handling

### Common Error Codes
- `401`: Unauthorized (missing or invalid token)
- `403`: Forbidden (insufficient permissions for admin endpoints)
- `404`: Not Found (review/product not found)
- `409`: Conflict (duplicate review)
- `422`: Validation Error (invalid input data)

### Error Response Format
```json
{
    "success": false,
    "message": "Error description",
    "errors": {
        "field_name": ["Error message"]
    }
}
```

## Usage Examples

### Frontend Integration

#### Submit Review Form
```javascript
const submitReview = async (productId, comment, stars) => {
    try {
        const response = await fetch('/api/reviews', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${userToken}`
            },
            body: JSON.stringify({
                product_id: productId,
                comment: comment,
                stars_count: stars
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('Review submitted successfully!');
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        console.error('Error submitting review:', error);
    }
};
```

#### Display Product Reviews
```javascript
const getProductReviews = async (productId) => {
    try {
        const response = await fetch(`/api/products/${productId}/reviews`);
        const data = await response.json();
        
        if (data.success) {
            displayReviews(data.data);
        }
    } catch (error) {
        console.error('Error fetching reviews:', error);
    }
};
```

### Admin Panel Integration

#### Approve Review
```javascript
const approveReview = async (reviewId) => {
    try {
        const response = await fetch(`/api/admin/reviews/${reviewId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${adminToken}`
            },
            body: JSON.stringify({
                status: true
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('Review approved successfully!');
        }
    } catch (error) {
        console.error('Error approving review:', error);
    }
};
```

## Security Considerations

1. **Authentication Required**: Users must be logged in to submit reviews
2. **Admin Authorization**: Admin endpoints require admin role verification
3. **Input Validation**: All inputs are validated and sanitized
4. **SQL Injection Protection**: Uses Eloquent ORM with parameterized queries
5. **Rate Limiting**: Consider implementing rate limiting for review submission

## Performance Considerations

1. **Eager Loading**: Reviews are loaded with user and product relationships
2. **Indexing**: Consider adding database indexes on frequently queried fields:
   - `(product_id, status)` for product review queries
   - `(user_id, product_id)` for duplicate check queries
3. **Caching**: Consider caching approved reviews for frequently viewed products

## Migration

To set up the review system, run:
```bash
php artisan migrate
```

This will create the `reviews` table with all necessary fields and constraints.

## Testing

### Test Cases to Consider
1. User can submit review for product
2. User cannot submit duplicate review for same product
3. Only approved reviews are shown publicly
4. Admin can approve/reject reviews
5. Admin can edit/delete reviews
6. Validation works for all input fields
7. User name is correctly retrieved from user relationship

### Sample Test Data
```php
// Create test review
Review::create([
    'user_id' => 1,
    'product_id' => 1,
    'comment' => 'Great product!',
    'stars_count' => 5,
    'status' => false
]);
```

## Troubleshooting

### Common Issues

1. **User name not showing**: Ensure user relationship is loaded and `getNameAttribute()` is working
2. **Duplicate review error**: Check if user already reviewed the product
3. **Validation errors**: Verify all required fields are provided with correct data types
4. **Permission denied**: Ensure user has proper authentication and admin role for admin endpoints

### Debug Tips

1. Check database constraints and foreign key relationships
2. Verify user authentication and role permissions
3. Test API endpoints with proper headers and authentication
4. Check Laravel logs for detailed error messages
