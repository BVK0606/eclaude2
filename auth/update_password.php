<?php
require_once '../config.php'; // your database connection

$newPassword = "student@gmail.com"; // 🔑 your new password
$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
$username = "student"; // the username or email of the account

try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("UPDATE users SET upassword = ? WHERE uname = ?");
    $stmt->execute([$hashedPassword, $username]);
    echo "Password updated successfully!";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
