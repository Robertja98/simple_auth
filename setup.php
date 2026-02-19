<?php
/**
 * Simple Auth Interactive Setup
 * 
 * Auto-generates config.php with project-specific settings
 * Run once per project: php setup.php
 */

// Prevent running in web browser - CLI only
if (PHP_SAPI !== 'cli') {
    // For web browser: show a simple HTML form
    handleWebSetup();
    exit;
}

// CLI mode
runCliSetup();

/**
 * CLI Setup - Interactive command line setup
 */
function runCliSetup() {
    echo "\n";
    echo "╔════════════════════════════════════════════════════════════════╗\n";
    echo "║     Simple Auth - Interactive Setup                           ║\n";
    echo "║     https://github.com/Robertja98/simple_auth                 ║\n";
    echo "╚════════════════════════════════════════════════════════════════╝\n\n";
    
    // Check if config already exists
    $configPath = __DIR__ . '/config.php';
    if (file_exists($configPath)) {
        echo "⚠️  config.php already exists!\n";
        echo "Do you want to overwrite it? (y/n): ";
        $response = trim(fgets(STDIN));
        if (strtolower($response) !== 'y') {
            echo "Setup cancelled.\n";
            return;
        }
        echo "\n";
    }
    
    // Gather configuration
    echo "📝 Project Configuration\n";
    echo "═══════════════════════════════════════════════════════════════\n\n";
    
    $config = [
        'project_name' => ask('Project Name (e.g., "My CRM System")', 'Auth System'),
        'session_name' => ask('Session Name (unique identifier for cookies)', 'SIMPLE_AUTH_SESSION'),
        'base_url' => ask('Base URL (e.g., "http://localhost/myproject")', 'http://localhost/project'),
        'use_https' => ask('Using HTTPS in production? (y/n)', 'n'),
        'require_email_verification' => ask('Require email verification? (y/n)', 'n'),
        'enable_2fa' => ask('Enable 2FA? (y/n)', 'n'),
        'admin_email' => ask('Admin email address', 'admin@example.com'),
    ];
    
    // Generate config.php
    echo "\n\n✨ Generating config.php...\n";
    generateConfigFile($config);
    
    // Create data directory
    echo "📁 Creating data directory...\n";
    $dataDir = __DIR__ . '/data';
    if (!is_dir($dataDir)) {
        mkdir($dataDir, 0755, true);
        echo "✓ Directory created: $dataDir\n";
    } else {
        echo "✓ Directory already exists: $dataDir\n";
    }
    
    // Set permissions
    if (PHP_OS_FAMILY === 'Windows') {
        echo "🔐 Windows: Using icacls for permissions\n";
        echo "Run this command as Administrator if needed:\n";
        echo "   icacls \"" . $dataDir . "\" /grant \"IUSR:(OI)(CI)F\" /inheritance:e\n";
    } else {
        chmod($dataDir, 0755);
        echo "✓ Permissions set: 755\n";
    }
    
    echo "\n";
    echo "╔════════════════════════════════════════════════════════════════╗\n";
    echo "║ ✅ Setup Complete!                                             ║\n";
    echo "╚════════════════════════════════════════════════════════════════╝\n\n";
    
    echo "Next steps:\n";
    echo "  1. Set file permissions (see above)\n";
    echo "  2. Visit: install.php to verify installation\n";
    echo "  3. Visit: test.php to test functionality\n";
    echo "  4. Add this to your protected pages:\n";
    echo "     <?php require_once __DIR__ . '/simple_auth/middleware.php'; ?>\n\n";
}

/**
 * Web Setup - HTML form for browser
 */
