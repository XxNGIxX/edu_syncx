<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "edu_syncx");

$targetSubjectId = $_SESSION['subject_id'];

// ดึงข้อมูล assignments ที่เกี่ยวข้องกับ subject_id
$sel_sql = "SELECT * FROM assignments_new WHERE subject_id ='" . $targetSubjectId . "'";
$rs = mysqli_query($conn, $sel_sql);

?>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assignment Details</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h1>Assignment Details</h1>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Assignment Title</th>
                    <th>Assignment Details</th>
                    <th>Deadline</th>
                    <th>Total Points</th>
                    <th>Total Points</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($aina = mysqli_fetch_assoc($rs)) { ?>
                <tr>
                    <td><?= htmlspecialchars($aina['assignment_title']); ?></td>
                    <td><?= htmlspecialchars($aina['assignment_details']); ?></td>
                    <td><?= htmlspecialchars($aina['deadline']); ?></td>
                    <td><?= htmlspecialchars($aina['total_points']); ?></td>
                    <td><a href="edit_assign.php?assignment_id=<?=$aina["assignment_id"]?>"class="btn btn-primary">แก้ไขข้อมูล</a></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>

    
    </div>
</body>
</html>
