<?php

class MembershipService {
    private $connection;

    public function __construct($dbConnection) {
        $this->connection = $dbConnection;
    }

    public function updateRequestStatus($id, $status, $reason = null): array {
        $request = $this->findRequestById($id);

        if (!$request) {
            throw new \Exception("Solicitud no encontrada.");
        }

        if ($request['estado'] !== 'PENDIENTE') {
            throw new \Exception("La solicitud ya ha sido procesada.");
        }

        if ($status == 'APROBADA') {
            try {
                $solicitud = new SolicitudesMembresias(
                    (int)$request['id'],
                    (int)$request['MR_Miembros_id'],
                    (int)$request['MR_Membresias_id'],
                    $request['estado']
                );

                $membresiaUsuario = new MembresiaUsuario(
                    null,
                    $solicitud->getMiembroId(),
                    $solicitud->getMembresiaId()
                );

                // Verificar si existe una membresía activa, si existe, eliminarla
                $existingMembership = $this->findMembershipByMember($membresiaUsuario->getMemberId());
                if ($existingMembership) {
                    $this->deleteMembership($membresiaUsuario->getMemberId());
                }

                $this->registerMembership($membresiaUsuario);
                $this->updateMemberStatus($membresiaUsuario->getMemberId(), 1);
            } catch (\Throwable $th) {
                throw new \Exception("Error al registrar la membresía: " . $th->getMessage());
            }
        }

        // Actualizar estado de la solicitud con la razón si es rechazada
        return $this->updateRequestStatusRow($id, $status, $reason);
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
                                     VALUES (:memberId, :membershipId, NOW(), DATE_ADD(NOW(), INTERVAL 1 YEAR), :estado)");
        $stmt->execute([
            ':memberId' => $membresiaUsuario->getMemberId(),
            ':membershipId' => $membresiaUsuario->getMembershipId(),
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
}
