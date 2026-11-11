<?php
// Database settings — edit these to match your cPanel MySQL database.
// On cPanel the database and user names are usually prefixed, e.g. "cpuser_bincom_test".
return [
    'dsn'  => getenv('DB_DSN') ?: 'mysql:host=localhost;dbname=bincom_test;charset=utf8mb4',
    'user' => getenv('DB_USER') ?: 'root',
    'pass' => getenv('DB_PASS') ?: '',
    'state_id' => 25, // Delta State — the only state in the supplied database
];
