---
name: informe-14-09
description: Informe de lo construido y corregido el 2026-09-14, con los archivos tocados y las decisiones que Diana tomó
metadata:
  type: project
---

# Informe de cambios — 14 de septiembre de 2026

Sesión sobre `caine`. Al cierre, contra `89bbc0e`: **77 archivos tocados (16
nuevos), 3 468 líneas agregadas y 281 quitadas**, sin contar `public/build`.
Quedó en los commits `897762a` y `d321fbc`.

Fue un día de tanda larga: ocho rondas de pedidos de Diana, sin un tema único.
Lo que más peso tuvo fueron **Pagos** —que pasó de cobrar cita por cita a cobrar
el paquete completo— y **Objetivos**, que nació, se construyó mal y se rehizo el
mismo día. En el camino salieron a la luz seis bugs de antes: formularios que no
guardaban, relaciones mal nombradas, botones que borraban sin preguntar.

---

## 1. Diseño y experiencia

### 1.1 Poppins

`resources/views/app.blade.php` y `tailwind.config.js`. Antes Figtree. Se cargan
los pesos 300 a 700 desde Bunny Fonts. Los iconos vienen del paquete npm
`material-icons`, no del link, así que el cambio no los tocó.

### 1.2 Renombres de la sesión clínica

- «Observaciones psicológicas» → **Evolución**
- «Observaciones generales» → **Observaciones públicas**

Hubo un choque: **`SesionModal` ya tenía un campo etiquetado «Evolución»**, atado
a la columna `sesiones.evolucion`. Renombrar el otro habría dejado dos campos con
el mismo nombre. Diana decidió: *"el campo que ya existe como 'evolución' hay que
quitarlo"*.

Así que lo que en pantalla se llama **Evolución es la columna
`observaciones_clinicas`**, y `evolucion` dejó de mostrarse y de escribirse. **La
columna sigue en la base con sus datos** —no se tocó el esquema—, pero ya no hay
forma de verlos desde la aplicación. Si alguna vez hace falta, hay que migrarlos
a `observaciones_clinicas`.

El renombre se arrastró a `HistorialModal`, al informe de sesiones,
`SesionController` (dejó de validar `evolucion`) y `AgendaController` (dejó de
mandarlo).

### 1.3 La agenda cambió de código de color

Era por **estado de la cita**. Pasó a ser por **tipo de terapia**, que es lo que
se ve primero en la tarjeta.

Primer intento: se mantuvo el estado como franja izquierda de 3 px. Diana lo
descartó — *"para el estado de la cita es mejor que solo aparezca el filtro ya
que ya tenemos colores asignados para el tipo de terapia"*. Dos códigos de color
sobre el mismo evento no se leen. Se quitaron `COLOR_ESTADO`, `colorDe`,
`marcarEstado` y los cuadritos junto a las casillas; **el filtro por estado sigue
funcionando igual**, solo que sin color.

Los colores de terapia se reparten por id de servicio, **salvo los que Diana pida
por nombre**: `COLOR_POR_NOMBRE` en `Pages/Agenda.vue`. Hoy tiene una entrada,
`Evaluación Cognitiva → rosado`. Se fija por nombre y no por id porque el id
cambia entre ambientes.

La paleta vieja de estados tenía criterio detrás —el verde de *Atendida* más
oscuro que el de *Confirmada*, el gris de *Vencida* para no leerse como error
igual que *Cancelada*— y quedó en el historial de git por si se quiere recuperar.

### 1.4 La tarjeta del calendario muestra al niño

`comoEvento()` mandaba el servicio como `title`. Ahora manda
`paciente->nombre_completo`, y el servicio viaja aparte en `extendedProps`.

**Tres lugares leían la terapia desde `title`** y se adaptaron en el mismo
cambio: el encabezado de `SesionModal`, `SolicitarReprogramacionModal` y el panel
«Próximas citas» de la agenda, que si no mostraba el nombre del niño dos veces.

---

## 2. Filtros y consultas

### 2.1 Anamnesis: módulo y área

