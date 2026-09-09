<script setup>
import { computed, ref, watch } from 'vue'
import { Head, router } from '@inertiajs/vue3'

const props = defineProps({
  catalogo: { type: Array, default: () => [] },
  seleccion: { type: String, default: null },
  resultado: { type: Object, default: null },
})

/* ---------- Informe elegido ---------- */

const clave = ref(props.seleccion ?? '')
const columnas = ref([])
const filtros = ref({})

const definicion = computed(() =>
  props.catalogo.find((i) => i.clave === clave.value) ?? null
)

// Al cambiar de informe se parte de todas las columnas marcadas y sin filtros:
// las de un informe no significan nada en otro.
watch(clave, () => {
  columnas.value = definicion.value?.columnas.map((c) => c.clave) ?? []
  filtros.value = {}
}, { immediate: true })

const todasLasColumnas = computed({
  get: () => columnas.value.length === (definicion.value?.columnas.length ?? 0),
  set: (marcar) => {
    columnas.value = marcar ? definicion.value.columnas.map((c) => c.clave) : []
  },
})

/* ---------- Consulta y descarga ---------- */

// Los filtros vacíos no se mandan: ensucian la URL y el backend los ignora.
const parametros = computed(() => {
  const limpios = {}
  for (const [k, v] of Object.entries(filtros.value)) {
    if (v !== '' && v !== null && v !== false) limpios[k] = v
  }

  return { informe: clave.value, columnas: columnas.value, filtros: limpios }
})

function consultar() {
  if (!clave.value || !columnas.value.length) return

  router.get('/informes', parametros.value, {
    preserveState: true,
    preserveScroll: true,
    only: ['resultado', 'seleccion'],
  })
}

function descargar() {
  const query = new URLSearchParams()
  query.set('informe', clave.value)
  columnas.value.forEach((c) => query.append('columnas[]', c))
  for (const [k, v] of Object.entries(parametros.value.filtros)) {
    query.append(`filtros[${k}]`, v)
  }

  window.location.href = `/informes/exportar?${query.toString()}`
}
</script>

<template>
  <Head title="Informes" />

  <div class="p-8 max-w-7xl mx-auto">
    <h2 class="text-2xl font-bold text-caine-azul mb-6">Informes</h2>

    <!-- Elegir informe -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
      <label class="block text-sm font-medium text-caine-azul mb-2">Base de informes</label>
      <select v-model="clave"
        class="block w-full border rounded-md px-3 py-2 focus:ring-caine-celeste focus:border-caine-celeste">
        <option value="">Seleccione un informe...</option>
        <option v-for="i in catalogo" :key="i.clave" :value="i.clave">{{ i.nombre }}</option>
      </select>

      <template v-if="definicion">
        <p class="mt-3 text-sm text-gray-600">{{ definicion.descripcion }}</p>
        <p class="mt-1 text-xs text-gray-400">Tablas: {{ definicion.tablas }}</p>
      </template>
    </div>

    <!-- Columnas y filtros -->
    <div v-if="definicion" class="bg-white rounded-lg shadow-md p-6 mb-6 space-y-6">
      <div>
        <div class="flex items-center justify-between mb-3">
          <h3 class="text-sm font-bold text-caine-azul">Columnas</h3>
          <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" v-model="todasLasColumnas"
              class="rounded border-gray-300 text-caine-azul focus:ring-caine-azul" />
            <span class="text-xs text-gray-600">Todas</span>
          </label>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
          <label v-for="c in definicion.columnas" :key="c.clave"
            class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
            <input type="checkbox" :value="c.clave" v-model="columnas"
              class="rounded border-gray-300 text-caine-azul focus:ring-caine-azul" />
            {{ c.etiqueta }}
          </label>
        </div>
      </div>

      <div v-if="definicion.filtros.length">
        <h3 class="text-sm font-bold text-caine-azul mb-3">Filtros</h3>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div v-for="f in definicion.filtros" :key="f.clave">
            <label class="block text-xs font-medium text-gray-600 mb-1">{{ f.etiqueta }}</label>

            <select v-if="f.tipo === 'select'" v-model="filtros[f.clave]"
              class="block w-full border rounded-md px-3 py-2 text-sm focus:ring-caine-celeste focus:border-caine-celeste">
              <option value="">Todos</option>
              <option v-for="o in f.opciones" :key="o.valor" :value="o.valor">{{ o.etiqueta }}</option>
            </select>

            <label v-else-if="f.tipo === 'checkbox'" class="flex items-center gap-2 py-2">
              <input type="checkbox" v-model="filtros[f.clave]"
                class="rounded border-gray-300 text-caine-azul focus:ring-caine-azul" />
              <span class="text-sm text-gray-600">Sí</span>
            </label>

            <input v-else :type="f.tipo" v-model="filtros[f.clave]"
              class="block w-full border rounded-md px-3 py-2 text-sm focus:ring-caine-celeste focus:border-caine-celeste" />
          </div>
        </div>
      </div>

      <div class="flex flex-wrap items-center gap-3 pt-2 border-t border-gray-100">
        <button type="button" @click="consultar" :disabled="!columnas.length"
          class="bg-caine-azul text-white px-5 py-2 rounded-lg font-semibold hover:opacity-90 disabled:opacity-40">
          Consultar
        </button>

        <button type="button" @click="descargar" :disabled="!columnas.length"
          class="inline-flex items-center gap-1 border border-caine-azul text-caine-azul px-5 py-2
                 rounded-lg font-semibold hover:bg-caine-azul hover:text-white transition disabled:opacity-40">
          <span class="material-icons text-base">download</span>
          Descargar CSV
        </button>

        <span v-if="!columnas.length" class="text-xs text-caine-error">
          Elija al menos una columna.
        </span>
      </div>
    </div>

    <!-- Resultado -->
    <div v-if="resultado" class="bg-white rounded-lg shadow-md p-6">
      <div class="flex flex-wrap items-baseline justify-between gap-2 mb-4">
        <h3 class="font-bold text-caine-azul">{{ resultado.nombre }}</h3>
        <span class="text-sm text-gray-500">
          {{ resultado.total }} registros<template v-if="resultado.mostradas < resultado.total">,
            mostrando los primeros {{ resultado.mostradas }}</template>
        </span>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full border border-gray-200 text-sm">
          <thead class="bg-gray-100 text-caine-azul">
            <tr>
              <th v-for="h in resultado.encabezados" :key="h" class="px-3 py-2 text-left whitespace-nowrap">
                {{ h }}
              </th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(fila, i) in resultado.filas" :key="i" class="border-t hover:bg-[#FAF9F7]">
              <td v-for="(celda, j) in fila" :key="j" class="px-3 py-2 text-gray-700">{{ celda }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <p v-if="!resultado.filas.length" class="py-10 text-center text-sm text-gray-400">
        No hay registros que cumplan con los filtros.
      </p>

      <p v-else-if="resultado.mostradas < resultado.total" class="mt-3 text-xs text-gray-400">
        La descarga incluye los {{ resultado.total }} registros, no solo los que se ven acá.
      </p>
    </div>
  </div>
</template>

<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

export default { layout: AuthenticatedLayout }
</script>
