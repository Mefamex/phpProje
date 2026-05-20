<?php

declare(strict_types=1);

define('BASE_DIR', dirname(__DIR__));
define('DATA_DIR', BASE_DIR . '/data');
define('UPLOAD_DIR', BASE_DIR . '/uploads');

define('ADMIN_DEFAULT_USER', 'admin');
define('ADMIN_DEFAULT_PASS', 'admin123');

define('ALLOWED_UPLOAD_EXT', ['pdf']);
define('MAX_UPLOAD_BYTES', 5 * 1024 * 1024);
