# Zone-Based Shipping System - Frontend API Documentation

## Overview
The zone-based shipping system allows dynamic shipping pricing based on customer address zones. Admins can manage zones and their shipping prices, while customers see accurate shipping costs during checkout.

## Table of Contents
1. [Zone Management APIs](#zone-management-apis)
2. [Shipping Price APIs](#shipping-price-apis)
3. [Cart Integration](#cart-integration)
4. [Checkout Integration](#checkout-integration)
5. [Frontend Implementation Examples](#frontend-implementation-examples)
6. [Error Handling](#error-handling)

---

## Zone Management APIs

### 1. Get All Zones (Public)
**Endpoint:** `GET /api/zones`

**Description:** Retrieve all available zones with their shipping prices.

**Response:**
```json
{
  "success": true,
  "zones": [
    {
      "id": 1,
      "name": "Beirut",
      "shipping_price": "2.00",
      "addresses_count": 15
    },
    {
      "id": 2,
      "name": "Mount Lebanon",
      "shipping_price": "3.00",
      "addresses_count": 8
    }
  ]
}
```

**Frontend Usage:**
```javascript
// Fetch zones for address selection dropdown
const fetchZones = async () => {
  try {
    const response = await fetch('/api/zones');
    const data = await response.json();
    if (data.success) {
      setZones(data.zones);
    }
  } catch (error) {
    console.error('Error fetching zones:', error);
  }
};
```

---

### 2. Create Zone (Admin Only)
**Endpoint:** `POST /api/zones`

**Headers:** `Authorization: Bearer {admin_token}`

**Request Body:**
```json
{
  "name": "New Zone",
  "shipping_price": 5.50
}
```

**Response:**
```json
{
  "success": true,
  "message": "Zone created successfully",
  "zone": {
    "id": 3,
    "name": "New Zone",
    "shipping_price": "5.50"
  }
}
```

**Frontend Usage:**
```javascript
const createZone = async (zoneData) => {
  try {
    const response = await fetch('/api/zones', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${adminToken}`
      },
      body: JSON.stringify(zoneData)
    });
    const data = await response.json();
    return data;
  } catch (error) {
    console.error('Error creating zone:', error);
  }
};
```

---

### 3. Update Zone (Admin Only)
**Endpoint:** `PUT /api/zones/{zone_id}`

**Headers:** `Authorization: Bearer {admin_token}`

**Request Body:**
```json
{
  "name": "Updated Zone Name",
  "shipping_price": 6.00
}
```

**Response:**
```json
{
  "success": true,
  "message": "Zone updated successfully",
  "zone": {
    "id": 3,
    "name": "Updated Zone Name",
    "shipping_price": "6.00"
  }
}
```

---

### 4. Delete Zone (Admin Only)
**Endpoint:** `DELETE /api/zones/{zone_id}`

**Headers:** `Authorization: Bearer {admin_token}`

**Response:**
```json
{
  "success": true,
  "message": "Zone deleted successfully"
}
```

**Error Response (if zone has addresses):**
```json
{
  "success": false,
  "message": "Cannot delete zone that has addresses assigned to it"
}
```

---

## Shipping Price APIs

### 1. Get Shipping Price for Address
**Endpoint:** `POST /api/zones/shipping-price`

**Request Body:**
```json
{
  "address_id": 123
}
```

**Response:**
```json
{
  "success": true,
  "shipping_price": 3.00
}
```

**Frontend Usage:**
```javascript
const getShippingPrice = async (addressId) => {
  try {
    const response = await fetch('/api/zones/shipping-price', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ address_id: addressId })
    });
    const data = await response.json();
    return data.success ? data.shipping_price : 0;
  } catch (error) {
    console.error('Error fetching shipping price:', error);
    return 0;
  }
};
```

---

## Cart Integration

### Updated Cart API
**Endpoint:** `GET /api/cart?address_id={address_id}`

**Headers:** `Authorization: Bearer {user_token}`

**Query Parameters:**
- `address_id` (optional): If provided, calculates shipping based on zone

**Response:**
```json
{
  "success": true,
  "items": [...],
  "summary": {
    "subtotal": "25.00",
    "shipping": "3.00",
    "total": "28.00"
  }
}
```

**Frontend Usage:**
```javascript
const fetchCartWithShipping = async (addressId = null) => {
  try {
    const url = addressId ? `/api/cart?address_id=${addressId}` : '/api/cart';
    const response = await fetch(url, {
      headers: {
        'Authorization': `Bearer ${userToken}`
      }
    });
    const data = await response.json();
    if (data.success) {
      setCartItems(data.items);
      setCartSummary(data.summary);
    }
  } catch (error) {
    console.error('Error fetching cart:', error);
  }
};
```

---

## Checkout Integration

### Updated Checkout API
**Endpoint:** `POST /api/checkout`

**Headers:** `Authorization: Bearer {user_token}`

**Request Body:**
```json
{
  "address_id": 123,
  "payment_code": "cash"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Order placed successfully",
  "order": {
    "order_id": 456,
    "subtotal": "25.00",
    "shipping": "3.00",
    "total": "28.00",
    "order_status": "pending"
  }
}
```

**Note:** Shipping is automatically calculated based on the address zone.

---

## Frontend Implementation Examples

### 1. Zone Selection Component (React)
```jsx
import React, { useState, useEffect } from 'react';

