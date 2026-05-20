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
?>
<!doctype html>
<html lang="tr">

<head>
    <meta charset="utf-8">
    <title>Ogretmen Sayfasi</title>
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
                    <h1>Ogretmen Sayfasi</h1>
                    <p class="small">Ilanlar, basvurular ve ogrenci arama.</p>
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
        <section class="card">
            <h2 class="section-title">Ilan Ekle</h2>
            <form method="post" class="form-grid">
                <div class="field">
                    <label>Baslik</label>
                    <input name="title" type="text" required>
                </div>
                <div class="field">
                    <label>Aciklama</label>
                    <textarea name="description" required></textarea>
                </div>
                <div class="field">
                    <label>Gereken Yetenekler (virgul ile ayir)</label>
                    <input name="required_skills" type="text">
                </div>
                <button type="submit" name="create_project" class="button">Kaydet</button>
            </form>
        </section>
        <section class="card">
            <div class="split">
                <h2 class="section-title">Ilanlar</h2>
                <div class="search-inline">
                    <input id="project-search" type="text" placeholder="Ilan basligi, aciklama, yetenek">
                    <button type="button" class="button secondary" id="project-search-clear">Temizle</button>
                </div>
            </div>
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
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($projects as $project): ?>
                                <?php
                                $projectSearch = strtolower($project['title'] . ' ' . $project['description'] . ' ' . implode(' ', $project['required_skills']));
                                ?>
                                <tr data-project-search="<?php echo htmlspecialchars($projectSearch, ENT_QUOTES, 'UTF-8'); ?>">
                                    <td><?php echo htmlspecialchars($project['title'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($project['description'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars(implode(', ', $project['required_skills']), ENT_QUOTES, 'UTF-8'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
        <section class="card">
            <div class="split">
                <h2 class="section-title">Basvurular</h2>
                <div class="search-inline">
                    <input id="application-search" type="text" placeholder="Ogrenci no, ilan, durum">
                    <button type="button" class="button secondary" id="application-search-clear">Temizle</button>
                </div>
            </div>
            <?php if (empty($applications)): ?>
                <p class="small">Basvuru yok.</p>
            <?php else: ?>
                <div class="table-wrap">
                    <table class="table">
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
                                <?php
                                $applicationSearch = strtolower($application['student_no'] . ' ' . $projectTitle . ' ' . $application['status'] . ' ' . (string) $application['match']);
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
                                <tr data-application-search="<?php echo htmlspecialchars($applicationSearch, ENT_QUOTES, 'UTF-8'); ?>">
                                    <td><?php echo htmlspecialchars($application['student_no'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($projectTitle, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><span class="badge"><?php echo (int) $application['match']; ?>%</span></td>
                                    <td>
                                        <span class="badge status-<?php echo htmlspecialchars($statusKey, ENT_QUOTES, 'UTF-8'); ?>">
                                            <?php echo htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <form method="post" class="form-grid">
                                            <input type="hidden" name="application_id" value="<?php echo htmlspecialchars($application['id'], ENT_QUOTES, 'UTF-8'); ?>">
                                            <select name="status">
                                                <option value="pending" <?php echo $application['status'] === 'pending' ? 'selected' : ''; ?>>pending</option>
                                                <option value="approved" <?php echo $application['status'] === 'approved' ? 'selected' : ''; ?>>approved</option>
                                                <option value="rejected" <?php echo $application['status'] === 'rejected' ? 'selected' : ''; ?>>rejected</option>
                                                <option value="interview" <?php echo $application['status'] === 'interview' ? 'selected' : ''; ?>>interview</option>
                                            </select>
                                            <button type="submit" name="update_status" class="button secondary">Guncelle</button>
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
            <div class="split">
                <h2 class="section-title">Ogrenci Listesi</h2>
                <div class="search-inline">
                    <input id="student-search" name="q" type="text" placeholder="Ogrenci no, ad, soyad" value="<?php echo htmlspecialchars($query, ENT_QUOTES, 'UTF-8'); ?>">
                    <button type="button" class="button secondary" id="student-search-clear">Temizle</button>
                </div>
            </div>
            <?php if (empty($filteredStudents)): ?>
                <p class="small">Ogrenci bulunamadi.</p>
            <?php else: ?>
                <div class="table-wrap">
                    <table class="table">
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
                                <?php
                                $searchText = strtolower($student['student_no'] . ' ' . $student['name'] . ' ' . $student['surname'] . ' ' . $student['department'] . ' ' . $student['class'] . ' ' . implode(' ', $student['skills']));
                                ?>
                                <tr data-search="<?php echo htmlspecialchars($searchText, ENT_QUOTES, 'UTF-8'); ?>">
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
                </div>
            <?php endif; ?>
        </section>
    </div>
    <script>
        const searchInput = document.getElementById('student-search');
        const clearButton = document.getElementById('student-search-clear');
        const rows = Array.from(document.querySelectorAll('tr[data-search]'));

        const projectSearchInput = document.getElementById('project-search');
        const projectClearButton = document.getElementById('project-search-clear');
        const projectRows = Array.from(document.querySelectorAll('tr[data-project-search]'));

        const applicationSearchInput = document.getElementById('application-search');
        const applicationClearButton = document.getElementById('application-search-clear');
        const applicationRows = Array.from(document.querySelectorAll('tr[data-application-search]'));

        function applyFilter(input, rowList, attribute) {
            if (!input) {
                return;
            }
            const query = input.value.trim().toLowerCase();
            rowList.forEach((row) => {
                const haystack = row.getAttribute(attribute) || '';
                row.style.display = haystack.includes(query) ? '' : 'none';
            });
        }

        if (searchInput) {
            searchInput.addEventListener('input', () => applyFilter(searchInput, rows, 'data-search'));
            applyFilter(searchInput, rows, 'data-search');
        }

        if (clearButton) {
            clearButton.addEventListener('click', () => {
                searchInput.value = '';
                applyFilter(searchInput, rows, 'data-search');
                searchInput.focus();
            });
        }

        if (projectSearchInput) {
            projectSearchInput.addEventListener('input', () => applyFilter(projectSearchInput, projectRows, 'data-project-search'));
            applyFilter(projectSearchInput, projectRows, 'data-project-search');
        }

        if (projectClearButton) {
            projectClearButton.addEventListener('click', () => {
                projectSearchInput.value = '';
                applyFilter(projectSearchInput, projectRows, 'data-project-search');
                projectSearchInput.focus();
            });
        }

        if (applicationSearchInput) {
            applicationSearchInput.addEventListener('input', () => applyFilter(applicationSearchInput, applicationRows, 'data-application-search'));
            applyFilter(applicationSearchInput, applicationRows, 'data-application-search');
        }

        if (applicationClearButton) {
            applicationClearButton.addEventListener('click', () => {
                applicationSearchInput.value = '';
                applyFilter(applicationSearchInput, applicationRows, 'data-application-search');
                applicationSearchInput.focus();
            });
        }
    </script>
</body>

</html>