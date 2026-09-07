<!-- Components/personas/EncargadoModalVer.vue -->
<script setup>
import ModalBaseVer from '../ModalBaseVer.vue'

defineProps({
  encargado: { type: Object, required: true }
})
</script>

<template>
  <ModalBaseVer @close="$emit('close')">
    <!-- Encabezado -->
    <template #header>
      <h2 class="text-2xl font-bold text-[#2D2B5B]">Perfil Encargado</h2>
    </template>

    <!-- Avatar + nombre -->
    <div class="flex items-center space-x-4 mb-6">
      <img :src="encargado.avatar_url || '/images/avatar.webp'" class="w-20 h-20 rounded-full border" />
      <div>
        <p class="text-xl font-semibold">{{ encargado.nombres }} {{ encargado.apellidos }}</p>
        <p class="text-gray-600">{{ encargado.correo }}</p>
        <p class="text-gray-600">{{ encargado.telefono }}</p>
      </div>
    </div>

    <!-- Campos específicos -->
    <div class="grid grid-cols-2 gap-4 text-gray-700">
      <p><strong>Fecha nacimiento:</strong> {{ encargado.fecha_nacimiento }}</p>
      <p><strong>DPI:</strong> {{ encargado.dpi }}</p>
      <p><strong>Dirección:</strong> {{ encargado.direccion }}</p>
      <p><strong>Ocupación:</strong> {{ encargado.ocupacion }}</p>
      <p><strong>Género:</strong> {{ encargado.genero?.nombre }}</p>
      <p><strong>Estado civil:</strong> {{ encargado.estado_civil?.nombre }}</p>
      <p><strong>Relación con paciente:</strong> {{ encargado.relacion_paciente?.nombre }}</p>
    </div>
  </ModalBaseVer>
</template>