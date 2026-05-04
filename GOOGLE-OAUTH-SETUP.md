# Google OAuth Setup Guide

## Step 1: Install Google API Client

Run this command in your project root:

```bash
composer require google/apiclient:"^2.0"
```

## Step 2: Create Google Cloud Project

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Click "Select a project" → "New Project"
3. Enter project name: "SPED LMS"
4. Click "Create"

## Step 3: Enable Google+ API

1. In the left sidebar, go to "APIs & Services" → "Library"
2. Search for "Google+ API"
3. Click on it and click "Enable"

## Step 4: Create OAuth 2.0 Credentials

1. Go to "APIs & Services" → "Credentials"
2. Click "Create Credentials" → "OAuth client ID"
3. If prompted, configure the OAuth consent screen:
   - User Type: External
   - App name: SPED LMS
   - User support email: your email
   - Developer contact: your email
   - Click "Save and Continue"
   - Scopes: Add `email` and `profile`
   - Click "Save and Continue"
   - Test users: Add your email (for testing)
   - Click "Save and Continue"

4. Create OAuth Client ID:
   - Application type: Web application
   - Name: SPED LMS Web Client
   - Authorized JavaScript origins:
     - `http://localhost`
     - `http://localhost:8080` (if using different port)
   - Authorized redirect URIs:
     - `http://localhost/Signedd/public/auth/google/callback`
     - (Adjust based on your actual path)
   - Click "Create"

5. Copy the Client ID and Client Secret

## Step 5: Update .env File

Copy `.env.example` to `.env` and update:

```env
GOOGLE_CLIENT_ID=your_actual_client_id_here
GOOGLE_CLIENT_SECRET=your_actual_client_secret_here
GOOGLE_REDIRECT_URI=http://localhost/Signedd/public/auth/google/callback
```

**Important:** Make sure the redirect URI matches exactly what you entered in Google Cloud Console!

## Step 6: Test Google Sign-In

1. Go to your login page
2. Click "Sign in with Google"
3. You should be redirected to Google's consent screen
4. Authorize the app
5. You should be redirected back and logged in

## Troubleshooting

### Error: "redirect_uri_mismatch"
- Make sure the redirect URI in `.env` matches exactly what's in Google Cloud Console
- Check for trailing slashes
- Verify the protocol (http vs https)

### Error: "invalid_client"
- Double-check your Client ID and Client Secret in `.env`
- Make sure there are no extra spaces

### Error: "access_denied"
- User cancelled the authorization
- This is normal behavior

### Error: "Google Sign-In is not configured"
- Run `composer install` to install Google API client
- Check if `vendor/autoload.php` is being loaded in `public/index.php`

## Production Deployment

When deploying to production:

1. Update OAuth consent screen to "Production" (not "Testing")
2. Add your production domain to Authorized JavaScript origins
3. Add your production callback URL to Authorized redirect URIs
4. Update `.env` with production URLs
5. Use HTTPS (required for production)

## Security Notes

- Never commit `.env` file to version control
- Keep Client Secret secure
- Use HTTPS in production
- Regularly rotate credentials
- Monitor OAuth usage in Google Cloud Console
