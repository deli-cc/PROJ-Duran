<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
if (isset($_SESSION['is_admin'])) {
    $_SESSION['message'] = "Admins cannot make purchases.";
    header("Location: index.php");
    exit();
}
include 'db.php';

$item_id = intval($_POST['item_id']);
$quantity = intval($_POST['quantity']);
if ($quantity < 1) $quantity = 1;

// Get item details
$stmt = $conn->prepare("SELECT * FROM items WHERE id = ?");
$stmt->bind_param("i", $item_id);
$stmt->execute();
$result = $stmt->get_result();
$item = $result->fetch_assoc();
$stmt->close();

if ($item && $quantity <= $item['quantity']) {
    // Overwrite cart with only this item
    $_SESSION['cart'] = [[
        'id'      => $item['id'],
        'name'    => $item['name'],
        'price'   => $item['price'],
        'quantity'=> $quantity,
        'image'   => $item['image']
    ]];
    header("Location: checkout.php");
    exit();
} else {
    $_SESSION['message'] = "Unable to buy this item right now.";
    header("Location: index.php");
    exit();
}
?>