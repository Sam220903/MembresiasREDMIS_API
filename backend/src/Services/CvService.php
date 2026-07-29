<?php

class CvService {
    private $connection;
    private $storageDirectory;

    public function __construct($dbConnection, string $storageDirectory = '../src/pdfs/cvs/') {
        $this->connection = $dbConnection;
        $this->storageDirectory = $storageDirectory;
    }

    public function getCvsByMember(int $memberId): array {
        $query = "SELECT id, MR_Miembros_id, cv, fecha_subida 
                  FROM MR_ArchivosMiembros 
                  WHERE MR_Miembros_id = :memberId 
                  ORDER BY fecha_subida DESC";
        $stmt = $this->connection->prepare($query);
        $stmt->bindValue(':memberId', $memberId, PDO::PARAM_INT);
        $stmt->execute();

        $cvs = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $cvs[] = new Cv((int)$row['id'], (int)$row['MR_Miembros_id'], $row['cv'], $row['fecha_subida']);
        }
        return $cvs;
    }

    public function getLatestCvByMember(int $memberId): ?Cv {
        $cvs = $this->getCvsByMember($memberId);
        return $cvs[0] ?? null;
    }

    public function getCvById(int $id): ?Cv {
        $query = "SELECT id, MR_Miembros_id, cv, fecha_subida FROM MR_ArchivosMiembros WHERE id = :id";
        $stmt = $this->connection->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }

        return new Cv((int)$row['id'], (int)$row['MR_Miembros_id'], $row['cv'], $row['fecha_subida']);
    }

    // Lee el archivo físico de un CV y lo devuelve codificado en base64,
    // para que el frontend pueda previsualizarlo/descargarlo (igual que hace
    // con application.cv_base64 en la vista de revisión de solicitudes).
    public function getCvFileBase64(Cv $cv): ?string {
        $filePath = $this->storageDirectory . $cv->getFileName();

        if (!file_exists($filePath)) {
            return null;
        }

        return base64_encode(file_get_contents($filePath));
    }

    // Crea un registro NUEVO de CV (nueva fila en el historial del miembro)
    public function createCv(int $memberId, string $base64): Cv {
        $fileName = 'cv_' . $memberId . '_' . uniqid() . '.pdf';
        $filePath = $this->storageDirectory . $fileName;

        if (!PDFProcessor::savePDF($base64, $filePath)) {
            throw new \Exception('Error al guardar el archivo del CV.');
        }

        $cv = new Cv(null, $memberId, $fileName);

        $query = "INSERT INTO MR_ArchivosMiembros (MR_Miembros_id, cv) VALUES (:memberId, :cv)";
        $stmt = $this->connection->prepare($query);
        $stmt->bindValue(':memberId', $cv->getMemberId(), PDO::PARAM_INT);
        $stmt->bindValue(':cv', $cv->getFileName());
        $stmt->execute();

        $id = (int)$this->connection->lastInsertId();
        return $this->getCvById($id);
    }

    // Actualiza el archivo de un registro de CV YA EXISTENTE (mismo id, se sobrescribe el archivo)
    public function updateCv(int $id, string $base64): Cv {
        $existing = $this->getCvById($id);
        if (!$existing) {
            throw new \Exception('CV no encontrado.');
        }

        $filePath = $this->storageDirectory . $existing->getFileName();

        if (!PDFProcessor::savePDF($base64, $filePath)) {
            throw new \Exception('Error al actualizar el archivo del CV.');
        }

        $query = "UPDATE MR_ArchivosMiembros SET fecha_subida = NOW() WHERE id = :id";
        $stmt = $this->connection->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $this->getCvById($id);
    }

    // Usado por otros flujos (ej. edición de perfil): si el miembro ya tiene un CV,
    // lo actualiza en su lugar; si es la primera vez, crea el registro.
    public function createOrUpdateForMember(int $memberId, string $base64): Cv {
        $latest = $this->getLatestCvByMember($memberId);

        if ($latest) {
            return $this->updateCv($latest->getId(), $base64);
        }

        return $this->createCv($memberId, $base64);
    }
}
