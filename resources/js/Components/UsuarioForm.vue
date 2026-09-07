<script setup>
import { EscClose } from '@/Components/EscClose'

defineProps({
  user: Object,
  form: Object,
  roles: Array,
})

const emit = defineEmits(['close', 'save'])

EscClose(() => {
  emit('close')
})
</script>

<template>
  <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-30 z-50">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full p-8">

      <h2 class="text-lg font-bold text-caine-azul mb-6">
        {{ user ? 'Editar Usuario' : 'Nuevo Usuario' }}
      </h2>

      <!-- CREAR USUARIO -->
      <div v-if="!user" class="grid grid-cols-1 gap-4 mb-6">
      
        <!-- Correo -->
        <div>
          <label class="block text-sm font-medium text-gray-700">
            Correo
          </label>

          <input
            v-model="form.email"
            type="email"
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
          />

          <div v-if="form.errors.email" class="text-red-500 text-sm">
            {{ form.errors.email }}
          </div>
        </div>

        <!-- Contraseña -->
        <div>
          <label class="block text-sm font-medium text-gray-700">
            Contraseña
          </label>

          <input
            v-model="form.password"
            type="password"
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
          />

          <div v-if="form.errors.password" class="text-red-500 text-sm">
            {{ form.errors.password }}
          </div>
        </div>

      </div>

      <!-- EDITAR USUARIO -->
      <div v-else class="grid grid-cols-1 gap-4 mb-6">

        <!-- Correo -->
        <div>
          <label class="block text-sm font-medium text-gray-700">
            Correo
          </label>

          <input
            v-model="form.email"
            type="email"
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-gray-100 text-gray-700 focus:ring-caine-celeste focus:border-caine-celeste"
          />

          <div v-if="form.errors.email" class="text-red-500 text-sm">
            {{ form.errors.email }}
          </div>
        </div>

        <!-- Estado -->
        <div>
          <label class="block text-sm font-medium text-gray-700">
            Estado
          </label>

          <select
            v-model="form.status"
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-white focus:ring-caine-celeste focus:border-caine-celeste"
          >
            <option value="active">Activo</option>
            <option value="inactive">Inactivo</option>
          </select>

          <div v-if="form.errors.status" class="text-red-500 text-sm">
            {{ form.errors.status }}
          </div>
        </div>

      </div>

      <!-- BOTONES -->
      <div class="flex justify-end space-x-3 mt-6">

        <button
          type="button"
          class="px-4 py-2 bg-gray-200 rounded-md hover:bg-gray-300"
          @click="$emit('close')"
        >
          Cancelar
        </button>

        <button
          type="button"
          class="px-4 py-2 bg-caine-celeste text-white rounded-md hover:bg-caine-azul disabled:opacity-50"
          :disabled="form.processing"
          @click="$emit('save')"
        >
          {{ form.processing ? 'Guardando...' : 'Guardar' }}
        </button>

      </div>

    </div>
  </div>
</template>