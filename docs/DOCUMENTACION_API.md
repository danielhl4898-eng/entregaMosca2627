# Documentación de la API — Juego de la Mosca

**URL base:** `http://localhost:8080`
**Formato:** JSON (entrada y salida). **Autenticación:** credenciales `user` y `pass` en el cuerpo de cada petición que las requiera (ver README §7).

## Índice de endpoints

|Método|URL|Auth|Rol|Descripción|
|-|-|-|-|-|
|POST|`/register`|No|—|Registrar un usuario (siempre JUGADOR)|
|GET|`/todosUsu`|No|—|Listar usuarios|
|GET|`/{id}`|No|—|Consultar un usuario|
|DELETE|`/{id}`|Sí|ADMIN|Eliminar un usuario|
|POST|`/cambiarRol/{admin\|jugador}/{id}`|Sí|ADMIN|Cambiar el rol de un usuario|
|PATCH|`/pass`|Sí|Cualquiera|Cambiar la propia contraseña|
|PATCH|`/email`|Sí|Cualquiera|Cambiar el propio email|
|POST|`/olvido/{id}`|No|—|Recuperar contraseña por email|
|POST|`/crear`|Sí|JUGADOR|Crear un tablero|
|POST|`/login/jugar`|Sí|JUGADOR|Dar un golpe en una casilla|
|GET|`/consultar`|Sí|JUGADOR|Ver tablero activo e intentos restantes|
|POST|`/rendirse`|Sí|JUGADOR|Abandonar la partida y ver la mosca|

Cualquier otro método HTTP (p. ej. `PUT`) → `405 Method Not Allowed`.

## Convenciones

**Credenciales** (en el body de los endpoints con *Auth: Sí*):

```json
{ "user": "jugador", "pass": "jugador1234" }
```

**Errores:** siempre JSON con `"ok": false` y un mensaje.

|Código|Mensaje (`mensaje` / `error`)|Cuándo|
|-|-|-|
|400|`Parámetros incorrectos`|Datos inválidos, ruta GET desconocida|
|400|`Opción equivocada`|Subruta no válida|
|400|`Se ha producido un error`|El usuario no existe|
|401|`Algún parametro incorrecto`|Credenciales incorrectas|
|403|`Un administrador no puede jugar al juego`|Un ADMIN intenta jugar|
|403|`Solo pueden borrar usuarios los administradores`|Un no-admin intenta borrar|
|404|`No hay partidas activas`|El jugador no tiene tablero activo|
|404|`No existe ningún usuario…`|Lista de usuarios vacía|
|405|`Método no permitido`|Método distinto de GET/POST/PATCH/DELETE|
|409|`Ya existe un usuario con ese nombre`|Registro con `user` repetido|
|409|`Este usuario ya tiene una partida creada`|Crear tablero con uno activo|

**Justificación de códigos:** `401` = no se pudo identificar al usuario; `403` = identificado pero sin el rol necesario; `404` = el recurso «partida activa» no existe; `409` = la petición es correcta pero choca con el estado actual (usuario duplicado o ya hay un tablero activo).

\---

# Usuarios

## POST `/register`

Registra un usuario nuevo. El rol asignado es siempre **JUGADOR**; no se puede registrar un administrador por esta vía.

* **Autenticación:** No
* **Rol:** —
* **Parámetros URL:** ninguno

**Body:**

```json
{
    "user": "ana",
    "pass": "1234",
    "email": "ana@ejemplo.com",
    "edad": 22
}
```

**Respuesta correcta — `200 OK`:**

```json
{ "ok": true, "mensaje": "Se ha completado la solicitud correctamente" }
```

**Errores:**

|Código|Causa|
|-|-|
|409 Conflict|Ya existe un usuario con ese `user`|
|400 Bad Request|Faltan datos o el insert falla|

\---

## GET `/todosUsu`

Lista todos los usuarios.

* **Autenticación:** No
* **Rol:** —
* **Body:** ninguno

**Respuesta correcta — `200 OK`:** array con las filas de la tabla `usuarios`.

```json
\[
  { "id": 1, "user": "admin",   "edad": 30, "email": "admin@ejemplo.com",   "pass": "c93c…", "idRol": 2 },
  { "id": 2, "user": "jugador", "edad": 20, "email": "jugador@ejemplo.com", "pass": "28b8…", "idRol": 1 }
]
```