const ZoneSelector = ({ onZoneSelect, selectedZoneId }) => {
  const [zones, setZones] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetchZones();
  }, []);

  const fetchZones = async () => {
    try {
      const response = await fetch('/api/zones');
      const data = await response.json();
      if (data.success) {
        setZones(data.zones);
      }
    } catch (error) {
      console.error('Error fetching zones:', error);
    } finally {
      setLoading(false);
    }
  };

  if (loading) return <div>Loading zones...</div>;

  return (
    <select 
      value={selectedZoneId} 
      onChange={(e) => onZoneSelect(e.target.value)}
      className="zone-selector"
    >
      <option value="">Select a zone</option>
      {zones.map(zone => (
        <option key={zone.id} value={zone.id}>
          {zone.name} - ${zone.shipping_price} shipping
        </option>
      ))}
    </select>
  );
};

export default ZoneSelector;
```

### 2. Address Form with Zone Selection
```jsx
import React, { useState, useEffect } from 'react';

const AddressForm = ({ onSubmit, initialData = {} }) => {
  const [formData, setFormData] = useState({
    first_name: initialData.first_name || '',
    last_name: initialData.last_name || '',
    phone_number: initialData.phone_number || '',
    zone_id: initialData.zone_id || '',
    full_address: initialData.full_address || '',
    more_details: initialData.more_details || ''
  });
  const [zones, setZones] = useState([]);

  useEffect(() => {
    fetchZones();
  }, []);

  const fetchZones = async () => {
    try {
      const response = await fetch('/api/zones');
      const data = await response.json();
      if (data.success) {
        setZones(data.zones);
      }
    } catch (error) {
      console.error('Error fetching zones:', error);
    }
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    onSubmit(formData);
  };

  return (
    <form onSubmit={handleSubmit} className="address-form">
      <div className="form-group">
        <label>First Name</label>
        <input
          type="text"
          value={formData.first_name}
          onChange={(e) => setFormData({...formData, first_name: e.target.value})}
          required
        />
      </div>

      <div className="form-group">
        <label>Last Name</label>
        <input
          type="text"
          value={formData.last_name}
          onChange={(e) => setFormData({...formData, last_name: e.target.value})}
          required
        />
      </div>

      <div className="form-group">
        <label>Phone Number</label>
        <input
          type="tel"
          value={formData.phone_number}
          onChange={(e) => setFormData({...formData, phone_number: e.target.value})}
          required
        />
      </div>

      <div className="form-group">
        <label>Zone</label>
        <select
          value={formData.zone_id}
          onChange={(e) => setFormData({...formData, zone_id: e.target.value})}
          required
        >
          <option value="">Select a zone</option>
          {zones.map(zone => (
            <option key={zone.id} value={zone.id}>
              {zone.name} - ${zone.shipping_price} shipping
            </option>
          ))}
        </select>
      </div>

      <div className="form-group">
        <label>Full Address</label>
        <textarea
          value={formData.full_address}
          onChange={(e) => setFormData({...formData, full_address: e.target.value})}
          required
        />
      </div>

      <div className="form-group">
        <label>More Details (Optional)</label>
        <textarea
          value={formData.more_details}
          onChange={(e) => setFormData({...formData, more_details: e.target.value})}
        />
      </div>

      <button type="submit" className="submit-btn">
        Save Address
      </button>
    </form>
  );
};

export default AddressForm;
```

### 3. Cart Component with Dynamic Shipping
```jsx
import React, { useState, useEffect } from 'react';

