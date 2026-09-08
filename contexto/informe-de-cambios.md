---
name: informe-de-cambios
description: Informe de todo lo construido y corregido el 2026-09-07, con los archivos tocados
metadata:
  type: project
---

# Informe de cambios — 7 de septiembre de 2026

Sesión de trabajo sobre `caine`. Nada de esto está commiteado al cierre del
informe: son 32 archivos modificados, 15 nuevos, 1 eliminado y 1 movido.

---

## 1. Arranque del entorno

**Problema:** `php` no se reconocía en la terminal, y `npm run dev` fallaba con
`TypeError: Invalid URL`.

- **PHP** estaba en `C:\Users\72363\php83` y ya figuraba en el PATH del registro,
  pero VS Code arrastraba un PATH viejo heredado de `explorer.exe`. No fue un
  cambio de código.
- **`vite.config.js`** leía `VITE_APP_URL`, variable que el `.env` no define, y
  `new URL(undefined)` tumbaba el dev server. Ahora la resuelve en cascada
  (`VITE_APP_URL` → `APP_URL` → `localhost`) dentro de un `try/catch`.

**Archivo:** `vite.config.js`

---

## 2. Perfil de usuario

**Problema:** pantalla en blanco al entrar a `/perfil`.

`capitalize()` no toleraba `null` y recibía `administrativo.tipo`, columna que
**no existe** en la tabla `administrativos`. `null.charAt(0)` abortaba el render
de toda la página.

- `capitalize()` ahora tolera `null`.
- La vista leía `.nombre`, pero el controlador manda `nombre_completo`.
- Se quitaron del payload `direccion` y `tipo`, que no existen en la tabla.
- La tarjeta izquierda quedó para el avatar según el rol; nombre, correo y
  badges pasaron a "Información básica".

**Archivos:** `resources/js/Pages/Perfil.vue`,
`app/Http/Controllers/ProfileController.php`

---

## 3. Avatares centralizados

Cada vista repetía su propia cadena de ternarios y leía el género de forma
distinta, por lo que **Madre/Padre nunca acertaban** el match (la BD guarda
`"Femenino"` con mayúscula y el código comparaba contra `"femenino"`).

- **Nuevo** `resources/js/Utils/avatares.js` con `avatarUsuario()` y
  `avatarPaciente()`. Normaliza el género venga como objeto o como texto.
- `Auxiliar.webp` entró en uso por primera vez.
- Se agregó `genero` al payload de encargado en dos controladores.

**Archivos:** `resources/js/Utils/avatares.js` *(nuevo)*, `Pages/Usuarios.vue`,
`Pages/Pacientes.vue`, `Pages/Expedientes.vue`,
`app/Http/Controllers/UserController.php`, `ProfileController.php`

---

## 4. Expedientes: avatar por género

Se movió la columna del avatar antes de "Código". El avatar no distinguía género
porque el controlador cargaba `'paciente'` pero no `'paciente.genero'`.

**Archivos:** `app/Http/Controllers/ExpedienteController.php`,
`resources/js/Pages/Expedientes.vue`

---

## 5. Módulo de Agenda (nuevo)

Calendario con FullCalendar en vistas Día/Semana/Mes, panel de resumen del día y
próximas citas, leyenda por estado.

**Cambio de esquema:** `citas.terapeuta_id` se reemplazó por la relación
polimórfica `atendido_por_type` / `atendido_por_id`, porque **los auxiliares
atienden sus propias citas** cuando están solos en sucursal. Se agregó
`google_event_id` para la integración futura. La migración recrea la tabla, ya
que SQLite no soporta `dropForeign`.

**Permisos nuevos**, porque `gestionar citas` no distinguía ver de crear:

| Permiso | Roles |
|---|---|
| `ver agenda` | los 5 roles |
| `agendar citas` | administrador, coordinador, auxiliar |
| `ver ocupacion personal` | administrador, coordinador |

A `terapeuta` se le **quitó** `gestionar citas`: solo consulta.

**Alcance por rol:** administrador, coordinador y auxiliar ven todas las citas;
la terapeuta solo las que atiende; el encargado solo las de sus pacientes. El
caso por omisión es *no ver nada*.

**El auxiliar solo agenda para sí mismo**, validado en tres capas: el catálogo
solo lo lista a él, el `<select>` viene fijo, y el servidor rechaza el intento de
agendarle a otro.

Además, validación de solapamiento de horarios que nadie tenía.

