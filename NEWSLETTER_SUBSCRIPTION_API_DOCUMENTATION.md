# Newsletter Subscription API Documentation

## Base URL
```
http://127.0.0.1:8000/api
```

---

## Public Endpoints (No Authentication Required)

### 1. Subscribe to Newsletter
```http
POST /newsletter/subscribe
```

**Body Parameters:**
- `email` (required): Email address (valid email format, max 255 characters)
- `name` (optional): Subscriber name (string, max 255 characters)

**Example Request:**
```bash
POST /api/newsletter/subscribe
Content-Type: application/json

{
  "email": "user@example.com",
  "name": "John Doe"
}
```

**Response (Success - New Subscription):**
```json
{
  "success": true,
  "message": "Thank you for subscribing to our newsletter!",
  "data": {
    "id": 1,
    "email": "user@example.com",
    "name": "John Doe",
    "subscribed_at": "2025-09-23T11:47:35.000000Z",
    "unsubscribed_at": null,
    "created_at": "2025-09-23T11:47:35.000000Z",
    "updated_at": "2025-09-23T11:47:35.000000Z"
  }
}
```

**Response (Already Subscribed):**
```json
{
  "success": false,
  "message": "This email is already subscribed to our newsletter."
}
```

**Response (Resubscription):**
```json
{
  "success": true,
  "message": "Welcome back! You have been resubscribed to our newsletter.",
  "data": {
    "id": 1,
    "email": "user@example.com",
    "name": "John Doe",
    "subscribed_at": "2025-09-23T10:00:00.000000Z",
    "unsubscribed_at": null,
    "created_at": "2025-09-23T10:00:00.000000Z",
    "updated_at": "2025-09-23T11:47:35.000000Z"
  }
}
```

### 2. Unsubscribe from Newsletter
```http
POST /newsletter/unsubscribe
```

**Body Parameters:**
- `email` (required): Email address to unsubscribe

**Example Request:**
```bash
POST /api/newsletter/unsubscribe
Content-Type: application/json

{
  "email": "user@example.com"
}
```

**Response (Success):**
```json
{
  "success": true,
  "message": "You have been successfully unsubscribed from our newsletter."
}
```

**Response (Email Not Found):**
```json
{
  "success": false,
  "message": "Email not found in our subscription list."
}
```

**Response (Already Unsubscribed):**
```json
{
  "success": false,
  "message": "This email is already unsubscribed."
}
```

---

## Admin Endpoints (Authentication Required)

### 3. Get All Newsletter Subscriptions
```http
GET /newsletter-subscriptions
```

**Headers:**
```
Authorization: Bearer {token}
```

**Query Parameters:**
- `status` (optional): Filter by status (`active`, `inactive`, `all`) - default: `all`
  - `active`: Subscriptions that are not unsubscribed (unsubscribed_at is null)
  - `inactive`: Subscriptions that are unsubscribed (unsubscribed_at is not null)
  - `all`: All subscriptions
- `search` (optional): Search by email or name

**Example Request:**
```bash
GET /api/newsletter-subscriptions?status=active&search=john
Authorization: Bearer YOUR_TOKEN
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "email": "john.doe@example.com",
      "name": "John Doe",
      "subscribed_at": "2025-09-23T10:00:00.000000Z",
      "unsubscribed_at": null,
      "created_at": "2025-09-23T10:00:00.000000Z",
      "updated_at": "2025-09-23T10:00:00.000000Z"
    },
    {
      "id": 2,
      "email": "jane.smith@example.com",
      "name": "Jane Smith",
      "subscribed_at": "2025-09-23T09:00:00.000000Z",
      "unsubscribed_at": null,
      "created_at": "2025-09-23T09:00:00.000000Z",
      "updated_at": "2025-09-23T09:00:00.000000Z"
    }
  ]
}
```

### 4. Get Single Newsletter Subscription
```http
GET /newsletter-subscriptions/{id}
```

**Headers:**
```
Authorization: Bearer {token}
```

**Parameters:**
- `id` (required): Subscription ID

