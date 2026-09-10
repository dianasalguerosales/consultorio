<script setup>
import { EscClose } from '@/Utils/EscClose'

const props = defineProps({
  // Clases del panel. Cada modal trae su propio ancho y alto: el ancho es lo
  // que más varía entre ellos, así que no se fija acá.
  panel: { type: String, default: 'max-w-2xl' },
  // Para los modales que viven siempre montados y se muestran con un prop.
  mostrar: { type: Boolean, default: true },
  // Gancho sobre la capa, para quien necesite estilarla — por ejemplo el CSS
  // de impresión de la anamnesis.
  clase: { type: String, default: '' },
})

const emit = defineEmits(['close'])

// El guard hace falta cuando el modal está montado pero oculto: sin él,
// Escape cerraría algo que no se está viendo.
EscClose(() => {
  if (props.mostrar) emit('close')
})
</script>

<template>
  <div v-if="mostrar" class="fixed inset-0 z-50 flex items-center justify-center p-4" :class="clase">
    <!-- modal-fondo da un asidero para ocultarlo al imprimir. -->
    <div class="modal-fondo absolute inset-0 bg-black/40" @click="emit('close')"></div>

    <div class="relative w-full bg-white rounded-lg shadow-xl" :class="panel">
      <slot />
    </div>
  </div>
</template>
