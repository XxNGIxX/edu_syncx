<?php
session_start();
$parent_name = $_SESSION["user_id"];
$conn = mysqli_connect("localhost", "root", "", "edu_syncx");

// ดึงชื่อผู้ปกครอง
$sql = "SELECT fname, lname FROM user WHERE user_id = '$parent_name'";
$result = mysqli_query($conn, $sql);
$name_p = mysqli_fetch_assoc($result);

// ดึง parent_id
$parentQuery = "SELECT parent_id FROM parent WHERE user_id = '$parent_name'";
$rs_p = mysqli_query($conn, $parentQuery);
$parentData = mysqli_fetch_assoc($rs_p);
$parent_id = $parentData['parent_id'];

// ดึงข้อมูลนักเรียนที่มี parent_id ตรงกับผู้ปกครอง
$classQuery = "
    SELECT s.student_id, u.fname, u.lname, s.class 
    FROM students_new AS s
    JOIN user AS u ON s.user_id = u.user_id 
    WHERE s.parent_id = '$parent_id'
";
$classResult = mysqli_query($conn, $classQuery);


if ($_SERVER["REQUEST_METHOD"] == "POST") 
{

    $studentId = isset($_POST["student_id"]) ? $_POST["student_id"] : '';
    $_SESSION["student_id"] = $studentId;
    header("Location: student.php");
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
            <h4>เลือกบุตร</h4>
            <form method="post" action=""> <!-- เปลี่ยน your_action_page.php เป็น URL ของคุณ -->
                <p>
                    <select name="student_id" id="student_id" required>
                        <option value="" selected disabled>เลือกรายชื่อนักเรียน</option>

                        <?php while ($row = mysqli_fetch_assoc($classResult)): ?>
                            <option value="<?= htmlspecialchars($row['student_id']) ?>">
                                <?= htmlspecialchars($row['fname'] . " " . $row['lname']) ?> - ชั้น <?= htmlspecialchars($row['class']) ?>
            
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
