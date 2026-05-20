<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/storage.php';
require_once __DIR__ . '/../includes/helpers.php';

require_role('teacher');

$messages = [];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_project'])) {
        $title = sanitize($_POST['title'] ?? '');
        $description = sanitize($_POST['description'] ?? '');
        $skills = normalize_skills($_POST['required_skills'] ?? '');

        if ($title === '' || $description === '') {
            $errors[] = 'Baslik ve aciklama gerekli.';
        } else {
            $projects = load_json('projects.json', []);
            $projects[] = [
                'id' => uniqid('prj_', true),
                'title' => $title,
                'description' => $description,
                'required_skills' => $skills,
                'created_by' => $_SESSION['teacher_username'],
                'created_at' => date('c'),
            ];
            save_json('projects.json', $projects);
            $messages[] = 'Ilan eklendi.';
        }
    }

    if (isset($_POST['update_status'])) {
        $applicationId = sanitize($_POST['application_id'] ?? '');
        $status = sanitize($_POST['status'] ?? '');
        $allowed = ['pending', 'approved', 'rejected', 'interview'];

        if (!in_array($status, $allowed, true)) {
            $errors[] = 'Gecersiz durum.';
        } else {
            $applications = load_json('applications.json', []);
            foreach ($applications as &$application) {
                if ($application['id'] === $applicationId) {
                    $application['status'] = $status;
                    break;
                }
            }
            unset($application);
            save_json('applications.json', $applications);
            $messages[] = 'Durum guncellendi.';
        }
    }
}

$projects = load_json('projects.json', []);
$applications = load_json('applications.json', []);
$students = load_json('students.json', []);

$query = sanitize($_GET['q'] ?? '');
$filteredStudents = $students;
if ($query !== '') {
    $filteredStudents = array_filter($students, function ($student) use ($query) {
        $haystack = strtolower($student['student_no'] . ' ' . $student['name'] . ' ' . $student['surname']);
        return strpos($haystack, strtolower($query)) !== false;
    });
}
?>
<!doctype html>
<html lang="tr">

<head>
    <meta charset="utf-8">
    <title>Ogretmen Sayfasi</title>
</head>

<body>
    <h1>Ogretmen Sayfasi</h1>
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
    <h2>Ilan Ekle</h2>
    <form method="post">
        <label>Baslik</label>
        <input name="title" type="text" required>
        <label>Aciklama</label>
        <textarea name="description" required></textarea>
        <label>Gereken Yetenekler (virgul ile ayir)</label>
        <input name="required_skills" type="text">
        <button type="submit" name="create_project">Kaydet</button>
    </form>
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
                </tr>
            </thead>
            <tbody>
                <?php foreach ($projects as $project): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($project['title'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($project['description'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars(implode(', ', $project['required_skills']), ENT_QUOTES, 'UTF-8'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
    <h2>Basvurular</h2>
    <?php if (empty($applications)): ?>
        <p>Basvuru yok.</p>
    <?php else: ?>
        <table border="1" cellpadding="6">
            <thead>
                <tr>
                    <th>Ogrenci No</th>
                    <th>Ilan</th>
                    <th>Eslesme</th>
                    <th>Durum</th>
                    <th>Guncelle</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($applications as $application): ?>
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
                        <td><?php echo htmlspecialchars($application['student_no'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($projectTitle, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo (int) $application['match']; ?>%</td>
                        <td><?php echo htmlspecialchars($application['status'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td>
                            <form method="post">
                                <input type="hidden" name="application_id" value="<?php echo htmlspecialchars($application['id'], ENT_QUOTES, 'UTF-8'); ?>">
                                <select name="status">
                                    <option value="pending">pending</option>
                                    <option value="approved">approved</option>
                                    <option value="rejected">rejected</option>
                                    <option value="interview">interview</option>
                                </select>
                                <button type="submit" name="update_status">Guncelle</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
    <h2>Ogrenci Arama</h2>
    <form method="get">
        <input name="q" type="text" value="<?php echo htmlspecialchars($query, ENT_QUOTES, 'UTF-8'); ?>">
        <button type="submit">Ara</button>
    </form>
    <?php if (empty($filteredStudents)): ?>
        <p>Ogrenci bulunamadi.</p>
    <?php else: ?>
        <table border="1" cellpadding="6">
            <thead>
                <tr>
                    <th>Ogrenci No</th>
                    <th>Ad</th>
                    <th>Soyad</th>
                    <th>Bolum</th>
                    <th>Sinif</th>
                    <th>Yetenekler</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($filteredStudents as $student): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($student['student_no'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($student['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($student['surname'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($student['department'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($student['class'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars(implode(', ', $student['skills']), ENT_QUOTES, 'UTF-8'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</body>

</html>