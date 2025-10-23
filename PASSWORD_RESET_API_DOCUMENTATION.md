# Password Reset API Documentation

## Overview
This documentation covers the password reset functionality for the Mariamly Backend API. Users can request a 6-digit verification code and use it to set a new password.

## Base URL
```
https://your-domain.com/api
```

## Authentication
Password reset endpoints are **public** and do not require authentication.

---

## 1. Forgot Password

### Endpoint
```
POST /forgot-password
```

### Description
Sends a 6-digit verification code to the user's email address. The code is valid for 10 minutes.

### Request Body
```json
{
    "email": "user@example.com"
}
```

### Request Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| email | string | Yes | User's email address (must exist in database) |

### Validation Rules
- `email`: Required, must be valid email format, must exist in users table

### Success Response
```json
{
    "success": true,
    "message": "Password reset verification code sent to your email.",
    "email": "user@example.com",
    "expires_in_minutes": 10
}
```

### Error Responses

#### Email Not Found (422)
```json
{
    "message": "The given data was invalid.",
    "errors": {
        "email": [
            "The selected email is invalid."
        ]
    }
}
```

#### Invalid Email Format (422)
```json
{
    "message": "The given data was invalid.",
    "errors": {
        "email": [
            "The email must be a valid email address."
        ]
    }
}
```

#### Email Sending Failed (500)
```json
{
    "success": false,
    "message": "Failed to send verification code. Please try again later."
}
```

### Example cURL Request
```bash
curl -X POST https://your-domain.com/api/forgot-password \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "user@example.com"
  }'
```

### Example JavaScript/Fetch
```javascript
const response = await fetch('/api/forgot-password', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  },
  body: JSON.stringify({
    email: 'user@example.com'
  })
});

const data = await response.json();
console.log(data);
```

---

## 2. Verify Reset Code

### Endpoint
```
POST /verify-reset-code
```

### Description
Verifies the 6-digit verification code without resetting the password. Useful for frontend validation before showing the password reset form.

### Request Body
```json
{
    "email": "user@example.com",
    "verification_code": "123456"
}
```

### Request Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| email | string | Yes | User's email address |
| verification_code | string | Yes | 6-digit verification code |

### Validation Rules
- `email`: Required, must be valid email format, must exist in users table
- `verification_code`: Required, must be exactly 6 digits

### Success Response
```json
{
    "success": true,
    "message": "Verification code is valid.",
    "email": "user@example.com"
}
```

### Error Responses

#### Invalid Code (400)
```json
{
    "success": false,
    "message": "Invalid verification code."
}
```

#### Expired Code (400)
```json
{
    "success": false,
    "message": "Verification code has expired. Please request a new one."
}
```

#### No Code Found (400)
```json
{
    "success": false,
    "message": "No verification code found for this email. Please request a new one."
}
```

---

## 3. Reset Password

### Endpoint
```
POST /reset-password
```

### Description
Resets the user's password using the 6-digit verification code received from the forgot password endpoint.

### Request Body
```json
{
    "email": "user@example.com",
    "verification_code": "123456",
    "password": "newpassword123",
    "password_confirmation": "newpassword123"
}
```

### Request Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| email | string | Yes | User's email address |
| verification_code | string | Yes | 6-digit verification code |
| password | string | Yes | New password (minimum 6 characters) |
| password_confirmation | string | Yes | Password confirmation (must match password) |

### Validation Rules
- `email`: Required, must be valid email format, must exist in users table
- `verification_code`: Required, must be exactly 6 digits
- `password`: Required, minimum 6 characters
- `password_confirmation`: Required, must match password field

### Success Response
```json
{
    "success": true,
    "message": "Password has been reset successfully."
}
```

### Error Responses

#### Invalid Code (400)
```json
{
    "success": false,
    "message": "Invalid verification code."
}
```

#### Expired Code (400)
```json
{
    "success": false,
    "message": "Verification code has expired. Please request a new one."
}
```

#### No Code Found (400)
```json
{
    "success": false,
    "message": "No verification code found for this email. Please request a new one."
}
```

#### User Not Found (404)
```json
{
    "success": false,
    "message": "User not found."
}
```

#### Validation Errors (422)
```json
{
    "message": "The given data was invalid.",
    "errors": {
        "password": [
            "The password confirmation does not match."
        ]
    }
}
```

