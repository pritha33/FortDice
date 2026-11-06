<?php
require_once '../session.php';
requireLogin(['employee']);

require_once '../database.php';
$db = new Database();
$conn = $db->getConnection();

$employeeName = $_SESSION['full_name'] ?? 'Employee';
$employeeId = $_SESSION['employee_id'] ?? '';

// Get stats
$stmt = $conn->query("SELECT COUNT(*) as total FROM customers WHERE status = 'active'");
$stats['customers'] = $stmt->fetch()['total'];

$stmt = $conn->query("SELECT SUM(balance) as total FROM customers");
$stats['total_balance'] = $stmt->fetch()['total'] ?? 0;

$stmt = $conn->query("SELECT COUNT(*) as total FROM transactions");
$stats['transactions'] = $stmt->fetch()['total'];

$stmt = $conn->query("SELECT t.*, c.full_name, c.account_number FROM transactions t 
                       JOIN customers c ON t.customer_id = c.id 
                       ORDER BY t.created_at DESC LIMIT 10");
$transactions = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Dashboard</title>
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
            <a href="dashboard.php" class="active">
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
                    <strong><?= htmlspecialchars($employeeName) ?></strong>
                    <small>Employee</small>
                </div>
            </div>
        </div>
    </div>

    <div class="main-content">
        <header class="page-header">
            <div>
                <h1>Employee Dashboard</h1>
                <p>Welcome, <?= htmlspecialchars($employeeName) ?></p>
            </div>
            <div class="header-actions">
                <span class="security-badge">
                    <span class="lock-icon">🔐</span>
                    Security Key: <?= htmlspecialchars($_SESSION['security_key'] ?? 'N/A') ?>
                </span>
            </div>
        </header>

        <div class="employee-stats">
            <div class="employee-stat-card">
                <div class="stat-number"><?= number_format($stats['customers']) ?></div>
                <div class="stat-label">Total Customers</div>
            </div>
            <div class="employee-stat-card">
                <div class="stat-number">$<?= number_format($stats['total_balance'], 2) ?></div>
                <div class="stat-label">Total Balance</div>
            </div>
            <div class="employee-stat-card">
                <div class="stat-number"><?= number_format($stats['transactions']) ?></div>
                <div class="stat-label">Total Transactions</div>
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
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($transactions)): ?>
                        <tr><td colspan="6" class="text-center">No transactions found</td></tr>
                        <?php else: foreach ($transactions as $tx): ?>
                        <tr>
                            <td><span class="tx-id"><?= htmlspecialchars($tx['transaction_id']) ?></span></td>
                            <td><?= htmlspecialchars($tx['full_name']) ?></td>
                            <td class="<?= $tx['amount'] < 0 ? 'negative' : 'positive' ?>">
                                $<?= number_format(abs($tx['amount']), 2) ?>
                            </td>
                            <td><span class="badge badge-<?= $tx['transaction_type'] ?>"><?= $tx['transaction_type'] ?></span></td>
                            <td><span class="badge badge-<?= $tx['status'] ?>"><?= $tx['status'] ?></span></td>
                            <td><?= date('Y-m-d H:i', strtotime($tx['created_at'])) ?></td>
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