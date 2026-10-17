<?php

return [
    'name' => env('ADMIN_NAME', "Toño's Bellyssimo Admin"),
    'login' => env('ADMIN_LOGIN', env('ADMIN_EMAIL')),
    'password_hash' => env('ADMIN_PASSWORD_HASH'),
];
