---
name: que-significa-modularizar
description: "Cuando Diana dice 'modularizar' se refiere al patrón de app/Reporteria - un archivo por unidad, una base con el contrato y un registro que los junta"
metadata:
  type: feedback
---

Cuando Diana dice **"modularizar"** se refiere exactamente al patrón de
`app/Reporteria/`: **un archivo por unidad de trabajo, una base que define el
contrato y guarda lo compartido, y un registro que los junta.**

No es solamente "partir un archivo grande en varios". La forma tiene tres piezas:

```
app/Reporteria/
├── Informe.php       ← la base: contrato (métodos abstractos) + ayudas compartidas
├── Registro.php      ← el conglomerado: la lista de todos, y lo único que ve el controlador
└── Informes/         ← un archivo por informe, nombrado por lo que hace
```

**Why:** el mantenimiento. `Definiciones.php` había llegado a 436 líneas con los
8 informes apelmazados, y cada uno nuevo sumaba ~40. Repartido, ningún archivo
pasa de 126 y el que se toca al agregar uno son 70. Diana lo pidió así: *"hacer
un archivo por cada informe para que quede separado e identificado con nombre que
dé referencia de qué es, y que estos se unan en un archivo que sea el conglomerado"*.

**How to apply:**

- **La base es abstracta.** Si una unidad nueva olvida un método obligatorio, PHP
  no deja cargar la clase. Con un arreglo, esa falta se descubría cuando la
  pantalla reventaba.
- **Lo compartido sube a la base**, no se repite ni se deja suelto arriba del
  archivo grande: `fecha()`, `nombrePaciente()`, `rangoFechas()`, `opciones()`.
- **El registro es la única puerta.** El controlador nunca nombra una clase
  concreta; pregunta al registro. Agregar la unidad nueve es crear su archivo y
  sumar una línea.
- **El registro sirve de lista blanca** cuando lo que se registra se elige desde
  la URL: si no está en la lista, no existe.
- **El total de líneas sube**, y está bien. La ceremonia de las clases cuesta
  ~70%; lo que se gana es que ningún archivo sea inabordable.

En el frontend el equivalente de "lo compartido" es `resources/js/Utils/`
(`EscClose.js`, `avatares.js`, `anamnesis.js`, `paleta.js`, `fechas.js`): lógica
sin plantilla, en un solo lugar, importada donde haga falta. Es el mismo objetivo
a menor escala.

**Dónde falta aplicarlo** (del backlog al 9 de septiembre de 2026): las 6 páginas
idénticas de `Pages/Parametros/`, `PersonasController` —tres CRUD calcados en 332
líneas—, `OcupacionController` dentro de `AgendaController`, y la configuración
de Chart.js y Cytoscape metida en `Ocupacion.vue` e `Indicadores.vue`.

Al hacerlo aplica [No romper lo existente](no-romper-lo-existente.md): grep de
todos los usos **antes** de mover nada. Ver el caso completo en el
[Informe 09-09](informe_09-09.md), sección 10.
