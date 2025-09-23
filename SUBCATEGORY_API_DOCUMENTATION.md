# Subcategory API Documentation

## Base URL
```
http://127.0.0.1:8000/api
```

---

## Public Endpoints (No Authentication Required)

### 1. Get All Subcategories
```http
GET /subcategories
```

**Query Parameters:**
- `category_id` (optional): Filter subcategories by category ID

**Example Request:**
```bash
GET /api/subcategories
GET /api/subcategories?category_id=1
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Smartphones",
      "image": "subcategories/smartphones.jpg",
      "image_url": "http://127.0.0.1:8000/storage/subcategories/smartphones.jpg",
      "category_id": 1,
      "created_at": "2025-09-23T09:24:14.000000Z",
      "updated_at": "2025-09-23T09:24:14.000000Z",
      "category": {
        "id": 1,
        "name": "Electronics",
        "image": "categories/electronics.jpg",
        "image_url": "http://127.0.0.1:8000/storage/categories/electronics.jpg",
        "created_at": "2025-09-23T09:24:14.000000Z",
        "updated_at": "2025-09-23T09:24:14.000000Z"
      }
    }
  ]
}
```

### 2. Get Single Subcategory
```http
GET /subcategories/{id}
```

**Parameters:**
- `id` (required): Subcategory ID

**Example Request:**
```bash
GET /api/subcategories/1
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Smartphones",
    "image": "subcategories/smartphones.jpg",
    "image_url": "http://127.0.0.1:8000/storage/subcategories/smartphones.jpg",
    "category_id": 1,
    "created_at": "2025-09-23T09:24:14.000000Z",
    "updated_at": "2025-09-23T09:24:14.000000Z",
    "category": {
      "id": 1,
      "name": "Electronics",
      "image": "categories/electronics.jpg",
      "image_url": "http://127.0.0.1:8000/storage/categories/electronics.jpg",
      "created_at": "2025-09-23T09:24:14.000000Z",
      "updated_at": "2025-09-23T09:24:14.000000Z"
    },
    "products": [
      {
        "id": 1,
        "name": "iPhone 15",
        "desc": "Latest iPhone model",
        "category_id": 1,
        "subcategory_id": 1,
        "brand_id": 1,
        "selling_price": 999.99,
        "regular_price": 1099.99,
        "discount": 9.09,
        "quantity": 50,
        "is_trending": true,
        "is_new": true,
        "created_at": "2025-09-23T09:24:14.000000Z",
        "updated_at": "2025-09-23T09:24:14.000000Z"
      }
    ]
  }
}
```

### 3. Get Subcategories by Category
```http
GET /categories/{categoryId}/subcategories
```

**Parameters:**
- `categoryId` (required): Category ID

**Example Request:**
```bash
GET /api/categories/1/subcategories
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Smartphones",
      "image": "subcategories/smartphones.jpg",
      "image_url": "http://127.0.0.1:8000/storage/subcategories/smartphones.jpg",
      "category_id": 1,
      "created_at": "2025-09-23T09:24:14.000000Z",
      "updated_at": "2025-09-23T09:24:14.000000Z"
    },
    {
      "id": 2,
      "name": "Laptops",
      "image": "subcategories/laptops.jpg",
      "image_url": "http://127.0.0.1:8000/storage/subcategories/laptops.jpg",
      "category_id": 1,
      "created_at": "2025-09-23T09:24:14.000000Z",
      "updated_at": "2025-09-23T09:24:14.000000Z"
    }
  ]
}
```

---

## Admin Endpoints (Authentication Required)

### 4. Create Subcategory
```http
POST /subcategories
```

**Headers:**
```
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Body Parameters:**
- `name` (required): Subcategory name (string, max 255 characters)
- `category_id` (required): Category ID (must exist in categories table)
- `image` (optional): Image file (jpeg, png, jpg, gif, max 2MB)

**Example Request:**
```bash
POST /api/subcategories
Content-Type: multipart/form-data

