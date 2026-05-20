<?php

declare(strict_types=1);

function sanitize(string $value): string
{
    return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
}

function normalize_skills(string $raw): array
{
    $parts = array_filter(array_map('trim', explode(',', $raw)));
    $parts = array_map('strtolower', $parts);
    return array_values(array_unique($parts));
}

function calculate_match(array $studentSkills, array $requiredSkills): int
{
    if (count($requiredSkills) === 0) {
        return 0;
    }

    $student = array_map('strtolower', $studentSkills);
    $required = array_map('strtolower', $requiredSkills);
    $matched = array_intersect($student, $required);

    return (int) round((count($matched) / count($required)) * 100);
}

function is_valid_student_no(string $studentNo): bool
{
    return preg_match('/^[0-9]{5,12}$/', $studentNo) === 1;
}
