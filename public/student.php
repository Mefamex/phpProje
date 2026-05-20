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
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <div class="banner">
        <strong>PHP dersi projesi:</strong> Internet Programciligi (Prof. Dr. Emre Avuclu) | Aksaray Universitesi, Muhendislik Fakultesi, Yazilim Muhendisligi | Ogrenci: Mehmet Akif Akkoc (240211003, 2. sinif)
    </div>
    <div class="page">
        <header class="hero">
            <div class="split">
                <div>
                    <h1>Ogrenci Sayfasi</h1>
                    <p class="small">Ogrenci No: <?php echo htmlspecialchars($studentNo, ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
                <a href="logout.php" class="logout-pill">Cikis</a>
            </div>
        </header>
        <?php if (!empty($messages)): ?>
            <div class="alert success">
                <?php foreach ($messages as $message): ?>
                    <div><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($errors)): ?>
            <div class="alert error">
                <?php foreach ($errors as $error): ?>
                    <div><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <section class="grid two">
            <div class="card">
                <h2 class="section-title">Profil Bilgileri</h2>
                <form method="post" class="form-grid">
                    <div class="field">
                        <label>Ad</label>
                        <input name="name" type="text" value="<?php echo htmlspecialchars($student['name'], ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                    <div class="field">
                        <label>Soyad</label>
                        <input name="surname" type="text" value="<?php echo htmlspecialchars($student['surname'], ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                    <div class="field">
                        <label>Bolum</label>
                        <input name="department" type="text" value="<?php echo htmlspecialchars($student['department'], ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                    <div class="field">
                        <label>Sinif</label>
                        <input name="class" type="text" value="<?php echo htmlspecialchars($student['class'], ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                    <div class="field">
                        <label>E-posta</label>
                        <input name="email" type="email" value="<?php echo htmlspecialchars($student['email'], ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                    <div class="field">
                        <label>Telefon</label>
                        <input name="phone" type="text" value="<?php echo htmlspecialchars($student['phone'], ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                    <div class="field">
                        <label>Yetenekler (virgul ile ayir)</label>
                        <input name="skills" type="text" value="<?php echo htmlspecialchars(implode(', ', $student['skills']), ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                    <button type="submit" name="save_profile" class="button">Kaydet</button>
                </form>
            </div>
            <div class="card alt">
                <h2 class="section-title">CV Yukle</h2>
                <form method="post" enctype="multipart/form-data" class="form-grid">
                    <div class="field">
                        <label>PDF Dosyasi</label>
                        <input type="file" name="cv_file" accept="application/pdf" required>
                    </div>
                    <button type="submit" name="upload_cv" class="button secondary">Yukle</button>
                </form>
                <?php if (!empty($student['cv_path'])): ?>
                    <p class="small">Mevcut CV: <?php echo htmlspecialchars($student['cv_path'], ENT_QUOTES, 'UTF-8'); ?></p>
                <?php endif; ?>
            </div>
        </section>
        <section class="card">
            <h2 class="section-title">Ilanlar</h2>
            <?php if (empty($projects)): ?>
                <p class="small">Henuz ilan yok.</p>
            <?php else: ?>
                <div class="table-wrap">
                    <table class="table">
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
                                    <td><span class="badge"><?php echo calculate_match($student['skills'], $project['required_skills']); ?>%</span></td>
                                    <td>
                                        <form method="post">
                                            <input type="hidden" name="project_id" value="<?php echo htmlspecialchars($project['id'], ENT_QUOTES, 'UTF-8'); ?>">
                                            <button type="submit" name="apply_project" class="button">Basvur</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
        <section class="card">
            <h2 class="section-title">Basvurularim</h2>
            <?php
            $myApplications = array_filter($applications, function ($application) use ($studentNo) {
                return $application['student_no'] === $studentNo;
            });
            ?>
            <?php if (empty($myApplications)): ?>
                <p class="small">Basvurunuz yok.</p>
            <?php else: ?>
                <div class="table-wrap">
                    <table class="table">
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
                                <?php
                                $statusLabels = [
                                    'pending' => 'Bekliyor',
                                    'approved' => 'Onaylandi',
                                    'rejected' => 'Reddedildi',
                                    'interview' => 'Mulakat',
                                ];
                                $statusKey = $application['status'];
                                $statusLabel = $statusLabels[$statusKey] ?? $statusKey;
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($projectTitle, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td>
                                        <span class="badge status-<?php echo htmlspecialchars($statusKey, ENT_QUOTES, 'UTF-8'); ?>">
                                            <?php echo htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    </td>
                                    <td><span class="badge"><?php echo (int) $application['match']; ?>%</span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </div>
</body>

</html>