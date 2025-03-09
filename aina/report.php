<?php
session_start(); 
// สร้างการเชื่อมต่อกับฐานข้อมูล
$conn = mysqli_connect("localhost", "root", "", "edu_syncx");

// ตรวจสอบการเชื่อมต่อ
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// ดึงข้อมูล class จาก session
$class = isset($_SESSION['class']) ? $_SESSION['class'] : 'ไม่มีข้อมูลในเซสชัน';
$targetClass = $class;

$targetSubjectId = $_SESSION['subject_id']; // subject_id ของวิชาที่ต้องการแสดง

// ดึงข้อมูลงานทั้งหมดในวิชานี้
$assignmentsQuery = "
    SELECT DISTINCT
        a.assignment_id, 
        a.assignment_title
    FROM 
        assignments_new a
    WHERE 
        a.subject_id = '$targetSubjectId'
";
$assignmentsResult = mysqli_query($conn, $assignmentsQuery);

// เก็บรายการงาน
$assignments = [];
while ($row = mysqli_fetch_assoc($assignmentsResult)) {
    $assignments[] = $row;
}

// ดึงข้อมูลคะแนนของนักเรียน
$scoresQuery = "
    SELECT 
        s.student_number,
        CONCAT(u.fname, ' ', u.lname) AS full_name,
        s.class,
        sc.assignment_id,
        sc.score
    FROM 
        students_new s
    LEFT JOIN 
        user u ON s.user_id = u.user_id
    LEFT JOIN 
        scores_new sc ON s.student_id = sc.student_id
    WHERE 
        s.class = '$targetClass'
";
$scoresResult = mysqli_query($conn, $scoresQuery);

// เก็บข้อมูลนักเรียนและคะแนน
$students = [];
$assignmentScores = []; // เก็บว่ามีคะแนนของงานไหนบ้าง
while ($row = mysqli_fetch_assoc($scoresResult)) {
    $studentNumber = $row['student_number'];
    $assignmentId = $row['assignment_id'];
    $score = $row['score'];

    if (!isset($students[$studentNumber])) {
        $students[$studentNumber] = [
            'name' => $row['full_name'],
            'class' => $row['class'],
            'scores' => []
        ];
    }

    // บันทึกคะแนนของนักเรียน
    $students[$studentNumber]['scores'][$assignmentId] = $score;

    // บันทึกว่ามีการส่งคะแนนในงานนี้แล้ว
    if (!isset($assignmentScores[$assignmentId])) {
        $assignmentScores[$assignmentId] = 0;
    }
    $assignmentScores[$assignmentId] += ($score !== null) ? 1 : 0; // นับเฉพาะงานที่มีคะแนน
}

// กรองงานที่มีการส่งคะแนนจากนักเรียนอย่างน้อยหนึ่งคน
$filteredAssignments = [];
foreach ($assignments as $assignment) {
    $assignmentId = $assignment['assignment_id'];
    if (isset($assignmentScores[$assignmentId]) && $assignmentScores[$assignmentId] > 0) {
        $filteredAssignments[] = $assignment;
    }
}

// ดึงข้อมูลวิชา ชื่อวิชา เทอม และปีการศึกษา
$subjectQuery = "SELECT subject_name, term, year, class FROM subject WHERE subject_id = ?";
$stmt = mysqli_prepare($conn, $subjectQuery);
mysqli_stmt_bind_param($stmt, 'i', $targetSubjectId);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $subjectName, $term, $year, $class);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

// ปิดการเชื่อมต่อฐานข้อมูล
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Scores - ชั้น <?= htmlspecialchars($targetClass) ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 50px;
            background-color: #f2ecdd;
        }
        #table1 {
            width: 90%;
            margin: 20px auto;
            border-collapse: collapse;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        #table1 th, td {
            padding: 8px 12px;
            text-align: center;
            border: 1px solid #ddd;
        }
        #table1 th {
            background-color: #cd9c50;
            color: black;
        }
        #table1 tr:nth-child(even) {
            background-color: #f2ecdd;
        }
        #table1 tr:hover {
            background-color: #f5cb5c;
        }

        #table2 {
            width: 90%;
            margin: 20px auto;
            border-collapse: collapse;
        }
    </style>
</head>
<body>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <a href="teacher_menu.php" style="color: black; font-size: 24px;"><i class="fas fa-arrow-left"></i></a>
    
    <!-- ตารางแสดงชื่อวิชาและชั้นเรียน -->
    <table id="table2">
        <tr>
            <th style="text-align: left;">วิชา: <?= $subjectName ?> - ชั้น <?= $class ?> - เทอม <?= $term ?> / ปี <?= $year ?></th>
        </tr>
    </table>

    <!-- ตารางแสดงคะแนน -->
    <table id="table1">
        <tr>
            <th>เลขที่</th>
            <th>ชื่อ</th>
            <?php foreach ($filteredAssignments as $assignment): ?>
                <th><?= htmlspecialchars($assignment['assignment_title']) ?></th>
            <?php endforeach; ?>
            <th>รวมคะแนน</th>
        </tr>

        <!-- แสดงรายชื่อนักเรียน -->
        <?php foreach ($students as $studentNumber => $student): ?>
            <tr>
                <td><?= htmlspecialchars($studentNumber) ?></td>
                <td><?= htmlspecialchars($student['name']) ?></td>
                <?php 
                    $totalScore = 0;
                    foreach ($filteredAssignments as $assignment) {
                        $assignmentId = $assignment['assignment_id'];
                        $score = isset($student['scores'][$assignmentId]) ? $student['scores'][$assignmentId] : 0;
                        echo "<td>" . htmlspecialchars($score) . "</td>";
                        $totalScore += $score;
                    }
                ?>
                <td><?= htmlspecialchars($totalScore) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

    <a href="teacher.php" style="color: black; font-size: 24px; position: fixed; bottom: 20px; right: 120px;"><i class="fas fa-home"></i></a>
</body>
</html>