### Example cURL Request
```bash
curl -X POST https://your-domain.com/api/reset-password \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "user@example.com",
    "verification_code": "123456",
    "password": "newpassword123",
    "password_confirmation": "newpassword123"
  }'
```

### Example JavaScript/Fetch
```javascript
const response = await fetch('/api/reset-password', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  },
  body: JSON.stringify({
    email: 'user@example.com',
    verification_code: '123456',
    password: 'newpassword123',
    password_confirmation: 'newpassword123'
  })
});

const data = await response.json();
console.log(data);
```

---

## Complete Flow Example

### Step 1: Request Password Reset
```javascript
// User enters their email
const forgotPassword = async (email) => {
  try {
    const response = await fetch('/api/forgot-password', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({ email })
    });
    
    const data = await response.json();
    
    if (data.success) {
      // Store the email for the next steps
      localStorage.setItem('resetEmail', data.email);
      
      alert('Verification code sent! Check your email.');
      // Show verification code input form
      // User will receive a professional email with the 6-digit code
    } else {
      alert('Error: ' + data.message);
    }
  } catch (error) {
    console.error('Error:', error);
  }
};
```

### Step 2: Verify Code (Optional - for better UX)
```javascript
// User enters verification code
const verifyCode = async (verificationCode) => {
  const email = localStorage.getItem('resetEmail');
  
  if (!email) {
    alert('Please request a password reset first.');
    return;
  }
  
  try {
    const response = await fetch('/api/verify-reset-code', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        email,
        verification_code: verificationCode
      })
    });
    
    const data = await response.json();
    
    if (data.success) {
      alert('Code verified! You can now set your new password.');
      // Show password reset form
    } else {
      alert('Error: ' + data.message);
    }
  } catch (error) {
    console.error('Error:', error);
  }
};
```

### Step 3: Reset Password
```javascript
// User enters new password
const resetPassword = async (verificationCode, newPassword, confirmPassword) => {
  const email = localStorage.getItem('resetEmail');
  
  if (!email) {
    alert('Please request a password reset first.');
    return;
  }
  
  try {
    const response = await fetch('/api/reset-password', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        email,
        verification_code: verificationCode,
        password: newPassword,
        password_confirmation: confirmPassword
      })
    });
    
    const data = await response.json();
    
    if (data.success) {
      // Clear stored data
      localStorage.removeItem('resetEmail');
      
      alert('Password reset successfully! You can now login with your new password.');
      // Redirect to login page
    } else {
      alert('Error: ' + data.message);
    }
  } catch (error) {
    console.error('Error:', error);
  }
};
```

---

## Security Features

### Verification Code Security
- ✅ Codes are 6-digit numbers (100000-999999)
- ✅ Codes are hashed using `Hash::make()` before storage
- ✅ Codes expire after 10 minutes (shorter than tokens for better security)
- ✅ Codes are deleted after successful use
- ✅ Only one active code per email address
- ✅ Codes are validated for exact 6-digit format

### Password Security
- ✅ Minimum 6 character requirement
- ✅ Password confirmation validation
- ✅ Passwords are hashed using Laravel's Hash facade
- ✅ Old password verification for change password functionality

### Rate Limiting
Consider implementing rate limiting for production:
- Limit forgot password requests per IP/email (e.g., 3 per hour)
- Limit verification attempts per IP/email (e.g., 5 per hour)
- Limit reset password attempts per IP/email (e.g., 3 per hour)

---

## HTTP Status Codes

| Code | Description |
|------|-------------|
| 200 | Success |
| 400 | Bad Request (Invalid/Expired token) |
| 404 | Not Found (User not found) |
| 422 | Validation Error |
| 500 | Internal Server Error |

---

## Production Considerations

### Email Integration
✅ **Email functionality is already implemented!**

The system automatically:
1. ✅ Sends verification code via email (no code in API response)
2. ✅ Uses professional email template with user's name
3. ✅ Includes security warnings and expiration time
4. ✅ Handles email sending errors gracefully

**Email Template Features:**
- Professional HTML design
- User's full name in greeting
- Large, easy-to-read verification code
- Security warnings and expiration notice
- Branded with Mariamly logo
- Mobile-responsive design

### Email Template Details
The password reset email includes:

**Visual Elements:**
- Clean, professional layout with Mariamly branding
- Large, prominent verification code display (32px font, blue color)
- Dashed border around the code for emphasis
- Warning section with security information
- Responsive design for mobile devices

