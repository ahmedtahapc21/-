<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_GET['grade'])) {
    
    // 1. الاتصال بقاعدة البيانات الموحدة
    $mysqli = new mysqli("localhost", "root", "", "education_platform");

    if ($mysqli->connect_error) {
        die("خطأ في الاتصال: " . $mysqli->connect_error);
    }
    $mysqli->set_charset("utf8mb4");

    // 2. استقبال وتأمين البيانات المدخلة
    $grade_id = $mysqli->real_escape_string($_GET['grade']);
    $chapter_id = $mysqli->real_escape_string($_POST['chapter_id']);
    $title = $mysqli->real_escape_string($_POST['video_title']);

    // 3. التحقق من الملف المرفوع وأمانه
    $file_name = $_FILES['video_file']['name'];
    $file_tmp = $_FILES['video_file']['tmp_name'];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    // الامتدادات المسموح بها للفيديوهات فقط لضمان الأمان
    $allowed_extensions = array("mp4", "webm", "ogg", "mov");

    if (in_array($file_ext, $allowed_extensions)) {
        
        // تجهيز مسار مجلد الحفظ الموحد
        $folder = "uploads/videos/";
        if (!is_dir($folder)) {
            mkdir($folder, 0777, true);
        }

        // توليد اسم فريد للملف عشان الأسماء ما تضربش في بعضها
        $new_file_name = time() . "_" . uniqid() . "." . $file_ext;
        $path = $folder . $new_file_name;

        // نقل الفيديو للمجلد بنجاح
        if (move_uploaded_file($file_tmp, $path)) {
            
            // 4. حفظ مسار الفيديو في قاعدة البيانات الموحدة
            $sql = "INSERT INTO videos (chapter_id, video_title, video_file) VALUES ('$chapter_id', '$title', '$path')";
            
            if ($mysqli->query($sql)) {
                header("Location: dashboard.php?grade=$grade_id");
                exit;
            } else {
                echo "حدث خطأ أثناء حفظ بيانات الفيديو في قاعدة البيانات: " . $mysqli->error;
            }
        } else {
            echo "فشل نقل الملف إلى مجلد الرفع.";
        }
    } else {
        echo "عذراً، هذا النوع من الملفات غير مسموح به! برجاء رفع فيديو بامتداد مناسب مثل MP4.";
    }

    $mysqli->close();
} else {
    header("Location: dashboard.php");
    exit;
}
?>