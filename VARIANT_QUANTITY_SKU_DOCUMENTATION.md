# Product Variant Quantity & SKU System

## Overview
This document describes the enhanced product variant system that now includes individual quantity and SKU management for each variant.

## New Features

### 1. Database Changes
- Added `sku` column to `product_variants` table (nullable, string, max 100 chars)
- Added `quantity` column to `product_variants` table (integer, default 0)
- Migration: `2025_09_26_131650_add_quantity_and_sku_to_product_variants_table.php`

### 2. Model Enhancements

#### ProductVariant Model
- **New fillable fields**: `sku`, `quantity`
- **New scopes**:
  - `inStock()` - Filter variants with quantity > 0
  - `outOfStock()` - Filter variants with quantity <= 0
- **New methods**:
  - `isInStock()` - Check if variant has stock
  - `isOutOfStock()` - Check if variant is out of stock

### 3. Controller Updates

#### ProductVariantController
- **Enhanced `store()` method**: Now accepts `sku` and `quantity`
- **Enhanced `update()` method**: Now accepts `sku` and `quantity`
- **New `updateQuantity()` method**: Update only the quantity
- **New `inStock()` method**: Get variants with stock for a product
- **New `outOfStock()` method**: Get out of stock variants for a product

#### ProductController
- **Enhanced `store()` method**: Handles variant `sku` and `quantity` during product creation
- **Enhanced `update()` method**: Handles variant `sku` and `quantity` during product updates

## API Endpoints

### Main Variant Endpoints (Enhanced)
All main variant endpoints now support `sku` and `quantity` along with other fields:

```http
POST /api/admin/products/{product}/variants
PUT /api/admin/variants/{variant}
```

### Stock Management Endpoints

#### Get In-Stock Variants
```http
GET /api/admin/products/{product}/variants/in-stock
```

#### Get Out-of-Stock Variants
```http
GET /api/admin/products/{product}/variants/out-of-stock
```

## Usage Examples

### 1. Create Product with Variants (with SKU and Quantity)
```http
POST /api/admin/products
Content-Type: multipart/form-data

{
    "name": "T-Shirt",
    "variants": [
        {
            "color": "Red",
            "hex_color": "#FF0000",
            "sku": "TSHIRT-RED-M",
            "quantity": 25,
            "sort_order": 1,
            "images": [/* files */]
        },
        {
            "color": "Blue",
            "hex_color": "#0000FF",
            "sku": "TSHIRT-BLUE-M",
            "quantity": 30,
            "sort_order": 2,
            "images": [/* files */]
        }
    ]
}
```

### 2. Create Variant with SKU and Quantity
```http
POST /api/admin/products/1/variants
Content-Type: multipart/form-data

{
    "color": "Green",
    "hex_color": "#00FF00",
    "sku": "TSHIRT-GREEN-M",
    "quantity": 15,
    "sort_order": 3,
    "images": [/* files */]
}
```

### 3. Update Variant (All Fields Including SKU and Quantity)
```http
PUT /api/admin/variants/1
Content-Type: multipart/form-data

{
    "color": "Red",
    "hex_color": "#FF0000",
    "sku": "TSHIRT-RED-M-UPDATED",
    "quantity": 40,
    "sort_order": 1
}
```

**Note**: You can update any combination of fields including `sku` and `quantity` in the same request.

### 4. Get In-Stock Variants
```http
GET /api/admin/products/1/variants/in-stock
```

**Response:**
```json
{
    "success": true,
    "variants": [
        {
            "id": 1,
            "product_id": 1,
            "color": "Red",
            "hex_color": "#FF0000",
            "sku": "TSHIRT-RED-M",
            "quantity": 25,
            "sort_order": 1,
            "images": [...]
        }
    ]
}
```

### 5. Get Out-of-Stock Variants
```http
GET /api/admin/products/1/variants/out-of-stock
```

## Validation Rules

### Store/Update Variant
- `color`: required, string, max 50 characters
- `hex_color`: nullable, string, regex pattern for hex colors
- `sku`: nullable, string, max 100 characters, unique
- `quantity`: nullable, integer, minimum 0
- `sort_order`: nullable, integer, minimum 0
- `images`: required for store, optional for update


## Frontend Integration

### Display Variant Stock Status
```javascript
const getVariantStockStatus = (variant) => {
    if (variant.quantity > 0) {
        return `In Stock (${variant.quantity} available)`;
    }
    return 'Out of Stock';
};

// Usage
variants.forEach(variant => {
    console.log(`${variant.color}: ${getVariantStockStatus(variant)}`);
});
```

### Filter Variants by Stock
```javascript
const getInStockVariants = async (productId) => {
    try {
        const response = await fetch(`/api/admin/products/${productId}/variants/in-stock`, {
            headers: {
                'Authorization': `Bearer ${token}`
            }
        });
        
        const data = await response.json();
        return data.variants;
    } catch (error) {
        console.error('Error fetching in-stock variants:', error);
        return [];
    }
};
```

### Update Variant (including quantity)
```javascript
const updateVariant = async (variantId, variantData) => {
    try {
        const response = await fetch(`/api/admin/variants/${variantId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`
            },
            body: JSON.stringify({
                color: variantData.color,
                hex_color: variantData.hex_color,
                sku: variantData.sku,
                quantity: variantData.quantity,
                sort_order: variantData.sort_order
            })
        });
        
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Error updating variant:', error);
        throw error;
    }
};
```

## Stock Management Features

### 1. Individual Variant Stock
- Each variant now has its own quantity
- Independent stock management per variant
- SKU tracking for inventory management

### 2. Stock Status Methods
- `isInStock()` - Check if variant has available stock
- `isOutOfStock()` - Check if variant is out of stock
- Scopes for filtering by stock status

### 3. Inventory Tracking
- SKU-based tracking for each variant
- Quantity management per variant
- Stock status filtering and reporting

## Migration Notes

1. Run the migration: `php artisan migrate`
2. Existing variants will have `quantity = 0` and `sku = null`
3. Update existing variants with appropriate SKUs and quantities
4. Use the new endpoints to manage variant stock

## Benefits

1. **Individual Stock Management**: Each variant has its own quantity
2. **SKU Tracking**: Unique identifiers for inventory management
3. **Stock Status Filtering**: Easy filtering of in-stock/out-of-stock variants
4. **Inventory Control**: Better control over variant-specific inventory
5. **Unified API**: SKU and quantity managed through the same endpoints as other variant fields
6. **Backward Compatibility**: Existing functionality remains unchanged

## Example Use Cases

### E-commerce Product with Variants
```javascript
// Product: T-Shirt with size and color variants
const product = {
    name: "Cotton T-Shirt",
    variants: [
        {
            color: "Red",
            sku: "TSHIRT-RED-S",
            quantity: 20,
            size: "Small"
        },
        {
            color: "Red", 
            sku: "TSHIRT-RED-M",
            quantity: 15,
            size: "Medium"
        },
        {
            color: "Blue",
            sku: "TSHIRT-BLUE-S", 
            quantity: 0, // Out of stock
            size: "Small"
        }
    ]
};
```

### Stock Management Dashboard
```javascript
// Get stock overview for a product
const getStockOverview = async (productId) => {
    const [inStock, outOfStock] = await Promise.all([
        fetch(`/api/admin/products/${productId}/variants/in-stock`),
        fetch(`/api/admin/products/${productId}/variants/out-of-stock`)
    ]);
    
    return {
        inStock: (await inStock.json()).variants,
        outOfStock: (await outOfStock.json()).variants
    };
};
```
