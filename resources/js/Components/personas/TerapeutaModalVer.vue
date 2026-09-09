<script setup>
import { fecha } from '@/Utils/fechas'
import ModalBaseVer from '../ModalBaseVer.vue'

defineProps({
  terapeuta: { type: Object, required: true }
})
</script>

<template>
  <ModalBaseVer @close="$emit('close')">
    <template #header>
      <h2 class="text-2xl font-bold text-[#2D2B5B]">Perfil Terapeuta</h2>
    </template>

    <div class="flex items-center space-x-4 mb-6">
      <img :src="terapeuta.avatar_url || '/images/avatar.webp'" class="w-20 h-20 rounded-full border" />
      <div>
        <p class="text-xl font-semibold">{{ terapeuta.nombres }} {{ terapeuta.apellidos }}</p>
        <p class="text-gray-600">{{ terapeuta.correo }}</p>
        <p class="text-gray-600">{{ terapeuta.telefono }}</p>
      </div>
    </div>

    <div class="grid grid-cols-2 gap-4 text-gray-700">
      <p><strong>Fecha nacimiento:</strong> {{ fecha(terapeuta.fecha_nacimiento) }}</p>
      <p><strong>DPI:</strong> {{ terapeuta.dpi }}</p>
      <p><strong>Género:</strong> {{ terapeuta.genero?.nombre }}</p>
      <p><strong>Especialidad:</strong> {{ terapeuta.especialidad?.nombre }}</p>
      <p><strong>Experiencia:</strong> {{ terapeuta.experiencia }}</p>
      <p><strong>Certificaciones:</strong> {{ terapeuta.certificaciones }}</p>
      <p><strong>Cursos:</strong> {{ terapeuta.cursos }}</p>
    </div>
  </ModalBaseVer>
</template>