**Example Request:**
```bash
GET /api/newsletter-subscriptions/1
Authorization: Bearer YOUR_TOKEN
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "email": "john.doe@example.com",
    "name": "John Doe",
    "subscribed_at": "2025-09-23T10:00:00.000000Z",
    "unsubscribed_at": null,
    "created_at": "2025-09-23T10:00:00.000000Z",
    "updated_at": "2025-09-23T10:00:00.000000Z"
  }
}
```

### 5. Update Newsletter Subscription
```http
PUT /newsletter-subscriptions/{id}
```

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Parameters:**
- `id` (required): Subscription ID

**Body Parameters:**
- `email` (optional): Email address (valid email format, max 255 characters, must be unique)
- `name` (optional): Subscriber name (string, max 255 characters)

**Example Request:**
```bash
PUT /api/newsletter-subscriptions/1
Authorization: Bearer YOUR_TOKEN
Content-Type: application/json

{
  "name": "John Smith"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Subscription updated successfully",
  "data": {
    "id": 1,
    "email": "john.doe@example.com",
    "name": "John Smith",
    "subscribed_at": "2025-09-23T10:00:00.000000Z",
    "unsubscribed_at": null,
    "created_at": "2025-09-23T10:00:00.000000Z",
    "updated_at": "2025-09-23T11:50:00.000000Z"
  }
}
```

### 6. Delete Newsletter Subscription
```http
DELETE /newsletter-subscriptions/{id}
```

**Headers:**
```
Authorization: Bearer {token}
```

**Parameters:**
- `id` (required): Subscription ID

**Example Request:**
```bash
DELETE /api/newsletter-subscriptions/1
Authorization: Bearer YOUR_TOKEN
```

**Response:**
```json
{
  "success": true,
  "message": "Subscription deleted successfully"
}
```

### 7. Get Newsletter Subscription Statistics
```http
GET /newsletter-subscriptions/stats/overview
```

**Headers:**
```
Authorization: Bearer {token}
```

**Example Request:**
```bash
GET /api/newsletter-subscriptions/stats/overview
Authorization: Bearer YOUR_TOKEN
```

**Response:**
```json
{
  "success": true,
  "data": {
    "total_subscriptions": 150,
    "active_subscriptions": 120,
    "inactive_subscriptions": 30,
    "recent_subscriptions": 25,
    "monthly_stats": [
      {
        "month": "2024-09",
        "count": 15
      },
      {
        "month": "2024-10",
        "count": 22
      },
      {
        "month": "2024-11",
        "count": 18
      }
    ]
  }
}
```

---

## Error Responses