const CartComponent = () => {
  const [cartItems, setCartItems] = useState([]);
  const [cartSummary, setCartSummary] = useState({});
  const [selectedAddressId, setSelectedAddressId] = useState(null);
  const [addresses, setAddresses] = useState([]);

  useEffect(() => {
    fetchCart();
    fetchAddresses();
  }, []);

  useEffect(() => {
    if (selectedAddressId) {
      fetchCartWithShipping(selectedAddressId);
    }
  }, [selectedAddressId]);

  const fetchCart = async () => {
    try {
      const response = await fetch('/api/cart', {
        headers: {
          'Authorization': `Bearer ${userToken}`
        }
      });
      const data = await response.json();
      if (data.success) {
        setCartItems(data.items);
        setCartSummary(data.summary);
      }
    } catch (error) {
      console.error('Error fetching cart:', error);
    }
  };

  const fetchCartWithShipping = async (addressId) => {
    try {
      const response = await fetch(`/api/cart?address_id=${addressId}`, {
        headers: {
          'Authorization': `Bearer ${userToken}`
        }
      });
      const data = await response.json();
      if (data.success) {
        setCartSummary(data.summary);
      }
    } catch (error) {
      console.error('Error fetching cart with shipping:', error);
    }
  };

  const fetchAddresses = async () => {
    try {
      const response = await fetch('/api/addresses', {
        headers: {
          'Authorization': `Bearer ${userToken}`
        }
      });
      const data = await response.json();
      if (data.success) {
        setAddresses(data.addresses);
      }
    } catch (error) {
      console.error('Error fetching addresses:', error);
    }
  };

  return (
    <div className="cart-component">
      <h2>Shopping Cart</h2>
      
      {/* Address Selection */}
      <div className="address-selection">
        <label>Select Address for Shipping Calculation:</label>
        <select 
          value={selectedAddressId || ''} 
          onChange={(e) => setSelectedAddressId(e.target.value)}
        >
          <option value="">Select an address</option>
          {addresses.map(address => (
            <option key={address.id} value={address.id}>
              {address.first_name} {address.last_name} - {address.zone?.name}
            </option>
          ))}
        </select>
      </div>

      {/* Cart Items */}
      <div className="cart-items">
        {cartItems.map(item => (
          <div key={item.id} className="cart-item">
            <img src={item.product.image} alt={item.product.name} />
            <div className="item-details">
              <h3>{item.product.name}</h3>
              <p>Price: ${item.product.selling_price}</p>
              <p>Quantity: {item.quantity}</p>
            </div>
          </div>
        ))}
      </div>

      {/* Cart Summary */}
      <div className="cart-summary">
        <div className="summary-row">
          <span>Subtotal:</span>
          <span>${cartSummary.subtotal}</span>
        </div>
        <div className="summary-row">
          <span>Shipping:</span>
          <span>${cartSummary.shipping}</span>
        </div>
        <div className="summary-row total">
          <span>Total:</span>
          <span>${cartSummary.total}</span>
        </div>
      </div>

      <button 
        className="checkout-btn"
        disabled={!selectedAddressId}
      >
        Proceed to Checkout
      </button>
    </div>
  );
};

export default CartComponent;
```

### 4. Admin Zone Management Component
```jsx
import React, { useState, useEffect } from 'react';

