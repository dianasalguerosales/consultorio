---
name: anamnesis-escala-y-grafo
description: Escala de respuestas de la anamnesis (1=Observación es el punto deficiente) y cómo se arma el grafo de indicadores
metadata: 
  node_type: memory
  type: project
  originSessionId: a4bbc732-06d8-4517-9898-02e7027227e3
  modified: 2026-09-07T23:32:48.660Z
---

**Escala de la anamnesis** (`anamnesis_items.respuesta`, entero validado `in:1,2,3`). Las etiquetas están en `resources/js/Components/expedientes/Modulo1.vue`:

| valor | etiqueta | significado |
|---|---|---|
| 3 | Adecuado | sin problema |
| 2 | En desarrollo | parcial |
| **1** | **Observación** | **el punto deficiente** |

Ojo con la dirección: **el número más bajo es el peor**, no al revés.

**Los 86 criterios** se agrupan en 3 módulos × 18 áreas (`criterios.modulo`, `criterios.area`). El grafo de `/indicadores` usa **áreas** como nodo, no criterios individuales: 86 nodos serían ilegibles, y el área es la unidad clínicamente significativa. Los criterios concretos aparecen en el panel de detalle al seleccionar un área.

**Grafo de indicadores** (`IndicadoresController`, `/indicadores`, permiso `ver indicadores` → administrador y coordinador): bipartito diagnóstico ↔ área deficiente. El peso de la arista es en cuántos expedientes coinciden. Filtro `nivel`: `observacion` (solo 1) o `ambos` (1 y 2).

**Layout: dos columnas con posiciones calculadas, NO un layout de fuerzas.** Con `cose` los nodos se encimaban y las etiquetas quedaban ilegibles unas sobre otras. Las áreas se ordenan por baricentro (la altura promedio de sus diagnósticos) para cruzar menos líneas, y las etiquetas van hacia afuera —diagnósticos a la izquierda, áreas a la derecha— para no caer sobre las líneas del centro. El encuadre se hace a mano con el zoom topado en 1: el `fit` automático agrandaba los nodos hasta encimarlos.

**Colores de los nodos**: dos hues categóricos validados en todos los pares — diagnóstico `#48468a` (azul de marca) y área `#c17924` (naranja caine bajado a la banda de luminosidad; el `#F4A654` original quedaba fuera de banda y bajo 3:1). ΔE 27.4 bajo protanopia. Los diagnósticos además llevan forma de rombo, así la identidad no depende solo del color. Ver [Paleta dataviz caine](paleta-dataviz-caine.md).

**Bug corregido en el modelo `Anamnesis`**: le faltaba `protected $table = 'anamnesis'`. Eloquent pluralizaba a `anamneses` y toda escritura fallaba — `Anamnesis::create()` nunca había funcionado.

**Bug corregido en `Expediente::generarCodigoExpediente()`**: cortaba en el carácter 7 pero `KID-2026` son 8, así que el correlativo se comía un dígito del año y el código crecía un `6` por expediente (`KID-2026001` → `KID-20266002` → `KID-202666003`).

Datos de prueba en `AnamnesisPruebaSeeder`, con perfiles clínicos por diagnóstico (TDAH → Atención/Impulsividad/Funciones ejecutivas, TEA → Desarrollo social/lenguaje, etc.) para que el grafo muestre patrones y no ruido.

**Google Calendar sigue pendiente**: Diana no tiene servidor de correo en este entorno (sí en el proyecto base), así que se postergó. Ver [Agenda: decisiones de arquitectura](agenda-decisiones-arquitectura.md).
