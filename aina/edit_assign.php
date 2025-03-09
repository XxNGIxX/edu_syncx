<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "edu_syncx");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  
    $title = $_POST['assignment_title'];
    $points = $_POST['total_points'];
    $deadline = $_POST['deadline'];
    $details = $_POST['assignment_details'];

    // รับค่า assignment_id
    $Id = $_REQUEST['assignment_id'];
    $subjectId = $_SESSION['subject_id'];

    // อัปเดตข้อมูล assignment
    $update_sql = "UPDATE `assignments_new` SET 
        `assignment_title` = '$title',
        `total_points` = '$points',
        `deadline` = '$deadline',
        `assignment_details` = '$details'
    WHERE `assignment_id` = '$Id' AND `subject_id` = '$subjectId'";  // แก้ไขตรงนี้

    $rs = mysqli_query($conn, $update_sql);

    if ($rs) {
        header("Location: teacher.php");
    } else {
        echo "<script>
        alert('เกิดข้อผิดพลาดในการอัปเดตข้อมูล');
      </script>";
    }
}

// ดึงข้อมูล assignment ที่ต้องการแก้ไข
$targetSubjectId = $_SESSION['subject_id'];
$Id = $_REQUEST["assignment_id"];

$sel_sql = "SELECT * FROM assignments_new WHERE assignment_id ='" . $Id . "'";
$rs = mysqli_query($conn, $sel_sql);
$aina = mysqli_fetch_assoc($rs);

// ดึงข้อมูลวิชาที่เกี่ยวข้อง
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

?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Itim&family=Noto+Serif+Thai:wght@100..900&family=Roboto+Condensed:wght@300&family=Sriracha&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assing.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>assign</title>
</head>
<body>
    <a href="teacher.php" style="color: black;  font-size: 24px;
        position: absolute; top: 30px; left: 50px;"><i class="fas fa-arrow-left"></i></a>
    <div class="container">
        <form action="" method="POST">
            <div class="header"></div>

            <label for="subject_id_select"></label>
            วิชา: <?= htmlspecialchars($subjectName) ?>- ชั้น<?= htmlspecialchars($class) ?>- เทอม <?= htmlspecialchars($term) ?> / ปี <?= htmlspecialchars($year) ?></h2>

            <div class="section">
                <div class="label">หัวข้องาน</div>
                <input type="text" class="input-box" id="assignment_title" 
                       value="<?= isset($aina['assignment_title']) ? htmlspecialchars($aina['assignment_title']) : ''; ?>" 
                       name="assignment_title" required>
            </div>

            <div class="section">
                <div class="label">รายละเอียดงาน</div>
                <textarea class="input-box" id="assignment_details" rows="4" name="assignment_details" required><?= isset($aina['assignment_details']) ? htmlspecialchars($aina['assignment_details']) : ''; ?></textarea>
            </div>

            <div class="footer">
                <div class="label">กำหนดส่ง</div>
                <input type="date" id="deadline" class="date-picker" name="deadline" 
                       value="<?= isset($aina['deadline']) ? htmlspecialchars($aina['deadline']) : ''; ?>" required>

                <div class="score">คะแนน</div>
                <input type="number" id="total_points" class="score-input" name="total_points" 
                       min="0" max="100" placeholder="0" 
                       value="<?= isset($aina['total_points']) ? $aina['total_points'] : ''; ?>" required>

                <button type="submit" class="status">เสร็จสิ้น</button>
            </div>

            <a href="teacher.php" style="color: black;  font-size: 24px; position: absolute; top : 110%; right: 50px;"><i class="fas fa-home"></i></a>
        </form>
    </div>
</body>
</html>
