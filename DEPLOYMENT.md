# Production Deployment Guide

## Pre-Deployment Checklist

- [ ] Read INSTALLATION.md and complete all steps
- [ ] Run `install.php` and verify all checks pass
- [ ] Run `test.php` and complete all tests
- [ ] Configure HTTPS on your server
- [ ] Update config.php for production
- [ ] Test on staging environment first
- [ ] Backup existing data
- [ ] Plan rollback strategy

## Security Configuration

### 1. HTTPS/TLS (Required)

Your server MUST use HTTPS. Update `config.php`:

```php
'security' => [
    'session_cookie_secure' => true,  // Only send cookies over HTTPS
    'session_cookie_samesite' => 'Strict',  // CSRF protection
],

'app' => [
    'base_url' => 'https://yourdomainname.com/path',
],
```

### 2. Environment-Specific Configuration

Use environment variables (optional but recommended):

```php
$config = [
    'app' => [
        'base_url' => getenv('APP_BASE_URL') ?: 'http://localhost',
        'admin_email' => getenv('ADMIN_EMAIL') ?: 'admin@example.com',
    ],
    'security' => [
        'session_cookie_secure' => getenv('SECURE_COOKIES') === 'true',
    ],
];
```

Then set in `.env` or web server:

```bash
# .env file (not committed to git)
APP_BASE_URL=https://yoursite.com
ADMIN_EMAIL=admin@yoursite.com
SECURE_COOKIES=true
```

### 3. File Permissions

#### Linux/Mac (Apache/Nginx)

```bash
# Set proper ownership
sudo chown -R www-data:www-data /path/to/simple_auth
sudo chown -R www-data:www-data /path/to/simple_auth/data

# Set permissions
chmod 755 /path/to/simple_auth
chmod 755 /path/to/simple_auth/data
chmod 644 /path/to/simple_auth/*.php
chmod 600 /path/to/simple_auth/config.php  # Config readable by web server only

# Verify
ls -la /path/to/simple_auth/data
# Should show: drwxr-xr-x  www-data www-data
```

#### Windows (IIS)

```powershell
# Run as Administrator
$path = "C:\inetpub\wwwroot\mydomain\simple_auth\data"

# Grant IIS_IUSRS full control
icacls $path /grant:r "IIS_IUSRS:(OI)(CI)F" /T

# Remove inheritance to be more restrictive
icacls $path /inheritance:r
icacls $path /grant:r "IIS_IUSRS:(OI)(CI)F"
icacls $path /grant:r "SYSTEM:(OI)(CI)F"

# Verify
icacls $path
```

#### Windows (XAMPP/Apache)

```powershell
# Run as Administrator
$path = "C:\xampp\htdocs\mydomain\simple_auth\data"

icacls $path /grant:r "Apache:(OI)(CI)F" /T
```

### 4. .gitignore Configuration

Ensure your `.gitignore` protects user data:

```
# Don't commit auth config or user data
simple_auth/config.php
simple_auth/data/
simple_auth/data/*.csv
simple_auth/logs/

# System files
.env
.env.local
.env.*.local
```

Verify:
```bash
git check-ignore simple_auth/config.php simple_auth/data/*.csv
# Should output the ignored files
```

## Database Backup Strategy

### Automatic Backups

Create a backup script `backup_auth.php`:

```php
<?php
$dataDir = __DIR__ . '/simple_auth/data';
$backupDir = __DIR__ . '/backups';

if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}

$timestamp = date('YmdHis');
$backupName = "auth_backup_$timestamp.tar.gz";

// Create tar.gz of data directory
shell_exec("tar -czf $backupDir/$backupName -C $dataDir .");

// Keep only last 30 days
$files = glob("$backupDir/*.tar.gz");
foreach ($files as $file) {
    if (time() - filemtime($file) > 30 * 24 * 3600) {
        unlink($file);
    }
}

echo "Backup created: $backupName";
?>
```

Add to crontab (daily at 2 AM):

```bash
0 2 * * * php /path/to/backup_auth.php >> /var/log/auth_backup.log 2>&1
```

### Manual Backup

```bash
# Backup user data
tar -czf auth_backup_$(date +%Y%m%d).tar.gz simple_auth/data/

# Upload to safe location (AWS S3, Google Drive, etc.)
```

## Monitoring & Maintenance

### 1. Regular Cleanup

Run `maintenance.php` weekly:

```bash
0 2 * * 0 php /path/to/simple_auth/maintenance.php
```

This:
- Removes expired sessions
- Deletes old login attempts
- Prunes activity logs (90+ days)

### 2. Monitor Activity Logs

Check for suspicious patterns:

```bash
# View recent login attempts
tail -100 simple_auth/data/login_attempts.csv

# Look for failed attempts from single IP
grep ",0$" simple_auth/data/login_attempts.csv | grep "192.168.1.100"

# Count attempts by status
awk -F, '{print $4}' simple_auth/data/login_attempts.csv | sort | uniq -c
```