Dos selectores en `AnamnesisModal`, junto al filtro de deficientes, con contador
y «Limpiar filtros». Las opciones salen de los criterios que trae el expediente,
no de un catálogo: así no se ofrece nada que devuelva vacío. **Las áreas se
recortan al módulo elegido** y un área que deja de pertenecer se limpia sola.

Al imprimir se quitan los tres filtros y se restauran después — es la regla que
ya estaba para el filtro de deficientes: *el documento físico debe quedar
completo*.

### 2.2 Informes: paciente y fechas en los ocho

Se agregaron dos ayudas a `Reporteria/Informe.php`: `filtroPaciente()` y
`rangoFechasDe()` —para cuando la fecha vive en una relación—. Con eso **los ocho
informes tienen filtro por paciente y rango de fechas**. Cuatro no tenían fechas:

| Informe | Rango que se agregó |
|---|---|
| Pacientes y servicios | Apertura del expediente |
| Pacientes y profesionales | Registro del paciente |
| Encargados y pacientes | Registro del encargado |
| Evoluciones | Fecha de la cita, vía `whereHas` |

**Detalle en Encargados y pacientes:** el filtro va por `pacientes.encargado_id`,
que es de donde `expandir()` saca los hijos, y **no** por la relación
`pacientes()`, que pasa por el pivote `encargado_paciente`. Son dos fuentes
distintas —hoy coinciden, 9 y 9— y usar la del pivote habría dado filas que no
cuadran con las que el informe arma.

### 2.3 Pagos: tres filtros nuevos y tres sumas

El par de fechas que ya existía se rotuló **Sesión desde/hasta** —era eso lo que
filtraba— y al lado entraron **Pago desde/hasta** y **Tipo de pago**. Los tres
van al servidor, así que los totales reflejan lo filtrado.

**Cómo interactúan:** el rango de sesión es la base de la consulta y los filtros
de pago recortan sobre él. Buscar «pagos de octubre» con el rango de sesión en la
quincena actual solo trae los pagos de octubre *de esas citas*. Usar cualquier
filtro de pago deja fuera las citas pendientes, porque no tienen con qué
compararse; hay un aviso en pantalla para que el saldo no se lea como el del
período. **Queda pendiente de decisión** si la fecha de pago debe poder buscar
fuera del rango de sesión: eso exige hacer opcional ese rango.

Encima de la tabla hay tres desgloses: **por tipo de pago, por quien registra y
por quien autoriza**, con nombre, cantidad de cobros y monto. Se calculan sobre
lo que la tabla está mostrando, no sobre todo lo cargado, así que cambian con el
buscador y con los botones de estado. Es lo que le deja a Diana ver cuánto entró
por sucursal y cuánto por oficina central.

---

## 3. Pagos

### 3.1 Columnas nuevas

Fecha de pago, **Registró** y **Autorizó**. La primera ya viajaba a la vista y
solo faltaba mostrarla; la segunda ya se llenaba sola al guardar.

La tercera no existía. Diana explicó para qué es: *"este dato servirá para saber
quién autoriza el pago cuando sea el auxiliar el que lo registre... cuando el
auxiliar es quien lo registra entonces ahí sí requiere autorización"*.

`2026_09_14_120000_add_autorizado_por_a_pagos_table.php` agrega `autorizado_por`
(FK a `users`) y `autorizado_en`.

**Tres reglas que hay que respetar:**

- **Qué necesita autorización se deduce del rol de quien registró**, no de una
  bandera guardada con el pago. Si a alguien le cambian el rol, sus cobros viejos
  cambian con él. `Pago::ROLES_SIN_AUTORIZACION` son administrador y coordinador.
- **Corregir un pago tira abajo la autorización anterior.** Se autorizó un monto,
  no la fila: sin esto un auxiliar podía hacer autorizar Q50 y después cambiarlo
  a Q500.
- **El auxiliar no se autoriza solo.** La ruta está detrás de
  `role:administrador|coordinador` y el backend rechaza con 422 si el pago no lo
  registró un auxiliar.

También se renombró la columna **«Autorización» → «N.° de documento»** —Diana
aclaró que ese campo es el número del voucher, no una persona— en la tabla y en
el modal. La columna de la base sigue llamándose `numero_autorizacion`.

### 3.2 El nombre de un usuario

