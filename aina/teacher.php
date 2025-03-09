<?php
session_start();
// สร้างการเชื่อมต่อกับฐานข้อมูล
$conn = mysqli_connect("localhost", "root", "", "edu_syncx");

// ตรวจสอบการเชื่อมต่อ
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$message = ''; // ตัวแปรสำหรับเก็บข้อความแจ้งเตือน

// ดึงค่า subject_id จาก session
$targetSubjectId = $_SESSION['subject_id'];

// ดึงชื่อวิชาจากฐานข้อมูลโดยใช้ subject_id
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

// ตรวจสอบค่าที่ดึงมา




// ดึงรายการ class ที่มีอยู่
$classQuery = "SELECT DISTINCT s.class 
               FROM subject sub 
               INNER JOIN students_new s 
               ON sub.class = s.degree 
               WHERE sub.subject_id = ?";
$stmt = mysqli_prepare($conn, $classQuery);
mysqli_stmt_bind_param($stmt, 'i', $targetSubjectId);
mysqli_stmt_execute($stmt);
$classResult = mysqli_stmt_get_result($stmt);


// ปิดการเชื่อมต่อฐานข้อมูล
mysqli_close($conn);
?>

<style>
.container {
    font-family: Arial, sans-serif;
    width: 100%;
    height: 100vh; /* สูง 100% ของความสูงหน้าจอ */
    position: fixed; /* ยึดตำแหน่งคงที่ */
    top: 0 ;
    left: 0 ;
    margin: 0 ;
    padding: 0;
    background-color: #ffffff;
}

.header {
    display: flex;
    justify-content: flex-end;
    padding: 30px;
    background-color: #fff394;
    border-bottom: 1px solid #dddddd;
    border: 1px solid #ffea9d;
}

.user-info {
    display: flex;
    align-items: center;
}

.user-details {
    text-align: right;
}

.user-name {
    font-weight: bold;
    font-size: 1em; /* ลดขนาดฟอนต์ */
}

.user-status {
    color: #888888;
}

.main-content {
    font-family: Arial, sans-serif;
    width: 750px; /* กำหนดความกว้างของกล่อง */
    height: 350px;
    padding: 20px; /* ระยะห่างภายในกล่อง */
    margin: 50px 0 0 270px; /* ขยับให้ออกจาก sidebar */
    margin-top: 70px;
    border-radius: 5px; /* มุมมน */
    background-color: #f9f9f9; /* สีพื้นหลังของกล่อง */
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2); /* เงาของกล่อง */
}

.subject-section h2 {
    font-family: Arial, sans-serif;
    margin-top: 50px;
    margin-left: 70px;
    color: #333333;
    font-size: 1.2em; /* ขนาดเล็กลง */
}

.buttons {
    font-family: Arial, sans-serif;
    display: flex;
    flex-direction: column;
    gap: 20px;
    margin-left: 50px; /* เพิ่มระยะห่างทางซ้าย */
}

.buttons button {
    margin: 0 auto;
    margin-top: 30px;
    padding: 10px;
    background-color: #f5cb5c;
    border: 1px solid #cd9c20;
    border-radius: 5px;
    cursor: pointer;
    text-align: center;
    font-size: 1em;
    width: 450px; /* กำหนดความกว้างเป็น 450 pixels */
    height: 60px;
}

.buttons button:hover {
    background-color: #f5cb5c;
}

.class-option select {
    font-family: Arial, sans-serif;
    width: 150px; /* กำหนดความกว้างของปุ่ม */
    height: 40px; /* กำหนดความสูงของปุ่ม */
    background-color: #f5cb5c; /* สีพื้นหลังของปุ่ม */
    color: rgb(4, 4, 4); /* สีของตัวอักษร */
    border: none; /* ไม่มีเส้นขอบ */
    text-align: center;
    border-radius: 5px; /* ทำขอบปุ่มให้มน */
    font-size: 16px; /* ขนาดตัวอักษร */
    padding: 5px; /* เพิ่มพื้นที่ภายในปุ่ม */
}

