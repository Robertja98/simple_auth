# Cookie Troubleshooting Guide - CRM Registration

## Problem
Browser is not accepting or returning session cookies, causing CSRF validation to fail.

## Quick Fixes (Try in Order)

### 1. Clear Browser Cookies and Cache
1. Open DevTools (F12)
2. Go to **Application** tab (Chrome) or **Storage** tab (Firefox)
3. Under **Cookies**, find `http://localhost`
4. Delete all cookies
5. Close ALL browser tabs for localhost
6. Refresh the registration page

### 2. Check Browser Cookie Settings

**Chrome:**
1. Settings → Privacy and Security → Cookies
2. Make sure "Allow all cookies" is selected
3. OR add `http://localhost` to allowed sites

**Firefox:**
1. Settings → Privacy & Security → Cookies and Site Data
2. Uncheck "Delete cookies when Firefox is closed"
3. Set to "Standard" protection (not Strict)

**Edge:**
1. Settings → Cookies and site permissions → Cookies
2. Allow sites to save cookies
3. Make sure localhost isn't blocked

### 3. Try Different Browser
- If using Chrome, try Edge or Firefox
- If using Firefox, try Chrome
- Incognito/Private mode sometimes works better

### 4. Temporary Dev Workaround (LOCALHOST ONLY)

If you need to test registration NOW while troubleshooting cookies:

Edit `C:\xampp\htdocs\CRM\simple_auth\config.php`:

```php
// Add this TEMPORARY setting to security array:
'dev_bypass_csrf' => true,  // REMOVE IN PRODUCTION!
```

Edit `C:\xampp\htdocs\CRM\simple_auth\register.php`, find:
```php
if (!$auth->verifyCsrfToken($csrfToken)) {
```

Replace with:
```php
$devBypass = ($config['security']['dev_bypass_csrf'] ?? false) && $_SERVER['SERVER_NAME'] === 'localhost';
if (!$devBypass && !$auth->verifyCsrfToken($csrfToken)) {
```

**⚠️ WARNING**: Remove this bypass before deploying to production!

### 5. Check PHP Session Configuration

Run this in PowerShell:
```powershell
php -r "echo 'Session Save Path: ' . session_save_path();"
```

Make sure the path exists and is writable.

## What's Happening

1. Server creates session, sends cookie: `CRM_SESSION=...`
2. Browser receives cookie but doesn't store it (or blocks it)
3. When you submit form, browser doesn't send cookie back
4. Server creates NEW session (different ID)
5. CSRF token from original session doesn't match
6. Validation fails

## Permanent Fix

The root cause is browser cookie settings. Sessions MUST work for the CRM to function properly. Fix your browser settings rather than using the bypass.