name=Smartphones
category_id=1
image=[file]
```

**Response:**
```json
{
  "success": true,
  "message": "Subcategory created successfully",
  "data": {
    "id": 1,
    "name": "Smartphones",
    "image": "subcategories/1737623456.jpg",
    "image_url": "http://127.0.0.1:8000/storage/subcategories/1737623456.jpg",
    "category_id": 1,
    "created_at": "2025-09-23T09:24:14.000000Z",
    "updated_at": "2025-09-23T09:24:14.000000Z",
    "category": {
      "id": 1,
      "name": "Electronics",
      "image": "categories/electronics.jpg",
      "image_url": "http://127.0.0.1:8000/storage/categories/electronics.jpg",
      "created_at": "2025-09-23T09:24:14.000000Z",
      "updated_at": "2025-09-23T09:24:14.000000Z"
    }
  }
}
```

### 5. Update Subcategory
```http
PUT /subcategories/{id}
```

**Headers:**
```
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Parameters:**
- `id` (required): Subcategory ID

**Body Parameters:**
- `name` (optional): Subcategory name (string, max 255 characters)
- `category_id` (optional): Category ID (must exist in categories table)
- `image` (optional): Image file (jpeg, png, jpg, gif, max 2MB)

**Example Request:**
```bash
PUT /api/subcategories/1
Content-Type: multipart/form-data

name=Smartphones & Tablets
category_id=1
image=[file]
```

**Response:**
```json
{
  "success": true,
  "message": "Subcategory updated successfully",
  "data": {
    "id": 1,
    "name": "Smartphones & Tablets",
    "image": "subcategories/1737623456.jpg",
    "image_url": "http://127.0.0.1:8000/storage/subcategories/1737623456.jpg",
    "category_id": 1,
    "created_at": "2025-09-23T09:24:14.000000Z",
    "updated_at": "2025-09-23T09:24:14.000000Z",
    "category": {
      "id": 1,
      "name": "Electronics",
      "image": "categories/electronics.jpg",
      "image_url": "http://127.0.0.1:8000/storage/categories/electronics.jpg",
      "created_at": "2025-09-23T09:24:14.000000Z",
      "updated_at": "2025-09-23T09:24:14.000000Z"
    }
  }
}
```

### 6. Delete Subcategory
```http
DELETE /subcategories/{id}
```

**Headers:**
```
Authorization: Bearer {token}
```

**Parameters:**
- `id` (required): Subcategory ID

**Example Request:**
```bash
DELETE /api/subcategories/1
```

**Response:**
```json
{
  "success": true,
  "message": "Subcategory deleted successfully"
}
```

---

## Error Responses

### Validation Error (422)
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "name": ["The name field is required."],
    "category_id": ["The category id field is required."]
  }
}
```

### Not Found Error (404)
```json
{
  "message": "No query results for model [App\\Models\\Subcategory] 999"
}
```

### Unauthorized Error (401)
```json
{
  "message": "Unauthenticated."
}
```

### Forbidden Error (403)
```json
{
  "message": "This action is unauthorized."
}
```

---

## Integration with Products

### Filter Products by Subcategory
```http
GET /products?subcategory_id=1
```

### Create Product with Subcategory
```http
POST /products
```

**Body Parameters:**
- `category_id` (required): Category ID
- `subcategory_id` (optional): Subcategory ID (must belong to the selected category)
- `name` (required): Product name
- `brand_id` (required): Brand ID
- ... other product fields

**Example:**
```json
{
  "name": "iPhone 15 Pro",
  "desc": "Latest iPhone with advanced features",
  "category_id": 1,
  "subcategory_id": 1,
  "brand_id": 1,
  "buying_price": 800,
  "regular_price": 1099.99,
  "discount": 9.09,
  "quantity": 50,
  "is_trending": true,
  "is_new": true
}
```

---

## Frontend Integration Examples

### JavaScript/Fetch Examples

```javascript
// Get all subcategories
const getSubcategories = async () => {
  const response = await fetch('/api/subcategories');
  const data = await response.json();
  return data.data;
};

