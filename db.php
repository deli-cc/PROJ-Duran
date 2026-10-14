<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "duran";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
}
?>