**Archivos:** `AgendaController.php` *(nuevo)*, `Pages/Agenda.vue` *(nuevo)*,
`Components/CitaModal.vue` *(nuevo)*,
`migrations/2026_09_07_190000_add_atendido_por_to_citas_table.php` *(nueva)*,
`Models/Cita.php`, `Terapeuta.php`, `Administrativo.php`, `Encargado.php`,
`routes/web.php`, `seeders/RolesAndPermissionsSeeder.php`, `EstadoCitaSeeder.php`

---

## 6. Ocupación de personal (nuevo)

Heatmap semanal persona × día con Chart.js + `chartjs-chart-matrix`. Clic en una
celda desglosa las citas de ese día; clic en el total, toda la semana. Debajo,
tabla comparativa de horas que revela quién carga citas más largas.

La escala de color es una **rampa secuencial de un solo hue** derivada del azul
de marca y validada (luminosidad monótona, extremo claro ≥ 2:1). La tabla es la
**vista alterna accesible**, porque un `<canvas>` no recibe foco de teclado.

**Archivos:** `Pages/Ocupacion.vue` *(nuevo)*, `AgendaController.php`,
`routes/web.php`

---

## 7. Indicadores: grafo de diagnósticos (nuevo)

Grafo bipartito con Cytoscape que amarra cada **diagnóstico** con las **áreas que
salieron deficientes en la anamnesis**. El grosor de la línea es en cuántos
expedientes coinciden. Clic en un nodo aísla sus conexiones y el panel lista los
criterios concretos que fallaron y a qué paciente.

Filtro de nivel: *solo Observación* (respuesta 1) o *incluir En desarrollo*
(1 y 2).

Se usa **área** como nodo, no criterio: 86 nodos serían ilegibles. **Layout de
dos columnas** con posiciones calculadas y orden por baricentro — con un layout
de fuerzas (`cose`) los nodos se encimaban y las etiquetas quedaban ilegibles.

Permiso nuevo `ver indicadores` (administrador y coordinador). Se quitó
`indicadores` del menú del rol `pruebas`, que no tiene el permiso y habría
recibido 403.

**Archivos:** `IndicadoresController.php` *(nuevo)*, `Pages/Indicadores.vue`
*(nuevo)*, `routes/web.php`, `seeders/RolesAndPermissionsSeeder.php`,
`Layouts/AuthenticatedLayout.vue`

---

## 8. Modal de anamnesis (nuevo)

Reemplaza el placeholder `[Abrir modal de anamnesis aquí]` en la pestaña Historia
Clínica. Muestra los 86 criterios agrupados por módulo y área con su nivel, un
conteo por nivel, y un filtro para ver solo lo que requiere atención.

**Botón Imprimir** con `@media print`: quita el fondo, los botones y el filtro;
agrega ficha de cabecera (paciente, expediente, diagnósticos, motivo, fecha) y
líneas de firma del terapeuta y del encargado; evita que módulos y áreas se
partan entre páginas. Imprimir siempre saca el documento completo, aunque el
filtro esté activo.

El controlador cargaba `anamnesis` pero no `anamnesis.items.criterio`, así que no
había nada que mostrar.

**Archivos:** `Components/AnamnesisModal.vue` *(nuevo)*,
`Components/tabs/HistoriaClinica.vue`, `app/Http/Controllers/ExpedienteController.php`

---

## 9. Bugs corregidos

| Bug | Archivo | Efecto |
|---|---|---|
| `capitalize(null)` reventaba el render | `Pages/Perfil.vue` | pantalla en blanco en `/perfil` |
| `Cita::terapeuta()` eliminada sin buscar sus usos | `PacientesController.php` (2 puntos), `HistorialModal.vue` | `RelationNotFoundException` rompió **Pacientes** e Historial |
| `Encargado::citas()` apuntaba a `citas.encargado_id`, columna inexistente | `Models/Encargado.php` | relación rota desde siempre |
| `Anamnesis` sin `$table` — Eloquent pluralizaba a `anamneses` | `Models/Anamnesis.php` | `Anamnesis::create()` **nunca funcionó** |
| `generarCodigoExpediente()` cortaba en el carácter 7 y `KID-2026` son 8 | `Models/Expediente.php` | el código crecía un `6` por expediente: `KID-2026001` → `KID-20266002` → `KID-202666003` |
| Scope de solapamiento comparaba `'10:00:00'` contra `'10:00'` | `Models/Cita.php` | citas adyacentes se marcaban como choque |
| Bins de la escala del heatmap sin acotar al máximo | `Pages/Ocupacion.vue` | cortes `1-2 \| 3-4 \| 5-6 \| 7-8 \| 9-6`: bins vacíos y rango invertido |
| `<template #header>` sin `<slot name="header">` en el layout | `Pages/Perfil.vue`, `Pages/Agenda.vue` | el título y el botón "Nueva cita" **no se renderizaban**, sin ningún error |
| `CitaSeeder` buscaba un `TipoCita` llamado `'Evaluación'` (el catálogo dice `'Evaluación inicial'`) | `seeders/CitaSeeder.php` | nunca creaba ninguna cita |
| `PacientesSeeder` buscaba escolaridades `'Primaria'`/`'Secundaria'`, que no existen | `seeders/PacientesPruebaSeeder.php` | Pedro y Lucía quedaron sin escolaridad |
| Dos seeders creaban citas para la misma persona y semana | `CitaSeeder.php`, `CitasPruebaSeeder.php` | horarios encimados, violando la propia validación del controlador |
| `cita.modalidad` renderizado como objeto | `HistorialModal.vue` | mostraba `[object Object]` |
| Historial sin `orderBy` ni las relaciones que el modal usaba | `PacientesController.php` | citas desordenadas y "Evolución" siempre en *Pendiente* |
| `phpunit.xml` sin base de datos de prueba | `phpunit.xml` | correr tests borraba `database/database.sqlite` real |

