# Frontend Integration Guide - Product Variants with Hex Colors

This guide provides everything you need to integrate the product variants system with hex colors into your frontend application.

## API Endpoints

### Base URL
```
http://localhost:8000/api
```

### Authentication
All admin endpoints require Bearer token authentication:
```javascript
const headers = {
  'Authorization': `Bearer ${adminToken}`,
  'Content-Type': 'application/json'
};
```

## Product Variants API

### 1. Get Product with Variants
**GET** `/products/{id}`

```javascript
const getProduct = async (productId) => {
  const response = await fetch(`/api/products/${productId}`);
  const data = await response.json();
  return data.product;
};
```

**Response Structure:**
```json
{
  "success": true,
  "product": {
    "id": 15,
    "name": "ESSENCE Baby Got Blush",
    "desc": "Long-lasting blush with natural finish",
    "regular_price": 12.99,
    "selling_price": 12.99,
    "variants": [
      {
        "id": 45,
        "color": "50 - Cherry Baby",
        "hex_color": "#FF69B4",
        "css_color": "#FF69B4",
        "rgb_color": {
          "r": 255,
          "g": 105,
          "b": 180
        },
        "images": [
          {
            "id": 89,
            "path": "products/blush_50_packaging.jpg",
            "url": "http://localhost/storage/products/blush_50_packaging.jpg"
          }
        ]
      }
    ]
  }
}
```

### 2. Create Product with Variants (Admin)
**POST** `/products`

