<?php
session_start();
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // รับค่าจากฟอร์มนักเรียน
    $email_s = isset($_POST["email_student"]) ? $_POST["email_student"] : '';
    $fname_s = isset($_POST["fname_student"]) ? $_POST["fname_student"] : '';
    $lname_s = isset($_POST["lname_student"]) ? $_POST["lname_student"] : '';
    $tel_s = isset($_POST["tel_student"]) ? $_POST["tel_student"] : '';

    $std_num = isset($_POST["student_number"]) ? $_POST["student_number"] : '';
    $class = isset($_POST["class"]) ? $_POST["class"] : '';
    
    // รับค่าจากฟอร์มผู้ปกครอง
    $email_p = isset($_POST["email_parent"]) ? $_POST["email_parent"] : '';
    $fname_p = isset($_POST["fname_parent"]) ? $_POST["fname_parent"] : '';
    $lname_p = isset($_POST["lname_parent"]) ? $_POST["lname_parent"] : '';
    $tel_p = isset($_POST["tel_parent"]) ? $_POST["tel_parent"] : '';

    $conn = mysqli_connect("localhost", "root", "", "edu_syncx");

    // ตรวจสอบการเชื่อมต่อ
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    // ตรวจสอบรูปแบบอีเมลนักเรียนและผู้ปกครอง
    if (!filter_var($email_s, FILTER_VALIDATE_EMAIL) || !filter_var($email_p, FILTER_VALIDATE_EMAIL)) {
        $message = "รูปแบบอีเมลไม่ถูกต้อง!";
    } else {
        // ตรวจสอบว่าอีเมลนักเรียนมีในฐานข้อมูลแล้วหรือไม่
        $user_sql = "SELECT email FROM user WHERE email = ?";
        $stmt = mysqli_prepare($conn, $user_sql);
        mysqli_stmt_bind_param($stmt, 's', $email_s);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            $message = "อีเมลนักเรียนนี้ถูกใช้ไปแล้ว กรุณาใช้อีเมลอื่น!";
        } else {
            // ฟังก์ชันสร้างรหัสผ่าน
            function generate_password($prefix, $length = 7) {
                $numbers = '0123456789';
                $randomNumber = substr(str_shuffle($numbers), 0, $length);
                return $prefix . $randomNumber;
            }

            // รหัสผ่านนักเรียนและผู้ปกครอง
            $pwd_s = generate_password('S');
            $pwd_p = generate_password('P');

            // แปลงค่า class เป็น degree
            if (preg_match('/^(\d+)\//', $class, $matches)) {
                $degree = $matches[1]; // ตัวเลขที่อยู่หน้าสแลช
            } else {
                $degree = 0; // ค่าดีฟอลต์กรณีที่ไม่ตรงกับรูปแบบ
            }

            // เพิ่มข้อมูลนักเรียนลงในตาราง user
            $user_role_student = "นักเรียน";
            $insert_student_sql = "INSERT INTO user (fname, lname, role, tel, email, password) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt_student = mysqli_prepare($conn, $insert_student_sql);
            mysqli_stmt_bind_param($stmt_student, 'ssssss', $fname_s, $lname_s, $user_role_student, $tel_s, $email_s, $pwd_s);
            $result_student = mysqli_stmt_execute($stmt_student);

            if ($result_student) {
                // ตรวจสอบอีเมลผู้ปกครองในระบบ
                $parent_email_check_sql = "SELECT u.user_id, p.parent_id FROM user u JOIN parent p ON u.user_id = p.user_id WHERE u.email = ?";
                $stmt_parent_check = mysqli_prepare($conn, $parent_email_check_sql);
                mysqli_stmt_bind_param($stmt_parent_check, 's', $email_p);
                mysqli_stmt_execute($stmt_parent_check);
                mysqli_stmt_store_result($stmt_parent_check);
                mysqli_stmt_bind_result($stmt_parent_check, $user_id_parent, $parent_id);

                if (mysqli_stmt_num_rows($stmt_parent_check) > 0) {
                    // หากผู้ปกครองมีอยู่ในระบบแล้ว ให้ใช้ parent_id เดิม
                    mysqli_stmt_fetch($stmt_parent_check);
                } else {
                    // หากไม่มีผู้ปกครองในระบบ ให้บันทึกข้อมูลใหม่ในตาราง user และ parent
                    $user_role_parent = "ผู้ปกครอง";
                    $insert_parent_sql = "INSERT INTO user (fname, lname, role, tel, email, password) VALUES (?, ?, ?, ?, ?, ?)";
                    $stmt_parent = mysqli_prepare($conn, $insert_parent_sql);
                    mysqli_stmt_bind_param($stmt_parent, 'ssssss', $fname_p, $lname_p, $user_role_parent, $tel_p, $email_p, $pwd_p);
                    $result_parent = mysqli_stmt_execute($stmt_parent);

                    if ($result_parent) {
                        // เพิ่มข้อมูลลงในตาราง parent
                        $insert_parent = "INSERT INTO parent (user_id) VALUES ((SELECT user_id FROM user WHERE email = ? LIMIT 1))";
                        $stmt_insert_parent = mysqli_prepare($conn, $insert_parent);
                        mysqli_stmt_bind_param($stmt_insert_parent, 's', $email_p);
                        mysqli_stmt_execute($stmt_insert_parent);

                        // ดึง parent_id ใหม่
                        $parent_sh = "SELECT parent_id FROM parent WHERE user_id = (SELECT user_id FROM user WHERE email = ? LIMIT 1)";
                        $stmt_parent_sh = mysqli_prepare($conn, $parent_sh);
                        mysqli_stmt_bind_param($stmt_parent_sh, 's', $email_p);
                        mysqli_stmt_execute($stmt_parent_sh);
                        $result_parent_sh = mysqli_stmt_get_result($stmt_parent_sh);

                        if ($result_parent_sh && $parent_info = mysqli_fetch_assoc($result_parent_sh)) {
                            $parent_id = $parent_info['parent_id'];
                        } else {
                            $message = "ไม่สามารถดึงข้อมูลผู้ปกครองได้";
                            exit;
                        }
                    } else {
                        $message = "เกิดข้อผิดพลาดในการบันทึกข้อมูลผู้ปกครอง: " . mysqli_error($conn);
                        exit;
                    }
                }

                // ดึง user_id ของนักเรียน
                $user_sh = "SELECT user_id FROM user WHERE email = ?";
                $stmt_user_sh = mysqli_prepare($conn, $user_sh);
                mysqli_stmt_bind_param($stmt_user_sh, 's', $email_s);
                mysqli_stmt_execute($stmt_user_sh);
                $result_user_sh = mysqli_stmt_get_result($stmt_user_sh);

                if ($result_user_sh && $student_info = mysqli_fetch_assoc($result_user_sh)) {
                    $student_id = $student_info['user_id'];
                } else {
                    $message = "ไม่สามารถดึงข้อมูลนักเรียนได้";
                    exit;
                }

                // บันทึกข้อมูลนักเรียนในตาราง students_new โดยเพิ่มค่า degree
                $insert_student_sql = "INSERT INTO students_new (student_number, class, degree, user_id, parent_id) VALUES (?, ?, ?, ?, ?)";
                $stmt_insert_student = mysqli_prepare($conn, $insert_student_sql);
                mysqli_stmt_bind_param($stmt_insert_student, 'sssss', $std_num, $class, $degree, $student_id, $parent_id);
                $result_student_insert = mysqli_stmt_execute($stmt_insert_student);

                if ($result_student_insert) {
                    $message = "ลงทะเบียนสำเร็จทั้งนักเรียนและผู้ปกครอง!";
                    header('Location: admin.php');
                } else {
                    $message = "เกิดข้อผิดพลาดในการบันทึกข้อมูลนักเรียน: " . mysqli_error($conn);
                }
            }
        }
    }
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
            <input type="text" id="fname" name="fname_student" placeholder="Name" required><br>

            <label for="lname">นามสกุล*</label><br>
            <input type="text" id="lname" name="lname_student" placeholder="Lastname" required><br>

            
            <label for="fname">ชั้น*</label><br>
            <input type="text" id="class" name="class" placeholder="" required><br>

            <label for="lname">เลขที่*</label><br>
            <input type="text" id="student_number" name="student_number" placeholder="" required><br>
           

            <label for="email">อีเมล*</label><br>
            <input type="text" id="email" name="email_student" placeholder="email" required><br>

            <label for="tel">เบอร์โทร*</label><br>
            <input type="tel" id="tel" name="tel_student" placeholder="กรอกเบอร์โทร" required><br><br>

            
            <h3>ข้อมูลผู้ปกครอง</h3>

            <label for="fname">ชื่อ*</label><br>
            <input type="text" id="fname" name="fname_parent" placeholder="Name" required><br>

            <label for="lname">นามสกุล*</label><br>
            <input type="text" id="lname" name="lname_parent" placeholder="Lastname" required><br>

            <label for="email">อีเมล*</label><br>
            <input type="text" id="email" name="email_parent" placeholder="email" required><br>

            <label for="tel">เบอร์โทร*</label><br>
            <input type="tel" id="tel" name="tel_parent" placeholder="กรอกเบอร์โทร" required><br>

            <input type="submit" value="ถัดไป">

           
        </form>
    </div>
</body>
</html>