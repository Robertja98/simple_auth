# Quick Start (5 Minutes)

## 1. Copy to Your Project

```bash
cp -r simple_auth /path/to/your/project/
```

## 2. Create Config

```bash
cd simple_auth
cp config.example.php config.php
```

## 3. Test Installation

Open in browser: `http://yoursite.com/simple_auth/install.php`

All checks should pass ✅

## 4. Add Middleware

Protect your pages:

```php
<?php
require_once __DIR__ . '/simple_auth/middleware.php';
// Your protected page
?>
```

## 5. Add Login Links

```php
<?php if (auth_check()): ?>
    Welcome, <?= htmlspecialchars(auth_current_user()['username']) ?>
    <a href="/simple_auth/logout.php">Logout</a>
<?php else: ?>
    <a href="/simple_auth/register.php">Register</a>
    <a href="/simple_auth/login.php">Login</a>
<?php endif; ?>
```

## Next Steps

- Read **INSTALLATION.md** for detailed setup
- Read **DEPLOYMENT.md** for production deployment
- Check **README.md** for full API documentation
- Run `simple_auth/test.php` to test functionality

## Quick Links

- Register: `/simple_auth/register.php`
- Login: `/simple_auth/login.php` 
- Logout: `/simple_auth/logout.php`
- Test: `/simple_auth/test.php`
- Verify: `/simple_auth/install.php`

## Need Help?

1. Visit `install.php` to diagnose issues
2. Visit `test.php` for functionality test
3. Visit `test_permissions.php` for permission issues
4. Check documentation files

---

**That's it! You're ready to authenticate users securely.**
