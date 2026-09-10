<script setup>
import ModalBaseVer from '../ModalBaseVer.vue'

defineProps({
  item: { type: Object, required: true },
  titulo: { type: String, required: true },
  conDescripcion: { type: Boolean, default: false },
})

const emit = defineEmits(['close'])
</script>

<template>
  <ModalBaseVer @close="emit('close')">
    <template #header>
      <h2 class="text-xl font-bold text-[#2D2B5B]">Detalle de {{ titulo }}</h2>
    </template>

    <div class="space-y-4">
      <p><strong>Nombre:</strong> {{ item.nombre }}</p>
      <p v-if="conDescripcion"><strong>Descripción:</strong> {{ item.descripcion }}</p>
      <p><strong>Activo:</strong> {{ item.activo ? 'Sí' : 'No' }}</p>
    </div>
  </ModalBaseVer>
</template>
