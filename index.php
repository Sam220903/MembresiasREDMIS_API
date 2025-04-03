<?php
// Configuración de la conexión a la base de datos
$config = [
    "servername" => "localhost",
    "username" => "root",
    "password" => "O538n:ysC-UU0p",
    "dbname" => "lumacadc_membresias"
];

// Establecer conexión
$conn = new mysqli($config["servername"], $config["username"], $config["password"], $config["dbname"]);

// Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Consulta SQL
$sql = "SELECT 
            MR_Miembros.id,
            CONCAT(MR_Miembros.nombre, ' ', MR_Miembros.apellidos) AS nombre_completo,
            MR_Miembros.genero,
            MR_Miembros.fecha_registro,
            MR_Miembros.ultima_actualizacion,
            MR_Universidades.nombre AS universidad,
            MR_Estados.nombre AS estado,
            MR_Paises.nombre AS pais,
            MR_EstatusMiembros.nombre AS estatus,
            MR_TiposUsuario.nombre AS tipo_usuario,
            MR_Login.email,
            MR_Login.ultimo_acceso
        FROM MR_Miembros
        LEFT JOIN MR_Universidades ON MR_Miembros.MR_Universidades_id = MR_Universidades.id
        LEFT JOIN MR_Estados ON MR_Miembros.MR_Estados_id = MR_Estados.id
        LEFT JOIN MR_Paises ON MR_Miembros.MR_Paises_id = MR_Paises.id
        LEFT JOIN MR_EstatusMiembros ON MR_Miembros.MR_EstatusMiembros_id = MR_EstatusMiembros.id
        LEFT JOIN MR_TiposUsuario ON MR_Miembros.MR_TiposUsuario_id = MR_TiposUsuario.id
        LEFT JOIN MR_Login ON MR_Miembros.id = MR_Login.MR_Miembros_id";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prueba de Conexión DB - MembresiasREDMIS</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        h1 {
            color: #333;
            text-align: center;
        }
        .connection-status {
            text-align: center;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .success {
            background-color: #dff0d8;
            color: #3c763d;
        }
        .error {
            background-color: #f2dede;
            color: #a94442;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .no-data {
            text-align: center;
            padding: 20px;
            color: #777;
        }
    </style>
</head>
<body>
    <h1>Prueba de Conexión a Base de Datos</h1>
    
    <div class="connection-status success">
        <strong>¡Conexión exitosa a la base de datos!</strong>
    </div>
    
    <?php if ($result): ?>
        <?php if ($result->num_rows > 0): ?>
            <h2>Resultados de la consulta</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre Completo</th>
                        <th>Género</th>
                        <th>Fecha Registro</th>
                        <th>Última Actualización</th>
                        <th>Universidad</th>
                        <th>Estado</th>
                        <th>País</th>
                        <th>Estatus</th>
                        <th>Tipo Usuario</th>
                        <th>Email</th>
                        <th>Último Acceso</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo $row['nombre_completo']; ?></td>
                            <td><?php echo $row['genero']; ?></td>
                            <td><?php echo $row['fecha_registro']; ?></td>
                            <td><?php echo $row['ultima_actualizacion']; ?></td>
                            <td><?php echo $row['universidad']; ?></td>
                            <td><?php echo $row['estado']; ?></td>
                            <td><?php echo $row['pais']; ?></td>
                            <td><?php echo $row['estatus']; ?></td>
                            <td><?php echo $row['tipo_usuario']; ?></td>
                            <td><?php echo $row['email']; ?></td>
                            <td><?php echo $row['ultimo_acceso']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-data">No se encontraron registros en la base de datos.</div>
        <?php endif; ?>
    <?php else: ?>
        <div class="connection-status error">
            <strong>Error al ejecutar la consulta: <?php echo $conn->error; ?></strong>
        </div>
    <?php endif; ?>
    
    <?php $conn->close(); ?>
</body>
</html>
