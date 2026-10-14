<?php
session_start();
include 'db.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id = $_GET['id'];

$stmt = $conn->prepare("SELECT * FROM items WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
$stmt->close();

if (!$product) {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
<body>
    <!-- nav -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php"><i class="fas fa-seedling"></i> Shop</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="cart.php"><i class="fas fa-shopping-cart"></i> Cart (<?php echo isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?>)</a>
                <?php if(isset($_SESSION['is_admin'])): ?>
                    <a class="nav-link" href="admin_logout.php">Logout (Admin)</a>
                <?php elseif(isset($_SESSION['user_id'])): ?>
                    <span class="nav-link">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <a class="nav-link" href="logout.php">Logout</a>
                <?php else: ?>
                    <a class="nav-link" href="login.php">Login</a>
                    <a class="nav-link" href="register.php">Register</a>
                    <a class="nav-link" href="admin_login.php">Admin</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
<!-- pic -->
    <div class="container mt-5">
        <a href="index.php" class="btn btn-secondary mb-3"><i class="fas fa-arrow-left"></i> Back to Shop</a>

        <div class="row">
            <div class="col-md-6">
                <div>
                    <?php if ($product['image']): ?>
                        <img src="uploads/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" style="width: 100%; max-height: 500px; object-fit: cover; border-radius: 5px;">
                    <?php else: ?>
                        <div class="bg-light p-5 rounded text-center" style="width: 100%; height: 500px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-image fa-5x text-muted"></i>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
<!-- card -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h1><?php echo htmlspecialchars($product['name']); ?></h1>
                        <p><b>Type:</b> <?php echo ucfirst($product['type']); ?></p>
                        <p><b>Season:</b> <?php echo htmlspecialchars($product['plant_season']); ?></p>
                        <p><b>Lifespan:</b> <?php echo htmlspecialchars($product['lifespan']); ?></p>
                        <p class="text-muted">Stock: <?php echo $product['quantity']; ?></p>
                        <h3 class="text-success mt-3">₱<?php echo number_format($product['price'], 2); ?></h3>
                        <hr>
                        <h5>Description:</h5>
                        <p><?php echo htmlspecialchars($product['description']); ?></p>
                        <hr>
                        <?php if(isset($_SESSION['user_id'])): ?>
                            <?php if($product['quantity'] > 0): ?>
                                <form action="add_to_cart.php" method="post">
                                    <form action="buy_now.php" method="post" style="display: inline;">
    <input type="hidden" name="item_id" value="<?php echo $product['id']; ?>">
    <input type="hidden" name="quantity" value="1">
    <button type="submit" class="btn btn-success"><i class="fas fa-bolt"></i> Buy Now</button>
</form>
                                    <div class="mb-3">
                                        <label for="quantity" class="form-label">Quantity:</label>
                                        <input type="number" id="quantity" name="quantity" class="form-control" style="width: 100px;" value="1" min="1" max="<?php echo $product['quantity']; ?>" required>
                                    </div>
                                    <input type="hidden" name="item_id" value="<?php echo $product['id']; ?>">
                                    <button type="submit" class="btn btn-success"><i class="fas fa-cart-plus"></i> Add to Cart</button>
                                </form>
                            <?php else: ?>
                                <div class="alert alert-danger">Out of Stock</div>
                            <?php endif; ?>
                        <?php elseif(isset($_SESSION['is_admin'])): ?>
                            <!-- block admin add to cart -->
                        <?php else: ?>
                            <a href="login.php" class="btn btn-primary"><i class="fas fa-lock"></i> Login to Buy</a>
                        <?php endif; ?>

                        <?php if(isset($_SESSION['is_admin'])): ?>
                            <hr>
                            <div>
                                <a href="index.php?edit=1&id=<?php echo $product['id']; ?>" class="btn btn-warning"><i class="fas fa-edit"></i> Edit</a>
                                <a href="delete.php?id=<?php echo $product['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure?')"><i class="fas fa-trash"></i> Delete</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>