<script setup>
import BloqueAdministrativo from './BloqueAdministrativo.vue'
import BloqueTerapeuta from './BloqueTerapeuta.vue'
import BloqueEncargado from './BloqueEncargado.vue'
import { useForm } from '@inertiajs/vue3'

defineProps({
  user: Object,
  form: Object,
  roles: Array,
})
defineEmits(['close', 'save'])
</script>

<template>
  <!-- Overlay -->
  <div class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50">
    <!-- Contenedor modal -->
    <div class="bg-white rounded-lg shadow-xl w-11/12 max-w-5xl h-5/6 flex flex-col p-8">

      <!-- Header -->
      <h3 class="text-2xl font-bold text-[#2D2B5B] mb-6">
        {{ user ? 'Editar Usuario' : 'Nuevo Usuario' }}
      </h3>

      <!-- Bloques -->
      <div class="flex-1 overflow-y-auto">
        <!-- Campos iniciales (email, password, tipo_usuario) -->
        <div v-if="!user" class="grid grid-cols-3 gap-6 mb-8">
          <div>
            <label class="block text-sm font-medium text-[#2D2B5B]">Correo</label>
            <input v-model="form.email" type="email"
              class="mt-1 block w-full border rounded-md px-3 py-2 focus:ring-[#53C6D3] focus:border-[#53C6D3]" />
          </div>
          <div>
            <label class="block text-sm font-medium text-[#2D2B5B]">Contraseña</label>
            <input v-model="form.password" type="password"
              class="mt-1 block w-full border rounded-md px-3 py-2 focus:ring-[#53C6D3] focus:border-[#53C6D3]" />
          </div>
          <div>
            <label class="block text-sm font-medium text-[#2D2B5B]">Tipo de usuario</label>
            <select v-model="form.tipo_usuario"
              class="mt-1 block w-full border rounded-md px-3 py-2 focus:ring-[#53C6D3] focus:border-[#53C6D3]">
              <option value="">Seleccione...</option>
              <option value="administrativo">Administrativo</option>
              <option value="terapeuta">Terapeuta</option>
              <option value="encargado">Encargado</option>
            </select>
          </div>
        </div>

        <!-- Render dinámico de bloques -->
        <BloqueAdministrativo v-if="form.tipo_usuario === 'administrativo'" :form="form" />
        <BloqueTerapeuta v-if="form.tipo_usuario === 'terapeuta'" :form="form" />
        <BloqueEncargado v-if="form.tipo_usuario === 'encargado'" :form="form" />
    </div>

    <!-- Footer -->
    <div class="mt-8 flex justify-end space-x-3">
      <button @click="$emit('close')"
        class="inline-flex items-center px-6 py-2 bg-gray-200 text-[#2D2B5B] rounded-md hover:bg-gray-300 transition-colors">
        Cancelar
      </button>
      <button @click="$emit('save')"
        class="inline-flex items-center px-6 py-2 bg-[#2D2B5B] text-white rounded-md hover:bg-[#53C6D3] transition-colors">
        Guardar
      </button>
    </div>
  </div>
  </div>
</template>