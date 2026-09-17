---
name: impresion-de-documentos
description: Los dos caminos de impresión que ya existen (dompdf en Reportería y @media print en la anamnesis), y la decisión pendiente de unificarlos antes de imprimir evaluaciones y expedientes
metadata: 
  node_type: memory
  type: project
  modified: 2026-09-17
---

Hoy **conviven dos formas de producir un documento**, hechas para cosas distintas
y que no se conocen entre sí:

| Camino | Dónde | Qué produce | Cómo |
|---|---|---|---|
| **Servidor** | `app/Reporteria/Exportador.php` | Reportes tabulares (los nueve de `app/Reporteria/Informes/`) | dompdf, carta apaisada, con CSV y XLSX por el mismo `Exportador` |
| **Navegador** | `resources/js/Components/AnamnesisModal.vue` | La hoja de anamnesis | `@media print` sobre el DOM del modal + `window.print()` |

Y un tercer caso que no genera nada: el **consentimiento informado** es un PDF
fijo en `public/documentos/Consentimiento.pdf`, que el botón manda a imprimir en
un iframe fuera de pantalla (`tabs/DatosGenerales.vue`).

## Lo que viene

Diana quiere **imprimir evaluaciones e imprimir expedientes** con la misma
lógica de la anamnesis — el PDF armado desde el código, no un archivo subido.
Antes de escribir el segundo y el tercero, toca decidir si se separa el diseño
del documento de su generación, en vez de que cada modal traiga su propio
bloque `@media print`.

## Lo que ya se aprendió con la anamnesis, y hay que respetar

**Imprimir desde el navegador choca con los modales anidados.** El modal de
anamnesis vive dentro del de expediente, que es `position: fixed`; Chrome
**repite en cada página** todo lo que cuelga de un ancestro fijo, así que el
encabezado se repintaba encima del contenido. Se corrigió con
`<Teleport to="body">` en el modal, para sacarlo de esa cadena, y ocultando el
resto con `display: none` (no `visibility: hidden`, que oculta pero sigue
ocupando hojas). Cualquier documento nuevo que se imprima desde un modal
anidado va a toparse con exactamente lo mismo.

**El CSS de impresión necesita `!important`** para ganarle a las utilidades de
Tailwind, que empatan en especificidad, y al `document.body.style.overflow` que
pone `Modal.vue`, que al ser estilo inline solo se vence así.

## La decisión pendiente

Las dos opciones, con lo que se gana y lo que cuesta:

- **Todo al servidor, con dompdf**, reusando lo que ya existe en `Reporteria`.
  Un solo lugar donde vive el diseño, el resultado no depende del navegador ni
  de la configuración de impresión del usuario, y el PDF se puede guardar o
  adjuntar, no solo mandar a papel. A cambio, dompdf es limitado en CSS
  (nada de flex ni grid modernos) y hay que escribir la vista aparte del
  componente Vue: el documento deja de ser "lo que se ve en pantalla".
- **Seguir con `@media print`**, factorizando lo común: un componente
  `HojaImprimible` que ponga el `Teleport`, el encabezado con los datos del
  paciente, el pie de firmas y las reglas de paginación, y que cada documento
  solo llene su contenido. Se mantiene que lo impreso es lo que se ve, y el
  diseño se escribe con el mismo Tailwind de siempre. A cambio, sigue
  dependiendo del navegador y no deja un archivo.

Diana lo dejó anotado el **17 de septiembre de 2026** para retomarlo más
adelante — todavía no está decidido.
