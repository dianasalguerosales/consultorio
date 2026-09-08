---
name: comentarios-breves-y-directos
description: "Los comentarios van breves y directos - una o dos líneas que expliquen el por qué, nunca párrafos ni docblocks largos"
metadata:
  type: feedback
---

En `consultorio` los comentarios se escriben **breves y directos**: una o dos
líneas, entendibles de una lectura. Nada de párrafos, docblocks de diez líneas
ni archivos donde el comentario ocupa más que el código.

**Why:** Diana revisó `resources/js/Utils/paleta.js` y me lo señaló como regla
importante: *"no quiero un archivo lleno de comentarios, únicamente quiero
comentarios directos y breves, quiero que sean entendibles"*. Un archivo
sepultado en prosa cuesta más de leer que el código que explica, y el comentario
largo envejece peor — se vuelve mentira en cuanto alguien toca la línea de
abajo.

**How to apply:**

- Comentar el **por qué**, no el qué. Si el código ya se lee solo, no lleva
  comentario.
- Una o dos líneas. Si hacen falta más, el detalle va a una nota de `contexto/`
  y el comentario la referencia en una línea.
- Las advertencias que evitan un bug sí se quedan, pero comprimidas: la
  dirección invertida de la escala de la anamnesis cabe en un renglón.
- **Los encabezados de sección sí sirven y se mantienen.** Un
  `/* ---------- Sección ---------- */` que dice en una línea de qué es el
  bloque de código que sigue ayuda a ubicarse en un archivo largo. Diana lo
  pidió explícitamente. Ya se usan así en `Agenda.vue`, `Ocupacion.vue` e
  `Indicadores.vue`, y los archivos nuevos deben seguir el mismo patrón.
- Lo que no va es el **párrafo**: el encabezado se queda en su línea y no
  arrastra abajo una explicación de tres renglones.
- Tampoco va volver a nombrar en prosa lo que la función ya dice.

Aplica igual a PHP y a Vue/JS. Ver [Convenciones de Vue del proyecto](convenciones-vue-proyecto.md).
