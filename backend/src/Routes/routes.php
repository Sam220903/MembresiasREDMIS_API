<?php

return [
    // Rutas de autenticación
    'POST /api/auth/login' => ['AuthController', 'login'],
    'POST /api/auth/logout' => ['AuthController', 'logout'],
    'GET /api/auth/me' => ['AuthController', 'getCurrentUser'],
    
    // Rutas de usuarios (protegidas)
    'GET /api/users' => ['UserController', 'index'],
    'GET /api/users/{id}' => ['UserController', 'show'],
    'POST /api/users' => ['UserController', 'store'],
    'PUT /api/users/{id}' => ['UserController', 'update'],
    'DELETE /api/users/{id}' => ['UserController', 'delete']
];
