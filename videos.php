<?php
session_start();

// 🛑 الحماية الحديدية: التأكد من تسجيل الدخول ومروره بصفحة الـ index أولاً بالترتيب
if (!isset($_SESSION['student_id']) || !isset($_SESSION['allowed_to_videos'])) {
    // لو حاول ينط باللينك مباشر، اطرده لصفحة تسجيل الدخول فوراً
    header("Location: login.php");
    exit;
}

$student_grade = $_SESSION['grade_id']; // الحصول على سنة الطالب الدراسية من الـ Session
$student_name  = $_SESSION['student_name']; // اسم الطالب للترحيب به

// 2. الاتصال بقاعدة البيانات الموحدة
$mysqli = new mysqli("localhost", "root", "", "education_platform");
if ($mysqli->connect_error) {
    die("فشل الاتصال بقاعدة البيانات: " . $mysqli->connect_error);
}

// تعيين الترميز لدعم اللغة العربية بشكل صحيح
$mysqli->set_charset("utf8mb4");
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="0" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>الفيديوهات التعليمية - منصة نور</title>
    <link rel="stylesheet" href="vedios.css">
    
    <style>
        /* 🚫 منع التحميل عبر الـ CSS وحماية وسم الفيديو */
        video {
            /* منع ظهور قائمة التحميل في متصفحات المبنية على Chromium */
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }
        /* إخفاء زر التحميل نهائياً من المتصفحات */
        video::-internal-media-controls-download-button {
            display:none !important;
        }
        video::-webkit-media-controls-enclosure {
            overflow:hidden !important;
        }
        video::-webkit-media-controls-panel {
            width: calc(100% + 30px) !important; /* إزاحة زرار الثلاث نقط برة الشاشة تماماً */
        }
    </style>
</head>

<body oncontextmenu="return false;" onselectstart="return false;" ondragstart="return false;">

<header>أهلاً بك يا <?php echo htmlspecialchars($student_name, ENT_QUOTES, 'UTF-8'); ?> - الفيديوهات التعليمية</header>

<div class="container">

<?php
// 3. جلب الفصول الخاصة بالسنة الدراسية للطالب الحالي فقط
$chapters = $mysqli->query("SELECT * FROM chapters WHERE grade_id = '$student_grade' ORDER BY id ASC");

if ($chapters && $chapters->num_rows > 0) {
    while ($c = $chapters->fetch_assoc()) {

        $chapter_name = htmlspecialchars($c['chapter_name'], ENT_QUOTES, 'UTF-8');

        echo "
        <div class='chapter'>
            <div class='chapter-header'>
                <span>{$chapter_name}</span>
                <span class='arrow'>▼</span>
            </div>
            <div class='video-list'>
        ";

        $chapId = intval($c['id']);
        // جلب الفيديوهات التابعة لهذا الفصل
        $videos = $mysqli->query("SELECT * FROM videos WHERE chapter_id = {$chapId}");

        if ($videos && $videos->num_rows > 0) {
            while ($v = $videos->fetch_assoc()) {

                $vtitle = htmlspecialchars($v['video_title'], ENT_QUOTES, 'UTF-8');
                $vpath  = htmlspecialchars($v['video_file'], ENT_QUOTES, 'UTF-8');

                echo "
                <div class='video-item'>
                    <p class='video-title' data-video='{$vpath}'>• {$vtitle}</p>
                    <div class='video-box'></div>
                </div>
                ";
            }
        } else {
            echo "<p style='padding: 10px; color: #666;'>لا توجد فيديوهات في هذا الفصل حالياً.</p>";
        }

        echo "</div></div>";
    }
} else {
    echo "<div style='text-align: center; margin-top: 50px;'><h3>لا توجد فصول دراسية مضافة لمرحلتك حالياً.</h3></div>";
}

$mysqli->close();
?>

</div>

<footer style="
    position: fixed;
    bottom: 0;
    left: 0;
    width: 100%;
    text-align: center;
    padding: 12px 0;
    background-color: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(5px);
    font-size: 13px;
    font-family: 'Cairo', sans-serif;
    color: #64748b;
    border-top: 1px solid #e2e8f0;
    z-index: 9999;
    direction: rtl;
">
    © AHMED TAHA SEAFAN - جميع الحقوق محفوظة
</footer>

<script>
    document.onkeydown = function(e) {
        // منع F12، و Ctrl+Shift+I، و Ctrl+Shift+J، و Ctrl+U (عرض السورس كود)
        if (e.keyCode == 123 || 
            (e.ctrlKey && e.shiftKey && (e.keyCode == 73 || e.keyCode == 74)) || 
            (e.ctrlKey && e.keyCode == 85) ||
            (e.ctrlKey && e.keyCode == 83)) { 
            return false;
        }
    };
</script>
<script src='js/vadios.js'></script>

</body>
</html>