# Reto Libreria - Informe de cambios

Proyecto academico hecho con PHP, MySQL, HTML, CSS y JavaScript. La aplicacion sigue una estructura MVC sencilla: endpoints en `api/`, controladores en `controller/`, consultas en `model/dao/` y entidades en `model/entities/`.

## `entregado` vs `correccion`

`entregado` es el estado inicial del proyecto. `correccion` es la version revisada, con arreglos de API, seguridad, MVC, frontend y documentacion interna.

No se ha intentado rehacer el proyecto desde cero. Los cambios se han centrado en corregir puntos concretos que afectaban a mantenimiento, seguridad y coherencia entre frontend y backend.

---

## Problemas vistos en `entregado`

### Arquitectura MVC

- Habia endpoints con demasiada responsabilidad: validaban, hacian logica y en algunos casos se acercaban demasiado al acceso a datos.
- Algunas partes no separaban bien API, controlador y DAO.
- Varias entidades no tenian una salida clara para JSON, asi que la API acababa montando arrays manualmente.

### Respuestas JSON

- No todos los endpoints respondian igual.
- En algunos sitios aparecian claves distintas, como `exito`, `mensaje` o datos devueltos directamente.
- Algunos errores salian como `200 OK`, aunque realmente eran errores de validacion o metodo.

### Seguridad

- La sesion y el rol se comprobaban de forma irregular entre endpoints.
- La password hasheada podia acabar dentro de una respuesta API.
- La subida de portadas comprobaba la extension, pero no el MIME real del archivo.
- El login no regeneraba el ID de sesion despues de autenticarse.

### Frontend

- Las llamadas `fetch()` estaban repetidas en muchos archivos.
- El manejo de errores no era igual en todas las pantallas.
- Los `console.log()` mostraban partes sueltas de la respuesta, lo que hacia mas dificil depurar.

---

## Cambios aplicados en `correccion`

### API y contrato JSON

Los endpoints pasan a responder con el mismo formato:

```json
{
  "status": "success|error",
  "code": 200,
  "message": "Descripcion legible",
  "data": {}
}
```

- `code` coincide con el codigo HTTP real.
- Los errores de validacion devuelven `400`.
- Los recursos no encontrados devuelven `404`.
- Los metodos no permitidos devuelven `405`.
- Los errores internos devuelven `500` sin enseñar SQL ni trazas.

### Metodos HTTP

Se dejo una convencion simple para el proyecto:

- `GET` para leer datos.
- `POST` para crear, modificar, borrar o ejecutar acciones.
- Si se llama a un endpoint con un metodo incorrecto, responde `405`.

### Separacion por capas

| Capa | Responsabilidad |
|------|-----------------|
| `api/*.php` | Lee la peticion, valida, sanea, comprueba sesion/rol y responde JSON. |
| `controller/` | Hace de paso intermedio entre API y DAO. |
| `model/dao/` | Contiene SQL, prepared statements y transacciones. |
| `model/entities/` | Representa datos y define `toArray()` para la API. |

La idea es que la API no tenga SQL, que el DAO no sepa nada de HTTP y que las entidades controlen que datos salen hacia el frontend.

### Seguridad

- Login con `password_verify()`.
- Registro con `password_hash()`.
- `session_regenerate_id(true)` despues de un login correcto.
- Endpoints sensibles protegidos por sesion y rol.
- `Profile::toArray()` no devuelve `pswd`.
- Portadas validadas por tamaño, extension y MIME real con `finfo`.
- Errores tecnicos registrados en servidor, no enviados al navegador.

### Frontend

- Se creo `view/assets/js/apiClient.js` con `apiFetch()`.
- Las llamadas API usan `async/await` y `try/catch`.
- `apiFetch()` revisa que el JSON tenga el contrato esperado.
- `allowedStatuses` permite tratar casos como `401` sin romper el flujo.
- Los logs muestran la respuesta completa, no fragmentos sueltos.
- Las redirecciones por bloqueo usan `window.location.replace()`.

### Comentarios

Se comentaron las partes principales del codigo.

---

## Archivos tocados y motivo

| Ruta | Que se cambio |
|------|---------------|
| `api/*.php` | Contrato JSON comun, validacion de metodos, control de sesion/rol y saneamiento de entrada. |
| `controller/*.php` | Se mantiene como capa intermedia, sin SQL ni acceso directo a `$_GET` o `$_POST`. |
| `model/dao/*.php` | Consultas SQL, prepared statements y transacciones. |
| `model/entities/*.php` | `toArray()` para exponer solo datos necesarios. |
| `Config/Database.php` | Conexion PDO centralizada y errores de conexion sin detalles tecnicos para el cliente. |
| `Config/Session.php` | Arranque de sesion unificado y parametros de cookie compartidos. |
| `view/assets/js/apiClient.js` | Helper comun para `fetch`, validacion JSON y errores HTTP. |
| `view/assets/js/session.js` y `view/assets/js/header.js` | Estado de sesion, logout, cabecera y footer compartidos. |
| `view/assets/js/*.js` | Llamadas API mas homogeneas, `async/await` y manejo de errores. |
| `view/html/*.html` | Comentarios de secciones grandes y contenedores usados por JavaScript. |
| `view/assets/css/*.css` | Bloques de estilos mejor marcados para mantenimiento. |
| `SQL/CRUD_ADT.sql` | Comentarios en procedimientos con agregaciones y `JOIN`. |
| `README.md` | Resumen de problemas, arreglos, archivos tocados y verificaciones. |

---

## Resumen rapido

| Aspecto | En `entregado` | En `correccion` |
|---------|----------------|------------------|
| Contrato JSON | Inconsistente | `{status, code, message, data}` |
| Codigos HTTP | Algunos errores salian como `200` | `400`, `404`, `405`, `500` segun el caso |
| MVC | Responsabilidades mezcladas | API valida, controller coordina, DAO consulta |
| Entidades | Faltaban `toArray()` en partes clave | Entidades preparadas para JSON |
| SQL | Riesgo en puntos concretos | Prepared statements en entradas externas |
| Sesion | Comprobaciones dispersas | Validacion explicita en endpoints sensibles |
| Login | No regeneraba ID de sesion | Regenera ID tras login correcto |
| Password | Podia filtrarse el hash | `toArray()` no expone `pswd` |
| Portadas | Solo extension | Extension, tamaño y MIME real |
| Fetch | Logica repetida por archivo | `apiFetch()` comun |
| Comentarios | Pocos o informales | Comentarios en el codigo |

---

## Comprobaciones realizadas

- Endpoints GET revisados con contrato JSON comun.
- Endpoints POST con JSON invalido devuelven `400`.
- Endpoints llamados con metodo incorrecto devuelven `405`.
- La sesion se acepta o rechaza segun corresponda.
- Las respuestas API no devuelven la password hasheada.
