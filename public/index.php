<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'student') {
        header('Location: student.php');
        exit;
    }
    if ($_SESSION['role'] === 'teacher') {
        header('Location: teacher.php');
        exit;
    }
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['student_login'])) {
        $studentNo = sanitize($_POST['student_no'] ?? '');
        if (!login_student($studentNo)) {
            $errors[] = 'Gecerli bir ogrenci no girin.';
        } else {
            header('Location: student.php');
            exit;
        }
    }

    if (isset($_POST['teacher_login'])) {
        $username = sanitize($_POST['teacher_username'] ?? '');
        $password = $_POST['teacher_password'] ?? '';
        if (!login_teacher($username, $password)) {
            $errors[] = 'Ogretmen girisi basarisiz.';
        } else {
            header('Location: teacher.php');
            exit;
        }
    }
}
?>
<!doctype html>
<html lang="tr">

<head>
    <meta charset="utf-8">
    <title>Giris</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <div class="page">
        <header class="hero">
            <h1>Akademik Eslesme Portali</h1>
            <p>Ogrenci ve ogretmen girisi icin hizli ve guvenli erisim.</p>
        </header>
        <?php if (!empty($errors)): ?>
            <div class="alert error">
                <?php foreach ($errors as $error): ?>
                    <div><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <main class="grid two">
            <section class="card">
                <h2 class="section-title">Ogrenci Girisi</h2>
                <form method="post" class="form-grid">
                    <div class="field">
                        <label for="student_no">Ogrenci No</label>
                        <input id="student_no" name="student_no" type="text" value="240211003" required>
                    </div>
                    <button type="submit" name="student_login" class="button">Giris Yap</button>
                </form>
            </section>
            <section class="card alt">
                <h2 class="section-title">Ogretmen Girisi</h2>
                <form method="post" class="form-grid">
                    <div class="field">
                        <label for="teacher_username">Kullanici Adi</label>
                        <input id="teacher_username" name="teacher_username" type="text" value="admin" required>
                    </div>
                    <div class="field">
                        <label for="teacher_password">Sifre</label>
                        <input id="teacher_password" name="teacher_password" type="password" value="admin123" required>
                    </div>
                    <button type="submit" name="teacher_login" class="button secondary">Giris Yap</button>
                </form>
            </section>
        </main>
    </div>
</body>

</html>