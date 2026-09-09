---
name: informe-09-09
description: Informe de lo construido y corregido el 2026-09-09, con los archivos tocados
metadata:
  type: project
---

# Informe de cambios — 9 de septiembre de 2026

Sesión sobre `caine`. Cerró en dos commits (`edf00d0` y `bec4e7d`): **58 archivos
tocados, 21 nuevos, 2.112 líneas agregadas y 182 quitadas.**

Tres módulos nuevos (Hijos, Evaluaciones e Informes), el organigrama de
subalternos, y una tanda de correcciones que iban desde un desplegable que
borraba datos hasta el layout que se salía de pantalla en el teléfono.

---

## 1. Organigrama de subalternos

La jerarquía dejó de derivarse del cargo y pasó a una columna `superior_id` en
`administrativos` y `terapeutas`. Es más expresivo: con el mapa de cargos el
auxiliar tenía que colgar del coordinador, y en la práctica reporta al
administrador.

Se corrigió el travesaño horizontal, que se extendía hasta los bordes exteriores
de las tarjetas en vez de terminar en sus centros. Va en `:style` y no en clases
de Tailwind por algo que conviene recordar: **Tailwind lee el `/` de `calc()`
como el separador de opacidad**, así que `left-[calc(50%/var(--n))]` compila sin
error y la clase nunca se genera.

Las filas de hijos se agrupan de dos en dos a propósito, para que el esquema no
crezca a lo ancho.

**Archivos:** `SubalternosController.php`, `Components/personas/NodoOrganigrama.vue`,
`Pages/Personas/Subalternos.vue`, migraciones de `administrativos` y `terapeutas`,
`TerapeutasSeeder`, `PacientesPruebaSeeder`

---

## 2. El layout no era responsivo

Dos causas encadenadas, y afectaban a **los 15 módulos**, no solo al organigrama.

**Faltaba `min-w-0`** en el contenedor del contenido. Por spec de CSS un ítem
flex arranca con `min-width: auto` y se niega a encogerse bajo el ancho de su
contenido, así que **ningún `overflow-x-auto` de adentro llegaba a activarse**:
en vez de scrollear, el contenido empujaba la página y el `<aside>` fijo le
quedaba encima.

**No había un solo breakpoint.** `grep -c "md:\|sm:\|lg:"` daba **0**. El menú
medía 256px siempre, dejando 134px útiles en un teléfono. Ahora debajo de `md`
sale de pantalla y entra como cajón, con botón de hamburguesa y fondo que lo
cierra al tocar fuera. De `md` para arriba se comporta igual que antes.

**Archivo:** `Layouts/AuthenticatedLayout.vue`

---

## 3. El vínculo usuario ↔ persona estaba roto en tres lugares

El más grave: **`TerapeutaModalEditar` y `EncargadoModalEditar` leían
`props.administrativo`**, prop que no existe en esos componentes (sus props se
llaman `terapeuta` y `encargado`). Copia-pega. `user_id` nacía como `''`, Laravel
lo convierte a `null` y **guardar desasociaba al usuario** sin tocar nada.

Los otros dos: `PersonaModalNuevo` declaraba `usuarios: ''` mientras el `<select>`
hacía `v-model="form.user_id"` — y `useForm` de Inertia **solo manda las llaves
del objeto inicial**, así que el usuario elegido se descartaba en silencio. Y los
tres métodos de lista de `PersonasController` no mandaban `usuariosDisponibles`,
por lo que el desplegable no tenía ni una opción.

Se agregó un computed `opcionesUsuario` que antepone el usuario ya asignado a la
lista de libres — esa lista trae solo los libres, y el propio nunca lo está.

De paso salió un **`dd($usuariosDisponibles->toArray())` vivo** en
`editTerapeuta`: esa ruta volcaba JSON y nunca renderizaba.

**Archivos:** `PersonasController.php`, `PersonaModalNuevo.vue`,
`PersonaFormShared.vue`, `AdministrativoModalEditar.vue`, `TerapeutaModalEditar.vue`,
`EncargadoModalEditar.vue`

---

## 4. Avatares en Personas

Las tres tablas leían `avatar_url`, campo que no existe en ninguno de los tres
modelos, así que el `||` caía siempre al genérico y **todos salían iguales**.
Ahora usan `avatarUsuario()` de `Utils/avatares.js`: administrativos por cargo,
terapeutas por rol, encargados por género.

**Archivos:** `Pages/Personas/Administrativos.vue`, `Terapeutas.vue`, `Encargados.vue`

---

## 5. Módulo Hijos (nuevo)

El portal del encargado. El menú ya enlazaba `/hijos` pero la ruta no existía:
daba 404.

Filtra por `encargado_id` del usuario logueado, con `abort_unless` si no tiene
fila en `encargados` — sin eso la consulta traía los pacientes de todo el
consultorio.

