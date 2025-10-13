<?php
require '../config.php'; requireRole('admin');

if(isset($_POST['add_marks'])){
  $cid=$_POST['class_id']; $sid=$_POST['subject_id'];
  $exam=$_POST['exam_type']; $data=$_POST['marks']??[];
  if(!$cid||!$sid||!$exam){echo "All fields required.";exit;}

  // remove old marks & insert new
  mysqli_query($conn,"DELETE m FROM marks m 
    JOIN students s ON m.student_id=s.student_id 
    WHERE s.class_id='$cid' AND m.subject_id='$sid' AND m.exam_type='$exam'");

  foreach($data as $st=>$mk)
    if($mk!=='' && is_numeric($mk))
      mysqli_query($conn,"INSERT INTO marks(student_id,subject_id,exam_type,marks_obtained)
        VALUES('$st','$sid','$exam','$mk')");

  echo "Marks saved successfully!";
}
?>


