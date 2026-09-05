<script setup>
const props = defineProps({
  form: Object,
  evaluacionesList: Array
})
const emit = defineEmits(['prev','save'])
</script>

<template>
  <div class="max-w-5xl mx-auto bg-white rounded-lg shadow p-8">
    <h3 class="text-2xl font-bold text-[#2D2B5B] mb-6">
      Evaluaciones
    </h3>

    <!-- Lista de evaluaciones -->
    <div class="space-y-3 mb-6">
      <label v-for="eva in evaluacionesList" :key="eva.id" class="flex items-center space-x-2 text-gray-700">
        <input type="checkbox" :value="eva.id" v-model="form.evaluaciones" />
        <span>{{ eva.nombre }}</span>
      </label>
    </div>

    <!-- Observaciones -->
    <div class="mb-6">
      <label class="block text-sm font-medium text-[#2D2B5B]">Observaciones</label>
      <textarea v-model="form.observaciones" rows="3"
        class="mt-1 block w-full border rounded-md px-3 py-2 focus:ring-[#53C6D3] focus:border-[#53C6D3]"></textarea>
    </div>

    <!-- Navegación -->
    <div class="mt-8 flex justify-between">
      <button @click="emit('prev')" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">← Anterior</button>
      <button @click="emit('save')" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
        Guardar Expediente
      </button>
    </div>
  </div>
</template>
