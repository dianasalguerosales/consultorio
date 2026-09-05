<script setup>
const props = defineProps({
  form: Object,
  diagnosticosList: Array
})
const emit = defineEmits(['next','prev'])
</script>

<template>
  <div class="max-w-5xl mx-auto bg-white rounded-lg shadow p-8">
    <h3 class="text-2xl font-bold text-[#2D2B5B] mb-6">
      Historia Clínica
    </h3>

    <!-- Motivo de consulta -->
    <div class="mb-6">
      <label class="block text-sm font-medium text-[#2D2B5B]">Motivo de consulta</label>
      <textarea v-model="form.motivo_consulta" rows="2"
        class="mt-1 block w-full border rounded-md px-3 py-2 focus:ring-[#53C6D3] focus:border-[#53C6D3]"></textarea>
    </div>

    <!-- Antecedentes -->
    <div class="mb-6">
      <label class="block text-sm font-medium text-[#2D2B5B]">Antecedentes</label>
      <ul class="list-disc pl-6 text-gray-700">
        <li v-for="(item, idx) in form.antecedentes || []" :key="idx">
          {{ item.descripcion }}
        </li>
      </ul>
      <!-- Si quieres permitir edición, podrías agregar un input dinámico aquí -->
    </div>

    <!-- Diagnósticos -->
    <div class="mb-6">
      <label class="block text-sm font-medium text-[#2D2B5B]">Diagnósticos</label>
      <div class="space-y-2 mt-2">
        <label v-for="diag in diagnosticosList" :key="diag.id" class="flex items-center space-x-2 text-gray-700">
          <input type="checkbox" :value="diag.id" v-model="form.diagnosticos" />
          <span>{{ diag.nombre }}</span>
        </label>
      </div>
    </div>

    <!-- Navegación -->
    <div class="mt-8 flex justify-between">
      <button @click="emit('prev')" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">← Anterior</button>
      <button @click="emit('next')" class="px-4 py-2 bg-[#2D2B5B] text-white rounded hover:bg-[#53C6D3]">Siguiente →</button>
    </div>
  </div>
</template>
