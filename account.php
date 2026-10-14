<?php
session_start();
include 'db.php';

// user page
if (!isset($_SESSION['user_id']) || isset($_SESSION['is_admin'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

//user info
$stmt = $conn->prepare("SELECT username, email, created_at FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// order list
$stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// order list
function get_order_items($conn, $order_id) {
    $stmt = $conn->prepare("SELECT name, price, quantity FROM order_items WHERE order_id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $items;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Account</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
    :root {
      --main-green: #19A44B;
      --main-yellow: #FFE066;
      --main-brown: #EDE5CF;
      --main-green-dark: #137534;
      --main-text-dark: #1A1A1A;
    }
    body {
        background-color: var(--main-brown) !important;
        color: var(--main-text-dark) !important;
    }
    #logout{
        background-color: #f02d2d;
        border-radius: 5px;
        padding: 10px;
        margin-left: 10px;
        margin-right: 10px;
        margin-top: 4px;
        margin-bottom: 4px;
    }
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
    .card {
        background: #fffbea !important;
        border-radius: 13px;
        border: 1px solid #D2B48C !important;
        color: var(--main-text-dark) !important;
    }
    .table th, .table td {
        vertical-align: middle;
    }
    .badge {
        font-size: 1em;
    }
    </style>
</head>
<body>
    <!-- nav -->
    <nav class="navbar navbar-expand-lg mb-3">
        <div class="container">
            <a class="navbar-brand" href="index.php">Shop</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="cart.php"><i class="fas fa-shopping-cart"></i> Cart (<?php echo isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?>)</a>
                <a class="nav-link" href="index.php">Home</a>
                <a class="nav-link" id="logout" href="logout.php">Logout</a>
            </div>
        </div>
    </nav>
    
    <div class="container mt-4" style="max-width:800px;">
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <h2><i class="fas fa-user-circle"></i> My Account</h2>

        <div class="row mt-4">
            <div class="col-md-4 mb-3">
                <div class="card p-3 h-100">
                    <h5 class="mb-3 text-success"><i class="fas fa-id-badge"></i> Profile Details</h5>
                    <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                    <p><strong>Registered:</strong> <?php echo htmlspecialchars($user['created_at']); ?></p>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card p-3 h-100">
                    <h5 class="mb-3 text-success"><i class="fas fa-box"></i> My Orders</h5>
                    <?php if (count($orders)): ?>
                        <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>#Order</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Total</th>
                                    <th>Payment</th>
                                    <th>Items</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($orders as $order): ?>
                                    <tr>
                                        <td><?php echo $order['id']; ?></td>
                                        <td><?php echo htmlspecialchars($order['created_at']); ?></td>
                                        <td>
                                            <?php if ($order['status']=="Approved"): ?>
                                                <span class="badge bg-success">Approved</span>
                                            <?php elseif ($order['status']=="Rejected"): ?>
                                                <span class="badge bg-danger">Rejected</span>
                                            <?php elseif ($order['status']=="Cancelled"): ?>
                                                <span class="badge bg-secondary">Cancelled</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning text-dark">Pending</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>₱<?php echo number_format($order['total'], 2); ?></td>
                                        <td><?php echo htmlspecialchars($order['payment_method']); ?></td>
                                        <td>
                                            <ul class="mb-0">
                                                <?php foreach(get_order_items($conn, $order['id']) as $oi): ?>
                                                <li>
                                                    <?php echo htmlspecialchars($oi['name']); ?> (x<?php echo $oi['quantity']; ?>)
                                                    <span class="text-muted small">₱<?php echo number_format($oi['price'],2); ?></span>
                                                </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </td>
                                        <td>
                                            <?php if($order['status']=="Pending"): ?>
                                                <a href="cancel_order.php?id=<?php echo $order['id']; ?>"
                                                    class="btn btn-danger btn-sm"
                                                    onclick="return confirm('Are you sure you want to cancel this order?');">
                                                    Cancel
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">You have not placed any orders yet.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>