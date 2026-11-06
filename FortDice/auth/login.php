<?php
require_once '../session.php';
require_once '../database.php';

// If already logged in, send to the right dashboard
if (isLoggedIn()) {
    header('Location: ../' . getUserType() . '/dashboard.php');
    exit;
}

$db = new Database();
$conn = $db->getConnection();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = $_POST['role'] ?? '';
    $identifier = sanitizeInput($_POST['identifier'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($role) || empty($identifier) || empty($password)) {
        $error = 'Please fill all fields';
    } else {
        try {
            if ($role === 'admin') {
                $stmt = $conn->prepare("SELECT * FROM admins WHERE username = ?");
                $stmt->execute([$identifier]);
                $user = $stmt->fetch();

                if ($user && verifyPassword($password, $user['password_hash'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_type'] = 'admin';
                    $_SESSION['full_name'] = 'Admin';
                    $_SESSION['username'] = $user['username'];
                    header('Location: ../admin/dashboard.php');
                    exit;
                } else {
                    $error = 'Invalid admin credentials';
                }
            } elseif ($role === 'employee') {
                $stmt = $conn->prepare("SELECT * FROM employees WHERE email = ? AND status = 'active'");
                $stmt->execute([$identifier]);
                $user = $stmt->fetch();

                if ($user && verifyPassword($password, $user['password_hash'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_type'] = 'employee';
                    $_SESSION['full_name'] = $user['full_name'];
                    $_SESSION['employee_id'] = $user['employee_id'];
                    $_SESSION['security_key'] = $user['security_key'];
                    header('Location: ../employee/dashboard.php');
                    exit;
                } else {
                    $error = 'Invalid credentials or account inactive';
                }
            } elseif ($role === 'customer') {
                $stmt = $conn->prepare("SELECT * FROM customers WHERE email = ? AND status = 'active'");
                $stmt->execute([$identifier]);
                $user = $stmt->fetch();

                if ($user && verifyPassword($password, $user['password_hash'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_type'] = 'customer';
                    $_SESSION['full_name'] = $user['full_name'];
                    $_SESSION['account_number'] = $user['account_number'];
                    $_SESSION['cipher_type'] = $user['cipher_type'];
                    $_SESSION['cipher_key'] = $user['cipher_key'];
                    header('Location: ../customer/dashboard.php');
                    exit;
                } else {
                    $error = 'Invalid credentials or account inactive/suspended';
                }
            } else {
                $error = 'Please choose a role';
            }
        } catch (PDOException $e) {
            $error = 'Login failed. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login · Secure Bank</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <link rel="stylesheet" href="../css/style.css" />
</head>
<body>
    <div class="app-container">
        <div class="auth-screen">
            <div class="auth-box card">
                <div class="auth-header">
                    <a href="../index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back</a>
                    <h2><i class="fas fa-shield-alt" style="color:#1e3a8a;"></i> Welcome Back</h2>
                    <p class="sub">Login to your secure banking account</p>
                </div>
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <form method="POST" action="">
                    <div class="form-group">
                        <label>Role</label>
                        <select name="role" required>
                            <option value="customer">Customer</option>
                            <option value="employee">Employee</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Username / Email</label>
                        <input name="identifier" placeholder="Admin username or your email" required />
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input name="password" type="password" placeholder="Enter password" required />
                    </div>
                    <div class="auth-actions">
                        <button type="submit" class="btn btn-primary btn-full">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </button>
                    </div>
                    <div class="auth-footer">
                        <p>Don't have an account? <a href="register.php">Register here</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
