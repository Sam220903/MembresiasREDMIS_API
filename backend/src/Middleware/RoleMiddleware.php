<?php

class RoleMiddleware
{
    private array $permissions;

    public  function __construct()
    {
        $this->permissions = [
          'admin' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'],
          'user' => ['GET', 'POST', 'PATCH'],
        ];
    }

    public function checkPermissions(array $payload, string $method)
    {
        $user_role = $payload['role'] ?? 'user';
        $allowed_methods = $this->permissions[$user_role] ?? [];
        return in_array($method, $allowed_methods);
    }
}