**Errores:** `404 Not Found` si no hay ningún usuario.

\---

## GET `/{id}`

Consulta el nombre y el email de un usuario.

* **Autenticación:** No
* **Rol:** —
* **Parámetros URL:** `id` (numérico) — identificador del usuario
* **Body:** ninguno

Ejemplo: `GET /2`

**Respuesta correcta — `200 OK`:**

```json
{ "user": "jugador", "email": "jugador@ejemplo.com" }
```

**Errores:** `400 Bad Request` si el usuario no existe.

\---

## DELETE `/{id}`

Elimina un usuario.

* **Autenticación:** Sí
* **Rol:** ADMIN
* **Parámetros URL:** `id` — usuario a eliminar

**Body** (credenciales de un administrador):

```json
{ "user": "admin", "pass": "admin1234" }
```

**Respuesta correcta — `200 OK`:**

```json
{ "ok": true, "Se ha eliminado el usuario correctamente": true }
```

**Errores:**

|Código|Causa|
|-|-|
|403 Forbidden|El usuario autenticado no es administrador|

\---

## POST `/cambiarRol/{rol}/{id}`

Cambia el rol de un usuario. Un jugador no puede cambiar roles (ni el suyo ni el de otros).

* **Autenticación:** Sí
* **Rol:** ADMIN
* **Parámetros URL:**

  * `rol` — `admin` o `jugador`
  * `id` — usuario al que se cambia el rol

Ejemplo: `POST /cambiarRol/admin/3`

**Body** (credenciales del administrador):

```json
{ "user": "admin", "pass": "admin1234" }
```

**Respuesta correcta — `200 OK`:**

```json
{ "ok": true, "mensaje": "Se ha completado el cambio correctamente" }
```

**Errores:**

|Código|Causa|
|-|-|
|400 Bad Request|`rol` distinto de `admin`/`jugador`|
|401 Unauthorized|Quien llama no es administrador o las credenciales no son válidas|

\---

## PATCH `/pass`

Cambia la contraseña del propio usuario.

* **Autenticación:** Sí
* **Rol:** Cualquiera (se actúa sobre el usuario autenticado)

**Body:**

```json
{ "user": "ana", "pass": "1234", "passNueva": "nueva5678" }
```

**Respuesta correcta — `201 Created`:**

```json
{ "ok": true, "mensaje": "Se ha cambiado la contraseña correctamente" }
```

**Errores:** `401` credenciales incorrectas · `400` usuario no encontrado.

\---

## PATCH `/email`

Cambia el email del propio usuario.

* **Autenticación:** Sí
* **Rol:** Cualquiera

**Body:**

```json
{ "user": "ana", "pass": "1234", "emailNuevo": "ana.nueva@ejemplo.com" }
```

**Respuesta correcta — `201 Created`:**

```json
{ "ok": true, "mensaje": "Se ha cambiado la contraseña correctamente" }
```

**Errores:** `401` credenciales incorrectas · `400` usuario no encontrado.

\---

## POST `/olvido/{id}`

Genera una contraseña aleatoria de 12 caracteres, la guarda (hash MD5) y se la envía por email al usuario.

* **Autenticación:** No
* **Rol:** —
* **Parámetros URL:** `id` — usuario que ha olvidado la contraseña
* **Body:** ninguno
* **Requisito:** correo SMTP configurado (README §3.3)

**Respuesta correcta — `200 OK`:**

```json
{ "ok": true, "mensaje": "Se ha enviado el correo correctamente" }
```

**Errores:**

|Código|Causa|
|-|-|
|401 Unauthorized|El usuario no existe o no tiene email (`Correo inválido`)|
|400 Bad Request|Fallo al enviar el correo|

\---

# Juego

Solo los usuarios con rol **JUGADOR** pueden usar estos endpoints. Todos llevan las credenciales en el body.

## POST `/crear`

Crea un tablero nuevo para el jugador. El tablero queda **activo** y la mosca se coloca en una casilla aleatoria.

* **Autenticación:** Sí
* **Rol:** JUGADOR

**Body:**

```json
{
    "user": "ana",
    "pass": "1234",
    "intentos": 8
}
```

