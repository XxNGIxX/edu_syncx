<?php
session_start(); // ตรวจสอบว่า session เริ่มต้นหรือไม่

// ตรวจสอบการเชื่อมต่อฐานข้อมูล
$conn = mysqli_connect("localhost", "root", "", "edu_syncx");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// ตรวจสอบว่า teacher_id อยู่ใน session หรือไม่
if (isset($_SESSION["teacher_id"])) {
    $teacher_id = $_SESSION["teacher_id"]; // ดึง teacher_id จากเซสชัน

    // ดึงข้อมูลวิชาที่ครูได้ลงทะเบียนสอนจากฐานข้อมูล
    $sql = "
        SELECT sub.subject_id, sub.subject_name, sub.class, sub.term, sub.year
        FROM subject sub
        JOIN subject ts ON sub.subject_id = ts.subject_id
        WHERE ts.teacher_id = ?
    ";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $teacher_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    mysqli_stmt_close($stmt);
} else {
    // หากไม่มี teacher_id ใน session ให้แสดงข้อความแจ้งเตือนหรือเปลี่ยนเส้นทางไปหน้าอื่น
    echo "ไม่พบข้อมูลครูในระบบ!";
    exit();
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Itim&family=Noto+Serif+Thai:wght@100..900&family=Roboto+Condensed:wght@300&family=Sriracha&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <link rel="stylesheet" href="assing.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>assign</title>
</head>
<body>
    <a href="teacher.php" style="color: black;  font-size: 24px;
        position: absolute; top: 30px; left: 50px;"><i class="fas fa-arrow-left"></i></a>
    <div class="container">
        <form action="do_assign.php" method="POST">
            <div class="header">
            </div>

            <label for="subject_id_select">วิชา :</label>
            <select name="subject_id" id="subject_id_select" required>
                <option value="">--เลือกวิชา--</option>
                <?php
                if ($result && mysqli_num_rows($result) > 0) {
                    // วนลูปข้อมูลแต่ละแถวจากฐานข้อมูล
                    while($row = mysqli_fetch_assoc($result)) {
                        // ปรับการแสดงผลวิชาตามที่ต้องการ
                        echo "<option value='" . $row["subject_id"] . "'>" 
                            . htmlspecialchars($row["subject_name"]) 
                            . "- ชั้น " . htmlspecialchars($row["class"])
                            . "- เทอม " . htmlspecialchars($row["term"]) 
                            . " / ปี " . htmlspecialchars($row["year"]) 
                            . "</option>";
                    }
                } else {
                    echo "<option value=''>ไม่มีวิชาที่ลงทะเบียน</option>";
                }
                ?>
            </select>
            
            <div class="section">
                <div class="label">หัวข้องาน</div>
                <input type="text" class="input-box" id="assignment_title" placeholder="กรอกหัวข้องาน" name="assignment_title" required>
            </div>
            
            <div class="section">
                <div class="label">รายละเอียดงาน</div>
                <textarea class="input-box" id="assignment_details" placeholder="กรอกรายละเอียดงานที่ต้องการมอบหมาย" rows="4" name="assignment_details" required></textarea>
            </div>
            
            <div class="footer">
                <div class="label">กำหนดส่ง</div>
                <input type="date" id="deadline" class="date-picker" name="deadline" required>
                <div class="score">คะแนน</div>
                <input type="number" id="total_points" class="score-input" name="total_points" min="0" max="100" placeholder="0" required>
                <button type="submit" class="status">เสร็จสิ้น</button>
            </div>
        </form>
    </div>
    <a href="teacher.php" style="color: black;  font-size: 24px; position: absolute; top : 110%; right: 50px;"><i class="fas fa-home"></i></a>

</body>
</html>
