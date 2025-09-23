# Product Variants Usage Guide

This guide explains how to use the new product variants system to add multiple colors with different images for each product.

## Database Structure

```
products (1) ──→ (many) product_variants (1) ──→ (many) product_images
```

- **products**: Main product information (name, price, description, etc.)
- **product_variants**: Color variants for each product
- **product_images**: Images specific to each color variant

## API Endpoints

### 1. Create Product with Variants

**POST** `/api/products`

```json
{
  "name": "Cotton T-Shirt",
  "desc": "Comfortable cotton t-shirt",
  "category_id": 1,
  "brand_id": 1,
  "buying_price": 10.00,
  "regular_price": 25.00,
  "discount": 20,
  "quantity": 100,
  "is_trending": true,
  "variants": [
    {
      "color": "Red",
      "color_image": "red_swatch.jpg",
      "images": [
        "red_front.jpg",
        "red_back.jpg",
        "red_detail.jpg"
      ]
    },
    {
      "color": "Blue",
      "color_image": "blue_swatch.jpg",
      "images": [
        "blue_front.jpg",
        "blue_back.jpg",
        "blue_detail.jpg"
      ]
    },
    {
      "color": "Green",
      "color_image": "green_swatch.jpg",
      "images": [
        "green_front.jpg",
        "green_back.jpg"
      ]
    }
  ]
}
```

### 2. Get Product with Variants

**GET** `/api/products/{id}`

Response:
```json
{
  "success": true,
  "product": {
    "id": 1,
    "name": "Cotton T-Shirt",
    "desc": "Comfortable cotton t-shirt",
    "regular_price": 25.00,
    "selling_price": 20.00,
    "variants": [
      {
        "id": 1,
        "color": "Red",
        "color_image": "color-swatches/red_swatch.jpg",
        "color_image_url": "http://localhost/storage/color-swatches/red_swatch.jpg",
        "images": [
          {
            "id": 1,
            "path": "products/red_front.jpg",
            "url": "http://localhost/storage/products/red_front.jpg"
          },
          {
            "id": 2,
            "path": "products/red_back.jpg",
            "url": "http://localhost/storage/products/red_back.jpg"
          }
        ]
      },
      {
        "id": 2,
        "color": "Blue",
        "color_image": "color-swatches/blue_swatch.jpg",
        "color_image_url": "http://localhost/storage/color-swatches/blue_swatch.jpg",
        "images": [
          {
            "id": 3,
            "path": "products/blue_front.jpg",
            "url": "http://localhost/storage/products/blue_front.jpg"
          }
        ]
      }
    ]
  }
}
```

### 3. Add New Variant to Existing Product

**POST** `/api/products/{product_id}/variants`

```json
{
  "color": "Black",
  "color_image": "black_swatch.jpg",
  "images": [
    "black_front.jpg",
    "black_back.jpg"
  ]
}
```

### 4. Update Variant

**PUT** `/api/variants/{variant_id}`

```json
{
  "color": "Dark Red",
  "color_image": "dark_red_swatch.jpg",
  "images": [
    "dark_red_front.jpg",
    "dark_red_back.jpg"
  ]
}
```

### 5. Add Images to Existing Variant

**POST** `/api/variants/{variant_id}/images`

```json
{
  "images": [
    "additional_image1.jpg",
    "additional_image2.jpg"
  ]
}
```

### 6. Remove Image from Variant

**DELETE** `/api/variants/{variant_id}/images/{image_id}`

### 7. Delete Variant

**DELETE** `/api/variants/{variant_id}`

## Real-World Example: Lipstick with Color Numbers

Here's how to create a lipstick product with color numbers and swatch images:

### Create Lipstick Product with Color Variants

**POST** `/api/products`

```json
{
  "name": "Matte Lipstick Collection",
  "desc": "Long-lasting matte lipstick with rich pigmentation",
  "category_id": 5,
  "brand_id": 2,
  "buying_price": 8.00,
  "regular_price": 24.00,
  "discount": 0,
  "quantity": 50,
  "variants": [
    {
      "color": "050 - Classic Red",
      "color_image": "lipstick_050_swatch.jpg",
      "images": [
        "lipstick_050_packaging.jpg",
        "lipstick_050_swatch_hand.jpg",
        "lipstick_050_application.jpg"
      ]
    },
    {
      "color": "120 - Nude Pink",
      "color_image": "lipstick_120_swatch.jpg",
      "images": [
        "lipstick_120_packaging.jpg",
        "lipstick_120_swatch_hand.jpg",
        "lipstick_120_application.jpg"
      ]
    },
    {
      "color": "200 - Deep Berry",
      "color_image": "lipstick_200_swatch.jpg",
      "images": [
        "lipstick_200_packaging.jpg",
        "lipstick_200_swatch_hand.jpg",
        "lipstick_200_application.jpg"
      ]
    }
  ]
}
```

### Response Structure