|Campo|Tipo|Descripción|
|-|-|-|
|`intentos`|entero|Número de intentos del tablero|

El tablero tiene 10 casillas.

**Respuesta correcta — `201 Created`:**

```json
{ "ok": true, "mensaje": "Se ha creado correctamente la partida" }
```

**Errores:**

|Código|Causa|
|-|-|
|409 Conflict|El jugador ya tiene un tablero activo (no se crea otro)|
|401 Unauthorized|Credenciales incorrectas o usuario administrador|

\---

## POST `/login/jugar`

Da un golpe sobre una casilla del tablero activo. Consume un intento. **Nunca** devuelve la posición de la mosca mientras la partida sigue activa.

* **Autenticación:** Sí
* **Rol:** JUGADOR
* **Parámetros URL:** el segundo segmento debe ser literalmente `jugar`; otro valor devuelve 400.

**Body:**

```json
{
    "user": "ana",
    "pass": "1234",
    "casilla": 4
}
```

|Campo|Tipo|Descripción|
|-|-|-|
|`casilla`|entero|Casilla golpeada, numerada desde **1**|

**Respuestas correctas — todas `200 OK`:**

|Resultado|Cuerpo|
|-|-|
|**Cazada** (la partida termina)|`{ "Menudo bimbazo!!1!": "Le has dado a la mosca", "Intentos sobrantes": 3 }`|
|**Casi le das** (la mosca se mueve)|`{ "vaya panoli": "casi le das, se ha movido", "intentos restantes": 3 }`|
|**Ni se entera**|`{ "buah": "ni cerca", "Intentos restantes": 3 }`|
|**Sin intentos** (la partida termina)|`{ "ok": "true", "Partida": "acabada", "posicion de la mosca final": 8 }`|

Regla de «cercana»: casilla adyacente (±1) a la de la mosca.

**Errores:**

|Código|Causa|
|-|-|
|404 Not Found|El jugador no tiene tablero activo (incluye partidas ya terminadas)|
|400 Bad Request|Subruta distinta de `jugar`|
|403 Forbidden|El usuario es administrador|
|401 Unauthorized|Credenciales incorrectas|

\---

## GET `/consultar`

Devuelve el tablero activo del jugador y los intentos que le quedan. No incluye la posición de la mosca.

* **Autenticación:** Sí
* **Rol:** JUGADOR

**Body:**

```json
{ "user": "ana", "pass": "1234" }
```

**Respuesta correcta — `200 OK`:**

```json
{ "id\_tablero": 5, "intentos": 3 }
```

**Errores:**

|Código|Causa|
|-|-|
|404 Not Found|No hay tablero activo|
|400 Bad Request|Usuario no encontrado|

\---

## POST `/rendirse`

Abandona la partida activa: el tablero pasa a `activo = 0` y se devuelve dónde estaba la mosca.

* **Autenticación:** Sí
* **Rol:** JUGADOR

**Body:**

```json
{ "user": "ana", "pass": "1234" }
```

**Respuesta correcta — `200 OK`:**

```json
{
    "ok": "true",
    "Partida": "rendida",
    "posicion de la mosca final": 8
}
```

**Errores:**

|Código|Causa|
|-|-|
|404 Not Found|No hay tablero activo|
|401 Unauthorized|Credenciales incorrectas|

\---

# Resumen de reglas y cómo se cumplen

|Regla|Implementación|
|-|-|
|Un usuario no autenticado no juega|Los endpoints de juego exigen `user`/`pass` válidos|
|Un jugador no gestiona a otros|Borrar usuarios y cambiar roles exigen ADMIN|
|Un jugador no cambia su rol|`/cambiarRol` solo para ADMIN; `/register` siempre crea JUGADOR|
|Un solo tablero activo|`/crear` → 409 si ya hay uno con `activo = 1`|
|Un golpe consume un intento|Se resta 1 en cada golpe|
|Sin intentos, fin de partida|`activo = 0` y se devuelve la posición|
|Partida terminada no recibe golpes|Al no haber tablero activo → 404|
|Sin tablero activo → error|`/login/jugar`, `/consultar`, `/rendirse` → 404|
|Posición oculta mientras se juega|Solo se devuelve al terminar o rendirse|



