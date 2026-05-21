# Reto Libreria — Documentacion Para El Profesorado

Proyecto academico PHP/MySQL con arquitectura MVC, endpoints REST JSON y frontend HTML/CSS/JS.

## Rama `main` vs Rama `alex`

Este documento describe el estado de la rama `alex`, que contiene las correcciones y
estandarizaciones aplicadas sobre la base inicial de `main`. A continuacion se detallan
los problemas detectados en `main`, los cambios realizados y las razones academicas
detras de cada decision.

---

## Problemas Detectados En `main`

### Arquitectura Y Separacion De Capas

- Algunos endpoints contenian logica de negocio y SQL mezclados, sin delegar
  correctamente al controlador y DAO.
- Controladores llegaban a leer variables HTTP globales (`$_POST`, `$_GET`),
  responsabilidad que corresponde exclusivamente a la capa API.
- Algunas entidades no tenian metodo `toArray()`, obligando a la API a construir
  arrays manualmente y arriesgando exponer datos sensibles.

### Contrato JSON

- No todos los endpoints respondian con el mismo formato.
- Distintas claves segun el endpoint (`exito`, `mensaje`, `data` directa).
- Codigos HTTP no siempre coincidian con el estado real de la respuesta.
- Errores de validacion llegaban como `200 OK` en vez de `400 Bad Request`.

### Seguridad

- La validacion de sesion y rol se replicaba de forma inconsistente entre endpoints.
- Algunas respuestas podian filtrar la contraseña hasheada del usuario.
- La subida de archivos (portadas) no validaba el MIME real, solo la extension.
- No se regeneraba el ID de sesion tras login correcto.

### Frontend

- Las llamadas `fetch()` usaban cadenas `.then().then()` sin manejo de errores
  centralizado.
- No habia un helper comun para llamadas API, duplicando logica de parseo,
  validacion de respuesta y tratamiento de errores en cada archivo JS.
- Los `console.log()` mostraban fragmentos sueltos del objeto respuesta en vez del
  JSON completo, dificultando la depuracion.

---

## Cambios Aplicados En `alex`

### API: Contrato JSON Estandar

Todos los endpoints siguen esta estructura unica:

```json
{
  "status": "success|error",
  "code": 200,
  "message": "Descripcion legible",
  "data": {}
}
```

- El campo `code` coincide con `http_response_code()`.
- Errores de validacion: `400`.
- Recurso no encontrado: `404`.
- Metodo HTTP incorrecto: `405`.
- Error interno del servidor: `500` sin detalles tecnicos.

### Metodos HTTP

Simplificacion academica compatible con `$_POST` y `FormData`:

- `GET` para lectura (lista libros, detalle, comentarios, perfil, sesion, etc.).
- `POST` para cualquier accion que cree, modifique, elimine o ejecute (login,
  registro, compra, subida de portada, etc.).
- Los endpoints rechazan metodos no soportados con `405`.

### MVC: Responsabilidades Claras

| Capa | Que hace | Que NO hace |
|------|----------|-------------|
| `api/*.php` | Lee input, valida, sanea, verifica sesion/rol, decide HTTP status, serializa con `toArray()` | No ejecuta SQL, no imprime HTML |
| `controller/` | Orquesta llamadas al DAO, recibe entidades | No lee `$_GET`/`$_POST`, no imprime JSON, no contiene SQL |
| `model/dao/` | Contiene el SQL, usa PDO + prepared statements | No imprime, no decide logica de negocio |
| `model/entities/` | Encapsula datos, expone `toArray()` | No conoce SQL ni HTTP |

### Seguridad

- Login con `password_verify()` y registro con `password_hash()`.
- Regeneracion del ID de sesion tras login correcto.
- Endpoints sensibles validan sesion activa y rol del usuario.
- `Profile::toArray()` excluye el campo `pswd` de la respuesta.
- Subida de portadas: validacion de tamaño maximo, extension y MIME real con
  `finfo`.
- JSON mal formado en el body → `400` con contrato estandar.
- No se exponen trazas, SQL ni detalles internos al cliente.

### Frontend: Centralizacion De Llamadas API

- Se creo `view/assets/js/apiClient.js` con la funcion `apiFetch()`.
- Todas las llamadas usan `async/await` + `try/catch`.
- `apiFetch()` valida JSON, `response.ok`, el campo `status` y que `code`
  coincida con el HTTP status.
- Parametro `allowedStatuses` para manejar casos como `401` sin lanzar error
  (necesario en `checkSession`).
- Los `console.log()` muestran el JSON completo de la respuesta.
- Las redirecciones por bloqueo usan `window.location.replace()`; las de
  navegacion normal usan `window.location.href`.

### Homogeneidad Interna

- Todos los endpoints siguen el mismo orden interno:
  `header()` → `require_once` → validacion metodo → logica.
- Sin cabeceras CORS innecesarias (mismo origen).
- `Config/Session.php` gestiona los cache-busting headers una sola vez; no se
  duplican en los endpoints.
- Los controladores reciben la conexion PDO por inyeccion de dependencia.

---

## Tabla Resumen: `main` vs `alex`

| Aspecto | Estado en `main` | Estado en `alex` |
|---------|------------------|-------------------|
| Contrato JSON | Inconsistente entre endpoints | Unico: `{status, code, message, data}` |
| Codigos HTTP | A veces `200` en errores | `400`, `404`, `405`, `500` segun corresponda |
| Separacion MVC | Logica mezclada en algunos endpoints | API valida, controller orquesta, DAO tiene SQL |
| Entidades `toArray()` | Faltante en varias entidades | Implementado en todas |
| Inyeccion SQL | Sin prepared statements en puntos concretos | Todas las consultas con PDO preparado |
| Manejo de sesion | Disperso y con algunas omisiones | Validacion explicita en endpoints sensibles |
| Regenerar session ID | No se hacia | Se hace tras login correcto |
| Filtracion de password | Podia aparecer en respuestas API | Excluida en `toArray()` |
| Subida de portadas | Solo extension | Extension + MIME real con `finfo` |
| Fetch del frontend | `.then().then()` duplicado en cada JS | `apiFetch()` centralizado con `async/await` |
| `console.log()` | Fragmentos sueltos | JSON completo de la respuesta |

---

## Como Se Verifica

`alex` ha pasado las siguientes comprobaciones:

- PHP lint completo sin errores sintacticos.
- Endpoints GET responden correctamente con contrato estandar.
- Endpoints POST con JSON invalido devuelven `400`.
- Endpoints GET llamados con POST devuelven `405`.
- Endpoints POST llamados con GET devuelven `405`.
- La sesion se mantiene o rechaza segun corresponda.
- Los datos sensibles (password hasheada) no aparecen en respuestas.
