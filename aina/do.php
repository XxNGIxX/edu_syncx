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
        SELECT u.user_id, u.role, s.student_id, t.teacher_id, t.subject_id
        FROM user u
        LEFT JOIN students_new s ON u.user_id = s.user_id
        LEFT JOIN teachers_new t ON u.user_id = t.user_id
        WHERE u.email = ? AND u.password = ?
    ";
    $stmt_login = mysqli_prepare($conn, $login_sql);
    mysqli_stmt_bind_param($stmt_login, 'ss', $email, $password);
    mysqli_stmt_execute($stmt_login);
    mysqli_stmt_bind_result($stmt_login, $userId, $userType, $studentId, $teacherId, $subjectId);
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
            $_SESSION["subject_id"] = $subjectId; // เก็บ subject_id ในเซสชัน
            header("Location: teacher.php");
        } elseif ($userType === 'ผู้ปกครอง') {
            // ดึง student_id ที่เชื่อมโยงกับผู้ปกครอง
            $parent_student_sql = "
                SELECT s.student_id 
                FROM parent psm
                JOIN students_new s ON psm.student_id = s.student_id
                WHERE psm.parent_id = ?
            ";
            $stmt_parent = mysqli_prepare($conn, $parent_student_sql);
            mysqli_stmt_bind_param($stmt_parent, 'i', $userId); // ใช้ user_id ที่เป็นผู้ปกครอง
            mysqli_stmt_execute($stmt_parent);
            mysqli_stmt_bind_result($stmt_parent, $linkedStudentId);
            mysqli_stmt_fetch($stmt_parent);
            mysqli_stmt_close($stmt_parent);

            if ($linkedStudentId) {
                $_SESSION["student_id"] = $linkedStudentId; // เก็บ student_id ของนักเรียนที่ผู้ปกครองดูแล
                header("Location: student.php"); // หรือหน้าอื่นที่เหมาะสม
            } else {
                echo "<script>
                    alert('ไม่พบนักเรียนที่เชื่อมโยงกับบัญชีผู้ปกครองนี้');
                    window.location = 'login.php';
                </script>";
            }
        }
        exit();
    } else {
        echo "<script>
            alert('รหัสผิด! กรุณาลองใหม่อีกครั้ง');
            window.location = 'login.php';
        </script>";
    }
}

// ปิดการเชื่อมต่อฐานข้อมูล
mysqli_close($conn);
?>
            