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

---

## Ramas De Trabajo Activas

Junto a `alex`, existen dos ramas locales con cambios en curso que aun no se han
fusionado con `main` ni con `alex`.

### `feature/front-side`

Rama dedicada a la homogeneizacion del frontend visual y la maquetacion HTML:

- Migracion de cadenas HTML construidas con `innerHTML` a plantillas `<template>`
  con `textContent`, eliminando riesgos de XSS.
- Centralizacion de cuadros de dialogo nativos (`alert()` y `confirm()`) en un
  unico componente `<dialog id="globalDialog">` compartido entre todas las
  paginas, con una API unificada (`alertModal()`, `confirmModal()`).
- Unificacion de la paleta de colores en un solo bloque `:root` dentro de
  `style.css`, eliminando 5 bloques `:root` duplicados en CSS de pagina y
  utilizando nombres semanticos (`--interactive-primary`, `--bg-page`,
  `--text-primary`, etc.).
- Eliminacion de codigo CSS muerto (selectores definidos dos veces, resets
  universales repetidos en cada archivo de pagina).
- Correccion del orden de carga de CSS para que `style.css` (global) cargue
  siempre primero y los estilos de pagina especifica despues.
- Migracion de eventos `onclick`/`onsubmit` en HTML a `addEventListener` en JS.

Estado actual: completada a nivel de codigo, pendiente de revision visual.

### `feature/centralizacion`

Rama dedicada a la centralizacion de los endpoints API:

- Creacion de 5 endpoints unificados (`api/Book.php`, `api/Auth.php`,
  `api/User.php`, `api/Comment.php`, `api/Order.php`) que reemplazan los 20
  archivos individuales (`GetAllBooks.php`, `AddBook.php`, `Login.php`, etc.),
  simplificando el mantenimiento y reduciendo la duplicacion de logica de
  validacion, sesion y respuesta JSON.
- Migracion de todas las llamadas del frontend JS para consumir estos endpoints
  centralizados en vez de los archivos individuales.
- Componentizacion del header y footer como funciones JS reutilizables,
  inyectadas desde `header.js` y `footer.js`, eliminando la repeticion del mismo
  marcado HTML en las 11 paginas del proyecto.

Estado actual: completada en la rama, pendiente de recepcion de cambios con
`alex` y pruebas de regresion.

---

## Notas Para La Defensa

- La rama `main` representa el estado inicial del proyecto, sin las correcciones
  solicitadas. La rama `alex` contiene el codigo estabilizado.
- Cada decision de cambio esta motivada por los criterios de la rubrica:
  separacion de capas, contrato REST, validacion en API, seguridad y
  mantenibilidad del frontend.
- `feature/front-side` y `feature/centralizacion` son ramas locales que aun no
  se han fusionado con `alex`; su contenido se mostrara en la defensa como
  trabajo complementario.
