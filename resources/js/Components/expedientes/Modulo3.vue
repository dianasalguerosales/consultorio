<script setup>
const props = defineProps({
  form: Object,
  criteriosModulo3: Array
})
const emit = defineEmits(['next','prev'])
</script>

<template>
  <div class="max-w-5xl mx-auto bg-white rounded-lg shadow p-8">
    <h3 class="text-2xl font-bold text-[#2D2B5B] mb-6">
      Anamnesis - Módulo 3 (Evaluación Socioemocional)
    </h3>

    <!-- Agrupamos por área -->
    <div v-for="area in [...new Set(criteriosModulo3.map(c => c.area))]" :key="area" class="mb-8">
      <h4 class="text-lg font-semibold text-blue-700 mb-3">{{ area }}</h4>

      <!-- Tabla compacta -->
      <div class="divide-y border rounded-md">
        <div v-for="(criterio, index) in criteriosModulo3.filter(c => c.area === area)" 
             :key="criterio.id" 
             class="flex justify-between items-center px-4 py-3">
          
          <!-- Texto del criterio -->
          <span class="text-sm font-medium text-gray-700 w-2/3">
            {{ criterio.numero }}. {{ criterio.descripcion }}
          </span>

          <!-- Radios alineados -->
          <div class="flex space-x-6 w-1/3 justify-end">
            <label class="flex items-center space-x-1">
              <input type="radio" value="3" v-model="form.itemsModulo3[index].respuesta" />
              <span class="text-xs">Adecuado</span>
            </label>
            <label class="flex items-center space-x-1">
              <input type="radio" value="2" v-model="form.itemsModulo3[index].respuesta" />
              <span class="text-xs">En desarrollo</span>
            </label>
            <label class="flex items-center space-x-1">
              <input type="radio" value="1" v-model="form.itemsModulo3[index].respuesta" />
              <span class="text-xs">Observación</span>
            </label>
          </div>
        </div>
      </div>
    </div>

    <!-- Observaciones -->
    <div class="mt-6">
      <label class="block text-sm font-medium text-gray-700">Observaciones</label>
      <textarea v-model="form.observaciones" rows="3"
        class="mt-1 block w-full border rounded-md px-3 py-2 focus:ring-[#53C6D3] focus:border-[#53C6D3]"></textarea>
    </div>

    <!-- Navegación -->
    <div class="mt-8 flex justify-between">
      <button @click="emit('prev')" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">← Anterior</button>
      <button @click="emit('next')" class="px-4 py-2 bg-[#2D2B5B] text-white rounded hover:bg-[#53C6D3]">Siguiente →</button>
    </div>
  </div>
</template>