### Validation Error (422)
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email field is required."],
    "email": ["The email must be a valid email address."]
  }
}
```

### Not Found Error (404)
```json
{
  "message": "No query results for model [App\\Models\\NewsletterSubscription] 999"
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

### Conflict Error (409)
```json
{
  "success": false,
  "message": "This email is already subscribed to our newsletter."
}
```

---

## Frontend Integration Examples

### JavaScript/Fetch Examples

```javascript
// Subscribe to newsletter
const subscribeToNewsletter = async (email, name = null) => {
  try {
    const response = await fetch('/api/newsletter/subscribe', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({ email, name })
    });
    
    const data = await response.json();
    
    if (data.success) {
      alert(data.message);
    } else {
      alert(data.message);
    }
    
    return data;
  } catch (error) {
    console.error('Subscription error:', error);
    alert('Something went wrong. Please try again.');
  }
};

// Unsubscribe from newsletter
const unsubscribeFromNewsletter = async (email) => {
  try {
    const response = await fetch('/api/newsletter/unsubscribe', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({ email })
    });
    
    const data = await response.json();
    alert(data.message);
    return data;
  } catch (error) {
    console.error('Unsubscription error:', error);
    alert('Something went wrong. Please try again.');
  }
};

// Get subscription statistics (admin)
const getNewsletterStats = async (token) => {
  try {
    const response = await fetch('/api/newsletter-subscriptions/stats/overview', {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    });
    
    const data = await response.json();
    return data.data;
  } catch (error) {
    console.error('Stats error:', error);
  }
};
```

### React Component Example
```jsx
import { useState } from 'react';

const NewsletterSubscription = () => {
  const [email, setEmail] = useState('');
  const [name, setName] = useState('');
  const [loading, setLoading] = useState(false);
  const [message, setMessage] = useState('');

  const handleSubscribe = async (e) => {
    e.preventDefault();
    setLoading(true);
    setMessage('');

    try {
      const response = await fetch('/api/newsletter/subscribe', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({ email, name })
      });

      const data = await response.json();
      setMessage(data.message);
      
      if (data.success) {
        setEmail('');
        setName('');
      }
    } catch (error) {
      setMessage('Something went wrong. Please try again.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="newsletter-section">
      <h2>Newsletter</h2>
      <p>Proudly crafting custom cosmetics</p>
      <p>Since 2025 | Beauty tailored to you. Place your orders!</p>
      
      <form onSubmit={handleSubscribe}>
        <input
          type="email"
          placeholder="Enter your email"
          value={email}
          onChange={(e) => setEmail(e.target.value)}
          required
        />
        <input
          type="text"
          placeholder="Your name (optional)"
          value={name}
          onChange={(e) => setName(e.target.value)}
        />
        <button type="submit" disabled={loading}>
          {loading ? 'Subscribing...' : 'Subscribe'}
        </button>
      </form>
      
      {message && (
        <div className={`message ${message.includes('Thank you') ? 'success' : 'error'}`}>
          {message}
        </div>
      )}
    </div>
  );
};

export default NewsletterSubscription;
```

### Vue.js Component Example
```vue
<template>
  <div class="newsletter-section">
    <h2>Newsletter</h2>
    <p>Proudly crafting custom cosmetics</p>
    <p>Since 2025 | Beauty tailored to you. Place your orders!</p>
    
    <form @submit.prevent="handleSubscribe">
      <input
        v-model="email"
        type="email"
        placeholder="Enter your email"
        required
      />
      <input
        v-model="name"
        type="text"
        placeholder="Your name (optional)"
      />
      <button type="submit" :disabled="loading">
        {{ loading ? 'Subscribing...' : 'Subscribe' }}
      </button>
    </form>
    
    <div v-if="message" :class="['message', message.includes('Thank you') ? 'success' : 'error']">
      {{ message }}
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      email: '',
      name: '',
      loading: false,
      message: ''
    }
  },
  methods: {
    async handleSubscribe() {
      this.loading = true;
      this.message = '';

      try {
        const response = await fetch('/api/newsletter/subscribe', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          },
          body: JSON.stringify({ 
            email: this.email, 
            name: this.name 
          })
        });

        const data = await response.json();
        this.message = data.message;
        
        if (data.success) {
          this.email = '';
          this.name = '';
        }
      } catch (error) {
        this.message = 'Something went wrong. Please try again.';
      } finally {
        this.loading = false;
      }
    }
  }
}
</script>
```

### Angular Component Example
```typescript
import { Component } from '@angular/core';
import { HttpClient } from '@angular/common/http';

interface SubscriptionResponse {
  success: boolean;
  message: string;
  data?: any;
}

@Component({
  selector: 'app-newsletter',
  template: `
    <div class="newsletter-section">
      <h2>Newsletter</h2>
      <p>Proudly crafting custom cosmetics</p>
      <p>Since 2025 | Beauty tailored to you. Place your orders!</p>
      
      <form (ngSubmit)="handleSubscribe()" #newsletterForm="ngForm">
        <input
          [(ngModel)]="email"
          name="email"
          type="email"
          placeholder="Enter your email"
          required
          #emailInput="ngModel"
        />
        <input
          [(ngModel)]="name"
          name="name"
          type="text"
          placeholder="Your name (optional)"
        />
        <button type="submit" [disabled]="loading || !emailInput.valid">
          {{ loading ? 'Subscribing...' : 'Subscribe' }}
        </button>
      </form>
      
      <div *ngIf="message" [class]="'message ' + (message.includes('Thank you') ? 'success' : 'error')">
        {{ message }}
      </div>
    </div>
  `
})
export class NewsletterComponent {
  email: string = '';
  name: string = '';
  loading: boolean = false;
  message: string = '';

  constructor(private http: HttpClient) {}

  handleSubscribe() {
    this.loading = true;
    this.message = '';

    this.http.post<SubscriptionResponse>('/api/newsletter/subscribe', {
      email: this.email,
      name: this.name
    }).subscribe({
      next: (data) => {
        this.message = data.message;
        if (data.success) {
          this.email = '';
          this.name = '';
        }
      },
      error: (error) => {
        this.message = 'Something went wrong. Please try again.';
      },
      complete: () => {
        this.loading = false;
      }
    });
  }
}
```

---

## cURL Examples

### Subscribe to Newsletter
```bash
curl -X POST "http://127.0.0.1:8000/api/newsletter/subscribe" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "user@example.com",
    "name": "John Doe"
  }'
```

### Unsubscribe from Newsletter
```bash
curl -X POST "http://127.0.0.1:8000/api/newsletter/unsubscribe" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "user@example.com"
  }'
```

### Get All Subscriptions (Admin)
```bash
curl -X GET "http://127.0.0.1:8000/api/newsletter-subscriptions?status=active&per_page=20" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

### Get Subscription Statistics (Admin)
```bash
curl -X GET "http://127.0.0.1:8000/api/newsletter-subscriptions/stats/overview" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

### Update Subscription (Admin)
```bash
curl -X PUT "http://127.0.0.1:8000/api/newsletter-subscriptions/1" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "John Smith",
    "is_active": false
  }'
```

### Delete Subscription (Admin)
```bash
curl -X DELETE "http://127.0.0.1:8000/api/newsletter-subscriptions/1" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

---

## Database Schema

### Newsletter Subscriptions Table
```sql
CREATE TABLE newsletter_subscriptions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    name VARCHAR(255) NULL,
    subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    unsubscribed_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

---

## Important Notes

1. **Email Uniqueness**: Each email can only have one subscription record
2. **Resubscription**: If a user tries to subscribe with an email that was previously unsubscribed, they will be automatically resubscribed
3. **Soft Unsubscribe**: Unsubscribing sets `unsubscribed_at` timestamp (no `is_active` field)
4. **Active Status**: A subscription is considered active when `unsubscribed_at` is null
5. **Admin Access**: All admin endpoints require authentication with admin privileges
6. **Validation**: All inputs are validated on the server side
7. **Error Handling**: Comprehensive error responses with appropriate HTTP status codes
8. **Statistics**: Admin can view subscription statistics including monthly trends
9. **Search**: Admin can search subscriptions by email or name
10. **Status Filtering**: Admin can filter subscriptions by active/inactive status based on `unsubscribed_at` field
11. **No Pagination**: All subscriptions are returned in a single request for easier management

---

## Integration with Frontend Newsletter Form

Based on your newsletter form design, here's how to integrate it:

```html
<!-- Your existing newsletter form -->
<div class="newsletter-section">
  <h2>Newsletter</h2>
  <p>Proudly crafting custom cosmetics</p>
  <p>Since 2025 | Beauty tailored to you. Place your orders!</p>
  
  <form id="newsletterForm">
    <input type="email" id="newsletterEmail" placeholder="Enter your email" required>
    <button type="submit">Subscribe</button>
  </form>
</div>

<script>
document.getElementById('newsletterForm').addEventListener('submit', async function(e) {
  e.preventDefault();
  
  const email = document.getElementById('newsletterEmail').value;
  const button = this.querySelector('button');
  const originalText = button.textContent;
  
  // Show loading state
  button.textContent = 'Subscribing...';
  button.disabled = true;
  
  try {
    const response = await fetch('/api/newsletter/subscribe', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({ email })
    });
    
    const data = await response.json();
    
    // Show success/error message
    alert(data.message);
    
    if (data.success) {
      document.getElementById('newsletterEmail').value = '';
    }
  } catch (error) {
    alert('Something went wrong. Please try again.');
  } finally {
    // Reset button
    button.textContent = originalText;
    button.disabled = false;
  }
});
</script>
```

---

*Last Updated: September 23, 2025*
*Version: 1.0.0*
