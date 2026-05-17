# Reto Libreria

Proyecto academico PHP/MySQL con arquitectura MVC, endpoints REST JSON y frontend HTML/CSS/JS.

## Estructura

```text
view/html/              Paginas HTML
view/assets/js/         JavaScript del cliente
view/assets/css/        Estilos
api/                    Endpoints PHP con respuesta JSON
controller/             Controladores puente
model/dao/              DAO con SQL y PDO
model/entities/         Entidades y toArray()
Config/                 Sesion y conexion a base de datos
SQL/CRUD_ADT.sql        Script de base de datos
```

## Flujo MVC

```text
view/html + view/assets/js
  -> api/*.php
  -> controller/*.php
  -> model/dao/*.php
  -> MySQL
```

La respuesta vuelve en sentido inverso:

```text
MySQL
  -> model/dao/*.php
  -> controller/*.php
  -> api/*.php con JSON estandar
  -> view/assets/js renderiza DOM
```

## Contrato JSON

Los endpoints deben responder con este formato:

```json
{
  "status": "success|error",
  "code": 200,
  "message": "Mensaje claro",
  "data": {}
}
```

Reglas aplicadas:

- `code` coincide con `http_response_code(...)`.
- `status` usa `success` o `error`.
- Los errores devuelven preferentemente `data: null`.
- Los endpoints declaran `Content-Type: application/json; charset=utf-8`.
- No se devuelven trazas, SQL ni detalles sensibles al cliente.
- Todos los endpoints siguen el orden: `header()` antes que `require_once`.

## Convenciones de Codigo

### Orden en API (`api/*.php`)

```
<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../controller/...';
require_once '../Config/Session.php';  // si aplica
if ($_SERVER['REQUEST_METHOD'] !== ...)
```

- El `header()` va siempre inmediatamente despues de `<?php`, antes de cualquier `require_once`.
- No se usan `Access-Control-Allow-Origin` ni CORS (mismo origen).
- `Config/Session.php` ya emite cache-busting headers; no se duplican en los endpoints.

## Metodos HTTP

El proyecto mantiene una convencion simple y compatible con PHP academico:

- `GET` para lecturas.
- `POST` para acciones que crean, modifican, eliminan o ejecutan operaciones.
- No se usan `PUT`, `PATCH` ni `DELETE` para evitar complejidad innecesaria con `$_POST`, `FormData` y subida de archivos.

Los endpoints validan explicitamente el metodo recibido. Si no corresponde, responden:

```json
{
  "status": "error",
  "code": 405,
  "message": "Método no permitido.",
  "data": null
}
```

Endpoints de lectura con `GET`:

```text
GetAllBooks.php
GetBook.php
GetComments.php
GetAllUsers.php
GetProfile.php
GetOrder.php
CheckSession.php
```

Endpoints de accion con `POST`:

```text
Login.php
Logout.php
AddUser.php
ModifyUser.php
ModifyAdmin.php
ModifyPassword.php
DeleteUser.php
AddBook.php
ModifyBook.php
AddComment.php
UpdateComment.php
DeleteComment.php
BuyNow.php
```

## Frontend JS

Los `fetch` del frontend se centralizan en `view/assets/js/apiClient.js` mediante `apiFetch()`.

```js
import { apiFetch } from './apiClient.js';

const response = await apiFetch('../../api/GetAllBooks.php');
```

Reglas aplicadas:

- No se usan cadenas `fetch().then().then()` en los JS de la aplicacion.
- Las llamadas usan `async/await` y `try/catch`.
- `apiFetch()` acepta un parametro `allowedStatuses` para manejar ciertos codigos HTTP sin lanzar error (ej. `401` en `checkSession`).
- `apiFetch()` valida JSON, `response.ok`, `status` y que `code` coincida con el HTTP status.
- Las llamadas de sesion/autenticacion usan `credentials: 'include'` cuando corresponde.
- Todos los `console.log()` muestran el objeto JSON completo (`data`), no solo `data.code`.
- Las redirecciones por acceso bloqueado usan `window.location.replace()`; las de navegacion normal usan `window.location.href`.

## Responsabilidades Por Capa

API (`api/`):

- Lee `$_GET`, `$_POST`, JSON body o archivos.
- Valida y sanea datos de entrada.
- Comprueba sesion y permisos.
- Decide codigo HTTP.
- Convierte entidades a array antes de `json_encode()`.

Controller (`controller/`):

- Orquesta llamadas.
- No lee variables HTTP globales.
- No imprime JSON.
- No contiene SQL.

DAO (`model/dao/`):

- Contiene el SQL.
- Usa PDO y prepared statements.
- Devuelve entidades cuando aplica.

Entities (`model/entities/`):

- Encapsulan datos.
- Exponen `toArray()` para serializacion.
- No exponen hashes de contrasena en respuestas API.

## Seguridad Aplicada

- Login con `password_verify()`.
- Registro/cambio de contrasena con `password_hash()`.
- Regeneracion de ID de sesion tras login correcto.
- Endpoints sensibles validan sesion y rol.
- `DeleteUser.php` permite borrar solo si es el propio usuario o un admin.
- `ModifyAdmin.php` exige rol admin.
- JSON invalido devuelve `400` con contrato estandar.
- Subida de portadas valida tamano, extension y MIME real con `finfo`.
- `Profile::toArray()` no serializa `pswd`.

## Comandos De Verificacion

Lint de PHP modificado o endpoint concreto:

```bash
php -l api/GetBook.php
```

Lint completo PHP:

```bash
find . -name "*.php" -not -path "./.git/*" -print0 | xargs -0 -n1 php -l
```

Comprobacion sintactica JS:

```bash
node --check view/assets/js/apiClient.js
```

Smoke test de endpoint publico:

```bash
curl -s -i http://localhost/Reto_Libreria/api/GetAllBooks.php
```

Smoke test con parametros:

```bash
curl -s -i "http://localhost/Reto_Libreria/api/GetBook.php?isbn=9780451524935"
```

Smoke test de sesion:

```bash
curl -s -i http://localhost/Reto_Libreria/api/CheckSession.php
```

Smoke test de metodo incorrecto:

```bash
curl -s -i -X POST http://localhost/Reto_Libreria/api/GetAllBooks.php
curl -s -i -X GET http://localhost/Reto_Libreria/api/Login.php
```

## Estado Actual

- Arquitectura MVC alineada con la rubrica academica.
- Contrato JSON estandar aplicado en todos los endpoints.
- Fetch del frontend centralizado y sin cadenas `.then()` fuera del helper.
- Validacion server-side reforzada en endpoints JSON y operaciones sensibles.
- Metodos HTTP restringidos a `GET` y `POST` con errores `405` cuando corresponde.
- `GetBook.php` obtiene entidad desde DAO y serializa en API.
- Todos los endpoints PHP siguen el mismo orden: `header()` > `require_once` > validacion metodo.
- Sin cabeceras CORS en endpoints (mismo origen).
- `console.log()` homogeneizado para mostrar JSON completo en todos los JS.
- Redirecciones de bloqueo unificadas con `window.location.replace()`.
- Sesion sin duplicacion de cache-busting headers (los gestiona `Config/Session.php`).
- Quedan como criterio de mantenimiento futuro revisar progresivamente endpoints no tocados antes de nuevas funcionalidades.
