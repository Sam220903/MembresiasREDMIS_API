<?php

trait AuthorizationTrait
{
    protected $payload;


    /**
     * Verifica si el usuario actual tiene un rol específico
     * @param string|array $roles Rol o array de roles permitidos
     * @throws Exception Si el usuario no tiene el rol requerido
     */
    protected function validateRole($roles) {
        $userRole = $this->payload['role'] ?? '';
        $allowedRoles = is_array($roles) ? $roles : [$roles];

        if (!in_array($userRole, $allowedRoles)) {
            http_response_code(403);
            echo json_encode([
                'error' => 'No tienes los permisos necesarios para realizar esta acción',
                'required_roles' => $allowedRoles
            ]);
            exit();
        }
    }

    /**
     * Verifica si el usuario actual es admin
     * @throws Exception Si el usuario no es admin
     */
    protected function validateAdminAccess() {
        $this->validateRole('admin');
    }

    /**
     * Verifica si el usuario tiene alguno de los roles especificados
     * @param array $allowedRoles Array de roles permitidos
     * @throws Exception Si el usuario no tiene ninguno de los roles requeridos
     */
    protected function validateRoles(array $allowedRoles) {
        $this->validateRole($allowedRoles);
    }

    /**
     * Verifica si el usuario actual es el propietario del recurso
     * @param int|string $resourceUserId ID del usuario propietario del recurso
     * @throws Exception Si el usuario no es el propietario ni admin
     */
    protected function validateOwnership($resourceUserId) {
        $userRole = $this->payload['role'] ?? '';
        $userId = $this->payload['id'] ?? '';

        if ($userRole !== 'admin' && $userId != $resourceUserId) {
            http_response_code(403);
            echo json_encode([
                'error' => 'No tienes permiso para acceder a este recurso'
            ]);
            exit();
        }
    }
}