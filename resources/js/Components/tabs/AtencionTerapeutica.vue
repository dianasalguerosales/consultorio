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
      <!-- Terapias -->
      <tr>
        <td class="p-2 border font-semibold bg-gray-100 w-1/5">Terapias</td>
        <td class="p-2 border w-4/5">
          <div class="space-y-2">
            <label
              v-for="terapia in expediente?.terapias || []"
              :key="terapia.id"
              class="flex items-center space-x-2 text-gray-800"
            >
              <input type="checkbox" checked disabled />
              <span>{{ terapia.nombre }}</span>
            </label>
          </div>
        </td>
      </tr>

      <!-- Objetivos terapéuticos -->
      <tr>
        <td class="p-2 border font-semibold bg-gray-100">Objetivos terapéuticos</td>
        <td class="p-2 border">{{ expediente?.objetivos_terapeuticos || 'No definidos' }}</td>
      </tr>

      <!-- Planificación terapéutica -->
      <tr>
        <td class="p-2 border font-semibold bg-gray-100">Planificación terapéutica</td>
        <td class="p-2 border">{{ expediente?.planificacion_terapeutica || 'Pendiente' }}</td>
      </tr>
    </tbody>
  </table>
</template>
