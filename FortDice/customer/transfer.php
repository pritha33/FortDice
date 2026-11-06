<?php
require_once '../session.php';
requireLogin(['customer']);

require_once '../database.php';
$db = new Database();
$conn = $db->getConnection();

$customerId = $_SESSION['user_id'];
$accountNumber = $_SESSION['account_number'] ?? '';

$stmt = $conn->prepare("SELECT * FROM customers WHERE id = ?");
$stmt->execute([$customerId]);
$customer = $stmt->fetch();

$stmt = $conn->prepare("SELECT id, full_name, account_number FROM customers WHERE id != ? AND status = 'active'");
$stmt->execute([$customerId]);
$recipients = $stmt->fetchAll();

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $toAccount = sanitizeInput($_POST['to_account'] ?? '');
    $amount = floatval($_POST['amount'] ?? 0);
    $description = sanitizeInput($_POST['description'] ?? '');
    
    if ($amount <= 0) {
        $message = "Amount must be greater than 0";
        $messageType = 'error';
    } elseif ($amount > $customer['balance']) {
        $message = "Insufficient balance";
        $messageType = 'error';
    } else {
        $stmt = $conn->prepare("SELECT id, full_name FROM customers WHERE account_number = ? AND status = 'active'");
        $stmt->execute([$toAccount]);
        $recipient = $stmt->fetch();
        
        if (!$recipient) {
            $message = "Recipient account not found";
            $messageType = 'error';
        } elseif ($toAccount === $accountNumber) {
            $message = "Cannot transfer to yourself";
            $messageType = 'error';
        } else {
            try {
                $conn->beginTransaction();
                
                $stmt = $conn->prepare("UPDATE customers SET balance = balance - ? WHERE id = ?");
                $stmt->execute([$amount, $customerId]);
                
                $stmt = $conn->prepare("UPDATE customers SET balance = balance + ? WHERE id = ?");
                $stmt->execute([$amount, $recipient['id']]);
                
                $txId = generateTransactionId();
                $stmt = $conn->prepare("INSERT INTO transactions (transaction_id, customer_id, from_account, to_account, amount, transaction_type, description, status) 
                                        VALUES (?, ?, ?, ?, ?, 'transfer', ?, 'completed')");
                $stmt->execute([$txId, $customerId, $accountNumber, $toAccount, -$amount, $description]);
                
                $txId2 = generateTransactionId();
                $stmt = $conn->prepare("INSERT INTO transactions (transaction_id, customer_id, from_account, to_account, amount, transaction_type, description, status) 
                                        VALUES (?, ?, ?, ?, ?, 'credit', ?, 'completed')");
                $stmt->execute([$txId2, $recipient['id'], $accountNumber, $toAccount, $amount, $description]);
                
                $conn->commit();
                $message = "Transfer of $" . number_format($amount, 2) . " sent successfully!";
                $messageType = 'success';
                
                // Refresh balance
                $stmt = $conn->prepare("SELECT * FROM customers WHERE id = ?");
                $stmt->execute([$customerId]);
                $customer = $stmt->fetch();
            } catch (Exception $e) {
                $conn->rollBack();
                $message = "Transaction failed: " . $e->getMessage();
                $messageType = 'error';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transfer Money</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/customer.css">
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
            <a href="transfer.php" class="active">
                <span class="nav-icon">💸</span>
                <span>Transfer</span>
            </a>
            <a href="transactions.php">
                <span class="nav-icon">📋</span>
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
                <span class="user-avatar"><?= strtoupper(substr($_SESSION['full_name'] ?? 'C', 0, 1)) ?></span>
                <div>
                    <strong><?= htmlspecialchars($_SESSION['full_name'] ?? 'Customer') ?></strong>
                    <small>Customer</small>
                </div>
            </div>
        </div>
    </div>

    <div class="main-content">
        <header class="page-header">
            <div>
                <h1>💸 Transfer Money</h1>
                <p>Send money securely to other accounts</p>
            </div>
            <div class="balance-display">
                <span>Available Balance: </span>
                <strong>$<?= number_format($customer['balance'] ?? 0, 2) ?></strong>
            </div>
        </header>

        <?php if ($message): ?>
        <div class="alert alert-<?= $messageType ?>"><?= $message ?></div>
        <?php endif; ?>

        <div class="panel transfer-panel">
            <div class="transfer-container">
                <div class="transfer-form-wrapper">
                    <h2>Send Money</h2>
                    <form method="POST" class="transfer-form">
                        <div class="form-group">
                            <label>Recipient Account Number</label>
                            <input type="text" name="to_account" placeholder="Enter recipient account number" required>
                            <small>Enter the 6-digit account number of the recipient</small>
                        </div>
                        
                        <div class="form-group">
                            <label>Amount ($)</label>
                            <input type="number" name="amount" placeholder="0.00" step="0.01" min="0.01" required>
                            <small>Maximum: $<?= number_format($customer['balance'] ?? 0, 2) ?></small>
                        </div>
                        
                        <div class="form-group">
                            <label>Description (Optional)</label>
                            <input type="text" name="description" placeholder="What's this for?">
                        </div>
                        
                        <button type="submit" class="btn-primary">Send Money</button>
                    </form>
                </div>
                
                <div class="recipients-list">
                    <h3>Quick Recipients</h3>
                    <?php if (empty($recipients)): ?>
                        <p class="text-muted">No other customers found</p>
                    <?php else: foreach ($recipients as $r): ?>
                        <div class="recipient-card" onclick="setRecipient('<?= $r['account_number'] ?>')">
                            <span class="recipient-avatar"><?= strtoupper(substr($r['full_name'], 0, 1)) ?></span>
                            <div>
                                <div class="recipient-name"><?= htmlspecialchars($r['full_name']) ?></div>
                                <div class="recipient-account"><?= htmlspecialchars($r['account_number']) ?></div>
                            </div>
                        </div>
                    <?php endforeach; endif; ?>
                </div>
            </div>
        </div>

        <div class="encryption-banner">
            <span>🔐</span>
            <span>All transfers are encrypted using <?= $_SESSION['cipher_type'] ?? 'Vigenere' ?> cipher</span>
        </div>
    </div>

    <script>
        function setRecipient(account) {
            document.querySelector('input[name="to_account"]').value = account;
        }
    </script>
</body>
</html>