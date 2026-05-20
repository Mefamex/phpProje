<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/storage.php';
require_once __DIR__ . '/../includes/helpers.php';

require_role('student');

$studentNo = $_SESSION['student_no'];
$students = load_json('students.json', []);
$student = $students[$studentNo] ?? null;

if ($student === null) {
    login_student($studentNo);
    $students = load_json('students.json', []);
    $student = $students[$studentNo];
}

$messages = [];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_profile'])) {
        $student['name'] = sanitize($_POST['name'] ?? '');
        $student['surname'] = sanitize($_POST['surname'] ?? '');
        $student['department'] = sanitize($_POST['department'] ?? '');
        $student['class'] = sanitize($_POST['class'] ?? '');
        $student['email'] = sanitize($_POST['email'] ?? '');
        $student['phone'] = sanitize($_POST['phone'] ?? '');
        $student['skills'] = normalize_skills($_POST['skills'] ?? '');
        $student['updated_at'] = date('c');

        $students[$studentNo] = $student;
        save_json('students.json', $students);
        $messages[] = 'Profil guncellendi.';
    }

    if (isset($_POST['upload_cv']) && isset($_FILES['cv_file'])) {
        $file = $_FILES['cv_file'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Dosya yukleme hatasi.';
        } else {
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ALLOWED_UPLOAD_EXT, true)) {
                $errors[] = 'Sadece PDF dosyasi yukleyebilirsiniz.';
            } elseif ($file['size'] > MAX_UPLOAD_BYTES) {
                $errors[] = 'Dosya boyutu cok buyuk.';
            } else {
                $filename = 'cv_' . $studentNo . '_' . time() . '.' . $ext;
                $targetPath = UPLOAD_DIR . '/' . $filename;
                if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                    $student['cv_path'] = 'uploads/' . $filename;
                    $student['updated_at'] = date('c');
                    $students[$studentNo] = $student;
                    save_json('students.json', $students);
                    $messages[] = 'CV yuklendi.';
                } else {
                    $errors[] = 'Dosya kaydedilemedi.';
                }
            }
        }
    }

    if (isset($_POST['apply_project'])) {
        $projectId = sanitize($_POST['project_id'] ?? '');
        $applications = load_json('applications.json', []);
        $exists = false;
        foreach ($applications as $application) {
            if ($application['project_id'] === $projectId && $application['student_no'] === $studentNo) {
                $exists = true;
                break;
            }
        }

        if ($exists) {
            $errors[] = 'Bu ilana zaten basvurdunuz.';
        } else {
            $projects = load_json('projects.json', []);
            $project = null;
            foreach ($projects as $item) {
                if ($item['id'] === $projectId) {
                    $project = $item;
                    break;
                }
            }

            if ($project === null) {
                $errors[] = 'Ilan bulunamadi.';
            } else {
                $match = calculate_match($student['skills'], $project['required_skills']);
                $applications[] = [
                    'id' => uniqid('app_', true),
                    'project_id' => $projectId,
                    'student_no' => $studentNo,
                    'status' => 'pending',
                    'match' => $match,
                    'created_at' => date('c'),
                ];
                save_json('applications.json', $applications);
                $messages[] = 'Basvuru alindi.';
            }
        }
    }
}

$projects = load_json('projects.json', []);
$applications = load_json('applications.json', []);
?>
<!doctype html>
<html lang="tr">

<head>
    <meta charset="utf-8">
    <title>Ogrenci Sayfasi</title>
</head>

<body>
    <h1>Ogrenci Sayfasi</h1>
    <p>Ogrenci No: <?php echo htmlspecialchars($studentNo, ENT_QUOTES, 'UTF-8'); ?></p>
    <p><a href="logout.php">Cikis</a></p>
    <?php if (!empty($messages)): ?>
        <ul>
            <?php foreach ($messages as $message): ?>
                <li><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
    <?php if (!empty($errors)): ?>
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
    <h2>Profil Bilgileri</h2>
    <form method="post">
        <label>Ad</label>
        <input name="name" type="text" value="<?php echo htmlspecialchars($student['name'], ENT_QUOTES, 'UTF-8'); ?>">
        <label>Soyad</label>
        <input name="surname" type="text" value="<?php echo htmlspecialchars($student['surname'], ENT_QUOTES, 'UTF-8'); ?>">
        <label>Bolum</label>
        <input name="department" type="text" value="<?php echo htmlspecialchars($student['department'], ENT_QUOTES, 'UTF-8'); ?>">
        <label>Sinif</label>
        <input name="class" type="text" value="<?php echo htmlspecialchars($student['class'], ENT_QUOTES, 'UTF-8'); ?>">
        <label>E-posta</label>
        <input name="email" type="email" value="<?php echo htmlspecialchars($student['email'], ENT_QUOTES, 'UTF-8'); ?>">
        <label>Telefon</label>
        <input name="phone" type="text" value="<?php echo htmlspecialchars($student['phone'], ENT_QUOTES, 'UTF-8'); ?>">
        <label>Yetenekler (virgul ile ayir)</label>
        <input name="skills" type="text" value="<?php echo htmlspecialchars(implode(', ', $student['skills']), ENT_QUOTES, 'UTF-8'); ?>">
        <button type="submit" name="save_profile">Kaydet</button>
    </form>
    <h2>CV Yukle</h2>
    <form method="post" enctype="multipart/form-data">
        <input type="file" name="cv_file" accept="application/pdf" required>
        <button type="submit" name="upload_cv">Yukle</button>
    </form>
    <?php if (!empty($student['cv_path'])): ?>
        <p>Mevcut CV: <?php echo htmlspecialchars($student['cv_path'], ENT_QUOTES, 'UTF-8'); ?></p>
    <?php endif; ?>
    <h2>Ilanlar</h2>
    <?php if (empty($projects)): ?>
        <p>Henuz ilan yok.</p>
    <?php else: ?>
        <table border="1" cellpadding="6">
            <thead>
                <tr>
                    <th>Baslik</th>
                    <th>Aciklama</th>
                    <th>Yetenekler</th>
                    <th>Eslesme</th>
                    <th>Islem</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($projects as $project): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($project['title'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($project['description'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars(implode(', ', $project['required_skills']), ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo calculate_match($student['skills'], $project['required_skills']); ?>%</td>
                        <td>
                            <form method="post">
                                <input type="hidden" name="project_id" value="<?php echo htmlspecialchars($project['id'], ENT_QUOTES, 'UTF-8'); ?>">
                                <button type="submit" name="apply_project">Basvur</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
    <h2>Basvurularim</h2>
    <?php
    $myApplications = array_filter($applications, function ($application) use ($studentNo) {
        return $application['student_no'] === $studentNo;
    });
    ?>
    <?php if (empty($myApplications)): ?>
        <p>Basvurunuz yok.</p>
    <?php else: ?>
        <table border="1" cellpadding="6">
            <thead>
                <tr>
                    <th>Ilan</th>
                    <th>Durum</th>
                    <th>Eslesme</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($myApplications as $application): ?>
                    <?php
                    $projectTitle = '';
                    foreach ($projects as $project) {
                        if ($project['id'] === $application['project_id']) {
                            $projectTitle = $project['title'];
                            break;
                        }
                    }
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($projectTitle, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($application['status'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo (int) $application['match']; ?>%</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</body>

</html>