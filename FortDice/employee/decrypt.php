<?php
require_once '../session.php';
requireLogin(['employee']);

require_once '../database.php';
require_once '../vigenere.php';
require_once '../columnar.php';
require_once '../railfence.php';
require_once '../hill.php';

$db = new Database();
$conn = $db->getConnection();

$decryptedNote = '';
$customerInfo = '';
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'decrypt') {
    $accountNumber = sanitizeInput($_POST['account_number'] ?? '');
    $securityKey = sanitizeInput($_POST['security_key'] ?? '');
    
    $employeeId = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT security_key FROM employees WHERE id = ?");
    $stmt->execute([$employeeId]);
    $employee = $stmt->fetch();
    
    if ($employee && $employee['security_key'] === $securityKey) {
        $stmt = $conn->prepare("SELECT * FROM customers WHERE account_number = ? AND status = 'active'");
        $stmt->execute([$accountNumber]);
        $customer = $stmt->fetch();
        
        if ($customer) {
            $customerInfo = "Customer: " . $customer['full_name'] . " (" . $customer['account_number'] . ")";
            
            $cipherType = $customer['cipher_type'];
            $cipherKey = $customer['cipher_key'];
            $encryptedNote = $customer['private_note'] ?? '';
            
            // Decrypt based on cipher type
            try {
                $decryptedText = '';
                switch ($cipherType) {
                    case 'vigenere':
                        $decryptedText = decryptVigenere($encryptedNote, $cipherKey);
                        break;
                    case 'columnar':
                        $decryptedText = decryptColumnar($encryptedNote, $cipherKey);
                        break;
                    case 'railfence':
                        $decryptedText = decryptRailFence($encryptedNote, $cipherKey);
                        break;
                    case 'hill':
                        $decryptedText = decryptHill($encryptedNote, $cipherKey);
                        break;
                    default:
                        $decryptedText = $encryptedNote;
                }
                
                $decryptedNote = "🔐 Decrypted Note:\n" . $decryptedText;
                $decryptedNote .= "\n\n📊 Balance: $" . number_format($customer['balance'], 2);
                $decryptedNote .= "\n📋 Cipher: " . strtoupper($cipherType);
                
                // Log decryption
                $logStmt = $conn->prepare("INSERT INTO encryption_logs (customer_id, operation, cipher_type, key_used, status) 
                                            VALUES (?, 'decrypt', ?, ?, 'success')");
                $logStmt->execute([$customer['id'], $cipherType, $cipherKey]);
                
                $message = "Data decrypted successfully!";
                $messageType = 'success';
            } catch (Exception $e) {
                $message = "Decryption failed: " . $e->getMessage();
                $messageType = 'error';
            }
        } else {
            $message = "Customer not found";
            $messageType = 'error';
        }
    } else {
        $message = "Invalid security key";
        $messageType = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Decrypt Customer Data</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/employee.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-brand">
            <span class="brand-icon">🏦</span>
            <span>Secure Bank</span>
        </div>
        <nav class="sidebar-nav">
            <a href="dashboard.php">
                <span class="nav-icon">📊</span>
                <span>Dashboard</span>
            </a>
            <a href="customers.php">
                <span class="nav-icon">👥</span>
                <span>Customers</span>
            </a>
            <a href="decrypt.php" class="active">
                <span class="nav-icon">🔐</span>
                <span>Decrypt Data</span>
            </a>
            <a href="transactions.php">
                <span class="nav-icon">💳</span>
                <span>Transactions</span>
            </a>
            <a href="profile.php">
                <span class="nav-icon">👤</span>
                <span>Profile</span>
            </a>
            <a href="../auth/logout.php" class="logout">
                <span class="nav-icon">🚪</span>
                <span>Logout</span>
            </a>
        </nav>
        <div class="sidebar-footer">
            <div class="user-info">
                <span class="user-avatar">E</span>
                <div>
                    <strong><?= htmlspecialchars($_SESSION['full_name'] ?? 'Employee') ?></strong>
                    <small>Employee</small>
                </div>
            </div>
        </div>
    </div>

    <div class="main-content">
        <header class="page-header">
            <div>
                <h1>🔐 Decrypt Customer Data</h1>
                <p>Use your security key to decrypt customer information</p>
            </div>
        </header>

        <?php if ($message): ?>
        <div class="alert alert-<?= $messageType ?>"><?= $message ?></div>
        <?php endif; ?>

        <div class="panel decrypt-panel">
            <h2>Enter Decryption Credentials</h2>
            <form method="POST" class="decrypt-form">
                <input type="hidden" name="action" value="decrypt">
                <div class="form-group">
                    <label>Customer Account Number</label>
                    <input type="text" name="account_number" placeholder="e.g., ACC1001" required>
                    <small style="color: var(--gray-500);">Enter the customer's account number</small>
                </div>
                <div class="form-group">
                    <label>Your Security Key</label>
                    <input type="password" name="security_key" placeholder="Enter your security key" required>
                    <small style="color: var(--gray-500);">Your employee security key</small>
                </div>
                <button type="submit" class="btn-primary">🔓 Decrypt</button>
            </form>

            <?php if ($decryptedNote): ?>
            <div class="decrypt-result">
                <h3>Decryption Result</h3>
                <p><strong><?= $customerInfo ?></strong></p>
                <div class="decrypted-note"><?= nl2br(htmlspecialchars($decryptedNote)) ?></div>
                <p style="margin-top: 12px; color: var(--gray-500); font-size: 0.8rem;">
                    ⚠️ This data is only visible to authorized employees
                </p>
            </div>
            <?php endif; ?>
        </div>

        <div class="encryption-banner">
            <span>🛡️</span>
            <span>All decryption operations are logged for security purposes</span>
        </div>
    </div>
    <script src="../js/employee.js"></script>
</body>
</html>