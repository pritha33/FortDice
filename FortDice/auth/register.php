<?php
require_once '../session.php';
require_once '../database.php';

if (isLoggedIn()) {
    header('Location: ../' . getUserType() . '/dashboard.php');
    exit;
}

$db = new Database();
$conn = $db->getConnection();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = sanitizeInput($_POST['full_name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $cipherType = $_POST['cipher_type'] ?? 'vigenere';
    $cipherKey = sanitizeInput($_POST['cipher_key'] ?? '');

    if (empty($fullName) || empty($email) || empty($phone) || empty($password) || empty($confirmPassword) || empty($cipherKey)) {
        $error = 'Please fill all fields';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } elseif (!in_array($cipherType, ['vigenere', 'columnar', 'railfence', 'hill'])) {
        $error = 'Invalid cipher type';
    } else {
        try {
            $stmt = $conn->prepare("SELECT id FROM customers WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = 'Email already registered';
            } else {
                $customerId = 'CUST' . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
                $accountNumber = 'ACC' . rand(100000, 999999);
                $passwordHash = hashPassword($password);

                $stmt = $conn->prepare(
                    "INSERT INTO customers (customer_id, full_name, email, phone, password_hash, account_number, balance, cipher_type, cipher_key, private_note, status)
                     VALUES (?, ?, ?, ?, ?, ?, 0.00, ?, ?, ?, 'active')"
                );
                $stmt->execute([$customerId, $fullName, $email, $phone, $passwordHash, $accountNumber, $cipherType, $cipherKey, 'Welcome to Secure Bank!']);

                $success = 'Registration successful! Your account number is ' . $accountNumber . '. Redirecting to login...';
            }
        } catch (PDOException $e) {
            $error = 'Registration failed. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Register · Secure Bank</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <link rel="stylesheet" href="../css/style.css" />
</head>
<body>
    <div class="app-container">
        <div class="auth-screen">
            <div class="auth-box card">
                <div class="auth-header">
                    <a href="../index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back</a>
                    <h2><i class="fas fa-user-plus" style="color:#1e3a8a;"></i> Create Account</h2>
                    <p class="sub">Register as a new customer</p>
                </div>
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                    <meta http-equiv="refresh" content="2;url=login.php">
                <?php endif; ?>
                <form method="POST" action="">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input name="full_name" placeholder="Enter your full name" required />
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input name="email" type="email" placeholder="Enter your email" required />
                    </div>
                    <div class="form-group">
                        <label>Phone</label>
                        <input name="phone" placeholder="Enter phone number" required />
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input name="password" type="password" placeholder="Create a password (min 6 chars)" required />
                    </div>
                    <div class="form-group">
                        <label>Confirm Password</label>
                        <input name="confirm_password" type="password" placeholder="Confirm your password" required />
                    </div>
                    <div class="form-group">
                        <label>Cipher Type</label>
                        <select name="cipher_type" required>
                            <option value="vigenere">Vigenère</option>
                            <option value="columnar">Columnar</option>
                            <option value="railfence">Rail Fence</option>
                            <option value="hill">Hill</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Cipher Key</label>
                        <input name="cipher_key" placeholder="Choose a cipher key" required />
                        <small>Used to encrypt/decrypt your private note</small>
                    </div>
                    <div class="auth-actions">
                        <button type="submit" class="btn btn-primary btn-full">
                            <i class="fas fa-user-plus"></i> Register
                        </button>
                    </div>
                    <div class="auth-footer">
                        <p>Already have an account? <a href="login.php">Login here</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
