<?php
include 'db.php';
session_start();

// Allow only admin
if(!isset($_SESSION['is_admin'])) {
    header("Location: admin_login.php");
    exit();
}

// Check for item ID
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id = $_GET['id'];

// Fetch item details
$stmt = $conn->prepare("SELECT * FROM items WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$item = $result->fetch_assoc();
$stmt->close();

if (!$item) {
    header("Location: index.php");
    exit();
}

$error = "";
$success = "";

// Handle submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];
    $type = $_POST['type'];
    $plant_season = trim($_POST['plant_season']);
    $lifespan = trim($_POST['lifespan']);

    // Handle image
    $image = $item['image'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["image"]["name"]);
        $check = getimagesize($_FILES["image"]["tmp_name"]);
        if($check !== false) {
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                $image = basename($_FILES["image"]["name"]);
            }
        }
    }

    // Validate
    if($name==="" || $price==="" || $quantity==="") {
        $error = "Please fill in all required fields.";
    } else {
        $stmt = $conn->prepare("UPDATE items SET name=?, description=?, price=?, quantity=?, image=?, type=?, plant_season=?, lifespan=? WHERE id=?");
        $stmt->bind_param("ssdissssi", $name, $description, $price, $quantity, $image, $type, $plant_season, $lifespan, $id);
        if($stmt->execute()) {
            $success = "Item updated successfully!";
            // Refresh item details
            $item['name'] = $name;
            $item['description'] = $description;
            $item['price'] = $price;
            $item['quantity'] = $quantity;
            $item['type'] = $type;
            $item['plant_season'] = $plant_season;
            $item['lifespan'] = $lifespan;
            $item['image'] = $image;
        } else {
            $error = "Failed to update item. Try again.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Item</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
    a {
        color: var(--main-green);
    }
    a:hover {
        color: #86592d;
        text-decoration: underline;
    }
    h2 {
        color: var(--main-green-dark);
        font-weight: 700;
    }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg mb-3">
        <div class="container">
            <a class="navbar-brand" href="index.php"><i class="fas fa-gem"></i> Shop Admin</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="index.php">Back to Shop</a>
                <a class="nav-link" href="admin_logout.php">Logout</a>
            </div>
        </div>
    </nav>
    <div class="container mt-4" style="max-width: 600px;">
        <h2>Edit Item</h2>
        <div class="card mt-3">
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                <form method="post" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="name" class="form-label">Name<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" required value="<?php echo htmlspecialchars($item['name']); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="type" class="form-label">Plant Type<span class="text-danger">*</span></label>
                        <select class="form-select" id="type" name="type" required>
                            <option value="seed" <?php if($item['type']=='seed') echo 'selected'; ?>>Seed</option>
                            <option value="seedling" <?php if($item['type']=='seedling') echo 'selected'; ?>>Seedling</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="plant_season" class="form-label">Plant Season</label>
                        <input type="text" class="form-control" id="plant_season" name="plant_season" value="<?php echo htmlspecialchars($item['plant_season']); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="lifespan" class="form-label">Plant Lifespan</label>
                        <input type="text" class="form-control" id="lifespan" name="lifespan" value="<?php echo htmlspecialchars($item['lifespan']); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="price" class="form-label">Price<span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0" class="form-control" id="price" name="price" value="<?php echo $item['price']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="quantity" class="form-label">Quantity<span class="text-danger">*</span></label>
                        <input type="number" min="1" class="form-control" id="quantity" name="quantity" value="<?php echo $item['quantity']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($item['description']); ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="image" class="form-label">Image</label>
                        <?php if ($item['image']): ?>
                            <div class="mb-2">
                                <img src="uploads/<?php echo htmlspecialchars($item['image']); ?>" alt="Current Image" class="img-thumbnail" style="max-width: 200px;">
                            </div>
                        <?php endif; ?>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                    </div>
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-success">Save Changes</button>
                        <a href="index.php" class="btn btn-secondary ms-2">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Optionally load fontawesome (for .fas icon in navbar-brand) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>