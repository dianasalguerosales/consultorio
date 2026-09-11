---
name: informe-11-09
description: Informe de lo construido y corregido el 2026-09-11, con los archivos tocados
metadata:
  type: project
---

# Informe de cambios — 11 de septiembre de 2026

Sesión sobre `caine`. Sin commitear al cierre: **23 archivos modificados y 11
nuevos, 336 líneas agregadas y 72 quitadas**, más 949 líneas en los archivos
nuevos.

El día tuvo un tema central —**el programa de cada niño y las citas que genera**—
y una tanda de arreglos pedidos al final: especialidad del terapeuta, responsive
del perfil, el nombre de una tabla, cumpleaños y una regla nueva de comentarios.

---

## 1. Programas: el paquete que lleva cada niño

Lo más grande del día. El coordinador entra a la ficha de un paciente, le asigna
un programa y **sus citas quedan en el calendario**.

### 1.1 Qué había antes

Tres hallazgos cambiaron el plan apenas se revisó el terreno:

- **Los catálogos que se pedían ya existían como tablas**: `diagnosticos`,
  `estado_citas`, `tipo_citas`, `cargos` y `evaluaciones`, con datos. No hubo
  que crear ninguna.
- **"Paquetes" ya existía con otro nombre.** La tabla `programas` tenía
  `nombre`, `descripcion`, `sesiones_por_mes` y `precio_mensual` —exactamente
  nombre, cantidad de citas y costo— con 2 filas y **99 citas apuntando a ella**.
- **El módulo Programas del menú estaba roto por triplicado** (ver 1.6).

Diana decidió que programa y paquete son lo mismo: *"yo hablo de paquetes solo
para que se entienda, pero son programas que van amarrados a cantidad de citas y
se le asignan al niño"*. Así que `programas` siguió siendo el catálogo y la
asignación al niño se fue a una tabla nueva.

### 1.2 La tabla nueva

`asignaciones_programa`: paciente, programa, servicio, quién atiende
(polimórfico, como en `citas`), modalidad, tipo de cita, precio, cantidad de
citas, días de la semana, horario, fecha de inicio y estado.

Y una columna en `citas`: **`asignacion_programa_id`**. Sin ella no hay forma
confiable de saber qué citas nacieron de qué programa —habría que adivinarlo por
paciente y fechas, y se rompe apenas alguien mueve una cita—. `programa_id` se
quedó como estaba, apuntando al catálogo, así que **las 99 citas existentes no se
tocaron**.

### 1.3 El precio se reparte al centavo

El requisito de Diana fue explícito: *"los montos deben de ser exactos de manera
que su suma dé el total del paquete"*.

`app/Programas/RepartoPrecio.php` trabaja **en centavos** —los flotantes no
representan 0.01 con exactitud— y reparte los que sobran de la división de a uno
entre las primeras citas, en vez de cargarlos todos a la última:

| Paquete | Citas | Resultado |
|---|---|---|
| Q800 | 22 | 36.37 y 36.36 mezclados → **Q800.00** |
| Q500 | 12 | ocho de 41.67 y cuatro de 41.66 → **Q500.00** |
| Q800 | 20 | 20 de Q40.00 → **Q800.00** |
| Q0.05 | 3 | 0.02, 0.02, 0.01 → **Q0.05** |

Si cada cita se redondeara por su cuenta, Q800 entre 22 daría Q799.92 y el
cierre quincenal arrastraría el descuadre.

### 1.4 Todo o nada, una decisión que hubo que tomar

El diseño original creaba las citas que podía y omitía las que chocaban de
horario. Al probarlo se vio que **eso rompe justo lo que se pidió**: un paquete
de 12 quedaba con 4 citas y Q166.64 en vez de Q500.

Ahora si alguna fecha choca **no se crea nada**: la excepción revierte la
transacción completa y se devuelven las fechas en conflicto para que el
coordinador cambie horario, días o terapeuta. Un paquete de 12 con 4 citas no es
un programa vendible.

Se verificó que el rollback funciona: tras un rechazo quedó una sola asignación,
no una a medias.

### 1.5 Los catálogos de Parámetros

De 6 pestañas a **12**. Los cinco simples fueron cinco entradas en
`Catalogos.php` —sin migración, sin controlador, sin página—, que es lo que el
registro de ayer prometía.

Programas fue el único con trabajo real: lleva dos campos numéricos que el
formulario genérico no contemplaba. Se agregó `campos` al registro, declarativo:

