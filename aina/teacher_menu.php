<?php
session_start();
// สร้างการเชื่อมต่อกับฐานข้อมูล
$conn = mysqli_connect("localhost", "root", "", "edu_syncx");

// ตรวจสอบการเชื่อมต่อ
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// รับค่า class ที่เลือกจากฟอร์ม หรือดึงจาก session ถ้าไม่ได้เลือกใหม่
$selectedClass = isset($_POST['class']) ? $_POST['class'] : (isset($_SESSION['class']) ? $_SESSION['class'] : '');

// เก็บ class ที่เลือกไว้ใน session
if (!empty($selectedClass)) {
    $_SESSION['class'] = $selectedClass;
}

// ดึง subject_name ตาม subject_id จาก session
$targetSubjectId = $_SESSION['subject_id'];
$subjectName = "";
$term = "";
$year = "";
$class = "";
$subjectQuery = "SELECT subject_name, term, year, class FROM subject WHERE subject_id = ?";
$stmt = mysqli_prepare($conn, $subjectQuery);
mysqli_stmt_bind_param($stmt, 'i', $targetSubjectId);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $subjectName, $term, $year, $class);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

// ดึงข้อมูลนักเรียนใน class ที่เลือก และรวม fname, lname จาก user
$studentsQuery = "
    SELECT s.student_number, u.fname, u.lname 
    FROM students_new s
    JOIN user u ON s.user_id = u.user_id
    WHERE s.class = ?
";
$stmt = mysqli_prepare($conn, $studentsQuery);
mysqli_stmt_bind_param($stmt, 's', $selectedClass);
mysqli_stmt_execute($stmt);
$studentsResult = mysqli_stmt_get_result($stmt);

// ปิดการเชื่อมต่อฐานข้อมูล
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เว็บแอพลิเคชันสำหรับครูผู้สอน</title>

    <link rel="stylesheet" href="teacher_menu.css">
    <!-- เพิ่มการใช้งาน Font Awesome สำหรับไอคอน -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="container">

        <main class="main-content">
            <a href="teacher.php" style="color: black; font-size: 24px;"><i class="fas fa-arrow-left"></i></a>
            <!-- แสดงชื่อวิชาและชั้นเรียนที่เลือก -->
            <h2 style="text-indent: 40px;">
            <h2>วิชา: <?= htmlspecialchars($subjectName) ?>- ชั้น<?= htmlspecialchars($class) ?>- เทอม <?= htmlspecialchars($term) ?> / ปี <?= htmlspecialchars($year) ?>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ชั้นประถมศึกษาปีที่ <?= htmlspecialchars($selectedClass) ?></h2>
            
            
            </h2>
            <div class="buttons">
                <button><a href='report.php'>รายงานสรุปคะแนน</a></button>
            </div>
            <div class="buttons">
                <button><a href='score.php'>กรอกคะแนน</a></button>
            </div>
            <!-- ปุ่มกลับไปหน้าหลัก -->
            <a href="teacher.php" style="color: black; font-size: 24px; position: fixed; bottom: 20px; right: 120px;">
                <i class="fas fa-home"></i>
            </a>
        </main>
    </div>
</body>
</html>
