<script setup>
const props = defineProps({
  expediente: {
    type: Object,
    required: true
  }
})
</script>

<template>
  <table class="w-full border-collapse bg-white shadow-sm rounded-md">
    <tbody>
      <!-- Evaluaciones aplicadas -->
      <tr>
        <td class="p-2 border font-semibold bg-gray-100 w-1/5">Evaluaciones aplicadas</td>
        <td class="p-2 border w-4/5">
          <div class="space-y-2">
            <label
              v-for="evaluacion in expediente?.evaluaciones || []"
              :key="evaluacion.id"
              class="flex items-center space-x-2 text-gray-800"
            >
              <input type="checkbox" checked disabled />
              <span>{{ evaluacion.nombre }}</span>
            </label>
          </div>
        </td>
      </tr>

      <!-- Resultados de evaluaciones -->
      <tr>
        <td class="p-2 border font-semibold bg-gray-100">Resultados</td>
        <td class="p-2 border">
          <ul class="list-disc pl-6 text-gray-800">
            <li v-for="resultado in expediente?.resultados_evaluacion || []" :key="resultado.id">
              {{ resultado.descripcion }}
            </li>
          </ul>
        </td>
      </tr>
    </tbody>
  </table>
</template>
