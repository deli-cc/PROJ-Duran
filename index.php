<?php
session_start();
include 'db.php';
// admin add
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_item'])) {
    if(!isset($_SESSION['is_admin'])) {
        header("Location: admin_login.php");
        exit();
    }
}
// add process 
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_item'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];
    $type = $_POST['type'];
    $plant_season = $_POST['plant_season'];
    $lifespan = $_POST['lifespan'];

    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["image"]["name"]);
        $check = getimagesize($_FILES["image"]["tmp_name"]);
        if ($check !== false) {
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                $image = basename($_FILES["image"]["name"]);
            }
        }
    }

    $stmt = $conn->prepare("INSERT INTO items (name, description, price, quantity, image, type, plant_season, lifespan) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdissss", $name, $description, $price, $quantity, $image, $type, $plant_season, $lifespan);
    $stmt->execute();
    $stmt->close();
    header("Location: index.php");
    exit();
}

// Get items / filter / search
$where = [];
$params = [];
$types = "";
if (isset($_GET['type']) && ($_GET['type'] === 'seed' || $_GET['type'] === 'seedling')) {
    $where[] = "type = ?";
    $params[] = $_GET['type'];
    $types .= "s";
}
if (!empty($_GET['search'])) {
    $where[] = "(name LIKE ? OR description LIKE ? OR plant_season LIKE ? OR lifespan LIKE ?)";
    $searchVal = '%' . $_GET['search'] . '%';
    $params = array_merge($params, [$searchVal, $searchVal, $searchVal, $searchVal]);
    $types .= "ssss";
}
$query = "SELECT * FROM items";
if ($where) {
    $query .= " WHERE " . implode(" AND ", $where);
}
$stmt = $conn->prepare($query);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$items = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop</title>
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
    #adminl{
        background-color: #c10e0e;
        border-radius: 5px;
        padding: 10px;
        margin-left: 10px;
        margin-right: 10px;
        margin-top: 4px;
        margin-bottom: 4px;
    }
    #register{
        background-color: #54cf1f;
        border-radius: 5px;
        padding: 10px;
        margin-left: 10px;
        margin-right: 10px;
        margin-top: 4px;
        margin-bottom: 4px;
    }
    #login{
        background-color: #1f74ad;
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
    .card {
        background: #fffbea !important;
        border-radius: 13px;
        border: 1px solid #D2B48C !important;
        color: var(--main-text-dark) !important;
    }
    .card-title {
        color: var(--main-green) !important;
        font-weight: 600;
    }
    .card-text {
        color: #5c5133 !important;
    }
    .form-label {
        color: var(--main-green-dark) !important;
        font-weight: 500;
    }
    .form-control, .form-select {
        background: #fffde3;
        border: 1px solid var(--main-yellow);
        color: var(--main-text-dark);
    }
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
    a {
        color: var(--main-green);
    }
    a:hover {
        color: #86592d;
        text-decoration: underline;
    }
    h2, h3, h4, h5 {
        color: var(--main-green-dark);
        font-weight: 700;
    }
    .text-muted {
        color: #857952 !important;
    }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="index.php"><i class="fas fa-seedling"></i> DURAN FARM</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="cart.php"><i class="fas fa-shopping-cart"></i> Cart (<?php echo isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?>)</a>
                <?php if(isset($_SESSION['is_admin'])): ?>
                    <a class="nav-link" href="admin_dashboard.php">Dashboard</a>
                    <a class="nav-link" id="adminl" href="admin_logout.php">Logout (Admin)</a>
                <?php elseif(isset($_SESSION['user_id'])): ?>
                    <?php if(!isset($_SESSION['is_admin'])): ?>
                        <a class="nav-link" href="account.php"><i class="fas fa-user"></i> Account</a>
                    <?php endif; ?>
                    <span class="nav-link">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <a class="nav-link" href="logout.php">Logout</a>
                <?php else: ?>
                    <a class="nav-link" id="register" href="register.php">Register</a>
                    <a class="nav-link" id="login" href="login.php">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['is_admin'])): ?>
            <div class="mb-3">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addItemModal"><i class="fas fa-plus"></i> Add New Item</button>
            </div>
        <?php endif; ?>

        <!-- SEARCH /FILTER -->
        <form method="get" class="mb-3 d-flex gap-2" style="max-width:600px;">
            <select name="type" class="form-select" style="max-width:140px;">
                <option value="">Show All</option>
                <option value="seed" <?php if(isset($_GET['type']) && $_GET['type']=='seed') echo 'selected'; ?>>Seeds</option>
                <option value="seedling" <?php if(isset($_GET['type']) && $_GET['type']=='seedling') echo 'selected'; ?>>Seedlings</option>
            </select>
            <input type="text" name="search" class="form-control" placeholder="Search plants..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
            <button type="submit" class="btn btn-primary">Search</button>
        </form>

        <?php if (!empty($_GET['search'])): ?>
            <div class="alert alert-info mb-3">
                Showing results for <strong><?php echo htmlspecialchars($_GET['search']); ?></strong>
            </div>
        <?php endif; ?>

        <div class="row">
            <?php foreach ($items as $item): ?>
            <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                <a href="view.php?id=<?php echo $item['id']; ?>" style="text-decoration: none; color: inherit;">
                    <div class="card h-100">
                        <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                            <?php if ($item['image']): ?>
                                <img src="uploads/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                            <?php else: ?>
                                <i class="fas fa-image fa-3x text-muted"></i>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($item['name']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars(substr($item['description'], 0, 100)); ?>...</p>
                            <p><b>Type:</b> <?php echo ucfirst($item['type']); ?></p>
                            <p><b>Season:</b> <?php echo htmlspecialchars($item['plant_season']); ?></p>
                            <p><b>Lifespan:</b> <?php echo htmlspecialchars($item['lifespan']); ?></p>
                            <p class="text-muted">Stock: <?php echo $item['quantity']; ?></p>
                            <p class="text-success fw-bold">₱<?php echo number_format($item['price'], 2); ?></p>
                            <?php if(isset($_SESSION['user_id'])): ?>
                                <form action="add_to_cart.php" method="post" class="mb-2">
                                    <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                    <div class="d-flex gap-2">
                                        <input type="number" name="quantity" value="1" min="1" max="<?php echo $item['quantity']; ?>" class="form-control form-control-sm" style="width: 60px;">
                                        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-cart-plus"></i> Add</button>
                                    </div>
                                </form>
                                <form action="buy_now.php" method="post" class="mb-2">
                                    <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-bolt"></i> Buy Now</button>
                                </form>
                            <?php elseif(isset($_SESSION['is_admin'])): ?>
                                <!-- admin block(add/buy)-->
                            <?php else: ?>
                                <a href="login.php" class="btn btn-primary btn-sm w-100"><i class="fas fa-lock"></i> Login to Buy</a>
                            <?php endif; ?>

                            <?php if (isset($_SESSION['is_admin'])): ?>
                                <div class="mt-2">
                                    <a href="edit.php?id=<?php echo $item['id']; ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i> Edit</a>
                                    <a href="delete.php?id=<?php echo $item['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')"><i class="fas fa-trash"></i> Delete</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Add Item Modal -->
<div class="modal fade" id="addItemModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="type" class="form-label">Plant Type <span class="text-danger">*</span></label>
                        <select class="form-select" id="type" name="type" required>
                            <option value="seed">Seed</option>
                            <option value="seedling">Seedling</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="plant_season" class="form-label">Plant Season</label>
                        <input type="text" class="form-control" id="plant_season" name="plant_season">
                    </div>
                    <div class="mb-3">
                        <label for="lifespan" class="form-label">Plant Lifespan</label>
                        <input type="text" class="form-control" id="lifespan" name="lifespan">
                    </div>
                    <div class="mb-3">
                        <label for="price" class="form-label">Price <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-control" id="price" name="price" required>
                    </div>
                    <div class="mb-3">
                        <label for="quantity" class="form-label">Quantity <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="quantity" name="quantity" required min="1">
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="image" class="form-label">Image</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="add_item" class="btn btn-primary">Add Item</button>
                </div>
            </form>
        </div>
    </div>
</div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        setTimeout(function() {
            var alertElement = document.querySelector('.alert');
            if (alertElement) {
                var bsAlert = new bootstrap.Alert(alertElement);
                bsAlert.close();
            }
        }, 3000);
    </script>
</body>
</html>