**Content:**
- Personalized greeting with user's full name
- Clear instructions for using the verification code
- Security warnings about expiration (10 minutes)
- Instructions for what to do if they didn't request the reset
- Contact information for support

**Security Features:**
- No sensitive information in plain text
- Clear expiration time warning
- Instructions not to share the code
- Professional appearance to build trust

### Environment Variables
Make sure these are set in your `.env`:
```
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="Your App Name"
```

### Frontend Integration
- Implement proper form validation
- Show loading states during API calls
- Handle all error cases gracefully
- Implement proper UX flow (email sent confirmation, code verification, etc.)
- Consider adding a countdown timer for code expiration (10 minutes)
- Add input formatting for 6-digit code (auto-focus, digit separation)
- Show user-friendly messages about checking email
- Implement resend code functionality if needed

---

## Testing

### Email Testing
To test the email functionality, use the test endpoint:

```bash
GET /api/test-email/{email}
```

**Example:**
```bash
curl -X GET https://your-domain.com/api/test-email/user@example.com
```

**Response:**
```json
{
    "success": true,
    "message": "Test email sent successfully",
    "verification_code": "123456"
}
```

**⚠️ Important:** Remove this test route before going to production!

### Test Cases
1. ✅ Valid email request
2. ✅ Invalid email format
3. ✅ Non-existent email
4. ✅ Valid verification code reset
5. ✅ Invalid verification code
6. ✅ Expired verification code
7. ✅ Password confirmation mismatch
8. ✅ Short password
9. ✅ Code reuse attempt
10. ✅ Verify code endpoint (valid/invalid/expired)
11. ✅ Email sending functionality
12. ✅ Email template rendering

### Test Data
```json
{
  "valid_email": "test@example.com",
  "invalid_email": "not-an-email",
  "non_existent_email": "nonexistent@example.com",
  "short_password": "123",
  "valid_password": "newpassword123",
  "valid_code": "123456",
  "invalid_code": "000000",
  "short_code": "123"
}
```

---

## Troubleshooting

### Common Issues

1. **"Invalid verification code"**
   - Code has been used already
   - Code doesn't match stored hash
   - Code was modified or incorrect

2. **"Verification code has expired"**
   - Code is older than 10 minutes
   - Request a new code

3. **"The selected email is invalid"**
   - Email doesn't exist in database
   - Check email spelling

4. **"The password confirmation does not match"**
   - Password and password_confirmation fields don't match
   - Check for typos

5. **"No verification code found for this email"**
   - No code was requested for this email
   - Code was already used or expired
   - Request a new code

6. **"Failed to send verification code"**
   - Email service configuration issue
   - SMTP server problems
   - Check email credentials in .env file
   - Verify email service is working

### Email Troubleshooting

**Common Email Issues:**

1. **Emails not being sent:**
   - Check SMTP configuration in `.env`
   - Verify email service credentials
   - Test with `/api/test-email/{email}` endpoint
   - Check Laravel logs for errors

2. **Emails going to spam:**
   - Configure SPF/DKIM records for your domain
   - Use a reputable email service (SendGrid, Mailgun, etc.)
   - Avoid spam trigger words in email content

3. **Email template not rendering:**
   - Check if `resources/views/emails/password-reset.blade.php` exists
   - Verify Mailable class is properly configured
   - Test email template in browser first

4. **SMTP Authentication errors:**
   - Double-check username/password
   - Verify SMTP server settings
   - Check if 2FA is enabled (use app passwords)

---

## File Structure

### Created Files
```
app/
├── Http/Controllers/AuthController.php (updated)
├── Mail/PasswordResetMail.php (new)
resources/
└── views/
    └── emails/
        └── password-reset.blade.php (new)
routes/
└── api.php (updated)
```

### Key Components

**1. AuthController Methods:**
- `forgotPassword()` - Sends verification code via email
- `verifyResetCode()` - Validates code without resetting password
- `resetPassword()` - Resets password using verification code

**2. PasswordResetMail Class:**
- Handles email composition and sending
- Uses professional HTML template
- Includes user personalization

**3. Email Template:**
- Professional HTML design
- Mobile-responsive layout
- Security warnings and branding

**4. Database Integration:**
- Uses existing `password_resets` table
- Stores hashed verification codes
- Automatic cleanup of expired codes

---

## Support

For technical support or questions about this API, please contact the development team or refer to the main API documentation.
