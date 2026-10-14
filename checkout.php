<?php
session_start();
include 'db.php';

// Only allow logged-in users (NOT admin)
if (!isset($_SESSION['user_id'])) {
    $_SESSION['message'] = "Please login as a user before checking out!";
    header("Location: login.php");
    exit();
}
// Prevent admin from ordering
if (isset($_SESSION['is_admin'])) {
    $_SESSION['message'] = "Admins cannot place orders!";
    header("Location: index.php");
    exit();
}

// Get cart or warn if empty
$cart_items = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
if (empty($cart_items)) {
    $_SESSION['message'] = "Your cart is empty!";
    header("Location: cart.php");
    exit();
}

$error = "";
$success = "";

// Calculate order total
$total = 0;
foreach ($cart_items as $item) {
    $total += $item['price'] * $item['quantity'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form values
    $name = trim($_POST['name'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $payment_method = $_POST['payment_method'] ?? '';

    if (!$name || !$address || !$payment_method) {
        $error = "All fields are required!";
    } else {
        // Check stock for each item
        $out_of_stock = [];
        foreach ($cart_items as $item) {
            $stmt = $conn->prepare("SELECT quantity FROM items WHERE id = ?");
            $stmt->bind_param("i", $item['id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $db_item = $result->fetch_assoc();
            $stmt->close();
            if (!$db_item || $db_item['quantity'] < $item['quantity']) {
                $out_of_stock[] = $item['name'];
            }
        }
        if (count($out_of_stock) > 0) {
            $error = "Insufficient stock for: " . implode(', ', $out_of_stock);
        } else {
            // Insert into orders
           // Insert into orders (set 'Pending' status)
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$stmt = $conn->prepare("INSERT INTO orders (user_id, name, address, payment_method, total, status) VALUES (?, ?, ?, ?, ?, 'Pending')");
$stmt->bind_param("isssd", $user_id, $name, $address, $payment_method, $total);
$stmt->execute();
$order_id = $stmt->insert_id;
$stmt->close();

            // Insert order items and deduct stock
            foreach ($cart_items as $item) {
                $stmt = $conn->prepare("INSERT INTO order_items (order_id, item_id, name, price, quantity) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("iisdi", $order_id, $item['id'], $item['name'], $item['price'], $item['quantity']);
                $stmt->execute();
                $stmt->close();

                $stmt = $conn->prepare("UPDATE items SET quantity = quantity - ? WHERE id = ?");
                $stmt->bind_param("ii", $item['quantity'], $item['id']);
                $stmt->execute();
                $stmt->close();
            }
            unset($_SESSION['cart']);
            $success = "Order placed!<br>
            Name: " . htmlspecialchars($name) . "<br>
            Address: " . nl2br(htmlspecialchars($address)) . "<br>
            Payment Method: " . htmlspecialchars($payment_method) . "<br>
            <b>Total Paid: ₱" . number_format($total, 2) . "</b>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Checkout</title>
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
    <div class="container mt-4" style="max-width: 600px;">
        <h2>Checkout</h2>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
            <a href="index.php" class="btn btn-primary">Back to Shop</a>
        <?php else: ?>
            <form method="post">
                <div class="mb-3">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="name" class="form-control" required autofocus>
                </div>
                <div class="mb-3">
                    <label class="form-label">Shipping Address</label>
                    <textarea name="address" class="form-control" rows="3" required></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Payment Method</label>
                    <select name="payment_method" class="form-select" required>
                        <option value="">Select...</option>
                        <option value="Cash on Delivery">Cash on Delivery</option>
                        <option value="Credit Card">Credit Card</option>
                        <option value="GCash">GCash</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Order Total</label>
                    <div class="form-control-plaintext fs-5 text-success">
                        ₱<?php echo number_format($total, 2); ?>
                    </div>
                </div>
                <div class="mb-3 text-end">
                    <button type="submit" class="btn btn-success">Place Order</button>
                    <a href="cart.php" class="btn btn-secondary">Back to Cart</a>
                </div>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>