<script setup>
import { computed, ref } from 'vue'
import { Head } from '@inertiajs/vue3'
import { fecha } from '@/Utils/fechas'
import AplicarEvaluacionModal from '@/Components/AplicarEvaluacionModal.vue'

const props = defineProps({
  aplicadas: { type: Array, default: () => [] },
  // pruebas y encargado solo consultan.
  puedeAplicar: { type: Boolean, default: false },
  evaluaciones: { type: Array, default: () => [] },
  expedientes: { type: Array, default: () => [] },
})

/* ---------- Filtros ---------- */

const busqueda = ref('')
const filtroEvaluacion = ref('')

const filtradas = computed(() =>
  props.aplicadas.filter((a) => {
    if (filtroEvaluacion.value && a.evaluacion_id !== Number(filtroEvaluacion.value)) return false

    const texto = `${a.paciente} ${a.codigo} ${a.evaluacion}`.toLowerCase()
    return texto.includes(busqueda.value.toLowerCase())
  })
)

/* ---------- Aplicar ---------- */

const aplicando = ref(false)
</script>

<template>
  <Head title="Evaluaciones" />

  <div class="p-8 max-w-7xl mx-auto">
    <h2 class="text-2xl font-bold text-caine-azul mb-6">Evaluaciones</h2>

    <div v-if="puedeAplicar" class="mb-6 flex justify-end">
      <button type="button" @click="aplicando = true"
        class="bg-caine-celeste text-white px-6 py-3 rounded-lg font-semibold shadow hover:scale-105 transition">
        + Aplicar evaluación
      </button>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
      <!-- Filtros -->
      <div class="flex flex-wrap items-center gap-3 mb-4">
        <input v-model="busqueda" type="text" placeholder="Buscar por paciente, expediente o evaluación"
          class="flex-1 min-w-[16rem] border rounded-md px-3 py-2 text-sm
                 focus:ring-caine-celeste focus:border-caine-celeste" />

        <select v-model="filtroEvaluacion"
          class="border rounded-md px-3 py-2 text-sm focus:ring-caine-celeste focus:border-caine-celeste">
          <option value="">Todas las evaluaciones</option>
          <option v-for="ev in evaluaciones" :key="ev.id" :value="ev.id">{{ ev.nombre }}</option>
        </select>

        <span class="text-sm text-gray-400">
          {{ filtradas.length }} de {{ aplicadas.length }}
        </span>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full border border-gray-200 text-sm rounded-lg">
          <thead class="bg-gray-100 text-caine-azul">
            <tr>
              <th class="px-4 py-2 text-left">Paciente</th>
              <th class="px-4 py-2 text-left">Expediente</th>
              <th class="px-4 py-2 text-left">Evaluación</th>
              <th class="px-4 py-2 text-left">Fecha de aplicación</th>
              <th class="px-4 py-2 text-center">Acciones</th>
            </tr>
          </thead>

          <tbody>
            <tr v-for="a in filtradas" :key="a.id" class="border-t hover:bg-[#FAF9F7] transition">
              <td class="px-4 py-2 font-medium text-caine-azul">{{ a.paciente }}</td>
              <td class="px-4 py-2 text-gray-700">{{ a.codigo }}</td>
              <td class="px-4 py-2 text-gray-700">{{ a.evaluacion }}</td>
              <td class="px-4 py-2 text-gray-700">{{ fecha(a.fecha) }}</td>
              <td class="px-4 py-2 text-center">
                <!-- Ver e Imprimir quedan pendientes: necesitan las tablas de
                     respuestas, interpretación y resultados. -->
                <button type="button" disabled
                  class="inline-flex items-center gap-1 px-3 py-1 text-gray-300 cursor-not-allowed">
                  <span class="material-icons text-base">assignment</span>
                  Ver
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <p v-if="!filtradas.length" class="py-10 text-center text-sm text-gray-400">
        {{ aplicadas.length ? 'Ninguna evaluación coincide con el filtro.' : 'Todavía no se ha aplicado ninguna evaluación.' }}
      </p>
    </div>

    <AplicarEvaluacionModal v-if="aplicando && puedeAplicar" :evaluaciones="evaluaciones"
      :expedientes="expedientes" @close="aplicando = false" />
  </div>
</template>

<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

export default { layout: AuthenticatedLayout }
</script>
