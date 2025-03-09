<?php
session_start(); 
// สร้างการเชื่อมต่อกับฐานข้อมูล
$conn = mysqli_connect("localhost", "root", "", "edu_syncx");

// ตรวจสอบการเชื่อมต่อ
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$subjectId = $_SESSION['subject_id'];

// ดึงรายการ class เพื่อใช้ใน dropdown
$classQuery = "SELECT DISTINCT class FROM students_new";
$classResult = mysqli_query($conn, $classQuery);

// ตรวจสอบการเลือก class และ assignment_id
$class = isset($_GET['class']) ? $_GET['class'] : (isset($_SESSION['class']) ? $_SESSION['class'] : null);
$sub = isset($_SESSION['subject_name']) ? $_SESSION['subject_name'] : 'ไม่มีข้อมูลในเซสชัน';
$assignmentId = isset($_GET['assignment_id']) ? $_GET['assignment_id'] : null;

// ดึงรายการ assignment ที่มี subject_id และ class ที่กำหนด
$assignmentListQuery = "SELECT assignment_id, assignment_title FROM assignments_new WHERE subject_id = ?";
$stmtAssignmentList = mysqli_prepare($conn, $assignmentListQuery);
mysqli_stmt_bind_param($stmtAssignmentList, 'i', $subjectId);
mysqli_stmt_execute($stmtAssignmentList);
$assignmentListResult = mysqli_stmt_get_result($stmtAssignmentList);

// ตรวจสอบการอัปเดตหรือเพิ่มข้อมูล
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['students'])) {
    foreach ($_POST['students'] as $studentId => $data) {
        $score = $data['score'];
        $status = $data['status'];

        // ตรวจสอบว่าข้อมูลการส่งงานของนักเรียนคนนี้มีอยู่แล้วหรือไม่
        $checkQuery = "SELECT * FROM scores_new WHERE student_id = ? AND assignment_id = ?";
        $stmtCheck = mysqli_prepare($conn, $checkQuery);
        mysqli_stmt_bind_param($stmtCheck, 'si', $studentId, $assignmentId);
        mysqli_stmt_execute($stmtCheck);
        $resultCheck = mysqli_stmt_get_result($stmtCheck);

        // กำหนดค่า submission_status ตามค่าของ $status
        $submission_status = match ($status) {
            'ส่งแล้ว' => '1',
            'ไม่ส่ง' => '0',
            'ไม่ตรวจ' => '2',
            default => '0'
        };

        if (mysqli_num_rows($resultCheck) == 0) {
            // ถ้าไม่มีข้อมูล ให้ทำการ INSERT ข้อมูลใหม่
            $insertQuery = "INSERT INTO scores_new (student_id, assignment_id, score, submission_status) VALUES (?, ?, ?, ?)";
            $stmtInsert = mysqli_prepare($conn, $insertQuery);
            mysqli_stmt_bind_param($stmtInsert, 'siis', $studentId, $assignmentId, $score, $submission_status);
            mysqli_stmt_execute($stmtInsert);
        } else {
            // ถ้ามีข้อมูล ให้ทำการ UPDATE ข้อมูลเดิม
            $updateQuery = "UPDATE scores_new SET score = ?, submission_status = ? WHERE student_id = ? AND assignment_id = ?";
            $stmtUpdate = mysqli_prepare($conn, $updateQuery);
            mysqli_stmt_bind_param($stmtUpdate, 'issi', $score, $submission_status, $studentId, $assignmentId);
            mysqli_stmt_execute($stmtUpdate);
        }
    }
}

// ดึงข้อมูลงานที่ระบุจาก assignment_id
if ($assignmentId && $class) {
    $assignmentQuery = "SELECT assignment_title FROM assignments_new WHERE assignment_id = ?";
    $stmtAssignment = mysqli_prepare($conn, $assignmentQuery);
    mysqli_stmt_bind_param($stmtAssignment, 'i', $assignmentId);
    mysqli_stmt_execute($stmtAssignment);
    $assignmentResult = mysqli_stmt_get_result($stmtAssignment);
    $assignment = mysqli_fetch_assoc($assignmentResult);

    // ดึงข้อมูลนักเรียนและคะแนนของงานนี้สำหรับ class ที่เลือก เฉพาะนักเรียนที่มี score_id
    $scoresQuery = "
        SELECT s.student_id, s.student_number, CONCAT(u.fname, ' ', u.lname) AS student_name, sc.score, sc.submission_status 
        FROM students_new s 
        LEFT JOIN user u ON s.user_id = u.user_id
        LEFT JOIN scores_new sc ON s.student_id = sc.student_id 
        WHERE sc.assignment_id = ? 
        AND sc.score_id IS NOT NULL
        AND s.class = ?";
    $stmtScores = mysqli_prepare($conn, $scoresQuery);
    mysqli_stmt_bind_param($stmtScores, 'is', $assignmentId, $class);
    mysqli_stmt_execute($stmtScores);
    $scoresResult = mysqli_stmt_get_result($stmtScores);
}

