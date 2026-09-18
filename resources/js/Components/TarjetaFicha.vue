<script setup>
/**
 * La ficha en rejilla del proyecto: imagen arriba, título, datos al centro y
 * las acciones abajo separadas por una línea.
 *
 * Estaba copiada en Pacientes y en Usuarios; vive acá para que todas se vean
 * igual y para que Indicadores use la misma sin volver a escribirla.
 *
 * Con `accion` la ficha entera es un botón —así la usan los indicadores, donde
 * tocar cualquier parte abre la gráfica— y en ese caso el pie de acciones no va.
 */
defineProps({
  titulo: { type: String, default: '' },
  // La ficha completa responde al clic en vez de tener botones abajo.
  accion: { type: Boolean, default: false },
})

const emit = defineEmits(['accion'])
</script>

<template>
  <component :is="accion ? 'button' : 'div'" :type="accion ? 'button' : null"
    @click="accion ? emit('accion') : null"
    class="bg-white shadow rounded-lg overflow-hidden flex flex-col items-center text-center w-full"
    :class="accion ? 'hover:shadow-md hover:-translate-y-0.5 transition cursor-pointer' : ''">

    <!-- Imagen: el avatar de una persona o el icono de un indicador. -->
    <div v-if="$slots.imagen" class="mt-6">
      <slot name="imagen" />
    </div>

    <div class="mt-4 px-4">
      <h3 v-if="titulo" class="text-lg font-semibold text-gray-900">{{ titulo }}</h3>
      <slot name="datos" />
    </div>

    <!-- Lo que va entre los datos y el pie: las etiquetas de rol, por ejemplo. -->
    <slot />

    <div v-if="$slots.acciones"
      class="mt-6 grid grid-cols-2 divide-x divide-gray-200 border-t border-gray-200 w-full">
      <slot name="acciones" />
    </div>

    <!-- Lo que va debajo del pie de acciones: en Pacientes, los botones de
         expediente e historial; en Usuarios, el de permisos. -->
    <slot name="pie" />
  </component>
</template>
