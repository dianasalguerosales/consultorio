<script setup>
import ModalBaseVer from '../ModalBaseVer.vue'
import FichaPersona from './FichaPersona.vue'

defineProps({
  administrativo: { type: Object, required: true }
})

const emit = defineEmits(['close'])
</script>

<template>
  <ModalBaseVer @close="emit('close')">
    <template #header>
      <h2 class="text-2xl font-bold text-[#2D2B5B]">Perfil administrativo</h2>
    </template>

    <FichaPersona :persona="administrativo" tipo="administrativo" />
  </ModalBaseVer>
</template>
