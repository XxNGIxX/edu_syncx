<?php
session_start();
$conn = new mysqli("localhost", "root", "", "edu_syncx");

$message = ""; // กำหนดตัวแปรสำหรับเก็บข้อความแจ้งเตือน

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $subject_name = $_POST['subject_name'];
    $term = $_POST['term'];
    $year = $_POST['year'];
    $class = $_POST['class'];
    $teacher = $_SESSION["teacher_id"];

    // Prepare SQL query to insert data into the 'subjects' table
    $sql = "INSERT INTO subject (subject_name, term, year, teacher_id, class) VALUES (?, ?, ?, ?, ?)";
    
    // Prepare and bind statement
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssiii", $subject_name, $term, $year, $teacher, $class); // Bind parameters

    if ($stmt->execute()) {
        // ดึง subject_id ล่าสุดที่ถูกสร้างขึ้น
        $_SESSION['subject_id'] = $conn->insert_id; // บันทึก subject_id ล่าสุดลงในเซสชัน
        $message = "Data inserted successfully!"; // กำหนดข้อความเมื่อบันทึกข้อมูลสำเร็จ
    } else {
        $message = "Error: " . $stmt->error; // กำหนดข้อความเมื่อเกิดข้อผิดพลาด
    }

    // Close the connection
    $stmt->close();
    $conn->close();
}

?>

<!doctype html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css2?family=Itim&family=Noto+Serif+Thai:wght@100..900&family=Roboto+Condensed:wght@300&family=Sriracha&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">
    <title>REGIST</title>
    <style>
        body {
            padding: 20px; 
            margin: 60px auto;
            background-color: #F2ECDD;
        }

        input[type=text], input[type=password], input[type=tel], select {
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
            background-color: #CD9C20;
            color: white;
            padding: 14px 20px;
            margin: 8px 0;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .gg {
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

        div {
            width: 700px;
            background-color: #fff;
            box-shadow: 0 7px 12px 0 #0000;
            border-radius: 10px;
            padding: 20px; 
            margin: auto;
            font-family: "Noto Serif Thai", serif;
        }

        h3 {
            text-align: center;
            font-family: "Noto Serif Thai", serif;
        }

        .message {
            color: red;
            text-align: center;
            font-weight: bold;
        }

        /* ปรับแต่งลิงค์เป็นปุ่ม */
        .btn-assign {
            width: 100%;
            background-color: #CD9C20;
            color: white;
            padding: 14px 20px;
            margin-top: 20px;
            border: none;
            border-radius: 4px;
            text-align: center;
            text-decoration: none; /* ทำให้ไม่มีกระดาษใต้ลิงค์ */
            display: inline-block; /* ทำให้แสดงเป็นปุ่ม */
            cursor: pointer;
        }

        .btn-assign:hover {
            background-color: #F5CB5C;
            color: black;
        }
    </style>
</head>
<body>
    <div>

        <h3>ลงทะเบียนรายวิชา</h3>
        <?php if (!empty($message)): ?>
            
            <script>
                alert("<?php echo htmlspecialchars($message); ?>"); // แสดงข้อความแจ้งเตือน
            </script>
        <?php endif; ?>
        <form action="" method="post">

            <label for="subject_name">ชื่อวิชา*</label><br>
            <input type="text" id="subject_name" name="subject_name" placeholder="" required><br>

            <label for="term">ภาคเรียน*</label><br>
            <input type="text" id="term" name="term" placeholder="" required><br>

            <label for="year">ปีการศึกษา*</label><br>
            <input type="text" id="year" name="year" placeholder="" required><br>

            <label for="class">ระดับชั้นที่สอน*</label><br>
            <input type="text" id="class" name="class" placeholder="" required><br>

            <input type="submit" value="ลงทะเบียน">
        </form>
        
        <a href="subject_teach.php" class="btn-assign">เสร็จสิ้น</a>

    </div>
</body>
</html>
