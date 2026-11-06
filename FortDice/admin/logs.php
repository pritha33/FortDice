<?php
require_once '../session.php';
requireLogin(['admin']);

require_once '../database.php';
$db = new Database();
$conn = $db->getConnection();

$stmt = $conn->query("SELECT l.*, c.full_name, c.account_number FROM encryption_logs l 
                       JOIN customers c ON l.customer_id = c.id 
                       ORDER BY l.created_at DESC LIMIT 50");
$logs = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Encryption Logs</title>
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
            <a href="dashboard.php">
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
            <a href="logs.php" class="active">
                <span class="nav-icon">📋</span>
                <span>Encryption Logs</span>
            </a>
            <a href="../auth/logout.php" class="logout">
                <span class="nav-icon">🚪</span>
                <span>Logout</span>
            </a>
        </nav>
    </div>

    <div class="main-content">
        <header class="page-header">
            <div>
                <h1>📋 Encryption Logs</h1>
                <p>Monitor all encryption and decryption activities</p>
            </div>
        </header>

        <div class="panel">
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Account</th>
                            <th>Operation</th>
                            <th>Cipher</th>
                            <th>Key Used</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($logs)): ?>
                        <tr><td colspan="7" class="text-center">No logs found</td></tr>
                        <?php else: foreach ($logs as $log): ?>
                        <tr>
                            <td><?= htmlspecialchars($log['full_name']) ?></td>
                            <td><?= htmlspecialchars($log['account_number']) ?></td>
                            <td>
                                <span class="badge badge-<?= $log['operation'] ?>">
                                    <?= strtoupper($log['operation']) ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($log['cipher_type']) ?></td>
                            <td><code><?= htmlspecialchars($log['key_used'] ?? 'N/A') ?></code></td>
                            <td>
                                <span class="badge badge-<?= $log['status'] ?>">
                                    <?= $log['status'] ?>
                                </span>
                            </td>
                            <td><?= date('M d, Y H:i:s', strtotime($log['created_at'])) ?></td>
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