function handleWebSetup() {
    $configPath = __DIR__ . '/config.php';
    $configExists = file_exists($configPath);
    
    $projectName = $_POST['project_name'] ?? 'Auth System';
    $sessionName = $_POST['session_name'] ?? 'SIMPLE_AUTH_SESSION';
    $baseUrl = $_POST['base_url'] ?? 'http://localhost/project';
    $useHttps = isset($_POST['use_https']) ? 'y' : 'n';
    $requireEmailVerification = isset($_POST['require_email_verification']) ? 'y' : 'n';
    $enable2fa = isset($_POST['enable_2fa']) ? 'y' : 'n';
    $adminEmail = $_POST['admin_email'] ?? 'admin@example.com';
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['setup_submit'])) {
        if ($configExists && !isset($_POST['overwrite'])) {
            $error = "config.php already exists. Check 'Overwrite' to replace it.";
        } else {
            $config = [
                'project_name' => $projectName,
                'session_name' => $sessionName,
                'base_url' => $baseUrl,
                'use_https' => $useHttps,
                'require_email_verification' => $requireEmailVerification,
                'enable_2fa' => $enable2fa,
                'admin_email' => $adminEmail,
            ];
            
            generateConfigFile($config);
            
            $dataDir = __DIR__ . '/data';
            if (!is_dir($dataDir)) {
                mkdir($dataDir, 0755, true);
            }
            
            $success = "✅ Setup complete! config.php has been created.";
        }
    }
    ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Simple Auth Setup</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            max-width: 600px;
            width: 100%;
            padding: 40px;
        }
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }
        input[type="text"],
        input[type="email"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            font-family: monospace;
        }
        input[type="text"]:focus,
        input[type="email"]:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }
        .alert {
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            border-left: 4px solid;
        }
        .alert-error {
            background: #fee;
            border-color: #f99;
            color: #c33;
        }
        .alert-success {
            background: #efe;
            border-color: #9f9;
            color: #3c3;
        }
        button {
            width: 100%;
            padding: 14px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }
        button:hover {
            background: #5568d3;
        }
        .small {
            font-size: 12px;
            color: #999;
            margin-top: 5px;
        }
        .next-steps {
            background: #f5f5f5;
            border-radius: 4px;
            padding: 20px;
            margin-top: 20px;
        }
        .next-steps h3 {
            color: #333;
            margin-bottom: 10px;
        }
        .next-steps ol {
            margin-left: 20px;
            color: #666;
            line-height: 1.6;
        }
        .next-steps a {
            color: #667eea;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔐 Simple Auth Setup</h1>
        <p class="subtitle">Configure your authentication module</p>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <div class="next-steps">
                <h3>✅ Next Steps</h3>
                <ol>
                    <li>Visit <a href="install.php">install.php</a> to verify installation</li>
                    <li>Visit <a href="test.php">test.php</a> to test the auth system</li>
                    <li>Add middleware to your protected pages:
                        <div style="background: #f0f0f0; padding: 10px; border-radius: 4px; margin-top: 5px; font-family: monospace; font-size: 12px;">
                            &lt;?php require_once __DIR__ . '/simple_auth/middleware.php'; ?&gt;
                        </div>
                    </li>
                </ol>
            </div>
        <?php else: ?>
        <form method="POST">
            <div class="form-group">
                <label for="project_name">Project Name</label>
                <input type="text" id="project_name" name="project_name" value="<?= htmlspecialchars($projectName) ?>" placeholder="e.g., My CRM System" required>
                <div class="small">Displayed on auth pages and config</div>
            </div>
            
            <div class="form-group">
                <label for="session_name">Session Name</label>
                <input type="text" id="session_name" name="session_name" value="<?= htmlspecialchars($sessionName) ?>" placeholder="e.g., MYPROJECT_SESSION" required>
                <div class="small">Unique identifier for session cookies (no spaces)</div>
            </div>
            
            <div class="form-group">
                <label for="base_url">Base URL</label>
                <input type="text" id="base_url" name="base_url" value="<?= htmlspecialchars($baseUrl) ?>" placeholder="e.g., http://localhost/myproject" required>
                <div class="small">Without trailing slash</div>
            </div>
            
            <div class="form-group">
                <div class="checkbox-group">
                    <input type="checkbox" id="use_https" name="use_https" <?= $useHttps === 'y' ? 'checked' : '' ?>>
                    <label for="use_https" style="margin-bottom: 0;">Using HTTPS in production?</label>
                </div>
                <div class="small">Enables secure cookies</div>
            </div>
            
            <div class="form-group">
                <div class="checkbox-group">
                    <input type="checkbox" id="require_email_verification" name="require_email_verification" <?= $requireEmailVerification === 'y' ? 'checked' : '' ?>>
                    <label for="require_email_verification" style="margin-bottom: 0;">Require email verification?</label>
                </div>
            </div>
            
            <div class="form-group">
                <div class="checkbox-group">
                    <input type="checkbox" id="enable_2fa" name="enable_2fa" <?= $enable2fa === 'y' ? 'checked' : '' ?>>
                    <label for="enable_2fa" style="margin-bottom: 0;">Enable 2FA?</label>
                </div>
            </div>
            
            <div class="form-group">
                <label for="admin_email">Admin Email</label>
                <input type="email" id="admin_email" name="admin_email" value="<?= htmlspecialchars($adminEmail) ?>" required>
            </div>
            
            <?php if ($configExists): ?>
                <div class="form-group">
                    <div class="checkbox-group">
                        <input type="checkbox" id="overwrite" name="overwrite">
                        <label for="overwrite" style="margin-bottom: 0;">I understand this will overwrite config.php</label>
                    </div>
                </div>
            <?php endif; ?>
            
            <button type="submit" name="setup_submit">Complete Setup</button>
        </form>
        <?php endif; ?>
    </div>
</body>
</html>
    <?php
}

