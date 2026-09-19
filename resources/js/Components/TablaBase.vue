<script setup>
/**
 * El molde de las tablas de listado del sistema.
 *
 * Cada pantalla traía el suyo y no había dos iguales: unas con encabezado
 * `bg-gray-100` y otras `bg-gray-200`, unas en `text-sm` y otras en `text-md`,
 * unas envueltas en una tarjeta blanca y otras sueltas.
 *
 * Y el detalle de las esquinas: casi todas llevaban `rounded-lg` en la etiqueta
 * `<table>`, donde no hace nada —una tabla no recorta sus celdas—, así que solo
 * se veían redondeadas las que además lo tenían en el `div` de afuera. Por eso
 * el marco vive acá y no en cada página.
 *
 * Las celdas se estilan desde `.tabla-caine` con estilos globales, porque el
 * contenido de un slot pertenece al padre y un `scoped` no lo alcanzaría. No se
 * toca la alineación: hay columnas centradas y otras a la derecha que deben
 * seguir como están.
 */
defineProps({
  // Para las tablas que ya viven dentro de una tarjeta y no necesitan otra.
  sinMarco: { type: Boolean, default: false },
})
</script>

<template>
  <div class="tabla-caine overflow-x-auto"
    :class="sinMarco ? '' : 'bg-white rounded-lg shadow-md'">
    <table class="min-w-full text-sm">
      <slot />
    </table>
  </div>
</template>

<style>
.tabla-caine thead {
  background-color: #f3f4f6;
  color: #2d2b5b;
}

.tabla-caine th {
  padding: 0.625rem 1rem;
  font-weight: 600;
  white-space: nowrap;
}

.tabla-caine td {
  padding: 0.625rem 1rem;
}

.tabla-caine tbody tr {
  border-top: 1px solid #f3f4f6;
}

.tabla-caine tbody tr:hover {
  background-color: #faf9f7;
}
</style>