```javascript
const createProduct = async (productData) => {
  const formData = new FormData();
  
  // Basic product info
  formData.append('name', productData.name);
  formData.append('desc', productData.desc);
  formData.append('category_id', productData.category_id);
  formData.append('brand_id', productData.brand_id);
  formData.append('regular_price', productData.regular_price);
  formData.append('quantity', productData.quantity);
  
  // Add variants
  productData.variants.forEach((variant, index) => {
    formData.append(`variants[${index}][color]`, variant.color);
    formData.append(`variants[${index}][hex_color]`, variant.hex_color);
    
    variant.images.forEach((image, imageIndex) => {
      formData.append(`variants[${index}][images][${imageIndex}]`, image);
    });
  });
  
  const response = await fetch('/api/products', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${adminToken}`
    },
    body: formData
  });
  
  return response.json();
};
```

### 3. Add New Variant (Admin)
**POST** `/products/{product_id}/variants`

```javascript
const addVariant = async (productId, variantData) => {
  const formData = new FormData();
  formData.append('color', variantData.color);
  formData.append('hex_color', variantData.hex_color);
  
  variantData.images.forEach((image, index) => {
    formData.append(`images[${index}]`, image);
  });
  
  const response = await fetch(`/api/products/${productId}/variants`, {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${adminToken}`
    },
    body: formData
  });
  
  return response.json();
};
```

### 4. Update Variant (Admin)
**PUT** `/variants/{variant_id}`

```javascript
const updateVariant = async (variantId, variantData) => {
  const formData = new FormData();
  formData.append('color', variantData.color);
  formData.append('hex_color', variantData.hex_color);
  
  if (variantData.images) {
    variantData.images.forEach((image, index) => {
      formData.append(`images[${index}]`, image);
    });
  }
  
  const response = await fetch(`/api/variants/${variantId}`, {
    method: 'PUT',
    headers: {
      'Authorization': `Bearer ${adminToken}`
    },
    body: formData
  });
  
  return response.json();
};
```

### 5. Delete Variant (Admin)
**DELETE** `/variants/{variant_id}`

```javascript
const deleteVariant = async (variantId) => {
  const response = await fetch(`/api/variants/${variantId}`, {
    method: 'DELETE',
    headers: {
      'Authorization': `Bearer ${adminToken}`
    }
  });
  
  return response.json();
};
```

## React Components

### 1. Product Color Selector Component

```jsx
import React, { useState, useEffect } from 'react';

const ProductColorSelector = ({ productId }) => {
  const [product, setProduct] = useState(null);
  const [selectedVariant, setSelectedVariant] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchProduct = async () => {
      try {
        const response = await fetch(`/api/products/${productId}`);
        const data = await response.json();
        setProduct(data.product);
        setSelectedVariant(data.product.variants[0]);
      } catch (error) {
        console.error('Error fetching product:', error);
      } finally {
        setLoading(false);
      }
    };

    fetchProduct();
  }, [productId]);

  if (loading) return <div>Loading...</div>;
  if (!product) return <div>Product not found</div>;

  return (
    <div className="product-color-selector">
      {/* Product Images */}
      <div className="product-images">
        {selectedVariant?.images.map((image) => (
          <img 
            key={image.id}
            src={image.url} 
            alt={`${product.name} - ${selectedVariant.color}`}
            className="product-image"
          />
        ))}
      </div>

      {/* Product Details */}
      <div className="product-details">
        <h1>{product.name}</h1>
        
        {/* Color Selection */}
        <div className="color-selection">
          <h3>Shade: {selectedVariant?.color}</h3>
          
          <div className="color-swatches">
            {product.variants.map((variant) => (
              <div 
                key={variant.id}
                className={`color-swatch ${selectedVariant?.id === variant.id ? 'selected' : ''}`}
                onClick={() => setSelectedVariant(variant)}
                style={{ backgroundColor: variant.hex_color || '#CCCCCC' }}
                title={variant.color}
              />
            ))}
          </div>
        </div>

        {/* Price and Add to Cart */}
        <div className="price-section">
          <div className="price">${product.selling_price}</div>
          <button className="add-to-cart-btn">
            Add to Cart - {selectedVariant?.color}
          </button>
        </div>
      </div>
    </div>
  );
};

export default ProductColorSelector;
```

### 2. Admin Product Form Component

```jsx
import React, { useState } from 'react';

const AdminProductForm = ({ onSubmit }) => {
  const [productData, setProductData] = useState({
    name: '',
    desc: '',
    category_id: '',
    brand_id: '',
    regular_price: '',
    quantity: '',
    variants: [
      {
        color: '',
        hex_color: '',
        images: []
      }
    ]
  });

  const handleVariantChange = (index, field, value) => {
    const newVariants = [...productData.variants];
    newVariants[index][field] = value;
    setProductData({ ...productData, variants: newVariants });
  };

  const addVariant = () => {
    setProductData({
      ...productData,
      variants: [
        ...productData.variants,
        { color: '', hex_color: '', images: [] }
      ]
    });
  };

  const removeVariant = (index) => {
    const newVariants = productData.variants.filter((_, i) => i !== index);
    setProductData({ ...productData, variants: newVariants });
  };

  const handleImageChange = (variantIndex, files) => {
    const newVariants = [...productData.variants];
    newVariants[variantIndex].images = Array.from(files);
    setProductData({ ...productData, variants: newVariants });
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    try {
      const result = await onSubmit(productData);
      console.log('Product created:', result);
    } catch (error) {
      console.error('Error creating product:', error);
    }
  };

  return (
    <form onSubmit={handleSubmit} className="admin-product-form">
      {/* Basic Product Info */}
      <div className="form-section">
        <h2>Product Information</h2>
        
        <div className="form-group">
          <label>Product Name</label>
          <input
            type="text"
            value={productData.name}
            onChange={(e) => setProductData({ ...productData, name: e.target.value })}
            required
          />
        </div>

        <div className="form-group">
          <label>Description</label>
          <textarea
            value={productData.desc}
            onChange={(e) => setProductData({ ...productData, desc: e.target.value })}
          />
        </div>

        <div className="form-row">
          <div className="form-group">
            <label>Category ID</label>
            <input
              type="number"
              value={productData.category_id}
              onChange={(e) => setProductData({ ...productData, category_id: e.target.value })}
              required
            />
          </div>

          <div className="form-group">
            <label>Brand ID</label>
            <input
              type="number"
              value={productData.brand_id}
              onChange={(e) => setProductData({ ...productData, brand_id: e.target.value })}
              required
            />
          </div>
        </div>

        <div className="form-row">
          <div className="form-group">
            <label>Regular Price</label>
            <input
              type="number"
              step="0.01"
              value={productData.regular_price}
              onChange={(e) => setProductData({ ...productData, regular_price: e.target.value })}
              required
            />
          </div>

          <div className="form-group">
            <label>Quantity</label>
            <input
              type="number"
              value={productData.quantity}
              onChange={(e) => setProductData({ ...productData, quantity: e.target.value })}
              required
            />
          </div>
        </div>
      </div>

      {/* Variants Section */}
      <div className="form-section">
        <h2>Color Variants</h2>
        
        {productData.variants.map((variant, index) => (
          <div key={index} className="variant-form">
            <div className="variant-header">
              <h3>Variant {index + 1}</h3>
              {productData.variants.length > 1 && (
                <button type="button" onClick={() => removeVariant(index)}>
                  Remove Variant
                </button>
              )}
            </div>

            <div className="form-row">
              <div className="form-group">
                <label>Color Name</label>
                <input
                  type="text"
                  value={variant.color}
                  onChange={(e) => handleVariantChange(index, 'color', e.target.value)}
                  placeholder="50 - Cherry Baby"
                  required
                />
              </div>

              <div className="form-group">
                <label>Hex Color Code</label>
                <div className="color-input-group">
                  <input
                    type="text"
                    value={variant.hex_color}
                    onChange={(e) => handleVariantChange(index, 'hex_color', e.target.value)}
                    placeholder="#FF69B4"
                    pattern="^#[0-9A-Fa-f]{6}$"
                  />
                  <input
                    type="color"
                    value={variant.hex_color || '#CCCCCC'}
                    onChange={(e) => handleVariantChange(index, 'hex_color', e.target.value)}
                    className="color-picker"
                  />
                </div>
              </div>
            </div>

            <div className="form-group">
              <label>Product Images</label>
              <input
                type="file"
                multiple
                accept="image/*"
                onChange={(e) => handleImageChange(index, e.target.files)}
                required
              />
              <small>Upload multiple images for this color variant</small>
            </div>
          </div>
        ))}

        <button type="button" onClick={addVariant} className="add-variant-btn">
          Add Another Variant
        </button>
      </div>

      <button type="submit" className="submit-btn">
        Create Product
      </button>
    </form>
  );
};

export default AdminProductForm;
```

## CSS Styles

### 1. Product Color Selector Styles

```css
.product-color-selector {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 40px;
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
}

.product-images {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.product-image {
  width: 100%;
  height: 400px;
  object-fit: cover;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.product-details {
  padding: 20px 0;
}

.product-details h1 {
  font-size: 2.5rem;
  margin-bottom: 20px;
  color: #333;
}

.color-selection {
  margin: 30px 0;
}

.color-selection h3 {
  font-size: 1.2rem;
  margin-bottom: 15px;
  color: #666;
}

.color-swatches {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
}

.color-swatch {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  border: 3px solid transparent;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 2px 8px rgba(0,0,0,0.15);
  position: relative;
}

.color-swatch:hover {
  transform: scale(1.1);
  border-color: #333;
}

.color-swatch.selected {
  border-color: #007bff;
  border-width: 4px;
  transform: scale(1.15);
}

.price-section {
  margin-top: 40px;
  padding-top: 20px;
  border-top: 1px solid #eee;
}

.price {
  font-size: 2rem;
  font-weight: bold;
  color: #333;
  margin-bottom: 20px;
}

.add-to-cart-btn {
  background: #007bff;
  color: white;
  border: none;
  padding: 15px 30px;
  font-size: 1.1rem;
  border-radius: 8px;
  cursor: pointer;
  transition: background 0.3s ease;
  width: 100%;
}

.add-to-cart-btn:hover {
  background: #0056b3;
}

/* Mobile Responsive */
@media (max-width: 768px) {
  .product-color-selector {
    grid-template-columns: 1fr;
    gap: 20px;
  }
  
  .color-swatches {
    justify-content: center;
  }
  
  .color-swatch {
    width: 45px;
    height: 45px;
  }
}
```

### 2. Admin Form Styles

```css
.admin-product-form {
  max-width: 800px;
  margin: 0 auto;
  padding: 20px;
}

.form-section {
  margin-bottom: 40px;
  padding: 20px;
  border: 1px solid #ddd;
  border-radius: 8px;
}

.form-section h2 {
  margin-bottom: 20px;
  color: #333;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  margin-bottom: 5px;
  font-weight: 500;
  color: #555;
}

.form-group input,
.form-group textarea {
  width: 100%;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 14px;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}

.color-input-group {
  display: flex;
  gap: 10px;
}

.color-input-group input[type="text"] {
  flex: 1;
}

.color-picker {
  width: 50px;
  height: 40px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.variant-form {
  margin-bottom: 30px;
  padding: 20px;
  border: 1px solid #eee;
  border-radius: 8px;
  background: #f9f9f9;
}

.variant-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.variant-header h3 {
  margin: 0;
  color: #333;
}

.add-variant-btn {
  background: #28a745;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 4px;
  cursor: pointer;
  margin-bottom: 20px;
}

.submit-btn {
  background: #007bff;
  color: white;
  border: none;
  padding: 15px 30px;
  font-size: 1.1rem;
  border-radius: 8px;
  cursor: pointer;
  width: 100%;
}

.submit-btn:hover {
  background: #0056b3;
}
```

## Common Hex Colors for Cosmetics

```javascript
const commonCosmeticColors = {
  // Reds
  'Classic Red': '#DC143C',
  'Cherry Red': '#DE3163',
  'Burgundy': '#800020',
  
  // Pinks
  'Hot Pink': '#FF69B4',
  'Rose Pink': '#F4A6A6',
  'Nude Pink': '#F8BBD9',
  'Coral Pink': '#FF7F7F',
  
  // Nudes
  'Nude': '#F5DEB3',
  'Beige': '#F5F5DC',
  'Peach': '#FFB366',
  'Caramel': '#D2691E',
  
  // Berries
  'Berry': '#8B008B',
  'Plum': '#DDA0DD',
  'Wine': '#722F37',
  
  // Browns
  'Brown': '#8B4513',
  'Chocolate': '#7B3F00',
  'Espresso': '#3C2415'
};
```

## Error Handling

```javascript
const handleApiError = (error) => {
  if (error.response) {
    // Server responded with error status
    console.error('API Error:', error.response.data);
    return error.response.data.message || 'An error occurred';
  } else if (error.request) {
    // Request was made but no response received
    console.error('Network Error:', error.request);
    return 'Network error. Please check your connection.';
  } else {
    // Something else happened
    console.error('Error:', error.message);
    return 'An unexpected error occurred';
  }
};
```

## Usage Examples

### 1. Basic Product Display

```jsx
import ProductColorSelector from './components/ProductColorSelector';

function ProductPage() {
  return (
    <div>
      <ProductColorSelector productId={15} />
    </div>
  );
}
```

### 2. Admin Product Creation

```jsx
import AdminProductForm from './components/AdminProductForm';
import { createProduct } from './api/products';

function AdminProductPage() {
  const handleSubmit = async (productData) => {
    return await createProduct(productData);
  };

  return (
    <div>
      <h1>Create New Product</h1>
      <AdminProductForm onSubmit={handleSubmit} />
    </div>
  );
}
```

This documentation provides everything you need to integrate the hex color variant system into your frontend application!
