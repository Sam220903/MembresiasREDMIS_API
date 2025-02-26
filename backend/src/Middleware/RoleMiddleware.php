<?php

class RoleMiddleware
{
    private $permissions;

    public function __construct()
    {
        $this->permissions = [
          1 => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'],
          2 => ['GET', 'POST', 'PATCH'],
        ];
    }

    public function checkPermissions(array $payload, string $method)
    {
        $user_role = $payload['role'] ?? 2;
        $allowed_methods = $this->permissions[$user_role] ?? [];
        return in_array($method, $allowed_methods);
    }
}