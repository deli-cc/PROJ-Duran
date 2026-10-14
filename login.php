<?php
session_start();
include 'db.php';

$error = "";

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if(empty($username) || empty($password)) {
        $error = "Username and password are required!";
    } else {
        $stmt = $conn->prepare("SELECT id, password, is_admin FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if(password_verify($password, $user['password'])) {
                if ($user['is_admin']) {
                    $_SESSION['is_admin'] = true;
                } else {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $username;
                }
                // Either way, set user id/username for admin, too, for easy queries:
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $username;

                header("Location: index.php");
                exit();
            } else {
                $error = "Invalid password!";
            }
        } else {
            $error = "Username not found!";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Login</title>
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
    <div class="container mt-5" style="max-width: 400px;">
        <div class="card">
            <div class="card-body">
                <h3 class="card-title mb-4">Account Login</h3>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <form method="post">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" name="username" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                    <button class="btn btn-primary w-100" type="submit">Login</button>
                    <p class="mt-3 text-center">Don't have an account? <a href="register.php">Register here</a></p>
                    <p class="text-center"><a href="index.php">Back to Shop</a></p>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>