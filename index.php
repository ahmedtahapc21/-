<?php
session_start();

// 🛑 1. الحماية الحديدية: لو مش جاي من صفحة الـ login، اطرده فوراً
if (!isset($_SESSION['student_id']) || !isset($_SESSION['allowed_to_index'])) {
    header("Location: login.php");
    exit;
}

// 🔑 2. المفتاح الثاني: الطالب فتح الاندكس بنجاح، نفتح له إذن الدخول لصفحة الفيديوهات
$_SESSION['allowed_to_videos'] = true;

// هنجيب اسم الصف الدراسي بشكل ديناميكي
$grade_name = isset($_SESSION['grade_name']) ? $_SESSION['grade_name'] : "الصف الدراسي الخاص بك";
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>منصة نور العلم</title>
    <link rel="stylesheet" href="index.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>مرحباً بك في <br>منصة نُور العلم</h1>
        </div>

        <div class="card">
            <div class="title">الفيديوهات التعليمية - <?php echo htmlspecialchars($grade_name); ?></div>
            <p>اضغط للدخول إلى محتوى الدروس الخاصة بك.</p>
            
            <a class="btn" href="video.php">مشاهدة الفيديوهات</a>
        </div>

        <div class="card">
            <div class="title">الدعم الفني</div>
            <p>تواصل معنا في أي وقت لو محتاج مساعدة.</p>
            <a class="btn" href="https://wa.me/201012404679" target="_blank">التواصل عبر واتساب</a> 
        </div>
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
</body>
</html>