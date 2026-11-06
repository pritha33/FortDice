<?php
require_once '../session.php';
requireLogin(['admin']);

require_once '../database.php';
$db = new Database();
$conn = $db->getConnection();

$message = '';
$messageType = '';

// Handle customer actions (status toggle / delete) posted by js/admin.js
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $customerId = $_POST['customer_id'] ?? 0;

    if ($action === 'toggle_status') {
        $status = $_POST['status'] ?? 'active';
        $stmt = $conn->prepare("UPDATE customers SET status = ? WHERE id = ?");
        $stmt->execute([$status, $customerId]);
        $message = 'Customer status updated';
        $messageType = 'success';
    } elseif ($action === 'delete') {
        $stmt = $conn->prepare("DELETE FROM customers WHERE id = ?");
        $stmt->execute([$customerId]);
        $message = 'Customer deleted';
        $messageType = 'success';
    }
}

$stmt = $conn->query("SELECT * FROM customers ORDER BY created_at DESC");
$customers = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Customers</title>
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
            <a href="customers.php" class="active">
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
    </div>

    <div class="main-content">
        <header class="page-header">
            <div>
                <h1>👥 Manage Customers</h1>
                <p>View, suspend, or remove customer accounts</p>
            </div>
        </header>

        <?php if ($message): ?>
        <div class="alert alert-<?= $messageType ?>"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <div class="panel">
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Customer ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Account</th>
                            <th>Balance</th>
                            <th>Cipher</th>
                            <th>Status</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($customers)): ?>
                        <tr><td colspan="9" class="text-center">No customers found</td></tr>
                        <?php else: foreach ($customers as $c): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($c['customer_id']) ?></strong></td>
                            <td><?= htmlspecialchars($c['full_name']) ?></td>
                            <td><?= htmlspecialchars($c['email']) ?></td>
                            <td><?= htmlspecialchars($c['account_number']) ?></td>
                            <td class="positive">$<?= number_format($c['balance'], 2) ?></td>
                            <td><span class="badge"><?= htmlspecialchars($c['cipher_type']) ?></span></td>
                            <td><span class="badge badge-<?= $c['status'] ?>"><?= $c['status'] ?></span></td>
                            <td><?= date('M d, Y', strtotime($c['created_at'])) ?></td>
                            <td class="action-buttons">
                                <button class="btn-sm btn-toggle" onclick="toggleCustomerStatus(<?= $c['id'] ?>, '<?= $c['status'] ?>')">
                                    <?= $c['status'] === 'active' ? '🔒' : '🔓' ?>
                                </button>
                                <button class="btn-sm btn-delete" onclick="deleteCustomer(<?= $c['id'] ?>)">🗑️</button>
                            </td>
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
