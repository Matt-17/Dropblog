<?php
// Copy to config.local.php next to this file; config.local.php is not in git.
return [
    'DEBUG'         => false,

    'DB_HOST'       => 'localhost',
    'DB_NAME'       => 'dropblog',
    'DB_USER'       => 'your_db_user',
    'DB_PASS'       => 'your_db_password',
    'DB_CHARSET'    => 'utf8mb4',

    'ADMIN_API_KEY' => 'your-secure-api-key-here',

    'BLOG_TITLE'    => 'My Dropblog',
    'LOCALE'        => 'en-US',
    'TIMEZONE'      => 'Europe/Berlin',
    'DATE_FORMAT'   => 'd. F Y',

    'URL_LENGTH'    => 8,
    'HASHIDS_SALT'  => 'dropblog',
];
