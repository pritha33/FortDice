<?php
require_once 'session.php';

if (isLoggedIn()) {
    header('Location: ' . getUserType() . '/dashboard.php');
    exit;
}

$loginError = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>🏦 FortDice</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <link rel="stylesheet" href="css/style.css" />
</head>
<body>
    <div class="app-container">
        <section class="hero-section">
            <div class="hero-content">
                <div class="logo-container">
                    <i class="fas fa-shield-alt"></i>
                    <h1>FortDice</h1>
                </div>
                <p class="hero-tagline">Secure Banking · Cipher-Protected</p>
                <p class="hero-description">Experience military-grade classical ciphers</p>

                <?php if ($loginError): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($loginError); ?></div>
                <?php endif; ?>

                <div class="hero-buttons">
                    <a href="auth/login.php" class="btn btn-primary btn-large">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </a>
                    <a href="auth/register.php" class="btn btn-outline btn-large">
                        <i class="fas fa-user-plus"></i> Register
                    </a>
                </div>
                <div class="cipher-badges">
                    <span class="badge">Vigenère</span>
                    <span class="badge">Rail Fence</span>
                    <span class="badge">Columnar</span>
                    <span class="badge">Hill</span>
                </div>
            </div>
            <div class="hero-image">
                <div class="feature-cards">
                    <div class="feature-card">
                        <i class="fas fa-user-shield"></i>
                        <h4>Admin</h4>
                        <p>Full control, manage employees & customers</p>
                    </div>
                    <div class="feature-card">
                        <i class="fas fa-user-tie"></i>
                        <h4>Employee</h4>
                        <p>View & decrypt customer data with a security key</p>
                    </div>
                    <div class="feature-card">
                        <i class="fas fa-user"></i>
                        <h4>Customer</h4>
                        <p>Secure transfers & encrypted private notes</p>
                    </div>
                </div>
            </div>
        </section>
        <footer class="footer">
            <p>© 2026 FortDice · Computer Security Project</p>
            <p class="footer-sub">All private notes encrypted server-side using classical ciphers</p>
        </footer>
    </div>
    <script src="js/index.js"></script>
</body>
</html>
