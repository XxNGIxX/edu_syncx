<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // รับค่าจากฟอร์มนักเรียน
    $email = isset($_POST["email"]) ? $_POST["email"] : '';
    $fname = isset($_POST["fname"]) ? $_POST["fname"] : '';
    $lname = isset($_POST["lname"]) ? $_POST["lname"] : '';
    $tel = isset($_POST["tel"]) ? $_POST["tel"] : '';

    $conn = mysqli_connect("localhost", "root", "", "edu_syncx");

    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    // ตรวจสอบว่ามี email นี้ในระบบแล้วหรือไม่
    $user_sql = "SELECT user_id FROM user WHERE email = ?";
    $stmt = mysqli_prepare($conn, $user_sql);
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) > 0) {
        // ถ้า email มีอยู่แล้ว ให้ดึง user_id
        mysqli_stmt_bind_result($stmt, $user_id);
        mysqli_stmt_fetch($stmt);
        $message = "ผู้ใช้นี้มีอยู่แล้วในระบบ! (User ID: $user_id)";

        // ตรวจสอบว่ามี user_id นี้ในตาราง teachers_new หรือยัง
        $check_teacher_sql = "SELECT user_id FROM teachers_new WHERE user_id = ?";
        $stmt_check_teacher = mysqli_prepare($conn, $check_teacher_sql);
        mysqli_stmt_bind_param($stmt_check_teacher, 'i', $user_id);
        mysqli_stmt_execute($stmt_check_teacher);
        mysqli_stmt_store_result($stmt_check_teacher);

        if (mysqli_stmt_num_rows($stmt_check_teacher) == 0) {
            // ถ้าไม่มีอยู่ใน teachers_new ให้เพิ่มใหม่
            $insert_teacher_sql = "INSERT INTO teachers_new (user_id) VALUES (?)";
            $stmt_insert_teacher = mysqli_prepare($conn, $insert_teacher_sql);
            mysqli_stmt_bind_param($stmt_insert_teacher, 'i', $user_id);
            mysqli_stmt_execute($stmt_insert_teacher);

            $message = "เพิ่มผู้ใช้ลงในตาราง teachers_new สำเร็จแล้ว!";
        } else {
            $message = "ผู้ใช้มีอยู่ในตาราง teachers_new แล้ว!";
        }
    } else {
        // ฟังก์ชันสร้างรหัสผ่าน
        function generate_password($prefix, $length = 7) {
            $numbers = '0123456789';
            $randomNumber = substr(str_shuffle($numbers), 0, $length);
            return $prefix . $randomNumber;
        }

        $pwd_t = generate_password('T');
        $user_role_teacher = "ครู";

        // เพิ่มข้อมูลในตาราง user
        $insert_teacher_sql = "INSERT INTO user (fname, lname, role, tel, email, password) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt_teacher = mysqli_prepare($conn, $insert_teacher_sql);
        mysqli_stmt_bind_param($stmt_teacher, 'ssssss', $fname, $lname, $user_role_teacher, $tel, $email, $pwd_t);
        $result_teacher = mysqli_stmt_execute($stmt_teacher);

        if ($result_teacher) {
            // ดึง user_id ของครูที่เพิ่งถูกเพิ่ม
            $user_id = mysqli_insert_id($conn);

            // เพิ่มข้อมูลในตาราง teachers_new
            $teacher_sql = "INSERT INTO teachers_new (user_id) VALUES (?)";
            $stmt_insert_teacher = mysqli_prepare($conn, $teacher_sql);
            mysqli_stmt_bind_param($stmt_insert_teacher, 'i', $user_id);
            mysqli_stmt_execute($stmt_insert_teacher);

            $message = "ลงทะเบียนผู้ใช้ใหม่และเพิ่มลงในตาราง teachers_new สำเร็จแล้ว!";
            header('Location: admin.php');
        }
    }

    mysqli_close($conn);
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
    </style>
</head>
<body>
    <div>

        <h3>ลงทะเบียน</h3>
        <?php if (!empty($message)): ?>
            <div class="message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <form action="" method="post">
            <img class="mb-4" src="login.png" alt="" height="300">
            <label for="fname">ชื่อ*</label><br>
            <input type="text" id="fname" name="fname" placeholder="Name" required><br>

            <label for="lname">นามสกุล*</label><br>
            <input type="text" id="lname" name="lname" placeholder="Lastname" required><br>           

            <label for="email">อีเมล*</label><br>
            <input type="text" id="email" name="email" placeholder="email" required><br>

            <label for="tel">เบอร์โทร*</label><br>
            <input type="tel" id="tel" name="tel" placeholder="กรอกเบอร์โทร" required><br><br>

            <input type="submit" value="ถัดไป">
        </form>
    </div>
</body>
</html>