<?php
session_start();
$teacher_name = $_SESSION["user_id"];
$conn = mysqli_connect("localhost", "root", "", "edu_syncx");

// ดึงชื่อผู้ปกครอง
$sql = "SELECT fname, lname FROM user WHERE user_id = '$teacher_name'";
$result = mysqli_query($conn, $sql);
$name_p = mysqli_fetch_assoc($result);

// ดึง teacher_id
$teacherQuery = "SELECT teacher_id FROM teachers_new WHERE user_id = '$teacher_name'";
$rs_t = mysqli_query($conn, $teacherQuery);
$teacherData = mysqli_fetch_assoc($rs_t);
$teacher_id = $teacherData['teacher_id'];

// ดึงข้อมูลนักเรียนที่มี parent_id ตรงกับผู้ปกครอง
$classQuery = "
    SELECT s.subject_id, s.subject_name, s.term, s.year, s.class 
    FROM subject AS s
    JOIN teachers_new AS t ON s.teacher_id = t.teacher_id 
    WHERE s.teacher_id = '$teacher_id'
";
$classResult = mysqli_query($conn, $classQuery);

if ($_SERVER["REQUEST_METHOD"] == "POST") 
{
    $subject_id = isset($_POST["subject_id"]) ? $_POST["subject_id"] : '';
    $_SESSION["subject_id"] = $subject_id;
    header("Location: teacher.php");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Scores - ชั้น <?= $targetClass ?? 'เลือกชั้นเรียน' ?></title>
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
            <h2>ชื่อ : <?php echo $name_p["fname"] . " " . $name_p["lname"]; ?></h2>
            <h4>เลือกวิชา</h4>
            <form method="post" action="">
                <p>
                    <select name="subject_id" id="subject_id" required>
                        <option value="" selected disabled>เลือกรายวิชา</option>

                        <?php while ($row = mysqli_fetch_assoc($classResult)): ?>
                            <option value="<?= htmlspecialchars($row['subject_id']) ?>">
                                <?= htmlspecialchars($row['subject_name'] . " - ชั้น " . $row['class'] . " - เทอม " . $row['term'] . " ปีการศึกษา " . $row['year']) ?>
                            </option>
                        <?php endwhile; ?>

                    </select>
                </p>
                <input type="submit" value="ถัดไป"> 
            </form>
        </main>
    </div>
</body>
</html>
