<script setup>
const props = defineProps({
  form: Object,
  terapiasList: Array
})
const emit = defineEmits(['next','prev'])
</script>

<template>
  <div class="max-w-5xl mx-auto bg-white rounded-lg shadow p-8">
    <h3 class="text-2xl font-bold text-[#2D2B5B] mb-6">
      Atención Terapéutica
    </h3>

    <!-- Terapias -->
    <div class="mb-6">
      <label class="block text-sm font-medium text-[#2D2B5B]">Terapias</label>
      <div class="space-y-2 mt-2">
        <label v-for="terapia in terapiasList" :key="terapia.id" class="flex items-center space-x-2 text-gray-700">
          <input type="checkbox" :value="terapia.id" v-model="form.terapias" />
          <span>{{ terapia.nombre }}</span>
        </label>
      </div>
    </div>

    <!-- Objetivos terapéuticos -->
    <div class="mb-6">
      <label class="block text-sm font-medium text-[#2D2B5B]">Objetivos terapéuticos</label>
      <textarea v-model="form.objetivos_terapeuticos" rows="3"
        class="mt-1 block w-full border rounded-md px-3 py-2 focus:ring-[#53C6D3] focus:border-[#53C6D3]"></textarea>
    </div>

    <!-- Planificación terapéutica -->
    <div class="mb-6">
      <label class="block text-sm font-medium text-[#2D2B5B]">Planificación terapéutica</label>
      <textarea v-model="form.planificacion_terapeutica" rows="3"
        class="mt-1 block w-full border rounded-md px-3 py-2 focus:ring-[#53C6D3] focus:border-[#53C6D3]"></textarea>
    </div>

    <!-- Navegación -->
    <div class="mt-8 flex justify-between">
      <button @click="emit('prev')" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">← Anterior</button>
      <button @click="emit('next')" class="px-4 py-2 bg-[#2D2B5B] text-white rounded hover:bg-[#53C6D3]">Siguiente →</button>
    </div>
  </div>
</template>
