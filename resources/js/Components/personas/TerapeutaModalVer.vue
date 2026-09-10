<script setup>
import ModalBaseVer from '../ModalBaseVer.vue'
import FichaPersona from './FichaPersona.vue'

defineProps({
  terapeuta: { type: Object, required: true }
})

const emit = defineEmits(['close'])
</script>

<template>
  <ModalBaseVer @close="emit('close')">
    <template #header>
      <h2 class="text-2xl font-bold text-[#2D2B5B]">Perfil del terapeuta</h2>
    </template>

    <FichaPersona :persona="terapeuta" tipo="terapeuta" />
  </ModalBaseVer>
</template>