`users` solo guarda el correo; el nombre vive en `terapeuta`, `encargado` o
`administrativo`. Se agregó el accessor **`User::nombre_completo`** que resuelve
las tres con el correo como último recurso, y se usó también en
`HandleInertiaRequests`, que tenía la misma cadena de ternarios escrita a mano.
Único cambio de comportamiento: un usuario sin ninguna de las tres relaciones
antes mostraba vacío, ahora muestra el correo.

### 3.3 La vista, responsive de verdad

Diana: *"no puedo ver todas las columnas"*. El problema no era que faltara
`overflow-x-auto` —ya estaba—: eran **catorce columnas**, y poder desplazarlas de
lado no es lo mismo que poder verlas.

Abajo de `lg` la tabla se cambia por **una tarjeta por cita**, con los doce datos
a la vista en dos columnas. De `lg` para arriba la tabla queda como estaba.
También se repartieron los campos de filtro para que se envuelvan en vez de
cortarse, y el padding de la página baja a `p-4` en pantalla chica.

### 3.4 Cobrar el paquete

Lo último del día y lo más pedido: *"los clientes pagan el paquete y el programa
internamente tiene que dividir el precio del paquete por el total de citas"*.

`app/Pagos/PagoDePaquete.php`. Arriba de la tabla hay un panel con una línea por
programa: costo, cobrado, saldo, y el botón **Cobrar paquete**.

**El cobro se convierte en un pago por cita.** Así la tabla, los totales, los
desgloses y el Cierre de mes siguen cuadrando sin tener que entender qué es un
paquete. **No hizo falta columna nueva**: las citas ya apuntan a su paquete por
`citas.asignacion_programa_id`, y los pagos del paquete son los de esas citas.

Tres decisiones:

- **Si se paga el saldo completo, cada cita recibe exactamente su
  `precio_aplicado`**, no una parte pareja. El reparto del paquete no siempre da
  parejo —Q800 entre 20 sí, Q801 no— y una cita un centavo corta quedaría marcada
  «pago parcial» con todo pagado.
- **Las canceladas no se cobran**, igual que en el total «esperado». Por eso el
  paquete de Pedro es de Q800 pero el saldo arranca en Q760: una de sus 20 citas
  está cancelada.
- **Autorizar y anular van por paquete.** Veinte citas cobradas por un auxiliar
  pedirían veinte autorizaciones para respaldar un solo cobro de mostrador.

Un bug que se corrigió sobre la marcha: el saldo se calculaba contando citas sin
pago, así que **después de un abono el paquete se veía saldado habiendo entrado
la mitad**. Ahora es `esperado − cobrado`, y un segundo abono completa lo que la
cita ya tenía en vez de pisarlo.

---

## 4. Informes

### 4.1 «Sesiones y observaciones» → «Evoluciones»

Primero solo se renombró la etiqueta y se dejó la clave `sesiones-observaciones`,
que viaja en la URL. Diana pidió después renombrarla de verdad *"y corregir todos
los enlaces de manera que nada quede roto"*: la clase se movió con `git mv` a
`Evoluciones.php` y la clave pasó a `evoluciones`. Eran cuatro referencias, todas
en la propia clase y en el registro.

### 4.2 «Cierre de mes»

`app/Reporteria/Informes/CierreDeMes.php`. Va sobre `pagos` y **no** sobre citas
como la vista de Pagos: ahí se listan las citas para saber cuáles faltan cobrar,
y acá interesa solo el dinero que entró.

Catorce columnas y siete filtros, entre ellos **Registró** y **Autorizó** —que es
lo que separa sucursal de oficina central— y «Solo cobros sin autorizar». La
columna de la fecha se llama **Fecha autorización**, a pedido de Diana.

### 4.3 Excel, PDF y CSV

Se instalaron `phpoffice/phpspreadsheet` y `dompdf/dompdf`. El armado quedó en
`app/Reporteria/Exportador.php`, aparte del controlador porque son tres formatos
con poco en común: el CSV se va escribiendo, el Excel y el PDF se arman completos.