// ปิดการเชื่อมต่อฐานข้อมูล
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($assignment['assignment_title']) ? $assignment['assignment_title'] : 'เลือกงานที่ต้องการ'; ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 100px;
            margin-top: 30px;
            padding: 20px;
            background-color: #f2ecdd;
        }
        #table,#table2 {
            width: 90%;
            margin: 20px auto;
            border-collapse: collapse;
        }
        #table th, td {
            padding: 8px 12px;
            text-align: center;
            border: 1px solid #ddd;
        }
        #table th {
            background-color: #cd9c20;
            color: black;
        }
        tr:nth-child(even) {
            background-color: #f5cb5c;
        }
        button {
            padding: 10px 20px;
            background-color: #cd9c20;
            color: black;
            border: none;
            cursor: pointer;
        }
        button:active {
            background-color: #F5CB5C; /* สีที่เปลี่ยนเมื่อปุ่มถูกกด */
        }
        input[type=submit] {
            width: 100%;
            background-color: #CD9C20;
            color: white;
            padding: 14px 20px;
            margin: 8px 0;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        input[type=submit]:hover {
            background-color: #F5CB5C;
            color: black;
        }
        input[type=submit]:active {
            background-color: #F5CB5C; /* สีที่เปลี่ยนเมื่อปุ่มถูกกด */
        }   
    </style>
</head>
<body>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <a href="teacher_menu.php" style="color: black;  font-size: 24px; "><i class="fas fa-arrow-left"></i></a>   

    <form method="get" action="">
        <label for="assignment_id_select">เลือก Assignment:</label>
        <select name="assignment_id" id="assignment_id_select">
            <?php while ($row = mysqli_fetch_assoc($assignmentListResult)): ?>
                <option value="<?= $row['assignment_id'] ?>" <?= isset($assignmentId) && $assignmentId == $row['assignment_id'] ? 'selected' : '' ?>>
                    <?= $row['assignment_title'] ?>
                </option>
            <?php endwhile; ?>
        </select>
        <button type="submit">แสดงข้อมูล</button>
    </form>

<?php if (isset($assignmentId) && $assignment && isset($class)): ?>
    <form method="post" action="">
        <table id="table2">
            <tr>
                <th style="text-align: left;"></th>
                <th></th>
                <th style="text-align: right;">ชั้นประถมศึกษาปีที่ : <?= $class ?></th> 
            </tr>
        </table>
        <table id="table">
            <tr>
                <th>เลขที่</th>
                <th>ชื่อ</th>
                <th>คะแนน</th>
                <th>สถานะการส่งงาน</th>
            </tr>
            <?php while ($student = mysqli_fetch_assoc($scoresResult)): ?>
                <tr>
                    <td><?= $student['student_number'] ?></td>
                    <td><?= $student['student_name'] ?></td>
                    <td>
                        <input type="text" name="students[<?= $student['student_id'] ?>][score]" value="<?= isset($student['score']) ? $student['score'] : '' ?>" size="5">
                    </td>
                    <td>
                        <select name="students[<?= $student['student_id'] ?>][status]">
                            <option value="ส่งแล้ว" <?= (isset($student['submission_status']) && $student['submission_status'] == '1') ? 'selected' : '' ?>>ส่งแล้ว</option>
                            <option value="ไม่ส่ง" <?= (isset($student['submission_status']) && $student['submission_status'] == '0') ? 'selected' : '' ?>>ไม่ส่ง</option>
                            <option value="ไม่ตรวจ" <?= (isset($student['submission_status']) && $student['submission_status'] == '2') ? 'selected' : '' ?>>ไม่ตรวจ</option>
                        </select>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
        <div style="text-align: center; margin: 20px;">
        <button type="submit" name="update">อัปเดต</button>
        <a href="report.php?assignment_id=<?= $assignmentId ?>">
            <button type="button">ไปที่หน้า Report</button>
        </a>
    </div>
    </form>
<?php endif; ?>
</body>
</html>
