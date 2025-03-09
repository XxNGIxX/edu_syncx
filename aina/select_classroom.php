<?php
// สร้างการเชื่อมต่อกับฐานข้อมูล
$conn = mysqli_connect("localhost", "root", "", "edu_syncx");

// ตรวจสอบการเชื่อมต่อ
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// ดึง subject_id จาก session
session_start();
$targetSubjectId = $_SESSION['subject_id'];

// ดึงข้อมูลชื่อวิชา class term year จากฐานข้อมูลโดยใช้ subject_id
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

// ดึงรายการ class จากตาราง subject ที่ตรงกับ degree ในตาราง students_new
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

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Scores - ชั้น <?= $class ?? 'เลือกชั้นเรียน' ?></title>
    <style>
        body {
            padding: 20px; 
            margin: 100px auto;
            background-color: #F2ECDD;
        }

        input[type=text], select {
            width: 100%;
            padding: 12px 20px;
            margin: 8px 0;
            display: inline-block;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        input[type=submit] {
            width: 100%;
            background-color:#CD9C20;
            color: white;
            padding: 14px 20px;
            margin: 8px 0;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        
        input[type=submit]:hover {
            background-color:#F5CB5C;
            color: black;
        }

        div {
            width: 700px;
            background-color: #ffff;
            box-shadow: 0 7px 12px 0 #0000;
            border-radius: 10px;
            padding: 20px; 
            margin: auto;
            font-family: "Noto Serif Thai", serif;
        }
        
        h4 {
            text-align: left;
            font-family: "Noto Serif Thai", serif;
        }

        span {
            text-align: center;
            font-family: "Noto Serif Thai", serif;
            margin: auto;
        }
    </style>
</head>
<body>
    <div>
        <main class="main-content">
            <!-- แสดงชื่อวิชาที่ดึงมา -->
            <h2>วิชา : <?= htmlspecialchars($subjectName) ?> - ชั้น<?= htmlspecialchars($class) ?> - เทอม <?= htmlspecialchars($term) ?> / ปี <?= htmlspecialchars($year) ?></h2>
            <h4>เลือกชั้นเรียนเพื่อมอบหมายงาน</h4>
            <form method="post" action="studentsOfroom.php"> <!-- เปลี่ยน your_action_page.php เป็น URL ของคุณ -->
                <p>
                    <select name="class" id="class" required>
                        <option value="" selected disabled>เลือกชั้นเรียน</option>
                        <?php while ($row = mysqli_fetch_assoc($classResult)): ?>
                            <option value="<?= htmlspecialchars($row['class']) ?>"> ชั้นประถมศึกษาปีที่ <?= htmlspecialchars($row['class']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </p>
                <input type="submit" value="ถัดไป"> 
            </form>
        </main>
    </div>
</body>
</html>