---

## 10. Datos de prueba

- **7 pacientes nuevos** con encargado, expediente, escolaridad, género y
  terapeutas asignados. Dos encargados con dos hijos cada uno.
- **3 terapeutas** y **5 encargados** con usuario y rol.
- **Citas** en tres semanas con densidad y duración variadas, sin solapes.
- **Anamnesis** para los 9 expedientes con perfiles clínicos por diagnóstico
  (TDAH → Atención/Impulsividad/Funciones ejecutivas; TEA → Desarrollo
  social/lenguaje), para que el grafo muestre patrones y no ruido.
- Se completó lo que faltaba: `ana@example.com` sin rol, `jose@example.com` como
  auxiliar con su cargo, y los cargos de los otros administrativos.

**Archivos:** `seeders/PacientesPruebaSeeder.php` *(nuevo)*,
`CitasPruebaSeeder.php` *(nuevo)*, `AnamnesisPruebaSeeder.php` *(nuevo)*,
`DatabaseSeeder.php`

---

## 11. Orden interno

- `EscClose.js` movido de `Components/` a `Utils/` — es un composable, no un
  componente, y era el único `.js` entre puros `.vue`. Se actualizaron sus 8
  imports.
- `CitaController.php` **eliminado**: cero referencias en el proyecto y su
  `redirect()->route('citas')` apuntaba a una ruta inexistente.
- Carpeta `contexto/` creada con las notas de contexto y reglas del proyecto.

---

## 12. Pruebas

**39 tests, 137 aserciones, todas pasando.**

- `tests/Feature/AgendaTest.php` *(nuevo)* — 29 tests: acceso por rol, quién
  puede crear, el auxiliar limitado a sí mismo, alcance de lo que cada rol ve,
  solapamiento (incluidos los casos límite de citas adyacentes), y validación.
- `tests/Feature/IndicadoresTest.php` *(nuevo)* — 10 tests: acceso, armado del
  grafo, que un criterio *Adecuado* no genere vínculo, el filtro amplio, pesos,
  comorbilidades e integridad del grafo.

**Pendiente:** los 20 tests que trajo Breeze siguen fallando porque
`database/factories/UserFactory.php` genera un campo `name` que la tabla `users`
de este proyecto no tiene. Es scaffolding desalineado del esquema, no un bug de
la aplicación.

---

## Dependencias agregadas

```
@fullcalendar/core, /vue3, /daygrid, /timegrid, /interaction   ^6.1.21
chart.js                                                      ^4.5.1
chartjs-chart-matrix                                          ^3.0.8
cytoscape                                                     ^3.34.3
```

---

## Qué quedó pendiente

1. **Google Calendar** — postergado: no hay servidor de correo en este entorno.
   `citas.google_event_id` ya existe en la tabla.
2. **Chart.js en `/indicadores`** — instalado y listo para sumar tendencias y
   distribuciones junto al grafo.
3. **Los 20 tests de Breeze** — adaptarlos al esquema o borrarlos.
4. **`pacientes` no tiene `fecha_nacimiento`** — solo la tiene `expedientes`. Es
   cambio de esquema, requiere decisión.
5. **Dos mecanismos para la relación encargado-paciente**: `pacientes.encargado_id`
   (con datos) y el pivote `encargado_paciente`. Conviene unificarlos.

---

Ver [Agenda: decisiones de arquitectura](agenda-decisiones-arquitectura.md),
[Anamnesis: escala y grafo](anamnesis-escala-y-grafo.md),
[Convenciones de Vue del proyecto](convenciones-vue-proyecto.md) y
[Paleta dataviz caine](paleta-dataviz-caine.md) para el detalle de cada decisión.
