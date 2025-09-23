# Lipstick Color Variants Example

This example shows how to create a lipstick product with color numbers and swatch images, perfect for cosmetics where each color has a specific number (like "050", "120", "200").

## Example: Creating a Lipstick Product

### 1. Create Product with Color Variants

**POST** `/api/products`

```javascript
const formData = new FormData();

// Basic product info
formData.append('name', 'Matte Lipstick Collection');
formData.append('desc', 'Long-lasting matte lipstick with rich pigmentation');
formData.append('category_id', '5'); // Cosmetics category
formData.append('brand_id', '2'); // Your brand ID
formData.append('buying_price', '8.00');
formData.append('regular_price', '24.00');
formData.append('discount', '0');
formData.append('quantity', '50');

// Variant 1: Color 050 - Classic Red
formData.append('variants[0][color]', '050 - Classic Red');
formData.append('variants[0][color_image]', redSwatchFile); // File object
formData.append('variants[0][images][0]', redPackagingFile);
formData.append('variants[0][images][1]', redSwatchHandFile);
formData.append('variants[0][images][2]', redApplicationFile);

// Variant 2: Color 120 - Nude Pink
formData.append('variants[1][color]', '120 - Nude Pink');
formData.append('variants[1][color_image]', nudeSwatchFile); // File object
formData.append('variants[1][images][0]', nudePackagingFile);
formData.append('variants[1][images][1]', nudeSwatchHandFile);
formData.append('variants[1][images][2]', nudeApplicationFile);

// Variant 3: Color 200 - Deep Berry
formData.append('variants[2][color]', '200 - Deep Berry');
formData.append('variants[2][color_image]', berrySwatchFile); // File object
formData.append('variants[2][images][0]', berryPackagingFile);
formData.append('variants[2][images][1]', berrySwatchHandFile);
formData.append('variants[2][images][2]', berryApplicationFile);

// Send request
const response = await fetch('/api/products', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${adminToken}`
  },
  body: formData
});
```

### 2. Response Structure

```json
{
  "success": true,
  "message": "Product created successfully",
  "product": {
    "id": 15,
    "name": "Matte Lipstick Collection",
    "desc": "Long-lasting matte lipstick with rich pigmentation",
    "regular_price": 24.00,
    "selling_price": 24.00,
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
          },
          {
            "id": 90,
            "path": "products/lipstick_050_swatch_hand.jpg",
            "url": "http://localhost/storage/products/lipstick_050_swatch_hand.jpg"
          },
          {
            "id": 91,
            "path": "products/lipstick_050_application.jpg",
            "url": "http://localhost/storage/products/lipstick_050_application.jpg"
          }
        ]
      },
      {
        "id": 46,
        "color": "120 - Nude Pink",
        "color_image": "color-swatches/lipstick_120_swatch.jpg",
        "color_image_url": "http://localhost/storage/color-swatches/lipstick_120_swatch.jpg",
        "images": [
          {
            "id": 92,
            "path": "products/lipstick_120_packaging.jpg",
            "url": "http://localhost/storage/products/lipstick_120_packaging.jpg"
          },
          {
            "id": 93,
            "path": "products/lipstick_120_swatch_hand.jpg",
            "url": "http://localhost/storage/products/lipstick_120_swatch_hand.jpg"
          },
          {
            "id": 94,
            "path": "products/lipstick_120_application.jpg",
            "url": "http://localhost/storage/products/lipstick_120_application.jpg"
          }
        ]
      },
      {
        "id": 47,
        "color": "200 - Deep Berry",
        "color_image": "color-swatches/lipstick_200_swatch.jpg",
        "color_image_url": "http://localhost/storage/color-swatches/lipstick_200_swatch.jpg",
        "images": [
          {
            "id": 95,
            "path": "products/lipstick_200_packaging.jpg",
            "url": "http://localhost/storage/products/lipstick_200_packaging.jpg"
          },
          {
            "id": 96,
            "path": "products/lipstick_200_swatch_hand.jpg",
            "url": "http://localhost/storage/products/lipstick_200_swatch_hand.jpg"
          },
          {
            "id": 97,
            "path": "products/lipstick_200_application.jpg",
            "url": "http://localhost/storage/products/lipstick_200_application.jpg"
          }
        ]
      }
    ]
  }
}
```

## Frontend Display Example

### React Component for Color Selection

```jsx
import React, { useState } from 'react';