Dos decisiones sobre qué ve un padre:

- **Del expediente, tres pestañas**: Datos generales, Historia Clínica y Atención
  terapéutica. Evaluaciones queda fuera.
- **Del historial, solo las observaciones generales.** No se ocultan en el
  template: **no se cargan**. El controlador hace
  `'citas.sesion' => fn($q) => $q->select('id','cita_id','observaciones_generales')`,
  así las notas clínicas ni salen de la base. Ocultar una columna deja el dato
  viajando al navegador, donde cualquiera lo ve con F12.

**Archivos:** `HijosController.php` *(nuevo)*, `Pages/Hijos.vue` *(nuevo)*,
`ExpedienteModal.vue` (prop `tabs`), `HistorialModal.vue` (prop `mostrarClinicas`),
`routes/web.php`

---

## 6. Filtros en la Agenda

Casillas por estado y un selector por profesional, **del lado del cliente**: las
citas del rango ya vienen cargadas, así que filtra al instante sin pegarle al
servidor.

La leyenda se convirtió en las casillas — cada una lleva su cuadrito de color, así
que la fila sigue explicando qué significa cada color y de paso filtra. El
selector de profesional se arma con quienes tienen citas en el rango, y **no
aparece si solo hay una persona**: al terapeuta no le sirve.

Los filtros aplican a todo: calendario, Resumen del día y Próximas citas.

**Archivo:** `Pages/Agenda.vue`

---

## 7. Módulo Evaluaciones (nuevo, primera etapa)

El récord de evaluaciones aplicadas, **sin tablas nuevas**: corre sobre el pivote
`expediente_evaluacion`, que ya existía vacío y registra el par expediente ↔
evaluación con su `created_at`.

Se agregó **"Evolución anual"** al catálogo, que quedó en 11.

El botón "Aplicar evaluación" funciona y escribe en el pivote, con
`syncWithoutDetaching` porque hay un unique en el par.

**El botón "Ver" está deshabilitado a propósito.** Ahí va el visor estilo
`AnamnesisModal` —respuestas por sección, interpretación, resultados, imprimir— y
eso sí necesita las tres tablas que quedaron pendientes.

**Archivos:** `EvaluacionesController.php` *(nuevo)*, `Pages/Evaluaciones.vue` *(nuevo)*,
`Components/AplicarEvaluacionModal.vue` *(nuevo)*, `EvaluacionesSeeder.php`

---

## 8. Fechas en dd/mm/yyyy

Nuevo `Utils/fechas.js` con `fecha()`, `fechaCorta()`, `fechaLarga()` y
`fechaHora()`, aplicado en **12 lugares**.

Parte el texto a mano en vez de usar `new Date('2026-09-09')`: ese constructor
interpreta la fecha suelta como UTC y en Guatemala (UTC−6) **la muestra un día
antes**.

**No se tocaron los `<input type="date">`** — son 9 archivos y deben seguir en
ISO, que es el formato que exige el HTML para `value`.

El backend sigue en ISO, que es lo correcto para ordenar y comparar. El formato
vive solo en la capa de presentación.

---

## 9. Módulo Informes (nuevo)

Informes configurables con descarga en CSV **separado por pipe**, con BOM UTF-8
para que Excel no rompa las tildes.

La lista original de 14 informes venía sobre otro esquema: `profesionales`,
`expedientes_clinicos`, `historial_estados_cita`, `aplicaciones_instrumento`,
`registros_auditoria` y varios más no existen. **8 salieron sin tocar la base**;
los otros 6 necesitan tablas nuevas.

La arquitectura es una **lista blanca**: cada informe declara su consulta con los
joins, sus columnas y sus filtros. Nada de lo que llega del navegador toca la
consulta — se cruza contra lo declarado. Probado con un `; DROP TABLE citas` como
nombre de columna: se descarta.

Los 8: Pacientes y diagnósticos, Pacientes y profesionales, Pacientes y servicios,
Citas y pacientes, Expediente clínico, Sesiones y observaciones, Encargados y
pacientes, Profesionales y agenda.

**Archivos:** `InformesController.php` *(nuevo)*, `Pages/Informes.vue` *(nuevo)*,
`app/Reporteria/` *(nuevo)*

### 9.1 Permisos, que era un hueco real

`ver reportes` lo tenían administrador, **encargado** y pruebas. Un papá podría
haber sacado el listado de todos los pacientes del consultorio con sus
diagnósticos. Ahora: administrador, coordinador y pruebas. Al encargado se le
quitó, y "Informes" salió del menú del terapeuta y del encargado, que lo veían y
les habría dado 403.

**Archivos:** `RolesAndPermissionsSeeder.php`, `AuthenticatedLayout.vue`

---

## 10. Reestructura de la reportería

`Definiciones.php` llegó a **436 líneas** con los 8 informes apelmazados, y cada
informe nuevo sumaba ~40. Se partió en una clase por informe:

