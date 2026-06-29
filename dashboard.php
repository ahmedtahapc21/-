<?php
// ============================================================
// 1️⃣ أولاً: الاتصال بقاعدة البيانات ومعالجة الطلبات (POST)
// ============================================================
$grade = $_GET['grade'] ?? '';

$mysqli = new mysqli("localhost", "root", "", "education_platform");
if($mysqli->connect_error){
    die("❌ خطأ الاتصال: " . $mysqli->connect_error);
}

// معالجة البيانات القادمة من الفورمات فوراً قبل رندر الـ HTML
if($_SERVER['REQUEST_METHOD'] == "POST"){

    $action = $_POST['action'];

    // إضافة كود دخول فريد لطالب (الجزء المضاف حديثاً)
    if($action == "generate_code"){
        $student_num = $_POST['student_number'];
        $access_code = $_POST['access_code'];

        // حماية المدخلات من الثغرات
        $student_num = $mysqli->real_escape_string($student_num);
        $access_code = $mysqli->real_escape_string($access_code);

        // إدخال الرمز الفريد في جدول login_codes وجعله غير مستخدم مسبقاً (is_used = 0)
        $mysqli->query("INSERT INTO login_codes (student_number, access_code, is_used) 
                        VALUES ('$student_num', '$access_code', 0)")
                        or die("❌ خطأ في أسماء أعمدة جدول الأكواد: " . $mysqli->error);

        echo "<script>alert('✔ تم إنشاء رمز الدخول الفريد بنجاح'); window.location.href='dashboard.php?grade=$grade';</script>";
        exit;
    }

    // إضافة طالب
    if($action == "add_student"){
        $name     = $_POST['name'];
        $num      = $_POST['number'];
        $pass     = $_POST['password'];
        $grade_id = $_POST['grade_id']; 

        $mysqli->query("INSERT INTO students (student_name, student_number, password, grade_id)
                        VALUES ('$name','$num','$pass', '$grade_id')") 
                        or die("❌ خطأ في أسماء أعمدة جدول الطلاب: " . $mysqli->error);
        
        echo "<script>alert('✔ تم إضافة الطالب بنجاح'); window.location.href='dashboard.php?grade=$grade';</script>";
        exit;
    }

    // إضافة فصل
    if($action == "add_chapter"){
        $name     = $_POST['chapter_name'];
        $grade_id = $_POST['grade_id'];

        $mysqli->query("INSERT INTO chapters (chapter_name, grade_id) VALUES ('$name', '$grade_id')")
                        or die("❌ خطأ في أسماء أعمدة جدول الفصول: " . $mysqli->error);
                        
        echo "<script>alert('✔ تم إضافة الفصل للمرحلة المحددة بنجاح'); window.location.href='dashboard.php?grade=$grade';</script>";
        exit;
    }

    // إضافة فيديو
    if($action == "add_video"){
        $chapter = $_POST['chapter_id'];
        $title   = $_POST['video_title'];

        $fileName = time() . "_" . $_FILES["video_file"]["name"];
        $temp     = $_FILES["video_file"]["tmp_name"];

        $uploadFolder = "uploads/";
        if(!is_dir($uploadFolder)) mkdir($uploadFolder);

        move_uploaded_file($temp, $uploadFolder.$fileName);

        $mysqli->query("INSERT INTO videos (chapter_id, video_title, video_file)
                        VALUES ('$chapter', '$title', '$uploadFolder$fileName')")
                        or die("❌ خطأ في أسماء أعمدة جدول الفيديوهات: " . $mysqli->error);

        echo "<script>alert('✔ تم رفع الفيديو بنجاح'); window.location.href='dashboard.php?grade=$grade';</script>";
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>لوحة تحكم المدرس</title>
    <meta name="viewport" content="width=device-width, initial-scale=1"> 
    <link rel="stylesheet" href="dashboard.css">
    <style>
        table { width: 100%; border-collapse: collapse; margin-top: 15px; background: #fff; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: center; }
        th { background-color: #f4f4f4; }
        .chapter, .students-list { background: #f9f9f9; padding: 15px; margin-bottom: 20px; border-radius: 5px; border: 1px solid #ddd; }
        .code-card { background: #fff3cd; border: 1px solid #ffeeba; padding: 15px; margin-bottom: 20px; border-radius: 5px; }
    </style>
</head>
<body>

<div class="dashboard">

    <div class="code-card">
        <h2>🔑 توليد رمز دخول فريد لطالب</h2>
        <form method="POST">
            <input type="hidden" name="action" value="generate_code">

            <label>رقم هاتف / شخصي للطالب:</label>
            <input type="text" name="student_number" placeholder="مثال: 01000000000" required style="margin-top=10px;"> &nbsp;&nbsp;
<br>
            <label>الرمز الفريد المخصص له:</label>
            <input type="text" name="access_code" placeholder="مثال: ah121" required> &nbsp;&nbsp;

            <button type="submit" style="background-color: #ffc107; color: #212529; border: 1px solid #ffc107; font-weight: bold;     margin-top: 30px;" >توليد الرمز وعمل قفل 🔒</button>
        </form>
    </div>

    <hr>

    <h1>📝 إضافة طالب جديد</h1>

    <form method="POST" id="addStudentForm">
        <input type="hidden" name="action" value="add_student">

        <label>اسم الطالب:</label>
        <input type="text" name="name" required><br><br>

        <label>الرقم:</label>
        <input type="text" name="number" required><br><br>

        <label>كلمة المرور:</label>
        <input type="password" name="password" required><br><br>
        
        <label for="student_stage">المرحلة التعليمية:</label>
        <select id="student_stage" class="stage-select" required>
            <option value="" disabled selected>اختر المرحلة</option>
            <option value="primary">الابتدائي</option>
            <option value="preparatory">الإعدادي</option>
            <option value="secondary">الثانوي</option>
        </select><br><br>

        <label for="student_grade">السنة الدراسية:</label>
        <select name="grade_id" id="student_grade" class="grade-select" required>
            <option value="" disabled selected>برجاء اختيار المرحلة أولاً</option>
        </select><br><br>
        
        <button type="submit">➕ إضافة طالب</button>
    </form>

    <hr>

    <h2>📘 إضافة فصل جديد</h2>

    <form method="POST" class="card-form">
        <input type="hidden" name="action" value="add_chapter">

        <label for="chapter_stage">المرحلة التعليمية لهذا الفصل:</label>
        <select id="chapter_stage" class="stage-select" required>
            <option value="" disabled selected>اختر المرحلة</option>
            <option value="primary">الابتدائي</option>
            <option value="preparatory">الإعدادي</option>
            <option value="secondary">الثانوي</option>
        </select><br><br>

        <label for="chapter_grade">السنة الدراسية:</label>
        <select name="grade_id" id="chapter_grade" class="grade-select" required>
            <option value="" disabled selected>برجاء اختيار المرحلة أولاً</option>
        </select><br><br>

        <label>اسم الفصل:</label>
        <input type="text" name="chapter_name" required><br><br>

        <button type="submit">➕ إضافة فصل</button>
    </form>

    <hr>

    <h2>🎥 إضافة فيديو إلى فصل</h2>

    <form method="POST" enctype="multipart/form-data" class="card-form">
        <input type="hidden" name="action" value="add_video">

        <label for="video_stage">المرحلة التعليمية:</label>
        <select id="video_stage" class="stage-select" required>
            <option value="" disabled selected>اختر المرحلة</option>
            <option value="primary">الابتدائي</option>
            <option value="preparatory">الإعدادي</option>
            <option value="secondary">الثانوي</option>
        </select><br><br>

        <label for="video_grade">السنة الدراسية:</label>
        <select id="video_grade" class="grade-select" required>
            <option value="" disabled selected>برجاء اختيار المرحلة أولاً</option>
        </select><br><br>

        <label>اختر الفصل:</label>
        <select name="chapter_id" id="video_chapter" required>
            <option value="" disabled selected>برجاء اختيار السنة الدراسية أولاً</option>
            <?php
            $chapters = $mysqli->query("SELECT * FROM chapters ORDER BY id DESC");
            while($c = $chapters->fetch_assoc()){
                echo "<option value='{$c['id']}' data-grade='{$c['grade_id']}'>{$c['chapter_name']}</option>";
            }
            ?>
        </select><br><br>

        <label>اسم الفيديو:</label>
        <input type="text" name="video_title" required><br><br>

        <label>ارفع الفيديو:</label>
        <input type="file" name="video_file" accept="video/*" required><br><br>

        <button type="submit">⬆ رفع الفيديو</button>
    </form>

    <hr>

    <h2>📚 الفصول الحالية</h2>

    <?php
    $chs = $mysqli->query("SELECT * FROM chapters");
    while($c = $chs->fetch_assoc()){
        echo "<div class='chapter'>
                <h3>📘 {$c['chapter_name']} (كود الصف: {$c['grade_id']})</h3>";

        $vids = $mysqli->query("SELECT * FROM videos WHERE chapter_id={$c['id']}");

        if($vids->num_rows == 0){
            echo "<p>لا يوجد فيديوهات</p></div>";
            continue;
        }

        echo "<ul>";
        while($v = $vids->fetch_assoc()){
            echo "<li class='video-item'>🎥 {$v['video_title']} — 
                    <a href='{$v['video_file']}' target='_blank'>مشاهدة</a>
                  </li>";
        }
        echo "</ul></div>";
    }
    ?>

    <hr>

    <h2>👥 الطلاب المسجلون حالياً</h2>
    <div class="students-list">
        <?php
        $students = $mysqli->query("SELECT * FROM students ORDER BY id DESC");

        if($students->num_rows == 0){
            echo "<p>لا يوجد طلاب مسجلين حتى الآن.</p>";
        } else {
            echo "<table>
                    <thead>
                        <tr>
                            <th>الاسم</th>
                            <th>الرقم الشخصي / الهاتف</th>
                            <th>كود السنة الدراسية (grade_id)</th>
                            <th>حالة الرمز الفريد الحالي بالسيستم</th>
                        </tr>
                    </thead>
                    <tbody>";
            while($row = $students->fetch_assoc()){
                $student_grade_id = $row['grade_id'] ?? 'غير محدد';
                $s_num = $row['student_number'];
                
                // جلب آخر رمز تم توليده لهذا الطالب لمعرفة حالته (مستخدم أم لا)
                $code_q = $mysqli->query("SELECT access_code, is_used FROM login_codes WHERE student_number='$s_num' ORDER BY id DESC LIMIT 1");
                $code_status = "<span style='color: gray;'>لا يوجد رمز منشأ</span>";
                
                if($code_q && $code_q->num_rows > 0){
                    $c_data = $code_q->fetch_assoc();
                    if($c_data['is_used'] == 1){
                        $code_status = "<span style='color: red; font-weight: bold;'>❌ مستخدم (الجهاز مقفل)</span>";
                    } else {
                        $code_status = "<span style='color: green; font-weight: bold;'>✔ متاح للاستخدام ({$c_data['access_code']})</span>";
                    }
                }

                echo "<tr>
                        <td>{$row['student_name']}</td>
                        <td>{$row['student_number']}</td>
                        <td>{$student_grade_id}</td>
                        <td>{$code_status}</td>
                      </tr>";
            }
            echo "    </tbody>
                  </table>";
        }
        ?>
    </div>

</div>
<footer style="
    position: fixed;
    bottom: 0;
    left: 0;
    width: 100%;
    text-align: center;
    padding: 12px 0;
    background-color: rgba(255, 255, 255, 0.9); /* خلفية بيضاء شفافة ناعمة عشان ما تغطيش المحتوى */
    backdrop-filter: blur(5px); /* تأثير زجاجي فخم */
    font-size: 13px;
    font-family: 'Cairo', sans-serif;
    color: #64748b;
    border-top: 1px solid #e2e8f0;
    z-index: 9999; /* عشان يفضل ظاهر فوق أي عنصر في الصفحة */
    direction: rtl;
">
    © AHMED TAHA SEAFAN - جميع الحقوق محفوظة
</footer>
<script>
const gradesData = {
    primary: [
        { id: "11", name: "الصف الأول الابتدائي" },
        { id: "12", name: "الصف الثاني الابتدائي" },
        { id: "13", name: "الصف الثالث الابتدائي" },
        { id: "14", name: "الصف الرابع الابتدائي" },
        { id: "15", name: "الصف الخامس الابتدائي" },
        { id: "16", name: "الصف السادس الابتدائي" }
    ],
    preparatory: [
        { id: "1", name: "الصف الأول الإعدادي" },
        { id: "2", name: "الصف الثاني الإعدادي" },
        { id: "3", name: "الصف الثالث الإعدادي" }
    ],
    secondary: [
        { id: "21", name: "الصف الأول الثانوي" },
        { id: "22", name: "الصف الثاني الثانوي" },
        { id: "23", name: "الصف الثالث الثانوي" }
    ]
};

function setupDynamicDropdowns(stageId, gradeId) {
    const stageSelect = document.getElementById(stageId);
    const gradeSelect = document.getElementById(gradeId);

    if(stageSelect && gradeSelect) {
        stageSelect.addEventListener('change', function() {
            const selectedStage = this.value;
            gradeSelect.innerHTML = '<option value="" disabled selected>اختر السنة الدراسية</option>';
            
            if (gradesData[selectedStage]) {
                gradesData[selectedStage].forEach(function(grade) {
                    const option = document.createElement('option');
                    option.value = grade.id;
                    option.textContent = grade.name;
                    gradeSelect.appendChild(option);
                });
            }
        });
    }
}

setupDynamicDropdowns('student_stage', 'student_grade');
setupDynamicDropdowns('chapter_stage', 'chapter_grade');
setupDynamicDropdowns('video_stage', 'video_grade');

const videoGradeSelect = document.getElementById('video_grade');
const videoChapterSelect = document.getElementById('video_chapter');
const allChapterOptions = Array.from(videoChapterSelect.options);

videoGradeSelect.addEventListener('change', function() {
    const selectedGradeId = this.value;
    videoChapterSelect.innerHTML = '<option value="" disabled selected>اختر الفصل المناسب</option>';
    
    allChapterOptions.forEach(option => {
        if (option.getAttribute('data-grade') === selectedGradeId) {
            videoChapterSelect.appendChild(option.cloneNode(true));
        }
    });
});
</script>

</body>
</html>