const ZoneManagement = () => {
  const [zones, setZones] = useState([]);
  const [showForm, setShowForm] = useState(false);
  const [editingZone, setEditingZone] = useState(null);
  const [formData, setFormData] = useState({
    name: '',
    shipping_price: ''
  });

  useEffect(() => {
    fetchZones();
  }, []);

  const fetchZones = async () => {
    try {
      const response = await fetch('/api/zones', {
        headers: {
          'Authorization': `Bearer ${adminToken}`
        }
      });
      const data = await response.json();
      if (data.success) {
        setZones(data.zones);
      }
    } catch (error) {
      console.error('Error fetching zones:', error);
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    try {
      const url = editingZone ? `/api/zones/${editingZone.id}` : '/api/zones';
      const method = editingZone ? 'PUT' : 'POST';
      
      const response = await fetch(url, {
        method,
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${adminToken}`
        },
        body: JSON.stringify(formData)
      });
      
      const data = await response.json();
      if (data.success) {
        fetchZones();
        setShowForm(false);
        setEditingZone(null);
        setFormData({ name: '', shipping_price: '' });
      }
    } catch (error) {
      console.error('Error saving zone:', error);
    }
  };

  const handleEdit = (zone) => {
    setEditingZone(zone);
    setFormData({
      name: zone.name,
      shipping_price: zone.shipping_price
    });
    setShowForm(true);
  };

  const handleDelete = async (zoneId) => {
    if (window.confirm('Are you sure you want to delete this zone?')) {
      try {
        const response = await fetch(`/api/zones/${zoneId}`, {
          method: 'DELETE',
          headers: {
            'Authorization': `Bearer ${adminToken}`
          }
        });
        
        const data = await response.json();
        if (data.success) {
          fetchZones();
        } else {
          alert(data.message);
        }
      } catch (error) {
        console.error('Error deleting zone:', error);
      }
    }
  };

  return (
    <div className="zone-management">
      <h2>Zone Management</h2>
      
      <button 
        onClick={() => setShowForm(true)}
        className="add-zone-btn"
      >
        Add New Zone
      </button>

      {showForm && (
        <form onSubmit={handleSubmit} className="zone-form">
          <h3>{editingZone ? 'Edit Zone' : 'Add New Zone'}</h3>
          
          <div className="form-group">
            <label>Zone Name</label>
            <input
              type="text"
              value={formData.name}
              onChange={(e) => setFormData({...formData, name: e.target.value})}
              required
            />
          </div>

          <div className="form-group">
            <label>Shipping Price</label>
            <input
              type="number"
              step="0.01"
              min="0"
              value={formData.shipping_price}
              onChange={(e) => setFormData({...formData, shipping_price: e.target.value})}
              required
            />
          </div>

          <div className="form-actions">
            <button type="submit" className="save-btn">
              {editingZone ? 'Update' : 'Create'} Zone
            </button>
            <button 
              type="button" 
              onClick={() => {
                setShowForm(false);
                setEditingZone(null);
                setFormData({ name: '', shipping_price: '' });
              }}
              className="cancel-btn"
            >
              Cancel
            </button>
          </div>
        </form>
      )}

      <div className="zones-list">
        {zones.map(zone => (
          <div key={zone.id} className="zone-item">
            <div className="zone-info">
              <h3>{zone.name}</h3>
              <p>Shipping Price: ${zone.shipping_price}</p>
              <p>Addresses: {zone.addresses_count}</p>
            </div>
            <div className="zone-actions">
              <button 
                onClick={() => handleEdit(zone)}
                className="edit-btn"
              >
                Edit
              </button>
              <button 
                onClick={() => handleDelete(zone.id)}
                className="delete-btn"
                disabled={zone.addresses_count > 0}
              >
                Delete
              </button>
            </div>
          </div>
        ))}
      </div>
    </div>
  );
};

export default ZoneManagement;
```

---

## Error Handling

### Common Error Responses

**Validation Errors:**
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "name": ["The name field is required."],
    "shipping_price": ["The shipping price must be a number."]
  }
}
```

**Authentication Errors:**
```json
{
  "message": "Unauthenticated."
}
```

**Not Found Errors:**
```json
{
  "success": false,
  "message": "Zone not found"
}
```

**Frontend Error Handling Example:**
```javascript
const handleApiCall = async (apiCall) => {
  try {
    const response = await apiCall();
    const data = await response.json();
    
    if (!response.ok) {
      if (response.status === 401) {
        // Handle authentication error
        redirectToLogin();
      } else if (response.status === 422) {
        // Handle validation errors
        displayValidationErrors(data.errors);
      } else {
        // Handle other errors
        showErrorMessage(data.message || 'An error occurred');
      }
      return null;
    }
    
    return data;
  } catch (error) {
    console.error('API Error:', error);
    showErrorMessage('Network error. Please try again.');
    return null;
  }
};
```

---

## CSS Styling Examples

```css
/* Zone Selector */
.zone-selector {
  width: 100%;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 16px;
}

/* Address Form */
.address-form {
  max-width: 500px;
  margin: 0 auto;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  margin-bottom: 5px;
  font-weight: bold;
}

.form-group input,
.form-group select,
.form-group textarea {
  width: 100%;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 16px;
}

/* Cart Summary */
.cart-summary {
  border-top: 1px solid #ddd;
  padding-top: 20px;
  margin-top: 20px;
}

.summary-row {
  display: flex;
  justify-content: space-between;
  margin-bottom: 10px;
}

.summary-row.total {
  font-weight: bold;
  font-size: 18px;
  border-top: 1px solid #ddd;
  padding-top: 10px;
}

/* Zone Management */
.zone-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px;
  border: 1px solid #ddd;
  border-radius: 4px;
  margin-bottom: 10px;
}

.zone-info h3 {
  margin: 0 0 5px 0;
}

.zone-actions button {
  margin-left: 10px;
  padding: 5px 10px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.edit-btn {
  background-color: #007bff;
  color: white;
}

.delete-btn {
  background-color: #dc3545;
  color: white;
}

.delete-btn:disabled {
  background-color: #6c757d;
  cursor: not-allowed;
}
```

---

## Testing Checklist

### Frontend Testing
- [ ] Zone selection dropdown loads correctly
- [ ] Shipping price updates when address is selected
- [ ] Cart shows correct shipping cost
- [ ] Checkout process uses correct shipping price
- [ ] Admin can create/edit/delete zones
- [ ] Error handling works for all scenarios
- [ ] Responsive design on mobile devices

### API Testing
- [ ] All zone CRUD operations work
- [ ] Shipping price calculation is accurate
- [ ] Authentication works for admin routes
- [ ] Validation errors are properly returned
- [ ] Cart integration works with address selection

---

This documentation provides everything your frontend team needs to implement the zone-based shipping system. The examples are in React, but the API endpoints and data structures are framework-agnostic and can be adapted to any frontend technology.
