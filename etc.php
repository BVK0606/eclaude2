<?php
require '../config.php'; 
requireRole('admin');

// --- Add Notice ---
if(isset($_POST['submit'])){
  $title = $_POST['title'];
  $detail = $_POST['detail'];
  $date = date('Y-m-d');

  $sql = "INSERT INTO notices (title, detail, created_at) VALUES ('$title', '$detail', '$date')";
  mysqli_query($conn, $sql);
  echo "Notice added successfully!";
}

// --- Fetch All Notices ---
$res = mysqli_query($conn, "SELECT * FROM notices ORDER BY created_at DESC");
while($row = mysqli_fetch_assoc($res)){
  echo "<b>{$row['title']}</b> - {$row['detail']} ({$row['created_at']})<br>";
}
?>



