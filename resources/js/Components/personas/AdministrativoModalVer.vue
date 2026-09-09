<script setup>
import { fecha } from '@/Utils/fechas'
import ModalBaseVer from '../ModalBaseVer.vue'

defineProps({
  administrativo: { type: Object, required: true }
})
</script>

<template>
  <ModalBaseVer @close="$emit('close')">
    <template #header>
      <h2 class="text-2xl font-bold text-[#2D2B5B]">Perfil Administrativo</h2>
    </template>

    <div class="flex items-center space-x-4 mb-6">
      <img :src="administrativo.avatar_url || '/images/avatar.webp'" class="w-20 h-20 rounded-full border" />
      <div>
        <p class="text-xl font-semibold">{{ administrativo.nombres }} {{ administrativo.apellidos }}</p>
        <p class="text-gray-600">{{ administrativo.correo }}</p>
        <p class="text-gray-600">{{ administrativo.telefono }}</p>
      </div>
    </div>

    <div class="grid grid-cols-2 gap-4 text-gray-700">
      <p><strong>Fecha nacimiento:</strong> {{ fecha(administrativo.fecha_nacimiento) }}</p>
      <p><strong>DPI:</strong> {{ administrativo.dpi }}</p>
      <p><strong>Género:</strong> {{ administrativo.genero?.nombre }}</p>
      <p><strong>Cargo:</strong> {{ administrativo.cargo?.nombre }}</p>
      <p><strong>Especialidad:</strong> {{ administrativo.especialidad?.nombre }}</p>
      <p><strong>Experiencia:</strong> {{ administrativo.experiencia }}</p>
      <p><strong>Certificaciones:</strong> {{ administrativo.certificaciones }}</p>
      <p><strong>Cursos:</strong> {{ administrativo.cursos }}</p>
    </div>
  </ModalBaseVer>
</template>