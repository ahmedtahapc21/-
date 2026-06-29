<?php
// تأكد من أن الجلسة بدأت وتأكد من أن المستخدم مدرس (أمان إضافي لو تحب)
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_GET['grade'])) {
    
    // 1. الاتصال بقاعدة البيانات الموحدة للمنصة
    $mysqli = new mysqli("localhost", "root", "", "education_platform");

    if ($mysqli->connect_error) {
        die("فشل الاتصال: " . $mysqli->connect_error);
    }
    
    $mysqli->set_charset("utf8mb4");

    // 2. حماية وتجهيز البيانات
    $grade_id = $mysqli->real_escape_string($_GET['grade']);
    $chapter_name = $mysqli->real_escape_string($_POST['chapter_name']);

    // 3. إدخال الفصل مع ربطه برقم السنة الدراسية (grade_id) في نفس قاعدة البيانات
    // تأكد أن جدول chapters عندك يحتوي على عمود اسمه grade_id
    $sql = "INSERT INTO chapters (chapter_name, grade_id) VALUES ('$chapter_name', '$grade_id')";
    
    if ($mysqli->query($sql)) {
        // نجاح الإضافة، التوجيه للداشبورد
        header("Location: dashboard.php?grade=$grade_id");
        exit;
    } else {
        echo "حدث خطأ أثناء إضافة الفصل: " . $mysqli->error;
    }

    $mysqli->close();
} else {
    header("Location: dashboard.php");
    exit;
}
?>