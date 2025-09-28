# Brand Ordering System Documentation

## Overview
This document describes the brand ordering functionality implemented for the Mariamly backend system. The system allows administrators to control the display order of brands through a `sort_order` field and provides various methods to reorder brands.

## Features Implemented

### 1. Database Changes
- Added `sort_order` column to `brands` table
- Migration: `2025_09_28_165920_add_sort_order_to_brands_table.php`
- Column type: `integer` with default value `0`
- Position: After `is_active` column

### 2. Model Updates

#### Brand Model
- Added `sort_order` to `$fillable` array
- Added `scopeOrdered()` method for consistent ordering
- Ordering: `sort_order ASC, id ASC`

### 3. Controller Enhancements

#### BrandController
- **Enhanced `store()` method**: Supports `sort_order` parameter
- **Enhanced `update()` method**: Supports `sort_order` updates
- **Enhanced `index()` method**: Returns brands in ordered sequence
- **New `reorder()` method**: Bulk reorder brands
- **New `moveUp()` method**: Move brand up in order
- **New `moveDown()` method**: Move brand down in order

### 4. API Routes Added

```php
// Brand ordering routes
POST /api/admin/brands/reorder
POST /api/admin/brands/{brand}/move-up
POST /api/admin/brands/{brand}/move-down
```

## API Usage Examples

### 1. Create Brand with Sort Order
```http
POST /api/admin/brands
Content-Type: application/json

{
    "name": "Premium Brand",
    "sort_order": 1,
    "is_active": true,
    "image": "/* file upload */",
    "category_ids": [1, 2, 3]
}
```

**Response:**
```json
{
    "success": true,
    "message": "Brand created successfully",
    "brand": {
        "id": 1,
        "name": "Premium Brand",
        "sort_order": 1,
        "is_active": true,
        "image_url": "http://localhost/storage/brands/brand_image.jpg",
        "categories": [
            {"id": 1, "name": "Category 1"},
            {"id": 2, "name": "Category 2"}
        ]
    }
}
```

### 2. Update Brand Sort Order
```http
PUT /api/admin/brands/1
Content-Type: application/json

{
    "name": "Premium Brand",
    "sort_order": 5,
    "is_active": true
}
```

**Response:**
```json
{
    "success": true,
    "message": "Brand updated successfully",
    "brand": {
        "id": 1,
        "name": "Premium Brand",
        "sort_order": 5,
        "is_active": true,
        "categories": []
    }
}
```

### 3. Get All Brands (Ordered)
```http
GET /api/brands
```

**Response:**
```json
{
    "success": true,
    "brands": [
        {
            "id": 3,
            "name": "Brand A",
            "sort_order": 1,
            "is_active": true,
            "categories": []
        },
        {
            "id": 1,
            "name": "Brand B", 
            "sort_order": 2,
            "is_active": true,
            "categories": []
        },
        {
            "id": 2,
            "name": "Brand C",
            "sort_order": 3,
            "is_active": false,
            "categories": []
        }
    ]
}
```

### 4. Bulk Reorder Brands
```http
POST /api/admin/brands/reorder
Content-Type: application/json

{
    "brand_ids": [3, 1, 4, 2]
}
```

**Response:**
```json
{
    "success": true,
    "message": "Brands reordered successfully"
}
```

**Result:** Brands will be reordered as:
- Brand ID 3 → sort_order: 1
- Brand ID 1 → sort_order: 2  
- Brand ID 4 → sort_order: 3
- Brand ID 2 → sort_order: 4

### 5. Move Brand Up
```http
POST /api/admin/brands/2/move-up
```

**Response:**
```json
{
    "success": true,
    "message": "Brand moved up successfully",
    "brand": {
        "id": 2,
        "name": "Brand C",
        "sort_order": 1,
        "is_active": true
    }
}
```

### 6. Move Brand Down
```http
POST /api/admin/brands/1/move-down
```

**Response:**
```json
{
    "success": true,
    "message": "Brand moved down successfully",
    "brand": {
        "id": 1,
        "name": "Brand B",
        "sort_order": 3,
        "is_active": true
    }
}
```

## Validation Rules

### Store/Update Brand
- `name`: required, string, max:255, unique
- `image`: nullable, image file, max:2048KB
- `is_active`: nullable, boolean
- `sort_order`: nullable, integer, min:0
- `category_ids`: nullable, array
- `category_ids.*`: integer, exists in categories table

### Reorder Brands
- `brand_ids`: required, array
- `brand_ids.*`: integer, exists in brands table

## Database Schema

