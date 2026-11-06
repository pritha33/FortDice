<?php
require_once '../session.php';
requireLogin(['employee']);

require_once '../database.php';
$db = new Database();
$conn = $db->getConnection();

$stmt = $conn->query("SELECT t.*, c.full_name, c.account_number FROM transactions t 
                       JOIN customers c ON t.customer_id = c.id 
                       ORDER BY t.created_at DESC");
$transactions = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Transactions</title>
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
            <a href="decrypt.php">
                <span class="nav-icon">🔐</span>
                <span>Decrypt Data</span>
            </a>
            <a href="transactions.php" class="active">
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
                <h1>💳 All Transactions</h1>
                <p>View all customer transactions</p>
            </div>
        </header>

        <div class="panel">
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Transaction ID</th>
                            <th>Customer</th>
                            <th>Account</th>
                            <th>Amount</th>
                            <th>Type</th>
                            <th>From</th>
                            <th>To</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($transactions)): ?>
                        <tr><td colspan="9" class="text-center">No transactions found</td></tr>
                        <?php else: foreach ($transactions as $tx): ?>
                        <tr>
                            <td><span class="tx-id"><?= htmlspecialchars($tx['transaction_id']) ?></span></td>
                            <td><?= htmlspecialchars($tx['full_name']) ?></td>
                            <td><?= htmlspecialchars($tx['account_number']) ?></td>
                            <td class="<?= $tx['amount'] < 0 ? 'negative' : 'positive' ?>">
                                $<?= number_format(abs($tx['amount']), 2) ?>
                            </td>
                            <td><span class="badge badge-<?= $tx['transaction_type'] ?>"><?= $tx['transaction_type'] ?></span></td>
                            <td><?= htmlspecialchars($tx['from_account'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($tx['to_account'] ?? '-') ?></td>
                            <td><span class="badge badge-<?= $tx['status'] ?>"><?= $tx['status'] ?></span></td>
                            <td><?= date('M d, Y H:i', strtotime($tx['created_at'])) ?></td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script src="../js/employee.js"></script>
</body>
</html>