const LipstickColorSelector = ({ product }) => {
  const [selectedVariant, setSelectedVariant] = useState(product.variants[0]);

  return (
    <div className="lipstick-product">
      <h1>{product.name}</h1>
      
      {/* Color Selection */}
      <div className="color-selection">
        <h3>Choose Your Color:</h3>
        <div className="color-options">
          {product.variants.map((variant) => (
            <div 
              key={variant.id}
              className={`color-option ${selectedVariant.id === variant.id ? 'selected' : ''}`}
              onClick={() => setSelectedVariant(variant)}
            >
              {/* Color Swatch Image */}
              <img 
                src={variant.color_image_url} 
                alt={`${variant.color} swatch`}
                className="color-swatch"
              />
              
              {/* Color Name/Number */}
              <span className="color-name">{variant.color}</span>
            </div>
          ))}
        </div>
      </div>

      {/* Selected Variant Images */}
      <div className="product-images">
        <h3>Product Images for {selectedVariant.color}:</h3>
        <div className="image-gallery">
          {selectedVariant.images.map((image) => (
            <img 
              key={image.id}
              src={image.url} 
              alt={`${product.name} - ${selectedVariant.color}`}
              className="product-image"
            />
          ))}
        </div>
      </div>

      {/* Product Details */}
      <div className="product-details">
        <div className="price">
          <span className="current-price">${product.selling_price}</span>
          {product.discount > 0 && (
            <span className="original-price">${product.regular_price}</span>
          )}
        </div>
        
        <button className="add-to-cart">
          Add to Cart - {selectedVariant.color}
        </button>
      </div>
    </div>
  );
};

export default LipstickColorSelector;
```

### CSS for Color Selection

```css
.color-selection {
  margin: 20px 0;
}

.color-options {
  display: flex;
  gap: 15px;
  flex-wrap: wrap;
}

.color-option {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 10px;
  border: 2px solid #ddd;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s ease;
  min-width: 80px;
}

.color-option:hover {
  border-color: #007bff;
  transform: translateY(-2px);
}

.color-option.selected {
  border-color: #007bff;
  background-color: #f8f9fa;
}

.color-swatch {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  object-fit: cover;
  margin-bottom: 8px;
  border: 2px solid #fff;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.color-name {
  font-size: 12px;
  text-align: center;
  font-weight: 500;
}

.image-gallery {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 15px;
  margin-top: 15px;
}

.product-image {
  width: 100%;
  height: 200px;
  object-fit: cover;
  border-radius: 8px;
}
```

## Key Features

1. **Color Numbers**: Each variant has a color name that includes the number (e.g., "050 - Classic Red")
2. **Color Swatches**: Each variant has a `color_image` that shows the actual color
3. **Multiple Product Images**: Each variant can have multiple images (packaging, swatch on hand, application, etc.)
4. **Easy Selection**: Users can click on color swatches to see different product images
5. **Organized Storage**: Color swatches are stored in `color-swatches/` folder, product images in `products/` folder

## File Structure

```
storage/app/public/
├── color-swatches/
│   ├── lipstick_050_swatch.jpg
│   ├── lipstick_120_swatch.jpg
│   └── lipstick_200_swatch.jpg
└── products/
    ├── lipstick_050_packaging.jpg
    ├── lipstick_050_swatch_hand.jpg
    ├── lipstick_050_application.jpg
    ├── lipstick_120_packaging.jpg
    ├── lipstick_120_swatch_hand.jpg
    ├── lipstick_120_application.jpg
    ├── lipstick_200_packaging.jpg
    ├── lipstick_200_swatch_hand.jpg
    └── lipstick_200_application.jpg
```

This system allows you to display color numbers with their corresponding swatch images, making it easy for customers to see exactly what each color looks like before purchasing!