```php
'campos' => [
    ['clave' => 'sesiones_por_mes', 'etiqueta' => 'Cantidad de citas', 'tipo' => 'entero'],
    ['clave' => 'precio_mensual', 'etiqueta' => 'Costo', 'tipo' => 'moneda'],
],
```

Con eso el CRUD valida, la tabla agrega columnas y el formulario agrega inputs,
sin duplicar nada. Los tipos son `entero`, `moneda` y `texto`.

Dos detalles: los errores usan la etiqueta de la pantalla —decía *"el campo
sesiones por mes"* en vez de *"Cantidad de citas"*— y los campos extra se
declaran en el objeto inicial del `useForm`, porque **Inertia solo manda las
llaves que existen ahí** y un `v-model` sobre una llave no declarada se descarta
sin avisar.

Se cargaron los cuatro paquetes reales —4, 8 y 12 sesiones, y **Caine Kids**
(lunes a viernes, 9:15 a 12:15, mensual)— **con el costo vacío**: no se dieron
precios y no se inventaron.

### 1.6 El módulo Programas estaba roto por triplicado

1. `ProgramaController::index` hacía `with(['servicios', 'especialidades'])` y
   esas relaciones **no existen** en el modelo `Programa`.
2. Renderizaba `'Programas'`, pero el resolver busca `Pages/Programas.vue` y lo
   que había era `Pages/Programas/Index.vue`.
3. Su contenido eran Servicios y Especialidades —que ahora viven en Parámetros—
   y sus botones llamaban a `/servicios` y `/especialidades`, **rutas borradas
   el día anterior** con la unificación de catálogos.

Ahora `/programas` lista los programas asignados con costo, precio por cita,
días y horario.

**Archivos:** `AsignacionProgramaController.php` *(nuevo, 159 líneas)*,
`Models/AsignacionPrograma.php` *(nuevo)*, `app/Programas/RepartoPrecio.php` y
`CitasDelPrograma.php` *(nuevos)*, `Components/AsignarProgramaModal.vue` *(nuevo)*,
`Pages/Programas.vue` *(nuevo)*, `Catalogos.php`, `CatalogoController.php`,
los tres componentes de `parametros/`, `ProgramaSeeder.php`, migración
`2026_09_11_100000`

---

## 2. Tres bugs que salieron en el camino

- **`CitaModal` le ponía a una cita suelta el costo del mes completo.** La línea
  era `form.precio_aplicado = programa.precio_mensual`. Por eso en Pagos
  aparecía una cita de Q800 junto a otras de Q250. Ahora sugiere el costo
  dividido entre las citas del paquete.
- **`asignacion_programa_id` no estaba en el `fillable` de `Cita`.** Las
  primeras 20 citas se crearon sin quedar ligadas a su programa: Eloquent
  descarta en silencio lo que no está en el fillable. Se detectó porque la
  relación devolvía vacío, y se limpiaron esas citas huérfanas.
- **`FichaPersona` tenía `avatarClase` declarado dos veces** en `defineProps`,
  copiado y pegado. Vue se quedaba con el segundo, así que no rompía nada, pero
  estaba ahí.

---

## 3. Pagos ahora muestra todas las citas

Antes solo salían las que tenían sesión registrada. Diana lo corrigió: *"es útil
que en pagos aparezcan todas las citas y no solo las atendidas"* — con razón,
porque **un programa se cobra por adelantado** y esperar a que la sesión se dé
dejaba fuera justo lo que hay que cobrar.

Pasó de 14 a **75 citas** en la quincena. Se agregó la columna de estado de la
cita, y las canceladas se ven pero **no suman al total esperado** (se verificó:
el esperado bajó exactamente el precio de la cancelada).

---

## 4. La especialidad sale de quien atiende

El servicio se elige al agendar o al asignar el programa, y eso ya estaba bien.
Lo que cambió es que **la especialidad ya no se selecciona**: se jala de la
persona asignada y aparece debajo del selector como texto informativo, en el
modal de cita y en el de programa.

Vive en `QuienAtiende`, que es de donde leen las dos pantallas, así que no hay
dos consultas distintas que puedan desalinearse.

---

## 5. Responsive del perfil

El avatar estaba fijo en `w-48` —192px de los 375 de un teléfono— y la cabecera
no envolvía, así que el nombre quedaba espichado contra el borde. Ahora el
avatar escala (`w-28` → `w-40` → `w-48`), la cabecera se apila y se centra en
móvil, y el correo largo ya no desborda. De `sm` para arriba se ve igual.

**Archivos:** `Pages/Perfil.vue`, `Components/personas/FichaPersona.vue`

---

## 6. `solicitudes_reprogramacion` → `reprogramaciones`