- **Excel**: encabezado en azul caine, fila congelada, autofiltro, ancho
  automático. **Todas las celdas se escriben como texto**: si no, Excel convierte
  un `0045873` en el número 45873 y un `KID-2026001` en cualquier cosa.
- **PDF**: horizontal, que es lo único en que entran catorce columnas. La hoja de
  estilos va embebida porque dompdf no sale a la red.
- **CSV**: como estaba, con BOM.

Probados los 27 archivos (9 informes × 3 formatos): los xlsx abren como zip
válido, los PDF empiezan con `%PDF` y las tildes están bien en los tres.

---

## 5. Objetivos terapéuticos

El módulo que nació, se construyó mal y se rehizo el mismo día. Vale la pena
dejar por qué.

### 5.1 Lo que se encontró

`expedientes` **no tiene** `objetivos_terapeuticos` ni
`planificacion_terapeutica`, pero el asistente de expediente tenía dos textareas
atadas a esos nombres: **el terapeuta escribía, guardaba, y se perdía en
silencio**. El controlador tampoco las validaba.

### 5.2 El primer intento, descartado

Se armó la tabla con `area` como texto, tomada de `criterios.area` —las 18 áreas
de la anamnesis—, con la lógica de que lo que la anamnesis marca en Observación
es justo donde se ponen objetivos.

Diana corrigió: *"en modulo objetivos se ingresa por áreas (servicios), de 3 a 4
objetivos por área asociado a cada niño. Se debe de ver como la tabla que tiene
el modulo de evaluaciones"*.

### 5.3 Cómo quedó

**El área es el servicio.** `objetivos_terapeuticos`: `paciente_id`,
`servicio_id`, `descripcion`, `terapeuta_id`. La migración
`2026_09_14_140000_objetivos_por_servicio.php` **rehace la tabla** en vez de
alterarla —SQLite no deja agregar una columna `NOT NULL` a una tabla existente— y
**se detiene con un error si encuentra filas** en lugar de borrarlas.

Módulo con página propia, `/objetivos`, igual que Evaluaciones. **El menú ya tenía
ese enlace apuntando a la nada.** Tabla con Paciente · Expediente · Terapia ·
Objetivos numerados · Terapeuta, buscador y filtro por terapia.

- **El mínimo de 3 avisa, el máximo de 4 bloquea.** No se puede exigir tres al
  guardar el primero.
- **Guardar reemplaza el grupo completo** de ese niño en esa terapia, para que lo
  guardado sea exactamente lo que se ve en pantalla.
- El paso muerto del asistente ahora explica dónde se llenan, en vez de fingir
  que guarda.

---

## 6. El expediente del encargado

Diana: *"a perfil de encargados en expedientes solo mostrar: terapeuta, terapias
y objetivos"*.

`TABS_ENCARGADO` pasó de tres pestañas a una sola, **«Terapias y objetivos»**
(`tabs/TerapiasObjetivos.vue`). Y se recortó `HijosController`: diagnósticos,
anamnesis, estado y modalidad ya no se cargan, **así que ni siquiera viajan a su
navegador**.

Dos cosas que hay que saber al leer esa pestaña:

- **El terapeuta cuelga del paciente** (`paciente_terapeuta`), no del expediente.
- **Las terapias son `expediente.servicios`.** La pestaña del equipo
  (`tabs/AtencionTerapeutica.vue`) leía `expediente.terapias`, relación que no
  existe, así que esa fila salía siempre vacía. Corregido.

---

## 7. Programas: generar el paquete del mes

Diana fue explícita sobre no automatizarlo: *"el usuario dijo 'renovar el
programa cada mes (se agregan citas en automático)' pero por funcionalidad quiero
que él las genere en botón 'generar paquete' y no un evento automático"*.

`app/Programas/RenovacionPrograma.php`. Botón en cada fila activa de
`/programas`, que abre un formulario ya lleno con las condiciones del paquete que
corre: **arranca el día siguiente a la última cita**, con la misma cantidad,
precio, días y horario, todo editable.

- **Renovar crea un paquete nuevo, no agrega citas al actual.** Cada mes es su
  propia `asignacion_programa` con su bloque de citas y su precio. Si se le
  agregaran citas al mismo, `citas_creadas / cantidad_citas` dejaría de querer
  decir algo y el cobro del mes se mezclaría con el anterior.
