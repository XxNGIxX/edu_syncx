<?php
session_start(); // เริ่มต้น session เพื่อเก็บ assignment_id

// รับค่าจากฟอร์ม
$assignment_title = $_REQUEST["assignment_title"];
$subject_id =  $_REQUEST["subject_id"];
$total_points = $_REQUEST["total_points"];
$deadline = $_REQUEST["deadline"];
$assignment_details = $_REQUEST["assignment_details"];

// เชื่อมต่อฐานข้อมูล
$conn = mysqli_connect("localhost", "root", "", "edu_syncx");

// ตรวจสอบการเชื่อมต่อ
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// สร้าง SQL เพื่อเพิ่มข้อมูลลงในตาราง assignments_new
$insert_sql = "INSERT INTO assignments_new(assignment_title,subject_id,total_points,deadline, assignment_details) 
                VALUES('$assignment_title','$subject_id','$total_points','$deadline','$assignment_details')";

// ดำเนินการ SQL
$result = mysqli_query($conn, $insert_sql);

if ($result) {
    // ดึง assignment_id ล่าสุดที่เพิ่งถูกสร้าง
    $assignment_id = mysqli_insert_id($conn);

    // เก็บ assignment_id ลงใน session
    $_SESSION['assignment_id'] = $assignment_id;

    // ถ้าสำเร็จ เปลี่ยนไปหน้า select_classroom.php
    header("Location: select_classroom.php");
    exit(); // หยุดการทำงานต่อไปเพื่อไม่ให้มีการส่งข้อมูลเพิ่มเติม
} else {
    // แสดงข้อความแจ้งข้อผิดพลาด
    $message = "เกิดข้อผิดพลาด: " . mysqli_error($conn);
    echo $message; // แสดงข้อความแจ้งเตือน
}

// ปิดการเชื่อมต่อฐานข้อมูล
mysqli_close($conn);
?>