### 3. Set Up Alerts

Create alert script `check_auth_health.php`:

```php
<?php
$config = require __DIR__ . '/simple_auth/config.php';
$store = new CsvDataStore($config);

// Check for suspicious activity
$attempts = $store->fetchAll('login_attempts');
$recentFailures = array_filter($attempts, function($a) {
    return $a['success'] === '0' && 
           strtotime($a['attempted_at']) > time() - 3600;
});

if (count($recentFailures) > 50) {
    // Send alert email
    mail(
        'admin@example.com',
        'Alert: High failed login attempts',
        'Failed logins in last hour: ' . count($recentFailures)
    );
}

// Check directory size
$size = 0;
foreach (glob(__DIR__ . '/simple_auth/data/*') as $file) {
    $size += filesize($file);
}

if ($size > 100 * 1024 * 1024) {  // 100MB
    mail(
        'admin@example.com',
        'Alert: Auth data directory is growing',
        'Current size: ' . number_format($size / 1024 / 1024, 2) . ' MB'
    );
}
?>
```

Add to crontab (check hourly):

```bash
0 * * * * php /path/to/check_auth_health.php
```

## Troubleshooting Production Issues

### Issue: "Permission denied" on login

**Symptom:** Can't create sessions in `data/` directory

**Solution:**
```bash
# Check ownership
ls -l /path/to/simple_auth/data/

# May need to fix permissions
sudo chown www-data:www-data /path/to/simple_auth/data/
sudo chmod 755 /path/to/simple_auth/data/
```

### Issue: Sessions expire too quickly

**Symptom:** Users logged out after short time

**Solution:**
Check `config.php`:
```php
'session_lifetime' => 86400,  // 24 hours - adjust as needed
```

Also verify your web server's session settings:
```bash
# Check PHP session timeout
php -i | grep session.gc_maxlifetime
```

### Issue: Rate limiting not working

**Symptom:** Multiple failed logins not triggering lockout

**Solution:**
1. Check logs: `tail simple_auth/data/activity_log.csv`
2. Verify IP detection: Visit `install.php`
3. If behind proxy:
   ```php
   // Server automatically detects and validates X-Forwarded-For
   ```

### Issue: High CPU/Disk Usage

**Symptom:** Server getting slow

**Solution:**
1. Run maintenance to clean old data:
   ```bash
   php simple_auth/maintenance.php
   ```

2. Check file sizes:
   ```bash
   du -sh simple_auth/data/
   ls -lh simple_auth/data/
   ```

3. Archive old data if needed:
   ```bash
   # For activity_log.csv older than 90 days
   php simple_auth/maintenance.php
   ```

### Issue: Sudden Login Failures

**Symptoms:** Users can't login despite correct credentials

**Solutions:**
1. Check file permissions again
2. Verify disk space: `df -h`
3. Check web server logs: `/var/log/apache2/error.log`
4. Verify `config.php` syntax: `php -l simple_auth/config.php`
5. Check CSV file integrity:
   ```bash
   # Should show proper CSV format
   head -5 simple_auth/data/users.csv
   ```

## Scaling Considerations

### CSV File Limitations

The system works great for:
- Up to 10,000 users
- Low to medium traffic
- Development/testing

For larger deployments (100k+ users), consider:

1. **Migrate to Database**
   - Port the Auth class to use PDO
   - Keep the same public API

2. **Hybrid Approach**
   - Use CSV for sessions
   - Use database for users
   - Best of both worlds

3. **Caching Layer**
   - Cache user lookups in Redis
   - Reduces CSV reads significantly

## Rollback Plan

If something goes wrong:

### Quick Rollback

```bash
# Stop the application
sudo systemctl stop apache2

# Restore from backup
tar -xzf backups/auth_backup_<date>.tar.gz -C simple_auth/

# Restart
sudo systemctl start apache2
```

### Version Control Rollback

```bash
# Revert to previous working version
git log --oneline
git revert HEAD~1 -m 1
git push origin main
```

## Post-Deployment Verification

1. **Test Registration**
   ```
   https://yoursite.com/simple_auth/register.php
   Create test account with password: TestPass123!
   ```

2. **Test Login**
   ```
   https://yoursite.com/simple_auth/login.php
   Login with test account
   ```

3. **Test Protected Pages**
   Visit a protected page, should see user info

4. **Test Logout**
   Click logout, should be redirected

5. **Check Logs**
   ```bash
   tail simple_auth/data/activity_log.csv
   # Should show registration and login entries
   ```

6. **Monitor for 24 Hours**
   - Check CPU/Memory usage
   - Monitor error logs
   - Review activity logs
   - Test rate limiting (if applicable)

## Emergency Contact

If you encounter critical issues:

1. Check `simple_auth/data/activity_log.csv` for recent errors
2. Review web server error logs
3. Run `install.php` to diagnose issues
4. Restore from backup if needed

---

**Keep your data safe. Test in staging first. Monitor in production.**