.class-option select:hover {
    background-color:  #f5cb5c; /* เปลี่ยนสีเมื่อมีการ hover */
}

.class-option select option {
    color: black; /* สีตัวอักษรในรายการเลือก */
    background-color: #f5cb5c; /* สีพื้นหลังของตัวเลือก */
}

.button-select {
    margin-left: 175px;
    margin-top: 30px;
    padding: 10px;
    background-color: #f5cb5c;
    font-family: Arial, sans-serif;
    border: 1px solid #cd9c20;
    border-radius: 5px;
    cursor: pointer;
    text-align: center;
    font-size: 1em;
    width: 450px; /* กำหนดความกว้างเป็น 450 pixels */
    height: 60px;
}

.buttons a {
    text-decoration: none; /* เอาขีดเส้นใต้ของลิงก์ออก */
    color: black; /* ตั้งค่าสีข้อความเป็นสีดำ หรือสีที่คุณต้องการ */
}

.sidebar {
    width: 200px;
    background-color: #f5cb5c; /* สีพื้นหลังของปุ่ม */
    padding: 15px;
    position: fixed; /* กำหนดให้ sidebar ค้างที่ตำแหน่งด้านซ้าย */
    height: 100vh; /* ความสูงเต็มจอ */
    overflow-y: auto; /* ถ้าเนื้อหาเกิน จะมี scroll */
}

.sidebar a {
    color: #ffffff;
    padding: 10px;
    text-decoration: none;
    display: block;
}

.sidebar ul {
    list-style-type: none; /* Remove bullets from the list */
    padding: 0; /* Remove padding */
    margin: 0; /* Remove margin */
}

.sidebar a.active {
    color: black; /* เปลี่ยนสีข้อความเมื่อถูกคลิก */
    font-weight: bold; /* ทำให้ตัวหนา */
}

body {
    display: flex;
    margin: 0;
}

.content {
    margin-left: 250px; /* ทำให้เนื้อหาถูกเลื่อนไปทางขวา */
    padding: 20px;
    width: calc(100% - 250px); /* ปรับความกว้างของเนื้อหาให้สอดคล้องกับ sidebar */
}

</style>


<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เว็บแอพลิเคชันสำหรับครูผู้สอน</title>
    <link rel="stylesheet" href="teacher.css">
</head>
<body>

<div class="sidebar">
    <h2 class="text-light">รายการเมนู</h2>
    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link" href="select_edit.php"><i class="bi bi-bank"> </i> รายการสมุดบัญชี</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="income.php"><i class="bi bi-caret-up-square"> </i> เพิ่มรายรับ</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="expense.php"><i class="bi bi-caret-down-square"> </i> เพิ่มรายจ่าย</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="categorie.php"><i class="bi bi-clipboard-plus"> </i> จัดการหมวดหมู่</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="summary.php"><i class="bi bi-cash-coin"> </i> สรุปรายการ</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="putna.php">ผู้พัฒนา</a>
        </li>
    </ul>
</div>

    <main class="main-content">
        <div class="subject-info">
            <!-- แสดงชื่อวิชาที่ดึงมา -->
            <h2>วิชา: <?= htmlspecialchars($subjectName) ?>- ชั้น<?= htmlspecialchars($class) ?>- เทอม <?= htmlspecialchars($term) ?> / ปี <?= htmlspecialchars($year) ?></h2>
            <p>
                <h3>เลือกชั้นเรียน</h3>
            </p>
            <form action="teacher_menu.php" method="post">
                <button class="button-select">
                    <div class="class-option">
                        <select name="class" id="class" required>
                            <option value="" selected disabled>เลือกชั้นเรียน</option>
                            <?php while ($row = mysqli_fetch_assoc($classResult)): ?>
                                <option value="<?= htmlspecialchars($row['class']) ?>">ชั้นประถมศึกษาปีที่ <?= htmlspecialchars($row['class']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </button>
            </form>

            <div class="buttons">
                <button><a href="assign.php">มอบหมายงาน</a></button>
            </div>
        </div>
    </main>
</body>
</html>
