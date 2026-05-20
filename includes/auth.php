<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/storage.php';
require_once __DIR__ . '/helpers.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function ensure_default_teacher(): void
{
    $teachers = load_json('teachers.json', []);
    if (count($teachers) > 0) {
        return;
    }

    $teachers[] = [
        'username' => ADMIN_DEFAULT_USER,
        'password_hash' => password_hash(ADMIN_DEFAULT_PASS, PASSWORD_DEFAULT),
        'name' => 'Admin',
        'created_at' => date('c'),
    ];

    save_json('teachers.json', $teachers);
}

function login_teacher(string $username, string $password): bool
{
    ensure_default_teacher();
    $teachers = load_json('teachers.json', []);

    foreach ($teachers as $teacher) {
        if ($teacher['username'] === $username && password_verify($password, $teacher['password_hash'])) {
            $_SESSION['role'] = 'teacher';
            $_SESSION['teacher_username'] = $teacher['username'];
            return true;
        }
    }

    return false;
}

function login_student(string $studentNo): bool
{
    if (!is_valid_student_no($studentNo)) {
        return false;
    }

    $students = load_json('students.json', []);
    if (!isset($students[$studentNo])) {
        $students[$studentNo] = [
            'student_no' => $studentNo,
            'name' => '',
            'surname' => '',
            'department' => '',
            'class' => '',
            'skills' => [],
            'email' => '',
            'phone' => '',
            'cv_path' => '',
            'updated_at' => date('c'),
        ];
        save_json('students.json', $students);
    }

    $_SESSION['role'] = 'student';
    $_SESSION['student_no'] = $studentNo;

    return true;
}

function require_role(string $role): void
{
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== $role) {
        header('Location: index.php');
        exit;
    }
}

function logout(): void
{
    $_SESSION = [];
    session_destroy();
}
