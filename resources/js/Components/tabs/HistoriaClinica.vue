<script setup>
import { computed, ref } from 'vue'
import AnamnesisModal from '@/Components/AnamnesisModal.vue'
import {
  NIVELES,
  OBSERVACION,
  agruparPorArea,
  contarObservacion,
  esObservacion,
} from '@/Utils/anamnesis'

const props = defineProps({
  expediente: {
    type: Object,
    required: true
  }
})

const verAnamnesis = ref(false)

const items = computed(() => props.expediente?.anamnesis?.items ?? [])

// Resume la anamnesis sin abrirla.
const enObservacion = computed(() => contarObservacion(items.value))

// No hay tabla de antecedentes: son los criterios que la anamnesis dejó en
// Observación.
const antecedentes = computed(() =>
  agruparPorArea(items.value.filter(esObservacion))
)
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
        <td class="p-2 border">
          <div v-if="items.length" class="flex flex-wrap items-center gap-3">
            <button type="button" @click="verAnamnesis = true"
              class="inline-flex items-center gap-1 text-caine-azul font-medium hover:underline">
              <span class="material-icons text-base">assignment</span>
              Ver anamnesis
            </button>

            <span class="text-sm text-gray-500">
              {{ items.length }} criterios evaluados<template v-if="enObservacion">,
              <span class="font-medium" :style="{ color: NIVELES[OBSERVACION].texto }">{{ enObservacion }} en observación</span>
              </template>
            </span>
          </div>

          <span v-else class="text-gray-400">Sin anamnesis registrada</span>
        </td>
      </tr>

      <!-- Antecedentes -->
      <tr>
        <td class="p-2 border font-semibold bg-gray-100">Antecedentes</td>
        <td class="p-2 border">
          <ul class="list-disc pl-6 text-gray-800">
            <li v-for="area in antecedentes" :key="area.nombre">
              <span class="font-medium text-caine-azul">{{ area.nombre }}</span>
              <ul class="list-[circle] pl-5">
                <li v-for="item in area.items" :key="item.id">{{ item.criterio.descripcion }}</li>
              </ul>
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

  <AnamnesisModal v-if="verAnamnesis" :expediente="expediente"
    @close="verAnamnesis = false" />
</template>
