<script setup>
import { computed } from 'vue'

const props = defineProps({
  expediente: {
    type: Object,
    required: true
  }
})

// Los terapeutas cuelgan del paciente (`paciente_terapeuta`), no del
// expediente: hay que entrar por ahí.
const terapeutas = computed(() => props.expediente?.paciente?.terapeutas ?? [])

// La relación del expediente se llama `servicios`; en pantalla son las terapias.
const terapias = computed(() => props.expediente?.servicios ?? [])

// Los objetivos viven en su propia tabla, colgados del paciente y agrupados
// por terapia: de tres a cuatro por cada una.
const objetivosPorTerapia = computed(() => {
  const grupos = new Map()

  for (const o of props.expediente?.paciente?.objetivos ?? []) {
    const nombre = o.servicio?.nombre ?? 'Sin terapia'
    if (!grupos.has(nombre)) grupos.set(nombre, [])
    grupos.get(nombre).push(o)
  }

  return [...grupos.entries()]
    .map(([terapia, objetivos]) => ({ terapia, objetivos }))
    .sort((a, b) => a.terapia.localeCompare(b.terapia))
})
</script>

<template>
  <table class="w-full border-collapse bg-white shadow-sm rounded-md">
    <tbody>
      <!-- Terapeuta -->
      <tr>
        <td class="p-2 border font-semibold bg-gray-100 w-1/3 sm:w-1/5">Terapeuta</td>
        <td class="p-2 border">
          <ul v-if="terapeutas.length" class="space-y-1">
            <li v-for="t in terapeutas" :key="t.id" class="text-gray-800">
              {{ t.nombres }} {{ t.apellidos }}
              <span v-if="t.especialidad" class="text-gray-500">
                · {{ t.especialidad.nombre }}
              </span>
            </li>
          </ul>
          <span v-else class="text-gray-400">Sin terapeuta asignado</span>
        </td>
      </tr>

      <!-- Terapias -->
      <tr>
        <td class="p-2 border font-semibold bg-gray-100">Terapias</td>
        <td class="p-2 border">
          <ul v-if="terapias.length" class="space-y-1">
            <li v-for="terapia in terapias" :key="terapia.id" class="text-gray-800">
              {{ terapia.nombre }}
            </li>
          </ul>
          <span v-else class="text-gray-400">Sin terapias asignadas</span>
        </td>
      </tr>

      <!-- Objetivos, por terapia -->
      <tr>
        <td class="p-2 border font-semibold bg-gray-100">Objetivos terapéuticos</td>
        <td class="p-2 border">
          <div v-if="objetivosPorTerapia.length" class="space-y-3">
            <div v-for="grupo in objetivosPorTerapia" :key="grupo.terapia">
              <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                {{ grupo.terapia }}
              </p>
              <ol class="list-decimal pl-5 text-gray-800">
                <li v-for="o in grupo.objetivos" :key="o.id" class="whitespace-pre-line">
                  {{ o.descripcion }}
                </li>
              </ol>
            </div>
          </div>
          <span v-else class="text-gray-400">No definidos</span>
        </td>
      </tr>
    </tbody>
  </table>
</template>
