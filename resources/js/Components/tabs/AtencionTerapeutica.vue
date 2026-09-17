<script setup>
import { computed } from 'vue'
import { fecha } from '@/Utils/fechas'

const props = defineProps({
  expediente: {
    type: Object,
    required: true
  }
})

// El récord de lo que el niño ya llevó, no lo que se le planificó al ingresar:
// sale de sus citas atendidas. Lo arma App\Expedientes\TerapiasUsadas.
const terapias = computed(() => props.expediente?.terapias_usadas ?? [])
</script>

<template>
  <table class="w-full border-collapse bg-white shadow-sm rounded-md">
    <tbody>
      <!-- Terapias que el paciente ha llevado -->
      <tr>
        <td class="p-2 border font-semibold bg-gray-100 w-1/5 align-top">Terapias</td>
        <td class="p-2 border w-4/5">
          <ul v-if="terapias.length" class="space-y-1">
            <li v-for="t in terapias" :key="t.nombre"
              class="flex flex-wrap items-baseline justify-between gap-x-4 text-gray-800">
              <span>{{ t.nombre }}</span>
              <span class="text-sm text-gray-500">
                {{ t.citas }} {{ t.citas === 1 ? 'cita' : 'citas' }} · última {{ fecha(t.ultima) }}
              </span>
            </li>
          </ul>
          <span v-else class="text-gray-400">Sin terapias registradas</span>
        </td>
      </tr>
    </tbody>
  </table>
</template>