La tabla estaba vacía y el nombre se usaba en un solo lugar, así que el rename
salió limpio. Se hizo con una migración aparte en vez de editar la original: si
se editara, las bases que ya la corrieron se quedarían con el nombre viejo.

Se probó el flujo completo después del cambio: la solicitud se crea bien sobre
la tabla nueva.

---

## 7. Cumpleaños

**En Inicio**, dos tarjetas —hoy y mañana— con pacientes, terapeutas, personal y
encargados, cada uno con la edad que cumple. El Inicio era un closure que solo
renderizaba un texto de bienvenida; ahora tiene su `DashboardController`.

**Al atender una cita**, una franja avisa que ese día el niño cumple años. Se
compara contra **la fecha de la cita, no contra hoy**, así que una cita de
mañana también avisa cuando llegue el día. Verificado: de 94 citas del mes se
marcan solo las dos correctas.

Dos cosas que costaron encontrar:

- **La fecha de nacimiento del niño no está en `pacientes`**, está en su
  expediente. Del personal sí está en su propia tabla.
- **El filtro se hace en PHP y no en SQL** a propósito: sacar el mes y el día de
  una fecha se escribe distinto en SQLite y en MySQL (`strftime` contra
  `DATE_FORMAT`), y la base real es MySQL. Con 25 filas no hay razón para
  arriesgarse a que funcione aquí y falle allá.

**Archivos:** `app/Cumpleanos/Cumpleanos.php` *(nuevo)*, `DashboardController.php`
*(nuevo)*, `Pages/Dashboard.vue`, `Components/SesionModal.vue`,
`AgendaController.php`

---

## 8. Otros

- **Expediente en Pacientes**: mostraba `expediente.id`, así que salía "5" en
  vez de "KID-2026001". Un solo lugar, `Pacientes.vue:132`.
- **Regla nueva: las migraciones van sin comentarios**, salvo que sean muy
  necesarios. Quedó documentada en
  [Comentarios breves y directos](comentarios-breves-y-directos.md) y se aplicó
  a las tres migraciones propias. Sobrevivió una sola línea —`// Días en formato
  ISO: 1 lunes ... 7 domingo.`— porque sin ella no hay forma de saber si el 1 es
  lunes o domingo: es una advertencia que evita un bug, no una descripción del
  esquema. La justificación de por qué existe una tabla va en el modelo, que es
  donde alguien la va a leer.
- **Vocabulario**: "planificación" queda reservado para la planificación clínica
  que se hará después, y no se le dice "generación" a la cantidad de citas — es
  el paquete o el programa.

---

## Qué quedó pendiente

**Esperando decisión de Diana:**

- Los **costos de los cuatro paquetes** están vacíos en Parámetros.
- **Renombrar `sesiones_por_mes` → `cantidad_citas` y `precio_mensual` →
  `costo`.** Se propuso y no se confirmó, así que no se tocó; se usan las
  etiquetas correctas en pantalla. Son cuatro lugares en el código.
- **`ProgramaController` y `Pages/Programas/`** (Index, Servicios y
  Especialidades) quedaron sin uso. No se borran sin OK.
- Si se quitan los **datos de prueba**: dos programas asignados (Pedro con Caine
  Kids, Lucía con 12 sesiones), una cita marcada como Cancelada para ver Pagos,
  y cuatro cumpleaños sembrados para hoy y mañana.

**Rompe hoy** (heredado, sin tocar esta sesión):

- `ExpedienteController:128` redirige a `route('expedientes')`, nombre que no
  existe: editar un expediente truena.
- `ExpedienteController::destroy`, `PacientesController::observaciones` y
  `::seguimiento` no existen como métodos, pero tienen ruta.
- `Evaluacion::resultados()` apunta a `ResultadoEvaluacion`, clase inexistente.
- El rol `pruebas` no lo tiene ningún usuario y sus módulos están cerrados con
  `role:` en vez de `permission:`, así que le darían 403.
- `tabs/AtencionTerapeutica.vue` recorre `expediente.terapias` —la relación se
  llama `servicios`— y `objetivos_terapeuticos` y `planificacion_terapeutica` no
  son columnas.

**Módulos a medias:** el visor de Evaluaciones y sus 3 tablas; los 6 informes
que dependen de tablas nuevas; pacientes colgando de los terapeutas en el
organigrama.

**Modularización que queda:** extraer Chart.js de `Ocupacion.vue` (~130 líneas)
y Cytoscape de `Indicadores.vue` (~180).

**Dependencias:** falta `composer audit --no-dev` para identificar las 3
vulnerabilidades.