### brands table
```sql
CREATE TABLE brands (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) UNIQUE NOT NULL,
    image VARCHAR(255) NULL,
    is_active BOOLEAN DEFAULT TRUE,
    sort_order INTEGER DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

## Implementation Details

### Ordering Logic
1. **Primary sort**: `sort_order` ASC
2. **Secondary sort**: `id` ASC (for brands with same sort_order)
3. **Default sort_order**: 0 for new brands

### Move Up/Down Logic
- **Move Up**: Swaps sort_order with the brand that has the highest sort_order less than current
- **Move Down**: Swaps sort_order with the brand that has the lowest sort_order greater than current
- If no adjacent brand exists, no change is made

### Bulk Reorder Logic
- Assigns sequential sort_order values starting from 1
- Order follows the sequence of brand_ids in the request array

## Error Handling

### Common Error Responses

**Validation Error:**
```json
{
    "message": "The given data was invalid.",
    "errors": {
        "sort_order": ["The sort order must be at least 0."]
    }
}
```

**Brand Not Found:**
```json
{
    "message": "No query results for model [App\\Models\\Brand] 999"
}
```

**Database Connection Error:**
```json
{
    "message": "SQLSTATE[HY000] [2002] No connection could be made because the target machine actively refused it"
}
```

## Migration Instructions

1. **Run the migration:**
   ```bash
   php artisan migrate
   ```

2. **Update existing brands (optional):**
   ```sql
   UPDATE brands SET sort_order = id WHERE sort_order = 0;
   ```

## Frontend Integration Examples

### JavaScript/Axios Examples

**Create brand with ordering:**
```javascript
const createBrand = async (brandData) => {
    const formData = new FormData();
    formData.append('name', brandData.name);
    formData.append('sort_order', brandData.sortOrder);
    formData.append('is_active', brandData.isActive);
    
    if (brandData.image) {
        formData.append('image', brandData.image);
    }
    
    const response = await axios.post('/api/admin/brands', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
    });
    
    return response.data;
};
```

**Drag and drop reordering:**
```javascript
const reorderBrands = async (brandIds) => {
    const response = await axios.post('/api/admin/brands/reorder', {
        brand_ids: brandIds
    });
    
    return response.data;
};

// Usage with drag and drop
const handleDragEnd = (result) => {
    if (!result.destination) return;
    
    const items = Array.from(brands);
    const [reorderedItem] = items.splice(result.source.index, 1);
    items.splice(result.destination.index, 0, reorderedItem);
    
    const brandIds = items.map(brand => brand.id);
    reorderBrands(brandIds);
};
```

**Move up/down buttons:**
```javascript
const moveBrandUp = async (brandId) => {
    const response = await axios.post(`/api/admin/brands/${brandId}/move-up`);
    return response.data;
};

const moveBrandDown = async (brandId) => {
    const response = await axios.post(`/api/admin/brands/${brandId}/move-down`);
    return response.data;
};
```

## Testing

### Unit Tests Examples

```php
// Test brand ordering
public function test_brands_are_returned_in_sort_order()
{
    Brand::factory()->create(['name' => 'Brand A', 'sort_order' => 3]);
    Brand::factory()->create(['name' => 'Brand B', 'sort_order' => 1]);
    Brand::factory()->create(['name' => 'Brand C', 'sort_order' => 2]);

    $response = $this->getJson('/api/brands');

    $response->assertStatus(200);
    $brands = $response->json('brands');
    
    $this->assertEquals('Brand B', $brands[0]['name']);
    $this->assertEquals('Brand C', $brands[1]['name']);
    $this->assertEquals('Brand A', $brands[2]['name']);
}

// Test bulk reordering
public function test_brands_can_be_bulk_reordered()
{
    $brand1 = Brand::factory()->create(['sort_order' => 1]);
    $brand2 = Brand::factory()->create(['sort_order' => 2]);
    $brand3 = Brand::factory()->create(['sort_order' => 3]);

    $response = $this->postJson('/api/admin/brands/reorder', [
        'brand_ids' => [$brand3->id, $brand1->id, $brand2->id]
    ]);

    $response->assertStatus(200);
    
    $this->assertEquals(1, $brand3->fresh()->sort_order);
    $this->assertEquals(2, $brand1->fresh()->sort_order);
    $this->assertEquals(3, $brand2->fresh()->sort_order);
}
```

## Performance Considerations

1. **Indexing**: Consider adding an index on `sort_order` for better query performance:
   ```sql
   ALTER TABLE brands ADD INDEX idx_brands_sort_order (sort_order);
   ```

2. **Bulk Operations**: The reorder method uses individual UPDATE queries. For large datasets, consider using a single transaction with raw SQL.

3. **Caching**: Consider caching the ordered brand list if it's frequently accessed.

## Security Notes

- All ordering endpoints require admin authentication
- Input validation prevents SQL injection
- File upload validation for brand images
- CSRF protection enabled for web routes

## Troubleshooting

### Common Issues

1. **Migration fails**: Ensure database connection is working
2. **Sort order not updating**: Check if brand exists and validation passes
3. **Move up/down not working**: Verify there are adjacent brands to swap with
4. **Bulk reorder fails**: Ensure all brand IDs exist in the database

### Debug Commands

```bash
# Check migration status
php artisan migrate:status

# Check brand data
php artisan tinker
>>> Brand::ordered()->get(['id', 'name', 'sort_order']);

# Clear cache if needed
php artisan cache:clear
php artisan config:clear
```
