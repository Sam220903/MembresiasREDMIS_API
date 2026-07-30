<?php

class MembershipService {
    private $connection;
    private $membresiasService;
    private $certificateService;

    public function __construct($dbConnection, MembresiasService $membresiasService, MembershipCertificateService $certificateService) {
        $this->connection = $dbConnection;
        $this->membresiasService = $membresiasService;
        $this->certificateService = $certificateService;
    }

    public function updateRequestStatus($id, $status, $reason = null): array {
        $request = $this->findRequestById($id);

        if (!$request) {
            throw new \Exception("Solicitud no encontrada.");
        }

        if ($request['estado'] !== 'PENDIENTE') {
            throw new \Exception("La solicitud ya ha sido procesada.");
        }

        $certificate = null;

        if ($status == 'APROBADA') {
            try {
                $solicitud = new SolicitudesMembresias(
                    (int)$request['id'],
                    (int)$request['MR_Miembros_id'],
                    (int)$request['MR_Membresias_id'],
                    $request['estado']
                );

                $fechaInicio = date('Y-m-d');
                $fechaFin = date('Y-m-d', strtotime('+1 year'));

                $membresiaUsuario = new MembresiaUsuario(
                    null,
                    $solicitud->getMiembroId(),
                    $solicitud->getMembresiaId(),
                    $fechaInicio,
                    $fechaFin
                );

                // Verificar si existe una membresía activa, si existe, eliminarla
                $existingMembership = $this->findMembershipByMember($membresiaUsuario->getMemberId());
                if ($existingMembership) {
                    $this->deleteMembership($membresiaUsuario->getMemberId());
                }

                $this->registerMembership($membresiaUsuario);
                $this->updateMemberStatus($membresiaUsuario->getMemberId(), 1);

                // Generar la credencial en PDF para adjuntarla al correo de aprobación
                $memberRow = $this->findMemberNameById($membresiaUsuario->getMemberId());
                $membershipType = $this->membresiasService->getMembresia($membresiaUsuario->getMembershipId());

                if ($memberRow && $membershipType) {
                    $certificate = $this->certificateService->generate(
                        $memberRow['nombre'],
                        $membershipType->getName(),
                        $fechaInicio,
                        $fechaFin
                    );
                }
            } catch (\Throwable $th) {
                throw new \Exception("Error al registrar la membresía: " . $th->getMessage());
            }
        }

        // Actualizar estado de la solicitud con la razón si es rechazada
        $result = $this->updateRequestStatusRow($id, $status, $reason);

        if ($certificate) {
            $result['certificatePath'] = $certificate['path'];
            $result['certificateFileName'] = $certificate['fileName'];
        }

        return $result;
    }

    private function findRequestById($requestId) {
        $stmt = $this->connection->prepare("SELECT * FROM MR_SolicitudesMembresia WHERE id = :requestId");
        $stmt->bindValue(':requestId', $requestId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function updateRequestStatusRow($requestId, $status, $reason = null): array {
        $stmt = $this->connection->prepare("UPDATE MR_SolicitudesMembresia 
                                     SET estado = :status, fecha_respuesta = NOW(), comentarios = :reason 
                                     WHERE id = :requestId");
        $stmt->execute([
            ':status' => $status,
            ':reason' => $reason,
            ':requestId' => $requestId
        ]);

        // Obtener información del usuario para el envío de correo
        $stmt = $this->connection->prepare("SELECT m.nombre, l.email 
                                     FROM MR_SolicitudesMembresia s 
                                     JOIN MR_Miembros m ON s.MR_Miembros_id = m.id 
                                     JOIN MR_Login l ON m.id = l.MR_Miembros_id 
                                     WHERE s.id = :requestId");
        $stmt->execute([':requestId' => $requestId]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function registerMembership(MembresiaUsuario $membresiaUsuario) {
        $stmt = $this->connection->prepare("INSERT INTO MR_MiembrosMembresias (MR_Miembros_id, MR_Membresias_id, fecha_inicio, fecha_fin, estado) 
                                     VALUES (:memberId, :membershipId, :fechaInicio, :fechaFin, :estado)");
        $stmt->execute([
            ':memberId' => $membresiaUsuario->getMemberId(),
            ':membershipId' => $membresiaUsuario->getMembershipId(),
            ':fechaInicio' => $membresiaUsuario->getStartDate(),
            ':fechaFin' => $membresiaUsuario->getEndDate(),
            ':estado' => $membresiaUsuario->getStatus(),
        ]);
    }

    private function findMembershipByMember($memberId) {
        $stmt = $this->connection->prepare("SELECT * FROM MR_MiembrosMembresias WHERE MR_Miembros_id = :memberId;");
        $stmt->execute([
            ':memberId' => $memberId
        ]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function deleteMembership($memberId) {
        $stmt = $this->connection->prepare("DELETE FROM MR_MiembrosMembresias WHERE MR_Miembros_id = :memberId;");
        $stmt->execute([
            ':memberId' => $memberId,
        ]);
    }

    private function updateMemberStatus($memberId, $status) {
        $stmt = $this->connection->prepare("UPDATE MR_Miembros SET MR_EstatusMiembros_id = :status WHERE id = :memberId;");
        $stmt->execute([
            ':status' => $status,
            ':memberId' => $memberId,
        ]);
    }

    private function findMemberNameById($memberId) {
        $stmt = $this->connection->prepare("SELECT nombre, apellidos FROM MR_Miembros WHERE id = :memberId");
        $stmt->execute([':memberId' => $memberId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return ['nombre' => trim($row['nombre'] . ' ' . $row['apellidos'])];
    }
}