```
app/Reporteria/
├── Informe.php       126 líneas — contrato + ayudas compartidas
├── Registro.php       60 líneas — el conglomerado
└── Informes/          8 archivos de 66 a 76 líneas
```

`Informe` es abstracta: si un informe nuevo olvida `columnas()`, **PHP no deja
cargar la clase**. Con el arreglo eso se descubría cuando la pantalla reventaba.

El controlador solo conoce `Registro`. Agregar el noveno informe es crear su
clase y sumar una línea a `Registro::INFORMES`.

El total del proyecto subió de 436 a 757 líneas —la ceremonia de las clases
cuesta— pero ningún archivo pasa de 126.

---

## 11. Comentarios: nueva regla

Se documentó en [Comentarios breves y directos](comentarios-breves-y-directos.md)
y se aplicó a los 13 archivos con comentarios de esta sesión. Los docblocks de
5–8 líneas bajaron a 2.

Los **encabezados de sección** (`/* ---------- Sección ---------- */`) sí se
mantienen: en la primera redacción de la regla los había clasificado como
decorativos y Diana lo corrigió. Lo que no va es el párrafo colgado debajo.

---

## 12. Otras correcciones

- **`terapias-dia` salió del proyecto.** Existía solo en el layout: no tenía ruta
  ni página, así que daba 404 a terapeuta, coordinador y auxiliar.
- **Tailwind no escaneaba archivos `.js`.** Su `content` solo tenía `**/*.vue`, y
  al mover clases a `Utils/anamnesis.js` **dejaban de generarse sin ningún error
  de build**. Se agregó `'./resources/js/**/*.js'`.
- **`users` no tiene columna `name`.** El desplegable de usuarios mostraba
  ` (correo@ejemplo.com)` con un espacio suelto y paréntesis alrededor de nada.
- **Columna fantasma en el Historial**: un `<th>Observaciones</th>` que leía
  `cita.observaciones`, columna inexistente.
- **Vistas de pacientes por terapeuta y por encargado** (`TerapeutaPacientes.vue`,
  `EncargadoPacientes.vue`, `EncargadoController.php`), hechas por Diana.

---

## Dependencias

Se instaló y se **desinstaló** `maatwebsite/excel`. Traía 9 paquetes, entre ellos
`phpoffice/phpspreadsheet` 1.30.6, y quedó sin usarse: la descarga usa `fputcsv`
nativo. Al desinstalarlo las 3 vulnerabilidades del `composer audit` siguieron
apareciendo, así que **ya estaban antes** y no vienen de ahí. Quedó pendiente
identificarlas con `composer audit --no-dev`, que dirá si son de producción o
solo de herramientas de desarrollo.

---

## Qué quedó pendiente

**Rompe hoy:**

- `estado_sesiones` está vacío — falta correr `EstadoSesionSeeder`. Toda sesión se
  guarda con `estado_sesion_id = null`.
- `ExpedienteController:128` redirige a `route('expedientes')`, nombre que no
  existe: **editar un expediente truena**.
- Tres rutas apuntan a métodos inexistentes: `ExpedienteController::destroy`,
  `PacientesController::observaciones` y `::seguimiento`.
- Los 6 botones de eliminar de `Parametros/` dan 404.
- `SesionSeeder:26` busca un estado `'Activa'` que no está en el catálogo, y
  `Evaluacion::resultados()` apunta a `ResultadoEvaluacion`, clase que no existe.

**Del expediente:** `tabs/AtencionTerapeutica.vue` recorre `expediente.terapias`
—la relación se llama `servicios`— y `objetivos_terapeuticos` y
`planificacion_terapeutica` no son columnas.

**Módulos a medias:**

- Evaluaciones: el visor y sus 3 tablas (`evaluacion_items`,
  `evaluaciones_aplicadas`, `evaluacion_respuestas`).
- Informes: los 6 que faltan, todos dependientes de tablas nuevas — historial de
  estados de cita, resultados de evaluación, baremos, seguimiento y auditoría.
- Organigrama: pacientes colgando de los terapeutas.

**Deuda anotada:** hay **dos vínculos encargado ↔ paciente** —la columna
`pacientes.encargado_id` y el pivote `encargado_paciente`— con 9 filas cada uno.
Hoy coinciden, pero son dos fuentes de verdad.

**Modularización pendiente:** borrar código muerto (~470 líneas entre
`Components/usuarios/UsuarioForm.vue` y `Components/expedientes/`), unificar las
6 páginas idénticas de `Parametros/`, sacar `OcupacionController` de
`AgendaController`, migrar 7 modales a `ModalBaseVer`, partir `PersonasController`
en tres, y extraer la configuración de Chart.js y Cytoscape de `Ocupacion.vue` e
`Indicadores.vue`.
