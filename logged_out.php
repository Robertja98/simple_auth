<?php
// Simple logged-out confirmation page
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Logged Out</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        .auth-container { max-width: 550px; margin: 80px auto; padding: 50px; background: white; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); text-align: center; }
        .auth-container h1 { margin-bottom: 12px; color: #1e1b1b; font-size: 2.2rem; letter-spacing: -0.02em; }
        .auth-container .subtitle { color: #6b6b6b; margin-bottom: 40px; font-size: 1.05rem; line-height: 1.6; }
        .auth-btn { min-width: 200px; padding: 16px 28px; border-radius: 8px; font-size: 1rem; font-weight: 600; text-decoration: none; transition: all 0.2s ease; cursor: pointer; border: 2px solid transparent; display: inline-flex; align-items: center; justify-content: center; background: #1f7a4f; color: white; }
        .auth-btn:hover { background: #175c3a; }
    </style>
</head>
<body>
    <div class="auth-container">
        <h1>Logged Out</h1>
        <p class="subtitle">You have been logged out successfully.</p>
        <a href="login.php" class="auth-btn">Return to Login</a>
    </div>
</body>
</html>