- **El paquete anterior queda `finalizado`, y eso es lo que impide generarlo dos
  veces.** Ahí «finalizado» significa *ya se renovó*, no *ya se dio*: sus citas
  pendientes siguen en el calendario. Está escrito en el modal para que no se lea
  mal.
- **Solo administrador y coordinador.** Es decisión de coordinación.
- Un choque de horario revisa **todas** las fechas antes de crear ninguna y
  revierte entero: no queda un paquete de 20 con 4 citas y el precio repartido
  entre esas 4.

### 7.1 Caine Kids entra a las 9:15

Diana quería que ese horario se jalara solo. Se propuso agregar `hora_inicio` y
`hora_fin` a `programas`, junto a `sesiones_por_mes` y `precio_mensual`. Ella
decidió que no: *"solo CAINE KIDS tiene horario definido así exacto entonces
quizás sea mejor que se valide en el frontend"*.

Quedó como `HORARIO_FIJO` en `AsignarProgramaModal.vue`, un mapa por nombre
tolerante a mayúsculas. **La renovación mensual no lo usa**: copia el horario del
paquete que corre, que es lo correcto.

---

## 8. Pacientes: las observaciones a página completa

La ruta `/pacientes/{paciente}/observaciones` **ya existía apuntando a un método
que no estaba**. Se escribió el método y la página.

Va aparte del modal de Historial porque ahí la evolución cae dentro de una celda
de tabla, y lo que se escribe son párrafos largos: *"aquí colocan comentarios
extensos"*. Una tarjeta por sesión atendida, de la más reciente a la más vieja,
con Evolución y Observaciones públicas como texto corrido y un buscador que entra
dentro del texto.

---

## 9. La página de inicio

### 9.1 Cumpleaños de ayer

Dos cosas los hacían invisibles: solo se calculaban **hoy y mañana**, y **el
bloque entero tenía un `v-if` que lo borraba de la pantalla cuando nadie cumplía
años** — así que no había forma de saber si estaba vacío o roto.

Ahora son tres tarjetas —ayer, hoy, mañana— que se muestran siempre, y la vacía
dice «Nadie cumple años». `Cumpleanos::hoyYManana()` pasó a
`Cumpleanos::deLosTresDias()`.

### 9.2 Tareas de hoy

Lo primero de la página: las citas que la persona logueada tiene asignadas hoy,
por hora, con paciente, terapia, estado, si ya está atendida y un aviso de pastel
si el niño cumple años.

**El panel solo aparece para quien puede tener citas asignadas** —terapeutas,
auxiliares y el administrador, vía `QuienAtiende::atiendeCitas()`— o para quien
tenga alguna a su nombre. A un coordinador no se le deja una lista vacía todos
los días.

### 9.3 Flujos guiados: solo el dibujo

A pedido de Diana, *"por el momento quiero solo el diseño de esto para que no se
me olvide"*. Dos tarjetas con borde punteado y el botón deshabilitado, con la
etiqueta «Diseño, todavía sin construir». Los pasos salen de cómo se hace hoy a
mano:

- **Nuevo colaborador:** usuario y contraseña → rol → ficha de persona →
  especialidad y cargo → listo para agendarle.
- **Nuevo cliente:** encargado → niño ligado al encargado → expediente y
  anamnesis → terapeuta y terapias → paquete y sus citas.

**Falta que Diana confirme el orden y los pasos** antes de que alguien construya
sobre eso.

---

## 10. Arreglos de cosas que ya estaban rotas

### 10.1 Editar un expediente mostraba todo vacío

Cuatro causas distintas, todas corregidas:

- **Las fechas.** `Expediente` no tenía `$casts`, así que `fecha_inicio` viajaba
  como `"2026-09-09 15:12:24"` y **un `<input type="date">` no reconoce ese
  formato**: el campo salía en blanco con el dato guardado.
- **La anamnesis.** Los tres pasos de módulos se armaban siempre con
  `respuesta: null`, sin mirar lo contestado. Los 86 criterios abrían en blanco.
- **La escolaridad.** El selector leía `expediente.escolaridad_id`, columna que
  no existe — está en `pacientes`. Se llenaba y no guardaba nada.
