<?php
require_once '../session.php';
requireLogin(['customer']);

require_once '../database.php';
$db = new Database();
$conn = $db->getConnection();

$customerId = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT * FROM transactions WHERE customer_id = ? ORDER BY created_at DESC");
$stmt->execute([$customerId]);
$transactions = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction History</title>
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
            <a href="transactions.php" class="active">
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
                <h1>📋 Transaction History</h1>
                <p>View all your transactions</p>
            </div>
        </header>

        <div class="panel">
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Transaction ID</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>From</th>
                            <th>To</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($transactions)): ?>
                        <tr><td colspan="8" class="text-center">No transactions found</td></tr>
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
                            <td><?= htmlspecialchars($tx['from_account'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($tx['to_account'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($tx['description'] ?? '-') ?></td>
                            <td><span class="badge badge-<?= $tx['status'] ?>"><?= $tx['status'] ?></span></td>
                            <td><?= date('M d, Y H:i:s', strtotime($tx['created_at'])) ?></td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>