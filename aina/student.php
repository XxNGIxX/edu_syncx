<?php
session_start(); 
// เชื่อมต่อกับฐานข้อมูล
$conn = new mysqli("localhost", "root", "", "edu_syncx");

if ($conn->connect_error) {
    die("การเชื่อมต่อฐานข้อมูลล้มเหลว: " . $conn->connect_error);
}

// รับ student_id จากเซสชัน
$student_id = $_SESSION["student_id"];

// ดึงข้อมูลชื่อจากตาราง user
$sqlStudent = "SELECT CONCAT(fname, ' ', lname) AS student_name FROM user WHERE user_id = (SELECT user_id FROM students_new WHERE student_id = ?)";
$stmtStudent = $conn->prepare($sqlStudent);
$stmtStudent->bind_param("i", $student_id);
$stmtStudent->execute();
$resultStudent = $stmtStudent->get_result();

if ($resultStudent->num_rows > 0) {
    $rowStudent = $resultStudent->fetch_assoc();
    $student_name = $rowStudent['student_name'];
} else {
    $student_name = "ไม่พบชื่อผู้ใช้งาน";
}

// รับค่าที่ค้นหาจาก POST
$searchSubject = isset($_POST['searchSubject']) ? $_POST['searchSubject'] : '';
$searchDate = isset($_POST['searchDate']) ? $_POST['searchDate'] : '';

// SQL query สำหรับงานที่ยังไม่ส่ง
$sqlPending = "SELECT a.deadline, a.assignment_title, a.total_points, sub.subject_name, s.submission_status
               FROM assignments_new a
               LEFT JOIN scores_new s ON a.assignment_id = s.assignment_id AND s.student_id = ?
               LEFT JOIN subject sub ON a.subject_id = sub.subject_id
               WHERE (s.submission_status IS NULL OR s.submission_status = 0) 
               AND s.score_id IS NOT NULL
               AND a.assignment_title LIKE ?";
if ($searchDate != '') {
    $sqlPending .= " AND a.deadline = ?";
}

// เตรียมคำสั่ง SQL
$stmt = $conn->prepare($sqlPending);
$searchSubject = "%$searchSubject%";
if ($searchDate != '') {
    $stmt->bind_param("iss", $student_id, $searchSubject, $searchDate);
} else {
    $stmt->bind_param("is", $student_id, $searchSubject);
}
$stmt->execute();
$resultPending = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบส่งงาน</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }
        .container {
            width: 90%;
            margin: 0 auto;
            max-width: 1200px;
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .header .profile {
            display: flex;
            align-items: center;
        }
        .header img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            margin-right: 10px;
        }
        .status {
            background-color: #ffcc00;
            padding: 5px 10px;
            border-radius: 5px;
            color: #333;
        }
        h1 {
            margin-bottom: 10px;
        }
        .search-bar {
            margin-bottom: 20px;
        }
        .search-bar input[type="text"], .search-bar input[type="date"] {
            padding: 10px;
            width: 200px;
            margin-right: 10px;
        }
        .tasks {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .tasks table {
            width: 100%;
            border-collapse: collapse;
        }
        .tasks table, .tasks th, .tasks td {
            border: 1px solid #ddd;
        }
        .tasks th, .tasks td {
            padding: 10px;
            text-align: center;
        }
        .tasks th {
            background-color: #f2f2f2;
        }
        .completed {
            background-color: #e0ffe0;
        }
        .pending {
            background-color: #ffe0e0;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <div>
            <h3>งานของนักเรียน: <?php echo $student_name; ?></h3> <!-- แสดงชื่อนักเรียนที่นี่ -->
            <span class="status">นักเรียน</span>
        </div>
    </div>

    <div class="search-bar">
        <form method="POST" action="">
            <input type="text" name="searchSubject" placeholder="ค้นหาวิชา...">
            <input type="date" name="searchDate">
            <button type="submit">ค้นหา</button>
        </form>
    </div>

    <h2>งานที่ยังไม่ส่ง</h2>
    <div class="tasks">
        <table id="pendingTasks">
            <tr>
                <th>วิชา</th>
                <th>งาน</th>
                <th>กำหนดส่ง</th>
                <th>คะแนนเต็ม</th>
            </tr>
            <?php
            if ($resultPending->num_rows > 0) {
                while ($row = $resultPending->fetch_assoc()) {
                    echo "<tr class='pending'>
                            <td>{$row['subject_name']}</td>
                            <td>{$row['assignment_title']}</td>
                            <td>{$row['deadline']}</td>
                            <td>{$row['total_points']}</td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='4'>ไม่มีข้อมูล</td></tr>";
            }
            ?>
        </table>
    </div>

    <h2>งานที่ส่งแล้ว</h2>
    <div class="tasks">
        <?php
        // SQL query สำหรับงานที่ส่งแล้วพร้อมคะแนนที่ได้
        $sqlCompleted = "SELECT a.deadline, a.assignment_title, s.score, a.total_points, sub.subject_name, s.submission_status
                         FROM assignments_new a
                         LEFT JOIN scores_new s ON a.assignment_id = s.assignment_id AND s.student_id = ?
                         LEFT JOIN subject sub ON a.subject_id = sub.subject_id
                         WHERE s.submission_status IN (1, 2) 
                         AND s.score_id IS NOT NULL
                         AND a.assignment_title LIKE ?";
        if ($searchDate != '') {
            $sqlCompleted .= " AND a.deadline = ?";
        }

        // เตรียมคำสั่ง SQL
        $stmt = $conn->prepare($sqlCompleted);
        if ($searchDate != '') {
            $stmt->bind_param("iss", $student_id, $searchSubject, $searchDate);
        } else {
            $stmt->bind_param("is", $student_id, $searchSubject);
        }
        $stmt->execute();
        $resultCompleted = $stmt->get_result();
        ?>
       <table id="completedTasks">
    <tr>
        <th>วิชา</th>
        <th>กำหนดส่ง</th>
        <th>งาน</th>
        <th>คะแนนที่ได้</th>
        <th>คะแนนเต็ม</th>
        <th>สถานะ</th>
    </tr>
    <?php
    if ($resultCompleted->num_rows > 0) {
        while ($row = $resultCompleted->fetch_assoc()) {
            // ตรวจสอบสถานะการส่งงาน
            $status = '';
            if ($row['submission_status'] == 1) {
                $status = 'ส่งแล้ว';
            } elseif ($row['submission_status'] == 2) {
                $status = 'ยังไม่ตรวจ';
            } else {
                $status = 'สถานะไม่ทราบ';
            }

            echo "<tr class='completed'>
                    <td>{$row['subject_name']}</td>
                    <td>{$row['deadline']}</td>
                    <td>{$row['assignment_title']}</td>
                    <td>" . (isset($row['score']) ? $row['score'] : 'ยังไม่ได้คะแนน') . "</td>
                    <td>{$row['total_points']}</td>
                    <td>{$status}</td>
                  </tr>";
        }
    } else {
        echo "<tr><td colspan='6'>ไม่มีข้อมูล</td></tr>"; // แก้ไขให้มี 6 คอลัมน์
    }
    ?>
</table>

    </div>

</div>
</body>
</html>

<?php
// ปิดการเชื่อมต่อฐานข้อมูล
$conn->close();
?>
