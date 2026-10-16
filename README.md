# API REST — Juego de la Mosca

API REST en **PHP** (sin framework, `mysqli` + MySQL/MariaDB) para jugar a **La Mosca**: una mosca se esconde en una casilla de un tablero y el jugador tiene un número limitado de intentos para cazarla. Incluye gestión de usuarios con dos roles (`JUGADOR` y `ADMIN`), partidas por usuario y recuperación de contraseña por email.

No tiene interfaz gráfica: se prueba con Postman (colección incluida) o con `curl`.

> Documentación detallada de cada endpoint: \[`DOCUMENTACION\_API.md`](DOCUMENTACION\_API.md)

\---

## 1\. Estructura del proyecto

```text
ServidorMosca/
├── index.php                       # Punto de entrada y enrutado por método/URL
├── auxiliar.php                    # Respuestas JSON + código HTTP reutilizables
├── .env                            # Credenciales SMTP (ver §3.3)
├── controllers/
│   ├── controller.php              # Usuarios, login, tableros, roles, contraseña
│   ├── controladorJuego.php        # Lógica de jugar y rendirse
│   └── tablero.php                 # Modelo del tablero y reglas del golpe
├── Database/
│   ├── parametros.php              # Host, usuario, clave y nombre de la BD
│   ├── database.php                # Conexión mysqli
│   └── DAO.php                     # Todas las consultas SQL (sentencias preparadas)
├── phpmailer/                      # Librería PHPMailer (envío de correo)
└── mosca.postman\_collection.json   # Colección de Postman
```

## 2\. Requisitos

* PHP 8.x con las extensiones `mysqli` y `mbstring` (y `openssl` para el envío de correo por SMTP).
* MySQL 5.7+ o MariaDB 10.x.
* Postman (o `curl`) para probar.

## 3\. Instalación

### 3.1 Obtener el código

Descomprime `ServidorMosca.zip` y entra en la carpeta:

```bash
cd ServidorMosca
```

### 3.2 Configurar la base de datos

El nombre de la base de datos y las credenciales de conexión están en `Database/parametros.php`:

```php
static $host    = '127.0.0.1';
static $usuario = 'root';
static $clave   = '';
static $bd      = 'moscafix';
```

Ajusta estos valores a tu entorno y crea la base de datos con el script de §5 (esquema + datos de prueba):

```bash
mysql -u root -p < base\_de\_datos.sql
```

### 3.3 Configurar el correo (solo para «he olvidado mi contraseña»)

`POST /olvido/{id}` envía el correo con **PHPMailer** por SMTP de Gmail (`smtp.gmail.com`, puerto 465, SSL). Las credenciales se leen de **variables de entorno** (`getenv`), definidas en el fichero `.env`:

```text
MAIL\_PROPIO=cuenta@gmail.com        # cuenta remitente
SMTP\_PASSWORD=xxxx xxxx xxxx xxxx   # contraseña de aplicación de Google (no la contraseña normal)
```

PHP no lee `.env` por sí solo: hay que exportar las variables antes de arrancar el servidor (ver §4). El resto de endpoints funcionan sin configurar el correo.

> ⚠️ El `.env` contiene credenciales reales: \*\*no lo subas a un repositorio ni lo incluyas en la entrega\*\*. Entrega un `.env.example` con valores vacíos.

## 4\. Arrancar la API

Desde la carpeta `ServidorMosca`:

```bash
# Linux / macOS (cargar las variables del .env y arrancar)
export $(grep -v '^#' .env | xargs)
php -S localhost:8080 index.php
```

```powershell
# Windows PowerShell
Get-Content .env | ForEach-Object { $k,$v = $\_ -split '=',2; \[Environment]::SetEnvironmentVariable($k,$v) }
php -S localhost:8080 index.php
```

La API queda disponible en `http://localhost:8080`. Todas las respuestas son JSON (`Content-Type: application/json`).

## 5\. Cómo funciona el juego

### 5.1 Tablero

Cada tablero se guarda como una cadena de dígitos, un carácter por casilla:

|Dígito|Significado|
|-|-|
|`2`|Casilla donde está la mosca|
|`1`|Casilla **cercana** (adyacente a la mosca)|
|`0`|Casilla lejana|

Ejemplo (10 casillas, mosca en la casilla 4): `0012100000`.

Las casillas se numeran **desde 1**. El cliente nunca recibe esta cadena mientras la partida está activa; solo se devuelve la posición de la mosca cuando la partida termina o el jugador se rinde.

### 5.2 Resultado de un golpe

Cada golpe consume **1 intento**.

|Casilla golpeada|Resultado|Efecto|
|-|-|-|
|La de la mosca|`cazada`|La partida termina (`activo = 0`)|
|**Adyacente** a la mosca (±1 casilla)|`casi le das`|La mosca **revolotea**: el servidor la recoloca en una casilla aleatoria del tablero|
|Cualquier otra|`ni se entera`|La mosca no se mueve|

