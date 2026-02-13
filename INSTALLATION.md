# Installation Guide

## System Requirements

- PHP 7.4 or higher
- File system write permissions on web directory
- No database required
- Works on Linux, macOS, and Windows

## Step 1: Download

### Option A: Copy Files (Easiest)

```bash
# Copy the auth folder to your project
cp -r simple_auth /path/to/your/project/
```

### Option B: Clone from GitHub

```bash
cd /path/to/your/project
git clone https://github.com/Robertja98/simple_auth.git
```

### Option C: Composer (If available)

```bash
composer require robertja98/simple-auth
```

## Step 2: Create Configuration

```bash
cd simple_auth
cp config.example.php config.php
```

Edit `config.php` with your settings:

```php
return [
    'storage' => [
        'data_dir' => __DIR__ . '/data',  // Where CSV files store user data
    ],
    
    'security' => [
        'password_algo' => PASSWORD_ARGON2ID,
        'password_options' => [
            'memory_cost' => 65536,  // 64 MB
            'time_cost' => 4,
            'threads' => 3,
        ],
        
        'session_name' => 'SIMPLE_AUTH_SESSION',
        'session_lifetime' => 86400,  // 24 hours
        'session_cookie_secure' => false,  // Set to true when using HTTPS
        'session_cookie_httponly' => true,
        'session_cookie_samesite' => 'Strict',
        
        'max_login_attempts' => 5,
        'lockout_duration' => 900,  // 15 minutes
        'rate_limit_window' => 900,
    ],
    
    'app' => [
        'name' => 'My App',
        'base_url' => 'http://localhost/myproject',
        'require_email_verification' => false,
    ],
    
    'validation' => [
        'username_min_length' => 3,
        'username_max_length' => 50,
        'password_min_length' => 8,
        'password_require_uppercase' => true,
        'password_require_lowercase' => true,
        'password_require_number' => true,
        'password_require_special' => true,
    ],
    
    'logging' => [
        'enabled' => true,
        'log_failed_logins' => true,
        'log_successful_logins' => true,
    ],
];
```

## Step 3: Verify Installation

### Via Browser

Open: `http://localhost/simple_auth/install.php`

This checks:
- ✅ PHP version
- ✅ Required functions
- ✅ File permissions
- ✅ Directory creation
- ✅ Argon2ID support

### Via Command Line

```bash
php install.php
```

### Run Tests

```
http://localhost/simple_auth/test.php
```

Test the complete flow:
1. Register a test user
2. Login with that user
3. Check session verification
4. Logout

## Step 4: Set File Permissions

### Linux/Mac

```bash
chmod 755 simple_auth/data/
chmod 644 simple_auth/data/*.csv
chmod 755 simple_auth/
find simple_auth -type f -name "*.php" -exec chmod 644 {} \;
```

### Windows (Command Prompt as admin)

```batch
icacls "simple_auth\data" /grant:r "%USERNAME%:(OI)(CI)F" /T
icacls "simple_auth\data" /inheritance:e
```

### Windows (PowerShell as admin)

```powershell
$path = "C:\xampp\htdocs\myproject\simple_auth\data"
icacls $path /grant:r Everyone:(OI)(CI)F /T
attrib -R $path /S /D
```

### Verify Permissions

Visit: `http://localhost/simple_auth/test_permissions.php`

Should show:
- ✅ Directory exists
- ✅ Directory is readable
- ✅ Directory is writable
- ✅ Can create files

## Step 5: Protect Your Pages

Add middleware to pages that need authentication:

```php
<?php
require_once __DIR__ . '/simple_auth/middleware.php';

// Rest of your page code
?>
```

This will:
- Check if user is logged in
- Redirect to login if not authenticated
- Make `auth_check()` and `auth_current_user()` available

## Step 6: Add Login/Logout Links

### Basic Links

```html
<?php if (auth_check()): ?>
    <p>Welcome, <?= htmlspecialchars(auth_current_user()['username']) ?></p>
    <a href="/simple_auth/logout.php">Logout</a>
<?php else: ?>
    <a href="/simple_auth/login.php">Login</a>
    <a href="/simple_auth/register.php">Register</a>
<?php endif; ?>
```

### Navigation Bar Example

```php
<nav>
    <a href="/">Home</a>
    <a href="/dashboard">Dashboard</a>
    
    <?php if (auth_check()): ?>
        <a href="/profile">Profile</a>
        <a href="/simple_auth/logout.php">Logout (<?= htmlspecialchars(auth_current_user()['username']) ?>)</a>
    <?php else: ?>
        <a href="/simple_auth/login.php">Login</a>
        <a href="/simple_auth/register.php">Register</a>
    <?php endif; ?>
</nav>
```

## Step 7: Customize UI

Edit the PHP pages to match your theme:

- `login.php` - Login page styling
- `register.php` - Registration page styling
- `index.php` - Landing page
- `logout.php` - Can redirect to custom page

Example redirect in `logout.php`:

```php
$auth->logout();
header('Location: /thank-you-for-visiting');
exit;
```

## Step 8: Production Deployment

When deploying to production:

### 1. Update config.php

```php
'session_cookie_secure' => true,  // HTTPS required
'app' => [
    'base_url' => 'https://yourdomainname.com',
],
```

### 2. Enable HTTPS

Ensure your web server has SSL/TLS certificate configured.

### 3. Set File Permissions

Follow the appropriate section above for your server OS.

### 4. Add to .gitignore

```
simple_auth/config.php
simple_auth/data/
simple_auth/data/*.csv
```

### 5. Backup Strategy

Create regular backups of:
- `simple_auth/data/users.csv` - User accounts
- `simple_auth/config.php` - Configuration

### 6. Set Up Maintenance

Create a cron job to run:

```bash
0 2 * * 0 php /path/to/simple_auth/maintenance.php
```

This cleans up:
- Expired sessions
- Old login attempts
- Activity logs (90+ days old)

## Troubleshooting

### "Configuration file not found"

Create `simple_auth/config.php`:
```bash
cp simple_auth/config.example.php simple_auth/config.php
```

### "Permission denied" on data directory

Windows users check:
```powershell
icacls "simple_auth\data"
```

Should show your user with `F` (Full Control).

### "Directory is not writable"

Ensure the web server process (www-data, apache, iis, etc.) has write permissions:

```bash
# Linux
sudo chown -R www-data:www-data simple_auth/data/
sudo chmod 755 simple_auth/data/
```

### HTTPS/Secure Cookie Issues

1. Ensure HTTPS is enabled on server
2. Update `config.php`: `'session_cookie_secure' => true`
3. Restart Apache/Nginx

### Rate Limiting Not Working

Check IP address validation at: `simple_auth/test_permissions.php`

If `X-Forwarded-For` shows, your server is behind a proxy - this is handled automatically.

### Sessions Not Persisting

1. Check if `data/sessions.csv` is being created
2. Verify PHP `session.save_path` is writable
3. Check `session_lifetime` in config.php
4. Clear browser cookies and try again

## Getting Help

1. Run `install.php` to verify requirements
2. Run `test.php` to test functionality
3. Run `test_permissions.php` to check permissions
4. Review `data/activity_log.csv` for errors
5. Check your web server error logs

## Next Steps

1. Add security headers to your pages:
   ```php
   <?php require_once __DIR__ . '/simple_auth/security_headers.php'; ?>
   ```

2. Integrate with your existing user system

3. Add password reset flow

4. Enable email verification

5. Monitor `data/activity_log.csv` for security

See examples in `simple_auth/examples/` directory.
