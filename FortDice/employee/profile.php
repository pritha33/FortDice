<?php
require_once '../session.php';
requireLogin(['employee']);

require_once '../database.php';
$db = new Database();
$conn = $db->getConnection();

$employeeId = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM employees WHERE id = ?");
$stmt->execute([$employeeId]);
$employee = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Profile</title>
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
            <a href="transactions.php">
                <span class="nav-icon">💳</span>
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
                <span class="user-avatar">E</span>
                <div>
                    <strong><?= htmlspecialchars($employee['full_name'] ?? 'Employee') ?></strong>
                    <small>Employee</small>
                </div>
            </div>
        </div>
    </div>

    <div class="main-content">
        <header class="page-header">
            <div>
                <h1>👤 Employee Profile</h1>
                <p>View your employee details</p>
            </div>
        </header>

        <div class="panel">
            <div class="profile-card">
                <div>
                    <h3>Personal Information</h3>
                    <div class="profile-info">
                        <div class="label">Employee ID</div>
                        <div class="value"><?= htmlspecialchars($employee['employee_id'] ?? '') ?></div>
                    </div>
                    <div class="profile-info">
                        <div class="label">Full Name</div>
                        <div class="value"><?= htmlspecialchars($employee['full_name'] ?? '') ?></div>
                    </div>
                    <div class="profile-info">
                        <div class="label">Email</div>
                        <div class="value"><?= htmlspecialchars($employee['email'] ?? '') ?></div>
                    </div>
                    <div class="profile-info">
                        <div class="label">Status</div>
                        <div class="value">
                            <span class="badge badge-<?= $employee['status'] ?>"><?= $employee['status'] ?></span>
                        </div>
                    </div>
                    <div class="profile-info">
                        <div class="label">Joined</div>
                        <div class="value"><?= date('F d, Y', strtotime($employee['created_at'])) ?></div>
                    </div>
                </div>
                
                <div>
                    <h3>Security Information</h3>
                    <div class="encryption-info-card">
                        <div class="label">Security Key</div>
                        <div><code><?= htmlspecialchars($employee['security_key'] ?? 'N/A') ?></code></div>
                        <br>
                        <div style="background: #fef3c7; padding: 12px; border-radius: 8px; border-left: 4px solid #f59e0b;">
                            <strong>⚠️ Important:</strong>
                            <p style="font-size: 0.9rem; margin-top: 4px; color: #92400e;">
                                Keep your security key confidential. It is required to decrypt customer data.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="../js/employee.js"></script>
</body>
</html>