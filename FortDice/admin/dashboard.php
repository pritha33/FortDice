<?php
require_once '../session.php';
requireLogin(['admin']);

require_once '../database.php';
$db = new Database();
$conn = $db->getConnection();

$adminName = $_SESSION['username'] ?? 'Admin';

// Get stats
$stats = [];
$stmt = $conn->query("SELECT COUNT(*) as total FROM customers WHERE status = 'active'");
$stats['customers'] = $stmt->fetch()['total'];

$stmt = $conn->query("SELECT COUNT(*) as total FROM employees WHERE status = 'active'");
$stats['employees'] = $stmt->fetch()['total'];

$stmt = $conn->query("SELECT SUM(balance) as total FROM customers");
$stats['total_balance'] = $stmt->fetch()['total'] ?? 0;

$stmt = $conn->query("SELECT COUNT(*) as total FROM transactions");
$stats['transactions'] = $stmt->fetch()['total'];

$stmt = $conn->query("SELECT t.*, c.full_name, c.account_number FROM transactions t 
                       JOIN customers c ON t.customer_id = c.id 
                       ORDER BY t.created_at DESC LIMIT 10");
$recentTransactions = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../css/style.css">
   <link rel="stylesheet" href="../css/dashboard.css">
   <link rel="stylesheet" href="../css/admin.css">
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
            <a href="employees.php">
                <span class="nav-icon">👤</span>
                <span>Employees</span>
            </a>
            <a href="transactions.php">
                <span class="nav-icon">💳</span>
                <span>Transactions</span>
            </a>
            <a href="logs.php">
                <span class="nav-icon">📋</span>
                <span>Encryption Logs</span>
            </a>
            <a href="../auth/logout.php" class="logout">
                <span class="nav-icon">🚪</span>
                <span>Logout</span>
            </a>
        </nav>
        <div class="sidebar-footer">
            <div class="user-info">
                <span class="user-avatar">A</span>
                <div>
                    <strong><?= htmlspecialchars($adminName) ?></strong>
                    <small>Administrator</small>
                </div>
            </div>
        </div>
    </div>

    <div class="main-content">
        <header class="page-header">
            <div>
                <h1>Dashboard</h1>
                <p>Welcome back, <?= htmlspecialchars($adminName) ?></p>
            </div>
            <div class="header-actions">
                <button onclick="location.reload()" class="btn-secondary">🔄 Refresh</button>
            </div>
        </header>

        <div class="stats-grid">
            <div class="stat-card primary">
                <div class="stat-icon">👥</div>
                <div class="stat-info">
                    <h3><?= number_format($stats['customers']) ?></h3>
                    <p>Active Customers</p>
                </div>
            </div>
            <div class="stat-card success">
                <div class="stat-icon">👤</div>
                <div class="stat-info">
                    <h3><?= number_format($stats['employees']) ?></h3>
                    <p>Active Employees</p>
                </div>
            </div>
            <div class="stat-card warning">
                <div class="stat-icon">💰</div>
                <div class="stat-info">
                    <h3>$<?= number_format($stats['total_balance'], 2) ?></h3>
                    <p>Total Balance</p>
                </div>
            </div>
            <div class="stat-card info">
                <div class="stat-icon">💳</div>
                <div class="stat-info">
                    <h3><?= number_format($stats['transactions']) ?></h3>
                    <p>Total Transactions</p>
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
                            <th>Customer</th>
                            <th>Account</th>
                            <th>Amount</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentTransactions)): ?>
                        <tr><td colspan="7" class="text-center">No transactions found</td></tr>
                        <?php else: foreach ($recentTransactions as $tx): ?>
                        <tr>
                            <td><span class="tx-id"><?= htmlspecialchars($tx['transaction_id']) ?></span></td>
                            <td><?= htmlspecialchars($tx['full_name']) ?></td>
                            <td><?= htmlspecialchars($tx['account_number']) ?></td>
                            <td class="<?= $tx['amount'] < 0 ? 'negative' : 'positive' ?>">
                                $<?= number_format($tx['amount'], 2) ?>
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
    <script src="../js/admin.js"></script>
</body>
</html>