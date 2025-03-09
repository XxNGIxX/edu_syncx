<?php
session_start();
// สร้างการเชื่อมต่อกับฐานข้อมูล
$conn = mysqli_connect("localhost", "root", "", "edu_syncx");

// ตรวจสอบการเชื่อมต่อ
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// รับค่า class ที่เลือกจากฟอร์ม
$selectedClass = isset($_POST['class']) ? $_POST['class'] : '';

// ดึงข้อมูลนักเรียนใน class ที่เลือก และรวม student_id, fname, lname จาก user
$studentsQuery = "
    SELECT s.student_id, s.student_number, u.fname, u.lname 
    FROM students_new s
    JOIN user u ON s.user_id = u.user_id
    WHERE s.class = ?
";
$stmt = mysqli_prepare($conn, $studentsQuery);
mysqli_stmt_bind_param($stmt, 's', $selectedClass);
mysqli_stmt_execute($stmt);
$studentsResult = mysqli_stmt_get_result($stmt);

// ตรวจสอบว่ามี assignment_id ใน session หรือไม่
if (!isset($_SESSION['assignment_id'])) {
    die("assignment_id ไม่ถูกตั้งค่าใน session");
}

$assignmentId = $_SESSION['assignment_id']; // ดึง assignment_id จาก session

// บันทึกข้อมูลลงในตาราง scores_new (ถ้าข้อมูลยังไม่ถูกบันทึก)
$insertQuery = "
    INSERT INTO scores_new (student_id, assignment_id, score, submission_status)
    VALUES (?, ?, 0, 'ยังไม่ส่ง')
";
$insertStmt = mysqli_prepare($conn, $insertQuery);

// ทำการ loop เพื่อเพิ่มข้อมูลคะแนน 0 และสถานะยังไม่ส่งในตาราง scores_new



// ปิดการเชื่อมต่อฐานข้อมูล

?>


<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student List - Class <?= htmlspecialchars($selectedClass) ?></title>
    <style>
        body {
            font-family: 'Noto Serif Thai', serif;
            background-color: #f2ecdd;
            padding: 20px;
            margin: 100px auto;
            max-width: 900px;
            color: #333;
        }

        h2 {
           
            color: #333;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #cd9d20;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border: 50px red ;
            overflow: hidden;
        }

        th, td {
            padding: 15px;
            
            text-align: left;
            border: 1px solid white;
        }

        th {
            background-color: #cd9d20;
            color: black;
            text-transform: uppercase;
            font-weight: bold;
            text-align: center;
        }

        td {
            text-align: center;
        }

        tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        td:first-child {
            width: 20%;
        }

        td:nth-child(2) {
            width: 80%;
        }

        a {
            text-decoration: none;
            color: #ffffff;
        }

        .btn {
            display: block;
            width: 100px;
            margin: 20px auto;
            text-align: center;
            padding: 10px;
            background-color: #cd9d20;
            color: black;
            border-radius: 5px;
            transition: background-color 0.3s ease;
            float: right; /* ทำให้ปุ่มอยู่ทางขวา */
            margin-right: 20px; /* เพิ่มระยะห่างจากขอบขวา */
        }

        .btn:hover {
            background-color: #f5cb5c;
        }
    </style>
</head>
<body>
    <h2>รายชื่อนักเรียนในชั้นเรียน <?= htmlspecialchars($selectedClass) ?></h2>
    <table>
        <tr>
            <th>เลขที่</th>
            <th>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;  ชื่อ-สกุล  </th>
        </tr>
        <?php 
        // ทำการ loop เพื่อแสดงรายชื่อนักเรียน
        while ($student = mysqli_fetch_assoc($studentsResult)): ?>
            <tr>
                <td><?= htmlspecialchars($student['student_number']) ?></td>
                <td><?= htmlspecialchars($student['fname'] . ' ' . $student['lname']) ?></td>
            </tr>
        <?php endwhile; ?>

        <?php
        // Reset the result pointer to loop again if needed
        mysqli_data_seek($studentsResult, 0); // เลื่อนไปยังจุดเริ่มต้นของผลลัพธ์อีกครั้ง

        // บันทึกข้อมูลลงในตาราง scores_new
        while ($student = mysqli_fetch_assoc($studentsResult)) {
            $studentId = $student['student_id']; 
            

            // ทำการเพิ่มข้อมูลลงใน scores_new
            mysqli_stmt_bind_param($insertStmt, 'ii', $studentId, $assignmentId);
            if (!mysqli_stmt_execute($insertStmt)) {
                echo "เกิดข้อผิดพลาดในการเพิ่มข้อมูล: " . mysqli_stmt_error($insertStmt);
            }
        }
        ?>
    </table>

    <a href="teacher.php" class="btn">ถัดไป</a>
</body>

</html>