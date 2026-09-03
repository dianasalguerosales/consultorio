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
      <!-- Motivo de consulta -->
      <tr>
        <td class="p-2 border font-semibold bg-gray-100 w-1/5">Motivo de consulta</td>
        <td class="p-2 border 4/5">{{ expediente?.motivo_consulta || 'Ninguno' }}</td>
      </tr>

      <!-- Anamnesis -->
      <tr>
        <td class="p-2 border font-semibold bg-gray-100">Anamnesis</td>
        <td class="p-2 border">[Abrir modal de anamnesis aquí]</td>
      </tr>

      <!-- Antecedentes -->
      <tr>
        <td class="p-2 border font-semibold bg-gray-100">Antecedentes</td>
        <td class="p-2 border">
          <ul class="list-disc pl-6 text-gray-800">
            <li v-for="item in expediente?.antecedentes || []" :key="item.id">
              {{ item.descripcion }}
            </li>
          </ul>
        </td>
      </tr>

      <!-- Diagnósticos -->
      <tr>
        <td class="p-2 border font-semibold bg-gray-100">Diagnósticos</td>
        <td class="p-2 border">
          <div class="space-y-2">
            <label
              v-for="diag in expediente?.diagnosticos || []"
              :key="diag.id"
              class="flex items-center space-x-2 text-gray-800"
            >
              <input type="checkbox" checked disabled />
              <span>{{ diag.nombre }}</span>
            </label>
          </div>
        </td>
      </tr>
    </tbody>
  </table>
</template>