```json
{
  "success": true,
  "product": {
    "id": 15,
    "name": "Matte Lipstick Collection",
    "variants": [
      {
        "id": 45,
        "color": "050 - Classic Red",
        "color_image": "color-swatches/lipstick_050_swatch.jpg",
        "color_image_url": "http://localhost/storage/color-swatches/lipstick_050_swatch.jpg",
        "images": [
          {
            "id": 89,
            "path": "products/lipstick_050_packaging.jpg",
            "url": "http://localhost/storage/products/lipstick_050_packaging.jpg"
          }
        ]
      }
    ]
  }
}
```

## Frontend Implementation Examples

### React/JavaScript Example

```javascript
// Create product with variants
const createProductWithVariants = async (productData) => {
  const formData = new FormData();
  
  // Add basic product info
  formData.append('name', productData.name);
  formData.append('desc', productData.desc);
  formData.append('category_id', productData.category_id);
  formData.append('brand_id', productData.brand_id);
  formData.append('buying_price', productData.buying_price);
  formData.append('regular_price', productData.regular_price);
  formData.append('discount', productData.discount);
  formData.append('quantity', productData.quantity);
  
  // Add variants
  productData.variants.forEach((variant, variantIndex) => {
    formData.append(`variants[${variantIndex}][color]`, variant.color);
    
    // Add color image if provided
    if (variant.color_image) {
      formData.append(`variants[${variantIndex}][color_image]`, variant.color_image);
    }
    
    variant.images.forEach((image, imageIndex) => {
      formData.append(`variants[${variantIndex}][images][${imageIndex}]`, image);
    });
  });
  
  const response = await fetch('/api/products', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`
    },
    body: formData
  });
  
  return response.json();
};

// Get product with variants
const getProduct = async (productId) => {
  const response = await fetch(`/api/products/${productId}`);
  return response.json();
};

// Add new variant
const addVariant = async (productId, variantData) => {
  const formData = new FormData();
  formData.append('color', variantData.color);
  
  // Add color image if provided
  if (variantData.color_image) {
    formData.append('color_image', variantData.color_image);
  }
  
  variantData.images.forEach((image, index) => {
    formData.append(`images[${index}]`, image);
  });
  
  const response = await fetch(`/api/products/${productId}/variants`, {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`
    },
    body: formData
  });
  
  return response.json();
};
```

### Vue.js Example

```vue
<template>
  <div class="product-variants">
    <div v-for="variant in product.variants" :key="variant.id" class="variant">
      <div class="variant-header">
        <h3>{{ variant.color }}</h3>
        <img 
          v-if="variant.color_image_url"
          :src="variant.color_image_url" 
          :alt="`${variant.color} swatch`"
          class="color-swatch"
        />
      </div>
      <div class="variant-images">
        <img 
          v-for="image in variant.images" 
          :key="image.id"
          :src="image.url" 
          :alt="`${product.name} - ${variant.color}`"
          class="variant-image"
        />
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      product: {
        variants: []
      }
    }
  },
  async mounted() {
    await this.loadProduct();
  },
  methods: {
    async loadProduct() {
      const response = await fetch(`/api/products/${this.$route.params.id}`);
      const data = await response.json();
      this.product = data.product;
    },
    
    async addVariant(variantData) {
      const formData = new FormData();
      formData.append('color', variantData.color);
      
      // Add color image if provided
      if (variantData.color_image) {
        formData.append('color_image', variantData.color_image);
      }
      
      variantData.images.forEach((image, index) => {
        formData.append(`images[${index}]`, image);
      });
      
      const response = await fetch(`/api/products/${this.product.id}/variants`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${this.$store.state.token}`
        },
        body: formData
      });
      
      if (response.ok) {
        await this.loadProduct(); // Reload to show new variant
      }
    }
  }
}
</script>
```

## Migration from Old System

If you have existing products with direct images, you'll need to migrate them to the new variant system:

### 1. Run the Migrations

```bash
php artisan migrate
```

### 2. Create Migration Script

Create a migration script to move existing product images to variants:

```php
// In a new migration file
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;

public function up()
{
    // Get all products that have images but no variants
    $products = Product::whereDoesntHave('variants')
        ->whereHas('images')
        ->get();
    
    foreach ($products as $product) {
        // Create a default variant
        $variant = $product->variants()->create([
            'color' => 'Default'
        ]);
        
        // Move images to the variant
        foreach ($product->images as $image) {
            $image->update(['variant_id' => $variant->id]);
        }
    }
}
```

## Best Practices

1. **Always include at least one variant** when creating a product
2. **Use descriptive color names** (e.g., "Navy Blue" instead of just "Blue")
3. **Include multiple images per variant** for better product presentation
4. **Use consistent image naming** (e.g., "color_front.jpg", "color_back.jpg")
5. **Optimize images** before uploading to reduce file sizes
6. **Handle image uploads properly** in your frontend with progress indicators

## Error Handling

The API will return appropriate error messages for validation failures:

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "variants": ["The variants field is required."],
    "variants.0.color": ["The variants.0.color field is required."],
    "variants.0.images": ["The variants.0.images field is required."]
  }
}
```

## Security Notes

- All variant management endpoints require admin authentication
- Image uploads are validated for type and size
- File paths are sanitized to prevent directory traversal attacks
- Images are stored in the `public` disk with proper permissions
