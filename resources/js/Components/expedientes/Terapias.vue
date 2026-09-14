<script setup>
const props = defineProps({
  form: Object
})
const emit = defineEmits(['next','prev'])
</script>

<template>
  <div class="max-w-5xl mx-auto bg-white rounded-lg shadow p-8">
    <h3 class="text-2xl font-bold text-[#2D2B5B] mb-6">
      Atención Terapéutica
    </h3>

    <!-- Estos dos campos eran textareas que no guardaban nada: `expedientes` no
         tiene esas columnas y el controlador tampoco las validaba. Los
         objetivos pasaron a su propia tabla, por área y colgados del paciente,
         y se editan desde la ficha del niño. -->
    <div class="mb-6 rounded-md bg-[#FAF9F7] border border-gray-200 p-4">
      <p class="text-sm text-[#2D2B5B] font-medium">Objetivos terapéuticos</p>
      <p class="mt-1 text-sm text-gray-600">
        Se plantean por área y se revisan durante todo el tratamiento, así que
        no se llenan al abrir el expediente: van en
        <strong>Pacientes → Objetivos</strong>.
      </p>
    </div>

    <!-- Navegación -->
    <div class="mt-8 flex justify-between">
      <button @click="emit('prev')" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">← Anterior</button>
      <button @click="emit('next')" class="px-4 py-2 bg-[#2D2B5B] text-white rounded hover:bg-[#53C6D3]">
        Siguiente →
      </button>
    </div>
  </div>
</template>