- **Las terapias.** El formulario mandaba `terapias` y el controlador espera
  `servicios`, así que `sync()` recibía lista vacía: **cada guardado borraba las
  terapias del expediente**.

### 10.2 Ocupación resaltaba Agenda en el menú

La página vivía en `/agenda/ocupacion` y el menú marca activo con `startsWith`,
así que `/agenda` se encendía. Se movió a **`/indicadores/ocupacion`**, que es
además de donde se entra. Se actualizaron el enlace desde Indicadores, la
navegación por semana, el botón de volver y **las 8 referencias en
`tests/Feature/AgendaTest.php`** — esas se escaparon del primer grep y tumbaron 6
tests.

### 10.3 Botones que borraban sin preguntar

Diana pidió el mismo aviso en todos. El texto vive en `Utils/confirmar.js` y dice
**«¿Está seguro de realizar la eliminación permanente?»**, con una segunda línea
que nombra lo que se va a borrar. Pasan por ahí **12 botones**.

**Cuatro no preguntaban nada** y borraban de un solo clic: los catálogos de
`/parametros` y las tres pantallas de Personas. Usan `<Link method="delete">`,
que dispara la petición directo; ahora un `@click` frena el clic si se cancela.

Quedaron fuera dos confirmaciones que no son borrados: anular un pago y autorizar
un pago.

### 10.4 El administrador no aparecía para agendarle

`QuienAtiende::todos()` filtraba por un cargo hardcodeado, `'Auxiliar'`. Pasó a
`CARGOS_QUE_ATIENDEN = ['Auxiliar', 'Administrador']`, y **el puesto real viaja
en `rol`**, así que el selector dice «Administrador» y no «Auxiliar». El `tipo`
sigue siendo `'auxiliar'` para todos: es la clave del morph a `Administrativo`,
no el puesto de la persona.

---

## Cómo se verificó

No hay tests nuevos: lo que hay son **scripts de comprobación contra la base
real**, corridos con `php artisan tinker`, que crean datos, comprueban y
limpian. Los 54 tests de Feature existentes pasan, y el build también.

Lo que se comprobó de punta a punta: los 9 informes con cada filtro suelto y
combinado; los 27 archivos de exportación; el ciclo de autorización de pagos
(auxiliar cobra → coordinador autoriza → auxiliar corrige → se cae la
autorización); el cobro de paquete con abono y con saldo completo; la renovación
de programa con choque de horarios; el ciclo de objetivos con el tope de 4; y el
dashboard con cumpleaños sembrados para ayer y hoy.

---

## Qué quedó pendiente

**Esperando decisión de Diana:**

- **El selector de terapias en el asistente de expediente.**
  `expediente_servicios` está en **0 filas**: el controlador sincroniza la
  relación, pero el asistente no tiene dónde elegirlas. La fila «Terapias» del
  encargado va a decir «Sin terapias asignadas» hasta que exista. Ahora que el
  guardado dejó de borrarlas (10.1), es buen momento.
- **Si la fecha de pago debe poder buscar fuera del rango de sesión** (2.3).
- **Si el paquete anterior debe seguir activo** hasta que pasen todas sus citas,
  en vez de quedar `finalizado` al renovar (7). Exige una columna que enlace un
  paquete con su renovación.
- **El orden y los pasos de los dos flujos guiados** (9.3).
- **Si los objetivos necesitan estado** (en progreso / alcanzado) y fechas. Se
  dejaron fuera a propósito: no se pidieron.
- **Los datos de `sesiones.evolucion`**, que ya no se muestran (1.2).

**Rompe hoy** (heredado, sin tocar esta sesión):

- `/pacientes/{paciente}/historial` renderiza `Pages/Pacientes/Historial`, que no
  existe.
- `/pacientes/{paciente}/seguimiento` apunta a un método que no existe.
- Sigue todo lo listado en el informe del 11-09 que no se tocó acá.

**Nota sobre dependencias:** `composer.json` ganó `phpoffice/phpspreadsheet` y
`dompdf/dompdf`. Hay que correr `composer install` en cualquier otro ambiente.
