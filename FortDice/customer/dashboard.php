<?php
require_once '../session.php';
requireLogin(['customer']);

require_once '../database.php';
$db = new Database();
$conn = $db->getConnection();

$customerId = $_SESSION['user_id'];
$customerName = $_SESSION['full_name'] ?? 'Customer';
$accountNumber = $_SESSION['account_number'] ?? '';

$stmt = $conn->prepare("SELECT * FROM customers WHERE id = ?");
$stmt->execute([$customerId]);
$customer = $stmt->fetch();

$stmt = $conn->prepare("SELECT * FROM transactions WHERE customer_id = ? ORDER BY created_at DESC LIMIT 10");
$stmt->execute([$customerId]);
$transactions = $stmt->fetchAll();

// Handle transfer
$message = '';
$messageType = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'transfer') {
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
                    
                    // Refresh data
                    $stmt = $conn->prepare("SELECT * FROM customers WHERE id = ?");
                    $stmt->execute([$customerId]);
                    $customer = $stmt->fetch();
                    
                    $stmt = $conn->prepare("SELECT * FROM transactions WHERE customer_id = ? ORDER BY created_at DESC LIMIT 10");
                    $stmt->execute([$customerId]);
                    $transactions = $stmt->fetchAll();
                } catch (Exception $e) {
                    $conn->rollBack();
                    $message = "Transaction failed: " . $e->getMessage();
                    $messageType = 'error';
                }
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
    <title>Customer Dashboard</title>
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
            <a href="dashboard.php" class="active">
                <span class="nav-icon">📊</span>
                <span>Dashboard</span>
            </a>
            <a href="transfer.php">
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
                <span class="user-avatar"><?= strtoupper(substr($customerName, 0, 1)) ?></span>
                <div>
                    <strong><?= htmlspecialchars($customerName) ?></strong>
                    <small>Customer</small>
                </div>
            </div>
        </div>
    </div>

    <div class="main-content">
        <header class="page-header">
            <div>
                <h1>Welcome, <?= htmlspecialchars($customerName) ?></h1>
                <p>Account: <?= htmlspecialchars($accountNumber) ?></p>
            </div>
            <div class="header-actions">
                <button onclick="location.reload()" class="btn-secondary">🔄 Refresh</button>
            </div>
        </header>

        <?php if ($message): ?>
        <div class="alert alert-<?= $messageType ?>"><?= $message ?></div>
        <?php endif; ?>

        <div class="account-summary">
            <div class="balance-card">
                <div class="balance-label">Current Balance</div>
                <div class="balance-amount">$<?= number_format($customer['balance'] ?? 0, 2) ?></div>
                <div class="balance-sub">Encrypted with <?= $_SESSION['cipher_type'] ?> cipher</div>
            </div>
            
            <div class="quick-actions">
                <div class="action-card" onclick="document.getElementById('transferModal').classList.add('active')">
                    <span class="action-icon">💸</span>
                    <span>Transfer</span>
                </div>
                <div class="action-card" onclick="window.location.href='transactions.php'">
                    <span class="action-icon">📋</span>
                    <span>History</span>
                </div>
                <div class="action-card" onclick="window.location.href='profile.php'">
                    <span class="action-icon">🔐</span>
                    <span>Security</span>
                </div>
            </div>
        </div>

        <div class="panel">
            <div class="panel-header">
                <h2>Recent Transactions</h2>
                <a href="transactions.php" class="btn-link">View All →</a>
            </div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Transaction ID</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($transactions)): ?>
                        <tr><td colspan="6" class="text-center">No transactions yet</td></tr>
                        <?php else: foreach ($transactions as $tx): ?>
                        <tr>
                            <td><span class="tx-id"><?= htmlspecialchars($tx['transaction_id']) ?></span></td>
                            <td>
                                <span class="badge badge-<?= $tx['transaction_type'] ?>">
                                    <?= strtoupper($tx['transaction_type']) ?>
                                </span>
                            </td>
                            <td class="<?= $tx['amount'] < 0 ? 'negative' : 'positive' ?>">
                                $<?= number_format(abs($tx['amount']), 2) ?>
                            </td>
                            <td><?= htmlspecialchars($tx['description'] ?? '-') ?></td>
                            <td><span class="badge badge-<?= $tx['status'] ?>"><?= $tx['status'] ?></span></td>
                            <td><?= date('M d, Y H:i', strtotime($tx['created_at'])) ?></td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Transfer Modal -->
    <div id="transferModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>💸 Transfer Money</h2>
                <button class="modal-close" onclick="document.getElementById('transferModal').classList.remove('active')">×</button>
            </div>
            <form method="POST" class="transfer-form">
                <input type="hidden" name="action" value="transfer">
                <div class="form-group">
                    <label>Recipient Account Number</label>
                    <input type="text" name="to_account" placeholder="Enter recipient account number" required>
                </div>
                <div class="form-group">
                    <label>Amount ($)</label>
                    <input type="number" name="amount" placeholder="0.00" step="0.01" min="0.01" required>
                </div>
                <div class="form-group">
                    <label>Description (Optional)</label>
                    <input type="text" name="description" placeholder="Transfer description">
                </div>
                <button type="submit" class="btn-primary">Send Money</button>
            </form>
        </div>
    </div>

    <script>
        // Close modal on outside click
        document.getElementById('transferModal').addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.remove('active');
            }
        });
    </script>
</body>
</html>