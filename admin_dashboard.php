<?php
session_start();
include 'db.php';

// block user
if (!isset($_SESSION['is_admin'])) {
    header("Location: admin_login.php");
    exit();
}

// delete user
if (isset($_GET['delete_user'])) {
    $uid = intval($_GET['delete_user']);
    $conn->query("DELETE FROM users WHERE id = $uid");
    header("Location: admin_dashboard.php?tab=users");
    exit();
}

// order approval/rejection
if (isset($_GET['approve_order'])) {
    $oid = intval($_GET['approve_order']);
    $conn->query("UPDATE orders SET status = 'Approved' WHERE id = $oid");
    header("Location: admin_dashboard.php?tab=orders");
    exit();
}
if (isset($_GET['reject_order'])) {
    $oid = intval($_GET['reject_order']);
    $conn->query("UPDATE orders SET status = 'Rejected' WHERE id = $oid");
    header("Location: admin_dashboard.php?tab=orders");
    exit();
}

// Tab Navigation
$active_tab = $_GET['tab'] ?? 'orders';

// Fetch Users
$users = [];
$res = $conn->query("SELECT id, username, email, created_at FROM users ORDER BY created_at DESC");
if ($res) $users = $res->fetch_all(MYSQLI_ASSOC);

// Fetch Orders
$orders = [];
$res = $conn->query(
  "SELECT o.*, u.username, u.email FROM orders o
   LEFT JOIN users u ON o.user_id = u.id
   ORDER BY o.created_at DESC"
);
if ($res) $orders = $res->fetch_all(MYSQLI_ASSOC);