Si los intentos llegan a 0 la partida termina y se devuelve la posición de la mosca.

### 5.3 Decisiones de diseño

* **«Casilla cercana»**: se considera cercana la casilla inmediatamente anterior o posterior a la mosca (distancia 1). Con tableros de 10 casillas da una probabilidad razonable de «casi» sin que sea trivial.
* **Estado del tablero**: se usa un único campo booleano `activo` (1/0) en vez de los estados `ACTIVO/GANADO/PERDIDO/RENDIDO`. Es suficiente para aplicar la regla «un solo tablero activo por usuario». La causa del final (ganar, perder o rendirse) se comunica en la respuesta de la petición que cierra la partida.
* **Un tablero activo por usuario**: `POST /crear` comprueba si el usuario tiene un tablero con `activo = 1`; si lo tiene responde **409 Conflict** (la petición es válida pero choca con el estado actual del recurso) y no crea nada.
* **Códigos HTTP** (resumen): `200` OK, `201` recurso creado/actualizado, `400` petición mal formada o recurso inexistente, `401` credenciales incorrectas, `403` rol insuficiente, `404` no hay partida activa / no hay usuarios, `405` método no permitido, `409` conflicto (usuario o partida ya existente). Detalle por endpoint en la documentación de la API.
* **Roles**: `ADMIN` gestiona usuarios y no juega; `JUGADOR` juega y gestiona sus propios datos. El registro público siempre crea `JUGADOR`.

## 6\. Autenticación

La API **no usa tokens ni sesiones**: es *stateless* y cada petición que necesita identificar al usuario lleva sus credenciales en el cuerpo JSON:

```json
{ "user": "jugador", "pass": "jugador1234" }
```

* El servidor guarda la contraseña como hash MD5 y compara el hash de la que recibe.
* Los endpoints de administración (`DELETE /{id}`, `POST /cambiarRol/...`) exigen que esas credenciales pertenezcan a un usuario con rol `ADMIN`.
* Los endpoints de juego (`/login/jugar`, `/crear`, `/rendirse`, `/consultar`) exigen un usuario con rol `JUGADOR`.

## 7\. Cómo probar los endpoints

1. Arranca la API (§4).
2. En Postman: **Import** → `mosca.postman\_collection.json`. Todas las peticiones apuntan a `http://localhost:8080`.
3. Sustituye en cada petición el `user`/`pass` por los usuarios de prueba (§5.3).
4. Orden recomendado de una sesión de juego:

```bash
# 1. Registrar un jugador (rol JUGADOR automáticamente)
curl -X POST localhost:8080/register -d '{"user":"ana","pass":"1234","email":"ana@ejemplo.com","edad":22}'

# 2. Crear un tablero con 6 intentos
curl -X POST localhost:8080/crear -d '{"user":"ana","pass":"1234","intentos":6}'

# 3. Dar un golpe a la casilla 4
curl -X POST localhost:8080/login/jugar -d '{"user":"ana","pass":"1234","casilla":4}'

# 4. Consultar el tablero activo e intentos restantes
curl -X GET localhost:8080/consultar -d '{"user":"ana","pass":"1234"}'

# 5. Rendirse y ver dónde estaba la mosca
curl -X POST localhost:8080/rendirse -d '{"user":"ana","pass":"1234"}'

# 6. Como admin: listar usuarios, cambiar rol y borrar
curl localhost:8080/todosUsu
curl -X POST localhost:8080/cambiarRol/admin/3 -d '{"user":"admin","pass":"admin1234"}'
curl -X DELETE localhost:8080/3 -d '{"user":"admin","pass":"admin1234"}'
```

### 7.1 Casos a probar (checklist del enunciado)

|Caso|Petición|Resultado esperado|
|-|-|-|
|Registro|`POST /register`|200|
|Usuario repetido|`POST /register` mismo `user`|409|
|Login incorrecto|`POST /login/jugar` con `pass` errónea|401|
|Crear tablero|`POST /crear`|201|
|Segundo tablero activo|`POST /crear` otra vez|409|
|Golpe válido|`POST /login/jugar` con `casilla` 1‑N|200 con resultado|
|Sin tablero activo|`POST /login/jugar`, `GET /consultar`, `POST /rendirse`|404|
|Consultar intentos|`GET /consultar`|200 con `intentos`|
|Rendición|`POST /rendirse`|200 con la posición de la mosca|
|Victoria|golpear la casilla de la mosca|200 «Le has dado a la mosca»|
|Sin intentos|agotar los intentos|200 «acabada» + posición|
|Seguir jugando tras terminar|golpe tras finalizar|404 (ya no hay tablero activo)|
|Jugador intenta borrar|`DELETE /{id}` con un jugador|403|
|Admin borra|`DELETE /{id}` con admin|200|
|Cambio de rol|`POST /cambiarRol/admin/{id}` con admin|200|
|Método no permitido|`PUT /…`|405|



