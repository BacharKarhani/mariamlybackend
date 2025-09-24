# Offer Text Management API Documentation

## Overview
The Offer API provides a simple text management system where admins can set a single offer text that all users can view. The system enforces a single-record constraint, meaning only one offer can exist at a time.

## Features
- ✅ **Single Record Constraint**: Only one offer can exist in the system
- ✅ **Admin-Only Management**: Only users with `role_id = 1` can create, update, or delete offers
- ✅ **Public Read Access**: All users can view the current offer text without authentication
- ✅ **Automatic Update Logic**: Creating a new offer updates the existing one
- ✅ **Input Validation**: Offer text is required and limited to 1000 characters

## Database Schema

### Offers Table
```sql
CREATE TABLE offers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    offer_text TEXT NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

## API Endpoints

### Public Endpoints (No Authentication Required)

#### Get Current Offer Text
**Endpoint:** `GET /api/offer`

**Description:** Retrieves the current offer text. Returns `null` if no offer exists.

**Request:**
```http
GET /api/offer
```

**Response:**
```json
{
    "success": true,
    "offer": "Special 50% off on all lipsticks this week!"
}
```

**Response (No Offer):**
```json
{
    "success": true,
    "offer": null
}
```

---

### Admin Endpoints (Requires Admin Authentication)

#### Get Offer Details (Admin)
**Endpoint:** `GET /api/admin/offer`

**Description:** Retrieves the complete offer record for admin management.

**Headers:**
```http
Authorization: Bearer {admin_token}
```

**Request:**
```http
GET /api/admin/offer
```

**Response:**
```json
{
    "success": true,
    "offer": {
        "id": 1,
        "offer_text": "Special 50% off on all lipsticks this week!",
        "created_at": "2025-09-24T08:38:09.000000Z",
        "updated_at": "2025-09-24T10:15:30.000000Z"
    }
}
```

**Response (No Offer):**
```json
{
    "success": true,
    "offer": null
}
```

---

#### Create or Update Offer
**Endpoint:** `POST /api/admin/offer`

**Description:** Creates a new offer or updates the existing one. If an offer already exists, it will be updated with the new text.

**Headers:**
```http
Authorization: Bearer {admin_token}
Content-Type: application/json
```

**Request Body:**
```json
{
    "offer_text": "New Year Sale - Up to 70% off on all products!"
}
```

**Validation Rules:**
- `offer_text`: Required, string, maximum 1000 characters

**Response (Create New Offer):**
```json
{
    "success": true,
    "message": "Offer created successfully",
    "offer": {
        "id": 1,
        "offer_text": "New Year Sale - Up to 70% off on all products!",
        "created_at": "2025-09-24T08:38:09.000000Z",
        "updated_at": "2025-09-24T08:38:09.000000Z"
    }
}
```

**Response (Update Existing Offer):**
```json
{
    "success": true,
    "message": "Offer updated successfully",
    "offer": {
        "id": 1,
        "offer_text": "Updated offer text",
        "created_at": "2025-09-24T08:38:09.000000Z",
        "updated_at": "2025-09-24T10:15:30.000000Z"
    }
}
```

**Error Response (Validation Error):**
```json
{
    "message": "The offer text field is required.",
    "errors": {
        "offer_text": [
            "The offer text field is required."
        ]
    }
}
```

---

#### Delete Offer
**Endpoint:** `DELETE /api/admin/offer`

**Description:** Deletes the current offer from the system.

**Headers:**
```http
Authorization: Bearer {admin_token}
```

**Request:**
```http
DELETE /api/admin/offer
```

**Response (Success):**
```json
{
    "success": true,
    "message": "Offer deleted successfully"
}
```

**Response (No Offer to Delete):**
```json
{
    "success": false,
    "message": "No offer found to delete"
}
```

---

## Authentication

### Admin Authentication
All admin endpoints require:
1. **Sanctum Token**: Valid Bearer token in Authorization header
2. **Admin Role**: User must have `role_id = 1`

**Example:**
```http
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...
```

### Authentication Errors

**Unauthenticated (401):**
```json
{
    "message": "Unauthenticated."
}
```

**Unauthorized - Not Admin (403):**
```json
{
    "message": "Access denied. Admins only."
}
```

---

## Error Handling

### HTTP Status Codes
- `200` - Success
- `201` - Created (new offer)
- `401` - Unauthenticated
- `403` - Forbidden (not admin)
- `404` - Not Found (no offer to delete)
- `422` - Validation Error
- `500` - Server Error

### Common Error Responses

**Validation Error (422):**
```json
{
    "message": "The given data was invalid.",
    "errors": {
        "offer_text": [
            "The offer text field is required.",
            "The offer text may not be greater than 1000 characters."
        ]
    }
}
```

**Server Error (500):**
```json
{
    "message": "Server Error"
}
```

---

## Usage Examples

### Frontend Integration

#### JavaScript/Fetch Example
```javascript
// Get current offer (public)
async function getCurrentOffer() {
    try {
        const response = await fetch('/api/offer');
        const data = await response.json();
        
        if (data.success) {
            if (data.offer) {
                document.getElementById('offer-banner').textContent = data.offer;
                document.getElementById('offer-banner').style.display = 'block';
            } else {
                document.getElementById('offer-banner').style.display = 'none';
            }
        }
    } catch (error) {
        console.error('Error fetching offer:', error);
    }
}

