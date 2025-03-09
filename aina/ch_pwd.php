<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "edu_syncx");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $current_password = $_POST["current_password"];
    $new_password = $_POST["new_password"];
    $confirm_password = $_POST["confirm_password"];

    // ตรวจสอบว่าอีเมลมีอยู่ในฐานข้อมูล
    $sql = "SELECT user_id, password FROM user WHERE email = '$email'";
    $result = mysqli_query($conn, $sql);
    $userData = mysqli_fetch_assoc($result);

    if ($userData) {
        // ตรวจสอบรหัสผ่านปัจจุบัน
        if ($current_password === $userData['password']) {
            // ตรวจสอบว่ารหัสผ่านใหม่ตรงกัน
            if ($new_password === $confirm_password) {
            

                // อัปเดตรหัสผ่านในฐานข้อมูล
                $user_id = $userData['user_id'];
                $update_sql = "UPDATE user SET password = '$new_password' WHERE user_id = '$user_id'";
                if (mysqli_query($conn, $update_sql)) {
                    echo "เปลี่ยนรหัสผ่านเรียบร้อยแล้ว";
                } else {
                    echo "เกิดข้อผิดพลาดในการเปลี่ยนรหัสผ่าน";
                }
            } else {
                echo "รหัสผ่านใหม่ไม่ตรงกัน";
            }
        } else {
            echo "รหัสผ่านปัจจุบันไม่ถูกต้อง";
        }
    } else {
        echo "ไม่พบอีเมลนี้ในระบบ";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เปลี่ยนรหัสผ่าน</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            padding: 20px;
        }
        form {
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        input[type="email"], input[type="password"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        input[type="submit"] {
            background: #5cb85c;
            color: white;
            border: none;
            padding: 10px;
            cursor: pointer;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <h2>เปลี่ยนรหัสผ่าน</h2>
    <form method="post" action="">
        <label for="email">อีเมล:</label>
        <input type="email" name="email" required>
        
        <label for="current_password">รหัสผ่านปัจจุบัน:</label>
        <input type="password" name="current_password" required>
        
        <label for="new_password">รหัสผ่านใหม่:</label>
        <input type="password" name="new_password" required>
        
        <label for="confirm_password">ยืนยันรหัสผ่านใหม่:</label>
        <input type="password" name="confirm_password" required>
        
        <input type="submit" value="เปลี่ยนรหัสผ่าน">
    </form>
</body>
</html>