// Get subcategories by category
const getSubcategoriesByCategory = async (categoryId) => {
  const response = await fetch(`/api/categories/${categoryId}/subcategories`);
  const data = await response.json();
  return data.data;
};

// Create subcategory (admin only)
const createSubcategory = async (formData) => {
  const response = await fetch('/api/subcategories', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`
    },
    body: formData
  });
  return response.json();
};

// Filter products by subcategory
const getProductsBySubcategory = async (subcategoryId) => {
  const response = await fetch(`/api/products?subcategory_id=${subcategoryId}`);
  const data = await response.json();
  return data.data;
};
```

### React Example
```jsx
import { useState, useEffect } from 'react';

const ProductForm = () => {
  const [categories, setCategories] = useState([]);
  const [subcategories, setSubcategories] = useState([]);
  const [selectedCategory, setSelectedCategory] = useState('');
  const [selectedSubcategory, setSelectedSubcategory] = useState('');

  // Load categories on component mount
  useEffect(() => {
    fetch('/api/categories')
      .then(res => res.json())
      .then(data => setCategories(data.categories));
  }, []);

  // Load subcategories when category changes
  useEffect(() => {
    if (selectedCategory) {
      fetch(`/api/categories/${selectedCategory}/subcategories`)
        .then(res => res.json())
        .then(data => setSubcategories(data.data));
    } else {
      setSubcategories([]);
    }
    setSelectedSubcategory(''); // Reset subcategory selection
  }, [selectedCategory]);

  return (
    <form>
      <select 
        value={selectedCategory} 
        onChange={(e) => setSelectedCategory(e.target.value)}
      >
        <option value="">Select Category</option>
        {categories.map(category => (
          <option key={category.id} value={category.id}>
            {category.name}
          </option>
        ))}
      </select>

      <select 
        value={selectedSubcategory} 
        onChange={(e) => setSelectedSubcategory(e.target.value)}
        disabled={!selectedCategory}
      >
        <option value="">Select Subcategory (Optional)</option>
        {subcategories.map(subcategory => (
          <option key={subcategory.id} value={subcategory.id}>
            {subcategory.name}
          </option>
        ))}
      </select>
    </form>
  );
};
```

### Vue.js Example
```vue
<template>
  <div>
    <select v-model="selectedCategory" @change="loadSubcategories">
      <option value="">Select Category</option>
      <option v-for="category in categories" :key="category.id" :value="category.id">
        {{ category.name }}
      </option>
    </select>

    <select v-model="selectedSubcategory" :disabled="!selectedCategory">
      <option value="">Select Subcategory (Optional)</option>
      <option v-for="subcategory in subcategories" :key="subcategory.id" :value="subcategory.id">
        {{ subcategory.name }}
      </option>
    </select>
  </div>
</template>

<script>
export default {
  data() {
    return {
      categories: [],
      subcategories: [],
      selectedCategory: '',
      selectedSubcategory: ''
    }
  },
  async mounted() {
    await this.loadCategories();
  },
  methods: {
    async loadCategories() {
      const response = await fetch('/api/categories');
      const data = await response.json();
      this.categories = data.categories;
    },
    async loadSubcategories() {
      if (this.selectedCategory) {
        const response = await fetch(`/api/categories/${this.selectedCategory}/subcategories`);
        const data = await response.json();
        this.subcategories = data.data;
      } else {
        this.subcategories = [];
      }
      this.selectedSubcategory = '';
    }
  }
}
</script>
```

### Angular Example
```typescript
import { Component, OnInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';

interface Category {
  id: number;
  name: string;
  image: string;
  image_url: string;
}

interface Subcategory {
  id: number;
  name: string;
  image: string;
  image_url: string;
  category_id: number;
}

@Component({
  selector: 'app-product-form',
  template: `
    <select [(ngModel)]="selectedCategory" (change)="loadSubcategories()">
      <option value="">Select Category</option>
      <option *ngFor="let category of categories" [value]="category.id">
        {{ category.name }}
      </option>
    </select>

    <select [(ngModel)]="selectedSubcategory" [disabled]="!selectedCategory">
      <option value="">Select Subcategory (Optional)</option>
      <option *ngFor="let subcategory of subcategories" [value]="subcategory.id">
        {{ subcategory.name }}
      </option>
    </select>
  `
})
export class ProductFormComponent implements OnInit {
  categories: Category[] = [];
  subcategories: Subcategory[] = [];
  selectedCategory: string = '';
  selectedSubcategory: string = '';

  constructor(private http: HttpClient) {}

  ngOnInit() {
    this.loadCategories();
  }

  loadCategories() {
    this.http.get<any>('/api/categories').subscribe(data => {
      this.categories = data.categories;
    });
  }

  loadSubcategories() {
    if (this.selectedCategory) {
      this.http.get<any>(`/api/categories/${this.selectedCategory}/subcategories`)
        .subscribe(data => {
          this.subcategories = data.data;
        });
    } else {
      this.subcategories = [];
    }
    this.selectedSubcategory = '';
  }
}
```

---

## cURL Examples

### Get All Subcategories
```bash
curl -X GET "http://127.0.0.1:8000/api/subcategories" \
  -H "Accept: application/json"
```

### Get Subcategories by Category
```bash
curl -X GET "http://127.0.0.1:8000/api/categories/1/subcategories" \
  -H "Accept: application/json"
```

### Create Subcategory (Admin)
```bash
curl -X POST "http://127.0.0.1:8000/api/subcategories" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json" \
  -F "name=Smartphones" \
  -F "category_id=1" \
  -F "image=@/path/to/image.jpg"
```

### Update Subcategory (Admin)
```bash
curl -X PUT "http://127.0.0.1:8000/api/subcategories/1" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json" \
  -F "name=Smartphones & Tablets" \
  -F "category_id=1"
```

### Delete Subcategory (Admin)
```bash
curl -X DELETE "http://127.0.0.1:8000/api/subcategories/1" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

---

## Postman Collection

### Environment Variables
```
base_url: http://127.0.0.1:8000/api
token: YOUR_BEARER_TOKEN
```

### Collection Structure
```
Subcategory API
├── Public Endpoints
│   ├── GET All Subcategories
│   ├── GET Subcategory by ID
│   └── GET Subcategories by Category
├── Admin Endpoints
│   ├── POST Create Subcategory
│   ├── PUT Update Subcategory
│   └── DELETE Subcategory
└── Product Integration
    ├── GET Products by Subcategory
    └── POST Product with Subcategory
```

---

## Database Schema

### Subcategories Table
```sql
CREATE TABLE subcategories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    image VARCHAR(255) NULL,
    category_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);
```

### Products Table (Updated)
```sql
ALTER TABLE products ADD COLUMN subcategory_id BIGINT UNSIGNED NULL;
ALTER TABLE products ADD FOREIGN KEY (subcategory_id) REFERENCES subcategories(id) ON DELETE SET NULL;
```

---

## Important Notes

1. **Authentication**: Admin endpoints require a valid Bearer token with admin privileges
2. **Image Uploads**: Images are stored in `storage/app/public/subcategories/` and accessible via the `image_url` field
3. **Relationships**: Subcategories are linked to categories, and products can optionally belong to a subcategory
4. **Validation**: All inputs are validated on the server side
5. **Error Handling**: All endpoints return appropriate HTTP status codes and error messages
6. **CORS**: Make sure CORS is properly configured for your frontend domain
7. **Rate Limiting**: API endpoints are rate limited (60 requests per minute by default)

---

## Testing

### Unit Tests
```bash
php artisan test --filter=SubcategoryTest
```

### Feature Tests
```bash
php artisan test --filter=SubcategoryApiTest
```

### Manual Testing
Use the provided cURL examples or Postman collection to test all endpoints.

---

## Support

For any issues or questions regarding the Subcategory API, please refer to:
- Laravel Documentation: https://laravel.com/docs
- API Response Format: Follows Laravel's standard JSON response format
- Error Codes: Standard HTTP status codes are used throughout

---

*Last Updated: September 23, 2025*
*Version: 1.0.0*