/**
 * CLI helper: Ask question and get input
 */
function ask($question, $default = '') {
    echo "$question";
    if ($default) {
        echo " [$default]";
    }
    echo ": ";
    
    $input = trim(fgets(STDIN));
    return $input ?: $default;
}

/**
 * Generate config.php file
 */
function generateConfigFile($data) {
    $configContent = <<<'PHP'
<?php
/**
 * Simple Auth Configuration
 * 
 * AUTO-GENERATED by setup.php
 * DO NOT commit this file to version control
 * Add to .gitignore: simple_auth/config.php
 */

return [
    // Storage configuration (CSV files)
    'storage' => [
        'data_dir' => __DIR__ . '/data',
    ],
    
    // Security settings
    'security' => [
        'password_algo' => PASSWORD_ARGON2ID,
        'password_options' => [
            'memory_cost' => 65536,  // 64 MB
            'time_cost' => 4,
            'threads' => 3,
        ],
        
        'session_name' => '{SESSION_NAME}',
        'session_lifetime' => 86400,
        'session_cookie_secure' => {HTTPS_SETTING},
        'session_cookie_httponly' => true,
        'session_cookie_samesite' => 'Strict',
        
        'csrf_token_name' => 'csrf_token',
        'csrf_token_length' => 32,
        
        'max_login_attempts' => 5,
        'lockout_duration' => 900,
        'rate_limit_window' => 900,
    ],
    
    // Application settings
    'app' => [
        'name' => '{PROJECT_NAME}',
        'base_url' => '{BASE_URL}',
        'require_email_verification' => {EMAIL_VERIFICATION},
        'enable_2fa' => {2FA_ENABLED},
        'admin_email' => '{ADMIN_EMAIL}',
    ],
    
    // Validation rules
    'validation' => [
        'username_min_length' => 3,
        'username_max_length' => 50,
        'password_min_length' => 8,
        'password_require_uppercase' => true,
        'password_require_lowercase' => true,
        'password_require_number' => true,
        'password_require_special' => true,
    ],
    
    // Logging
    'logging' => [
        'enabled' => true,
        'log_failed_logins' => true,
        'log_successful_logins' => true,
        'log_registrations' => true,
        'log_password_changes' => true,
    ],
];
PHP;

    $https = $data['use_https'] === 'y' ? 'true' : 'false';
    $emailVerification = $data['require_email_verification'] === 'y' ? 'true' : 'false';
    $twofa = $data['enable_2fa'] === 'y' ? 'true' : 'false';
    
    $configContent = str_replace(
        ['{SESSION_NAME}', '{PROJECT_NAME}', '{BASE_URL}', '{HTTPS_SETTING}', '{EMAIL_VERIFICATION}', '{2FA_ENABLED}', '{ADMIN_EMAIL}'],
        [$data['session_name'], $data['project_name'], $data['base_url'], $https, $emailVerification, $twofa, $data['admin_email']],
        $configContent
    );
    
    file_put_contents(__DIR__ . '/config.php', $configContent);
    chmod(__DIR__ . '/config.php', 0644);
}
?>
