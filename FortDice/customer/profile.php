<?php
require_once '../session.php';
requireLogin(['customer']);

require_once '../database.php';
$db = new Database();
$conn = $db->getConnection();

$customerId = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT * FROM customers WHERE id = ?");
$stmt->execute([$customerId]);
$customer = $stmt->fetch();

$stmt = $conn->prepare("SELECT * FROM encryption_logs WHERE customer_id = ? ORDER BY created_at DESC LIMIT 5");
$stmt->execute([$customerId]);
$logs = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>
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
            <a href="transfer.php">
                <span class="nav-icon">💸</span>
                <span>Transfer</span>
            </a>
            <a href="transactions.php">
                <span class="nav-icon">📋</span>
                <span>Transactions</span>
            </a>
            <a href="profile.php" class="active">
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
                <span class="user-avatar"><?= strtoupper(substr($customer['full_name'] ?? 'C', 0, 1)) ?></span>
                <div>
                    <strong><?= htmlspecialchars($customer['full_name'] ?? 'Customer') ?></strong>
                    <small>Customer</small>
                </div>
            </div>
        </div>
    </div>

    <div class="main-content">
        <header class="page-header">
            <div>
                <h1>👤 My Profile</h1>
                <p>View and manage your account details</p>
            </div>
        </header>

        <div class="panel">
            <div class="profile-card">
                <div>
                    <h3>Personal Information</h3>
                    <div class="profile-info">
                        <div class="label">Full Name</div>
                        <div class="value"><?= htmlspecialchars($customer['full_name'] ?? '') ?></div>
                    </div>
                    <div class="profile-info">
                        <div class="label">Email</div>
                        <div class="value"><?= htmlspecialchars($customer['email'] ?? '') ?></div>
                    </div>
                    <div class="profile-info">
                        <div class="label">Phone</div>
                        <div class="value"><?= htmlspecialchars($customer['phone'] ?? 'Not provided') ?></div>
                    </div>
                    <div class="profile-info">
                        <div class="label">Customer ID</div>
                        <div class="value"><?= htmlspecialchars($customer['customer_id'] ?? '') ?></div>
                    </div>
                    <div class="profile-info">
                        <div class="label">Account Number</div>
                        <div class="value"><?= htmlspecialchars($customer['account_number'] ?? '') ?></div>
                    </div>
                    <div class="profile-info">
                        <div class="label">Balance</div>
                        <div class="value positive">$<?= number_format($customer['balance'] ?? 0, 2) ?></div>
                    </div>
                    <div class="profile-info">
                        <div class="label">Status</div>
                        <div class="value">
                            <span class="badge badge-<?= $customer['status'] ?>"><?= $customer['status'] ?></span>
                        </div>
                    </div>
                </div>
                
                <div>
                    <h3>Encryption Settings</h3>
                    <div class="encryption-info-card">
                        <div class="label">Cipher Type</div>
                        <div><span class="cipher-badge"><?= strtoupper($customer['cipher_type'] ?? 'N/A') ?></span></div>
                        <br>
                        <div class="label">Cipher Key</div>
                        <div><code><?= htmlspecialchars($customer['cipher_key'] ?? 'N/A') ?></code></div>
                        <br>
                        <div class="label">Private Note</div>
                        <div class="value" style="font-style: italic;">
                            <?= htmlspecialchars($customer['private_note'] ?? 'No private note') ?>
                        </div>
                        <br>
                        <div class="label">Last Encryption Logs</div>
                        <?php if (empty($logs)): ?>
                            <div class="text-muted">No encryption logs found</div>
                        <?php else: foreach ($logs as $log): ?>
                            <div class="log-entry">
                                <span class="log-time"><?= date('Y-m-d H:i', strtotime($log['created_at'])) ?></span>
                                - <?= $log['operation'] ?> (<?= $log['cipher_type'] ?>)
                                <span class="log-status-<?= $log['status'] ?>"><?= $log['status'] ?></span>
                            </div>
                        <?php endforeach; endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>