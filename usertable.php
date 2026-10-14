<?php
session_start();
include 'db.php';
if (!isset($_SESSION['is_admin'])) {
    header("Location: admin_login.php");
    exit();
}

$result = $conn->query("SELECT id, username, email, created_at FROM users ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Registered Users</title>
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
        <h2>Registered Users</h2>
        <a href="index.php" class="btn btn-secondary mb-3">Back to Dashboard</a>
        <div class="card">
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
                    <?php if ($result->num_rows > 0): ?>
                        <?php while($user = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo $user['created_at']; ?></td>
                                <td>
                                    <a href="delete_user.php?id=<?php echo $user['id']; ?>" class="btn btn-danger btn-sm"
                                    onclick="return confirm('Are you sure to delete this user?')">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center">No users found.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>