// Admin: Create/Update offer
async function saveOffer(offerText, adminToken) {
    try {
        const response = await fetch('/api/admin/offer', {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${adminToken}`,
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                offer_text: offerText
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert(data.message);
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        console.error('Error saving offer:', error);
    }
}

// Admin: Delete offer
async function deleteOffer(adminToken) {
    try {
        const response = await fetch('/api/admin/offer', {
            method: 'DELETE',
            headers: {
                'Authorization': `Bearer ${adminToken}`
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert(data.message);
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        console.error('Error deleting offer:', error);
    }
}
```

#### React Example
```jsx
import React, { useState, useEffect } from 'react';

const OfferBanner = () => {
    const [offer, setOffer] = useState(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        fetchOffer();
    }, []);

    const fetchOffer = async () => {
        try {
            const response = await fetch('/api/offer');
            const data = await response.json();
            
            if (data.success) {
                setOffer(data.offer);
            }
        } catch (error) {
            console.error('Error fetching offer:', error);
        } finally {
            setLoading(false);
        }
    };

    if (loading) return <div>Loading...</div>;
    
    if (!offer) return null;

    return (
        <div className="offer-banner">
            <p>{offer}</p>
        </div>
    );
};

export default OfferBanner;
```

#### Vue.js Example
```vue
<template>
    <div v-if="offer" class="offer-banner">
        <p>{{ offer }}</p>
    </div>
</template>

<script>
export default {
    data() {
        return {
            offer: null
        };
    },
    async mounted() {
        await this.fetchOffer();
    },
    methods: {
        async fetchOffer() {
            try {
                const response = await fetch('/api/offer');
                const data = await response.json();
                
                if (data.success) {
                    this.offer = data.offer;
                }
            } catch (error) {
                console.error('Error fetching offer:', error);
            }
        }
    }
};
</script>
```

---

## Testing with cURL

### Public Endpoints
```bash
# Get current offer
curl -X GET http://localhost:8000/api/offer
```

### Admin Endpoints
```bash
# Get offer details (admin)
curl -X GET http://localhost:8000/api/admin/offer \
  -H "Authorization: Bearer YOUR_ADMIN_TOKEN"

# Create/Update offer
curl -X POST http://localhost:8000/api/admin/offer \
  -H "Authorization: Bearer YOUR_ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"offer_text": "Special offer text here!"}'

# Delete offer
curl -X DELETE http://localhost:8000/api/admin/offer \
  -H "Authorization: Bearer YOUR_ADMIN_TOKEN"
```

---

## Business Logic

### Single Record Constraint
- The system automatically enforces that only one offer can exist
- When creating a new offer, if one already exists, it updates the existing record
- This prevents multiple offers from cluttering the system
- Simplifies frontend implementation (no need to handle multiple offers)

### Admin Management
- Only users with `role_id = 1` can manage offers
- Uses Laravel Sanctum for API authentication
- Middleware `is_admin` protects admin endpoints

### Public Access
- All users can view the current offer without authentication
- Returns `null` if no offer exists
- Frontend can easily show/hide offer banners based on response

---

## Security Considerations

1. **Authentication**: Admin endpoints require valid Sanctum tokens
2. **Authorization**: Only admin users (`role_id = 1`) can modify offers
3. **Input Validation**: Offer text is validated for required and length constraints
4. **SQL Injection**: Laravel Eloquent ORM prevents SQL injection attacks
5. **XSS Protection**: Text is stored as-is; frontend should escape HTML if needed

---

## Performance Notes

- **Database**: Simple table structure with minimal overhead
- **Caching**: Consider implementing Redis caching for frequently accessed offers
- **Indexing**: Primary key index is sufficient for single-record queries
- **Response Size**: Minimal JSON response size

---

## Future Enhancements

Potential improvements for the offer system:

1. **Expiration Dates**: Add `expires_at` field for time-limited offers
2. **Offer Types**: Different types of offers (percentage, fixed amount, etc.)
3. **Targeting**: Show different offers to different user segments
4. **Analytics**: Track offer views and effectiveness
5. **Rich Text**: Support for HTML formatting in offer text
6. **Images**: Add support for offer banners/images
7. **Multiple Languages**: Support for localized offers

---

## Support

For technical support or questions about the Offer API, please contact the development team or refer to the main API documentation.
