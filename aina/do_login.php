<?php
session_start(); // เริ่มต้นการจัดการเซสชัน

// ตรวจสอบการเชื่อมต่อฐานข้อมูล
$conn = mysqli_connect("localhost", "root", "", "edu_syncx");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$message = ''; // ตัวแปรสำหรับเก็บข้อความแจ้งเตือน

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = isset($_POST["email"]) ? $_POST["email"] : '';
    $password = isset($_POST["password"]) ? $_POST["password"] : '';

    // ป้องกัน SQL Injection
    $login_sql = "
        SELECT u.user_id, u.role, s.student_id, t.teacher_id
        FROM user u
        LEFT JOIN students_new s ON u.user_id = s.user_id
        LEFT JOIN teachers_new t ON u.user_id = t.user_id
        WHERE u.email = ? AND u.password = ?
    ";
    $stmt_login = mysqli_prepare($conn, $login_sql);
    mysqli_stmt_bind_param($stmt_login, 'ss', $email, $password);
    mysqli_stmt_execute($stmt_login);
    mysqli_stmt_bind_result($stmt_login, $userId, $userType, $studentId, $teacherId);
    mysqli_stmt_fetch($stmt_login);
    mysqli_stmt_close($stmt_login);

    if ($userId) {
        // ตั้งค่าข้อมูลเซสชัน
        $_SESSION["user_id"] = $userId;
        $_SESSION["role"] = $userType;

        // เก็บค่า student_id หรือ teacher_id และ subject_id ตามประเภทของบัญชี
        if ($userType === 'นักเรียน') {
            $_SESSION["student_id"] = $studentId; // เก็บ student_id ในเซสชัน
            header("Location: student.php");
        } elseif ($userType === 'ครู') {
            $_SESSION["teacher_id"] = $teacherId; // เก็บ teacher_id ในเซสชัน

            // ตรวจสอบว่าครูมีการเชื่อมโยงกับวิชาหรือไม่
            $subjectCheckQuery = "SELECT subject_id FROM subject WHERE teacher_id = ?";
            $stmt_subjectCheck = mysqli_prepare($conn, $subjectCheckQuery);
            mysqli_stmt_bind_param($stmt_subjectCheck, 'i', $teacherId);
            mysqli_stmt_execute($stmt_subjectCheck);
            mysqli_stmt_store_result($stmt_subjectCheck);

            if (mysqli_stmt_num_rows($stmt_subjectCheck) > 0) {
                // หากมีการเชื่อมโยงวิชา ให้เปลี่ยนเส้นทางไปที่ subject_teach.php
                header("Location: subject_teach.php");
            } else {
                // หากไม่มีให้ไปที่หน้า select_subject.php ตามปกติ
                header("Location: select_subject.php");
            }
            mysqli_stmt_close($stmt_subjectCheck);

        } elseif ($userType === 'ผู้ปกครอง') {
            // ดึง student_id ที่เชื่อมโยงกับผู้ปกครอง
            header("Location: select_student.php");
        } elseif ($userType === 'admin') {
            // ดึง student_id ที่เชื่อมโยงกับผู้ปกครอง
            header("Location: admin.php");
        }
        exit();
    } else {
        echo "<script>
                    alert('รหัสผิด!');
                    window.location = 'login.php';
                </script>";
    }
}

// ปิดการเชื่อมต่อฐานข้อมูล
mysqli_close($conn);

?>