// Fetch Order Items
function getOrderItems($oid, $conn) {
    $items = [];
    $r = $conn->query("SELECT * FROM order_items WHERE order_id = $oid");
    if ($r) $items = $r->fetch_all(MYSQLI_ASSOC);
    return $items;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Dashboard</title>
    <meta charset="UTF-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        
:root {
  --main-green: #19A44B;
  --main-yellow: #FFE066;
  --main-brown: #EDE5CF;
  --main-green-dark: #137534;
  --main-brown-dark: #c9b795;
  --main-text-dark: #1A1A1A;
}

body {
    background-color: var(--main-brown) !important;
    color: var(--main-text-dark) !important;
}

/* Navbar */
.navbar {
    background-color: var(--main-green) !important;
    color: #fff !important;
}
.navbar-brand {
    color: var(--main-yellow) !important;
    font-weight: bold;
    letter-spacing: 1px;
}
.navbar-nav .nav-link {
    color: #fff !important;
    font-weight: 500;
}
.navbar-nav .nav-link:hover {
    color: var(--main-green) !important;
    background: var(--main-yellow);
    border-radius: 8px;
}

/* Buttons */
.btn-primary, .btn-success {
    background: var(--main-green) !important;
    border: none;
    color: #fff !important;
}
.btn-primary:hover, .btn-success:hover {
    background: var(--main-green-dark) !important;
    color: var(--main-yellow) !important;
}
.btn-warning {
    background: var(--main-yellow) !important;
    color: var(--main-green) !important;
    border: none;
}
.btn-warning:hover {
    background: #FFD54F !important;
    color: var(--main-green) !important;
}
.btn-danger {
    color: #fff !important;
}

/* Card */
.card {
    background: #fffbea !important;
    border-radius: 13px;
    border: 1px solid #D2B48C !important;
    color: var(--main-text-dark) !important;
}
.card-title, .product-card .card-title {
    color: var(--main-green) !important;
    font-weight: 600;
}
.card-text, .product-card .card-text {
    color: #5c5133 !important;
}

.card .btn {
    margin-top: 0.5rem;
}

/* Product images */
.product-card, .card {
    box-shadow: 0 4px 18px rgba(25, 164, 75, 0.06);
}

/* Table */
.table {
    background: #FFF8E1;
    color: var(--main-text-dark) !important;
}
.table thead {
    background: var(--main-yellow);
    color: var(--main-green) !important;
    font-weight: 700;
}

/* Alerts */
.alert-success {
    background: #d9ffdc;
    color: var(--main-green);
    border: 1px solid var(--main-green);
}
.alert-danger {
    background: #fff0d0;
    color: #be3200;
    border: 1px solid #be3200;
}
.alert-info {
    background: #e0eed3;
    color: var(--main-green-dark);
    border: 1px solid var(--main-green);
}

/* Forms */
.form-control, .form-select {
    background: #fffde3;
    border: 1px solid var(--main-yellow);
    color: var(--main-text-dark);
}

.form-label {
    color: var(--main-green-dark) !important;
    font-weight: 500;
}

/* Links */
a {
    color: var(--main-green);
}
a:hover {
    color: #86592d;
    text-decoration: underline;
}

/* Headings */
h1, h2, h3, h4, h5, h6 {
    color: var(--main-green-dark) !important;
    font-family: inherit;
    font-weight: 700;
    letter-spacing: 0.5px;
}

.text-muted {
    color: #857952 !important;
}
    </style>
</head>
<body class="bg-light">
    <div class="container mt-4">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h2>Admin Dashboard</h2>
            <div>
                <a href="index.php" class="btn btn-secondary btn-sm">Back to Shop</a>
                <a href="admin_logout.php" class="btn btn-danger btn-sm">Logout</a>
            </div>
        </div>

        <!-- Tabs -->
        <ul class="nav nav-tabs mb-4">
            <li class="nav-item">
                <a class="nav-link <?= $active_tab == 'orders' ? 'active' : '' ?>" href="?tab=orders">Orders & Approvals</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $active_tab == 'users' ? 'active' : '' ?>" href="?tab=users">Registered Users</a>
            </li>
        </ul>

        <?php if ($active_tab == 'users'): ?>
            <!-- Users List -->
            <div class="card">
                <div class="card-header">Signed Users</div>
                <div class="card-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>#ID</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Date Registered</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (count($users)): ?>
                            <?php foreach($users as $user): ?>
                                <tr>
                                    <td><?= $user['id']; ?></td>
                                    <td><?= htmlspecialchars($user['username']); ?></td>
                                    <td><?= htmlspecialchars($user['email']); ?></td>
                                    <td><?= $user['created_at']; ?></td>
                                    <td>
                                        <a href="?tab=users&delete_user=<?= $user['id'] ?>" class="btn btn-danger btn-sm"
                                           onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center">No users found.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($active_tab == 'orders'): ?>
        <!-- Orders and Approval -->
        <div class="card">
            <div class="card-header">All Orders (Track & Approve)</div>
            <div class="card-body">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>Order #ID</th>
                            <th>User</th>
                            <th>Email</th>
                            <th>Receiver</th>
                            <th>Address</th>
                            <th>Total</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th>Placed</th>
                            <th>Items</th>
                            <th>Approval Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($orders)): ?>
                            <?php foreach($orders as $order): ?>
                                <tr>
                                    <td><?= $order['id']; ?></td>
                                    <td><?= htmlspecialchars($order['username']); ?></td>
                                    <td><?= htmlspecialchars($order['email']); ?></td>
                                    <td><?= htmlspecialchars($order['name']); ?></td>
                                    <td style="max-width:150px;word-break:break-word;"><?= htmlspecialchars($order['address']); ?></td>
                                    <td>₱<?= number_format($order['total'],2); ?></td>
                                    <td><?= htmlspecialchars($order['payment_method']); ?></td>
                                    <td>
                                        <?php if ($order['status'] == "Pending"): ?>
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        <?php elseif ($order['status'] == "Approved"): ?>
                                            <span class="badge bg-success">Approved</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Rejected</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $order['created_at'] ?? '---'; ?></td>
                                    <td>
                                        <button class="btn btn-link btn-sm" data-bs-toggle="collapse" data-bs-target="#orderitems<?= $order['id']; ?>">Show</button>
                                        <div class="collapse" id="orderitems<?= $order['id']; ?>">
                                            <ul class="list-group list-group-flush">
                                            <?php foreach(getOrderItems($order['id'], $conn) as $oi): ?>
                                                <li class="list-group-item p-1"><b><?= htmlspecialchars($oi['name']); ?></b>
                                                (x<?= $oi['quantity']; ?>) — ₱<?= number_format($oi['price'],2); ?>
                                                </li>
                                            <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    </td>
                                    <td>
                                    <?php if ($order['status']=='Pending'): ?>
                                        <a href="?tab=orders&approve_order=<?= $order['id']; ?>" class="btn btn-success btn-sm mb-1"
                                           onclick="return confirm('Approve this order?')">Approve</a><br>
                                        <a href="?tab=orders&reject_order=<?= $order['id']; ?>" class="btn btn-danger btn-sm"
                                           onclick="return confirm('Reject this order?')">Reject</a>
                                    <?php elseif ($order['status']=='Rejected'): ?>
                                        <span class="text-danger small">Order rejected</span>
                                    <?php else: ?>
                                        <span class="text-success small">Order approved</span>
                                    <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="11" class="text-center text-muted">No orders have been placed yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>