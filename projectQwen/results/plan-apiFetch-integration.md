# Plan: Integración de apiFetch en CORRECION

## Objetivo
Reemplazar todas las llamadas `fetch()` crudas por `apiFetch()` en los 9 archivos JS del frontend de CORRECION, centralizando el manejo de errores, parsing JSON y validación de respuestas.

## Contexto
- `apiClient.js` ya existe en el proyecto de Alex y está probado.
- CORRECION tiene 27 llamadas `fetch()` distribuidas en 9 archivos.
- Los ejemplos de clase usan `fetch()` crudo, pero `apiFetch` alinea con sus principios de respuestas estandarizadas y manejo de errores.
- Riesgo: 7/10, mitigado por usar una abstracción probada.

## Principios
1. **Incremental**: Un archivo JS por incremento, verificar sintaxis, commit.
2. **No romper**: Preservar todas las opciones existentes (`credentials`, `headers`, `body`, `method`).
3. **Ejemplos como brújula**: Mantener la misma estructura de manejo de errores que los ejemplos.
4. **Scope discipline**: Solo tocar lo necesario para reemplazar `fetch()` por `apiFetch()`.

## Archivos a Modificar
1. `Reto_LibreriaCORRECION/view/assets/js/apiClient.js` (nuevo)
2. `session.js`
3. `login.js`
4. `main.js`
5. `bookDetails.js`
6. `listBooksForComments.js`
7. `signUp.js`
8. `ordersHistory.js`
9. `manageComments.js`
10. `crudBook.js`
11. `configProfile.js`

## Pasos

### Paso 0: Copiar apiClient.js ✅
- Copiar `Reto_Libreria-alex/view/assets/js/apiClient.js` → `Reto_LibreriaCORRECION/view/assets/js/apiClient.js`
- Verificar que exporta `apiFetch` correctamente.

### Paso 1: session.js ✅
- Agregar `import { apiFetch } from './apiClient.js';`
- Reemplazar `fetch()` para `CheckSession.php` con `apiFetch()`
- Reemplazar `fetch()` para `Logout.php` con `apiFetch()`
- Preservar `credentials: 'include'` y `allowedStatuses: [401]`

### Paso 2: login.js ✅
- Agregar `import { apiFetch } from './apiClient.js';`
- Reemplazar `fetch()` en función `login()` con `apiFetch()`
- Preservar `credentials: 'include'` y body JSON

### Paso 3: main.js ✅
- Agregar `import { apiFetch } from './apiClient.js';`
- Reemplazar `fetch()` para `GetAllBooks.php` con `apiFetch()`

### Paso 4: bookDetails.js ✅
- Agregar `import { apiFetch } from './apiClient.js';`
- Reemplazar 6 llamadas `fetch()`:
  - `GetBook.php`
  - `GetProfile.php`
  - `AddComment.php` / `UpdateComment.php`
  - `GetComments.php`
  - `DeleteComment.php`
  - `BuyNow.php`

### Paso 5: listBooksForComments.js ✅
- Agregar `import { apiFetch } from './apiClient.js';`
- Reemplazar `fetch()` para `GetAllBooks.php`

### Paso 6: signUp.js ✅
- Agregar `import { apiFetch } from './apiClient.js';`
- Reemplazar `fetch()` para `AddUser.php`

### Paso 7: ordersHistory.js ✅
- Agregar `import { apiFetch } from './apiClient.js';`
- Reemplazar `fetch()` para `GetOrder.php`

### Paso 8: manageComments.js ✅
- Agregar `import { apiFetch } from './apiClient.js';`
- Reemplazar 2 llamadas `fetch()`:
  - `GetComments.php`
  - `DeleteComment.php`

### Paso 9: crudBook.js ✅
- Agregar `import { apiFetch } from './apiClient.js';`
- Reemplazar llamadas `fetch()` para:
  - `GetProfile.php` (admin check)
  - `GetAllBooks.php`
  - `GetBook.php`
  - `AddBook.php` / `ModifyBook.php`

### Paso 10: configProfile.js ✅
- Agregar `import { apiFetch } from './apiClient.js';`
- Reemplazar llamadas `fetch()` para:
  - `GetProfile.php`
  - `ModifyUser.php`
  - `CheckSession.php`
  - `GetAllUsers.php`
  - `DeleteUser.php`
  - `Login.php` (verificación contraseña)
  - `ModifyPassword.php`

### Paso 11: Verificación Final ✅
- ✅ Verificar sintaxis de todos los archivos
- ✅ Confirmar que no quedan `fetch()` crudas en los 9 archivos
- ✅ Validar que todos los imports apuntan a `./apiClient.js`

## Criterios de Éxito
- [x] `apiClient.js` copiado sin modificaciones
- [x] 0 llamadas `fetch()` crudas en los 9 archivos
- [x] Todos los imports de `apiFetch` presentes
- [x] Sintaxis válida en todos los archivos
- [x] Mismas opciones preservadas (`credentials`, `headers`, `body`, `method`)
- [x] Manejo de errores consistente con ejemplos de clase

## Riesgos y Mitigación
| Riesgo | Mitigación |
|--------|------------|
| `apiFetch` no maneja alguna opción | Verificar que `apiFetch` soporta todas las opciones usadas |
| Error de ruta en imports | Usar rutas relativas correctas (`./apiClient.js`) |
| Respuesta de API cambia | `apiFetch` ya valida `payload.code === response.status` |
| Cookie no se envía | Preservar `credentials: 'include'` en todas las llamadas |

## Notas
- No modificar archivos PHP de la API.
- No cambiar la estructura de los archivos HTML.
- Mantener los `console.log` existentes para debugging.
- Los ejemplos de clase son la referencia principal para el manejo de errores.
