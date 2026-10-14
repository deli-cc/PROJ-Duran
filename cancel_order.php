<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: account.php");
    exit();
}

$order_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

// Confirm this order belongs to the user and is pending
$stmt = $conn->prepare("SELECT status FROM orders WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();
$stmt->close();

if (!$order || $order['status'] !== 'Pending') {
    $_SESSION['message'] = "Order cannot be cancelled.";
    header("Location: account.php");
    exit();
}

// Set order status to Cancelled
$stmt = $conn->prepare("UPDATE orders SET status='Cancelled' WHERE id=? AND user_id=?");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$stmt->close();

$_SESSION['message'] = "Order cancelled successfully.";
header("Location: account.php");
exit();
?>