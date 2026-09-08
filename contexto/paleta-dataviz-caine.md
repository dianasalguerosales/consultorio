---
name: paleta-dataviz-caine
description: "Rampa secuencial validada derivada del azul de marca caine, para heatmaps y gráficas de magnitud"
metadata: 
  node_type: memory
  type: reference
  originSessionId: a4bbc732-06d8-4517-9898-02e7027227e3
  modified: 2026-09-07T23:01:51.429Z
---

Rampa secuencial de un solo hue para visualizaciones de **magnitud**, derivada del azul de marca caine `#2D2B5B` (OKLCH L=0.318 C=0.083 h=-77.6°) manteniendo su tono y escalonando la luminosidad:

| nivel | fondo | texto | contraste vs superficie |
|---|---|---|---|
| 1 | `#acaee6` | `#1f2937` | 2.06:1 |
| 2 | `#8788d2` | `#1f2937` | 3.16:1 |
| 3 | `#6665b4` | `#ffffff` | 5.01:1 |
| 4 | `#48468a` | `#ffffff` | 8.12:1 |
| 5 | `#2d2b5a` | `#ffffff` | 12.75:1 |

Valor cero (sin datos) va en `#fbfbfa` con `inset 0 0 0 1px #ececea` — la superficie con filete, **no** el paso más claro de la rampa, para que "libre" no se confunda con "poca carga".

**Validada** con el validador de la skill `dataviz` en modo `--ordinal`: luminosidad monótona, saltos ΔL ≥ 0.06, extremo claro ≥ 2:1, hue único. Pasa las cuatro. El primer intento fallaba con 1.97:1 y se hizo *snap-to-passing* subiendo el paso más claro a L=0.770.

**Reglas que aplican al usarla:**
- El color codifica **cantidad**, no identidad → rampa secuencial de un hue, nunca la paleta categórica. Colorear categorías nominales por su valor es un anti-patrón.
- Los bins se reparten por posición sobre `[1, max]`: `min = floor(i*max/pasos)+1`, `max = floor((i+1)*max/pasos)` con `pasos = min(5, max)`. Repartir por ancho fijo dejaba bins vacíos y rangos invertidos.
- El texto dentro de una celda coloreada elige blanco o tinta según la luminancia del fondo (única excepción a "el texto nunca lleva el color del dato").

**El proyecto es light-only**: `darkMode` no está configurado en Tailwind y hay cero clases `dark:`. No hay rampa dark y no se debe introducir un modo oscuro suelto en una vista.

**Chart.js**: instalado (`chart.js` ^4.5.1 + `chartjs-chart-matrix` ^3.0.8). El heatmap de ocupación es un chart `matrix` sobre canvas, con **la tabla como vista alterna conmutable** — el canvas no da foco de teclado ni lectores de pantalla, así que la tabla no es un extra sino el modo accesible equivalente. Dos detalles imprescindibles del canvas:
- Un plugin `afterDatasetsDraw` escribe el conteo dentro de cada celda (`elemento.getCenterPoint()`), eligiendo blanco o tinta según el paso. Sin él el valor solo viviría en el tooltip, y un tooltip nunca puede ser la única vía de lectura.
- `width`/`height` del dataset se calculan del `chart.chartArea` menos 3px, que es el hueco de superficie entre celdas.

**Cytoscape** para el grafo de diagnósticos sigue pendiente. Chart.js es lo correcto para `/indicadores` (tendencias, distribuciones), que aún no tiene ruta.

Ver [Convenciones de Vue del proyecto](convenciones-vue-proyecto.md) y [Agenda: decisiones de arquitectura](agenda-decisiones-arquitectura.md).
