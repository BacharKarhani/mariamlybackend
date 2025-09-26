# Product Variant Ordering System

## Overview
This document describes the enhanced variant editing and ordering functionality implemented for the Mariamly backend system.

## Features Implemented

### 1. Database Changes
- Added `sort_order` column to `product_variants` table
- Migration: `2025_09_26_094912_add_sort_order_to_product_variants_table.php`

### 2. Model Updates

#### ProductVariant Model
- Added `sort_order` to `$fillable` array
- Added `scopeOrdered()` method for consistent ordering
- Ordering: `sort_order ASC, id ASC`

#### Product Model
- Updated `variants()` relationship to use `ordered()` scope
- Variants are now automatically ordered by `sort_order`

### 3. Controller Enhancements

#### ProductVariantController
- **Enhanced `store()` method**: Supports `sort_order` parameter
- **Enhanced `update()` method**: Supports `sort_order` updates
- **New `reorder()` method**: Bulk reorder variants for a product
- **New `moveUp()` method**: Move variant up in order
- **New `moveDown()` method**: Move variant down in order

#### ProductController
- **Enhanced `store()` method**: Handles `sort_order` for variants during product creation
- **Enhanced `update()` method**: Handles `sort_order` for variants during product updates
- Auto-assigns sort order based on array index if not provided

### 4. API Routes Added

```php
// Variant ordering routes
POST /api/admin/products/{product}/variants/reorder
POST /api/admin/variants/{variant}/move-up
POST /api/admin/variants/{variant}/move-down
```

## API Usage Examples

### 1. Create Variant with Sort Order
```http
POST /api/admin/products/1/variants
Content-Type: application/json

{
    "color": "Red",
    "hex_color": "#FF0000",
    "sort_order": 1,
    "images": [/* file uploads */]
}
```

### 2. Update Variant Sort Order
```http
PUT /api/admin/variants/1
Content-Type: application/json

{
    "color": "Red",
    "hex_color": "#FF0000",
    "sort_order": 3
}
```

### 3. Bulk Reorder Variants
```http
POST /api/admin/products/1/variants/reorder
Content-Type: application/json

{
    "variants": [
        {"id": 1, "sort_order": 2},
        {"id": 2, "sort_order": 1},
        {"id": 3, "sort_order": 3}
    ]
}
```

### 4. Move Variant Up/Down
```http
POST /api/admin/variants/1/move-up
POST /api/admin/variants/1/move-down
```

### 5. Create Product with Ordered Variants
```http
POST /api/admin/products
Content-Type: multipart/form-data

{
    "name": "Test Product",
    "variants": [
        {
            "color": "Red",
            "hex_color": "#FF0000",
            "sort_order": 1,
            "images": [/* files */]
        },
        {
            "color": "Blue",
            "hex_color": "#0000FF",
            "sort_order": 2,
            "images": [/* files */]
        }
    ]
}
```

## Response Format

All variant endpoints return consistent JSON responses:

```json
{
    "success": true,
    "message": "Operation successful",
    "variant": {
        "id": 1,
        "product_id": 1,
        "color": "Red",
        "hex_color": "#FF0000",
        "sort_order": 1,
        "images": [
            {
                "id": 1,
                "variant_id": 1,
                "path": "products/image.jpg",
                "url": "http://domain.com/storage/products/image.jpg"
            }
        ]
    }
}
```

## Frontend Integration

### Displaying Ordered Variants
Variants are now automatically returned in the correct order when fetching products:

```javascript
// GET /api/products/1
{
    "product": {
        "id": 1,
        "name": "Product Name",
        "variants": [
            // Variants are automatically ordered by sort_order
            {"id": 2, "color": "Blue", "sort_order": 1},
            {"id": 1, "color": "Red", "sort_order": 2}
        ]
    }
}
```

### Drag & Drop Reordering
Use the `reorder` endpoint for drag-and-drop functionality:

```javascript
const reorderVariants = async (productId, variantOrders) => {
    const response = await fetch(`/api/admin/products/${productId}/variants/reorder`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token}`
        },
        body: JSON.stringify({
            variants: variantOrders
        })
    });
    
    return response.json();
};
```

## Migration Notes

1. Run the migration: `php artisan migrate`
2. Existing variants will have `sort_order = 0`
3. New variants will auto-assign sort order based on creation order
4. Use the reorder endpoint to set proper sort orders for existing variants

## Benefits

1. **Consistent Display**: Variants always appear in the intended order
2. **Admin Control**: Easy reordering through API endpoints
3. **Flexible**: Supports both bulk and individual variant reordering
4. **Backward Compatible**: Existing functionality remains unchanged
5. **Performance**: Efficient ordering with database-level sorting
