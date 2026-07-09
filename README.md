# SISTEMA DE GESTIÓN DE MEMBRESÍAS REDMIS

API desarrollada en PHP para la comunicación entre el front-end y la base de datos del sistema de membresías REDMIS.

## Visión general

Este proyecto implementa una API REST sencilla siguiendo un patrón MVC básico:

- El punto de entrada es el archivo de la carpeta backend/public.
- Las peticiones son dirigidas por un router central que interpreta la ruta de la URL.
- Los controladores reciben la solicitud HTTP, los servicios encapsulan la lógica de negocio y los modelos representan las entidades del sistema.
- La autenticación se maneja mediante JWT y un middleware de autorización.

## Arquitectura MVC del proyecto

La estructura principal del repositorio está organizada de la siguiente manera:

- backend/public/index.php
  - Es el punto de entrada de la API.
  - Registra automáticamente las clases PHP desde las carpetas del proyecto.
  - Recibe la petición, extrae la ruta, valida la autenticación y redirige la solicitud a través de un switch.

- backend/src/Controllers/
  - Contienen los controladores que atienden las peticiones HTTP.
  - Ejemplos: LoginController, MembresiasController, MiembrosController, UniversityController, entre otros.
  - Su responsabilidad es recibir datos, invocar servicios y devolver respuestas JSON.

- backend/src/Services/
  - Contienen la lógica de negocio y el acceso a la base de datos.
  - Ejemplos: UserService, MembershipApplicationService, MembresiasService, StatisticsService.
  - Son quienes interactúan con la conexión PDO y ejecutan las consultas.

- backend/src/Models/
  - Definen las entidades del sistema.
  - Representan objetos como miembros, membresías, universidades, países, estados e investigaciones.

- backend/src/Middleware/
  - Incluye autenticación, autorización por roles y manejo de JWT.
  - AuthMiddleware y RoleMiddleware son los encargados de validar permisos antes de procesar ciertas rutas.

- backend/src/Config/
  - Incluye la configuración de la base de datos y los headers de la API.
  - Database.php crea la conexión PDO y config.php define los parámetros de acceso.

- backend/src/Helpers/
  - Contiene utilidades auxiliares como procesamiento de PDF, casteo de tipos y otros helpers.

## Cómo funciona la API

Cuando una petición llega a la API, el flujo es el siguiente:

1. La solicitud entra al archivo backend/public/index.php.
2. Se cargan automáticamente las clases desde las carpetas src/Config, src/Controllers, src/Services, src/Models, src/Middleware y src/Helpers.
3. Se carga la configuración de la base de datos.
4. Se obtiene la ruta de la URL y se interpretan los segmentos para determinar la ruta principal y un posible identificador.
5. Se valida el token JWT mediante AuthMiddleware, salvo en rutas públicas como login o registro de miembros.
6. El archivo index.php usa un switch para despachar la petición al controlador apropiado.
7. El controlador invoca al servicio correspondiente y este devuelve una respuesta JSON.

## El index como router principal

El archivo backend/public/index.php contiene un switch central con todos los endpoints definidos para la API. Ahí se decide qué controlador se ejecuta según la ruta solicitada.

Los endpoints actualmente incluidos en el switch son:

- test
- login
- logout
- investigationLine
- solicitarMembresia
- aceptarMembresia
- rechazarMembresia
- membresias
- solicitudesMembresias
- statistics
- miembros
- verify
- resend-code
- universities
- countries
- states
- membresiaUsuario
- cambiarRol
- actualizarEstadoMembresia
- uploadFile
- investigationLines
- memberInvestigation

Esto significa que, si deseas agregar un nuevo endpoint, normalmente debes:

1. Añadir un nuevo case dentro del switch en backend/public/index.php.
2. Crear o reutilizar un controlador.
3. Crear o reutilizar un servicio con la lógica necesaria.
4. Registrar la ruta en la lógica de autorización si aplica.

## Requisitos previos

- Docker y Docker Compose instalados.
- PHP 7.4 o superior para ejecución local opcional.
- Acceso a la red local para consumir la API vía HTTP.

## Ejecutar el proyecto con Docker

El repositorio incluye un archivo docker-compose.yml que levanta dos servicios:

- redmis_db: base de datos MySQL.
- redmis_api: servidor PHP embebido con el código del backend.

### 1. Levantar los contenedores

```bash
docker compose up -d --build
```

### 2. Verificar que los servicios estén activos

```bash
docker compose ps
```

### 3. Probar un endpoint de ejemplo

La API quedará disponible en:

- http://localhost:8080/backend/public/test

Ese endpoint devuelve una respuesta JSON simple para verificar que el servidor PHP está funcionando.

### 4. Ejemplo de endpoint para login

```bash
curl -X POST http://localhost:8080/backend/public/login \
  -H "Content-Type: application/json" \
  -d '{"email":"usuario@ejemplo.com","password":"tu_password"}'
```

Si el login es correcto, la API responderá con un token JWT y los datos del usuario.

### 5. Detener los contenedores

```bash
docker compose down
```

Si quieres limpiar también la base de datos persistida:

```bash
docker compose down -v
```

## Variables de la base de datos

La configuración por defecto usa los siguientes valores:

- Host: redmis_db
- Base de datos: mr_db
- Usuario: mr_user
- Contraseña: #Redmis1
- Puerto expuesto en Docker: 3307

La base de datos se inicializa automáticamente con el script ubicado en db/MR_DB_V7.sql.

## Ejecutar el servidor PHP localmente y la base de datos con Docker

Si prefieres ejecutar la API localmente en tu máquina, puedes dejar solo la base de datos en Docker y levantar el servidor PHP de forma manual:

### 1. Levantar únicamente la base de datos

```bash
docker compose up -d redmis_db
```

### 2. Levantar el servidor PHP embebido

```bash
php -S 127.0.0.1:8000 -t backend/public
```

### 3. Probar un endpoint local

```bash
curl http://127.0.0.1:8000/index.php/test
```

O abrir directamente en el navegador:

- http://127.0.0.1:8000/index.php/test

## Notas importantes

- La mayoría de los endpoints que modifican datos esperan recibir JSON en el cuerpo de la solicitud.
- Los endpoints protegidos requieren el header Authorization con un token Bearer.
- Las rutas públicas como login, verify y algunas operaciones de consulta son excepciones del middleware de autenticación.
- Para añadir una nueva funcionalidad, lo más común es crear un controlador nuevo, un servicio nuevo y agregar un case en backend/public/index.php.

