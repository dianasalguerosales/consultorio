<script setup>
defineProps({
  paciente: Object,
  form: Object,
})
defineEmits(['close','save'])
</script>

<template>
  <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-30 z-50">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
      <h2 class="text-lg font-bold text-caine-azul mb-4">
        {{ paciente ? 'Editar Paciente' : 'Nuevo Paciente' }}
      </h2>

      <!-- Nombre -->
      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700">Nombre</label>
        <input v-model="form.nombre" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
        <div v-if="form.errors.nombre" class="text-red-500 text-sm">{{ form.errors.nombre }}</div>
      </div>

      <!-- Género -->
      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700">Género</label>
        <select v-model="form.genero" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
          <option value="">Seleccione...</option>
          <option value="Masculino">Masculino</option>
          <option value="Femenino">Femenino</option>
          <option value="Otro">Otro</option>
        </select>
        <div v-if="form.errors.genero" class="text-red-500 text-sm">{{ form.errors.genero }}</div>
      </div>

      <!-- Encargado -->
      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700">Encargado</label>
        <select v-model="form.encargado_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
          <option value="">Seleccione...</option>
          <option v-for="encargado in $page.props.encargados" :key="encargado.id" :value="encargado.id">
            {{ encargado.nombre }}
          </option>
        </select>
        <div v-if="form.errors.encargado_id" class="text-red-500 text-sm">{{ form.errors.encargado_id }}</div>
      </div>

      <!-- Fecha de nacimiento -->
      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700">Fecha de nacimiento</label>
        <input v-model="form.fecha_nacimiento" type="date" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
        <div v-if="form.errors.fecha_nacimiento" class="text-red-500 text-sm">{{ form.errors.fecha_nacimiento }}</div>
      </div>

      <!-- Teléfono -->
      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700">Teléfono</label>
        <input v-model="form.telefono" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
        <div v-if="form.errors.telefono" class="text-red-500 text-sm">{{ form.errors.telefono }}</div>
      </div>

      <!-- Dirección -->
      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700">Dirección</label>
        <input v-model="form.direccion" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
        <div v-if="form.errors.direccion" class="text-red-500 text-sm">{{ form.errors.direccion }}</div>
      </div>

      <!-- Botones -->
      <div class="flex justify-end space-x-3 mt-6">
        <button class="px-4 py-2 bg-gray-200 rounded-md hover:bg-gray-300" @click="$emit('close')">
          Cancelar
        </button>
        <button class="px-4 py-2 bg-caine-celeste text-white rounded-md hover:bg-caine-azul" @click="$emit('save')">
          Guardar
        </button>
      </div>
    </div>
  </div>
</template>
