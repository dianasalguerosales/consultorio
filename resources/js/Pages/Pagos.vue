<script setup>
import { ref, computed } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import RegistrarPagoModal from '@/Components/RegistrarPagoModal.vue'
import { avatarPaciente } from '@/Utils/avatares'
import { fecha } from '@/Utils/fechas'

const props = defineProps({
  filas: { type: Array, default: () => [] },
  rango: { type: Object, required: true },
  metodos: { type: Array, default: () => [] },
  totales: { type: Object, required: true },
  puedeRegistrar: { type: Boolean, default: false },
})

/* ---------- Filtro de fechas: el único que va al servidor ---------- */

const desde = ref(props.rango.desde)
const hasta = ref(props.rango.hasta)

function consultar() {
  router.get('/pagos', { desde: desde.value, hasta: hasta.value }, {
    preserveState: true,
    preserveScroll: true,
  })
}

/* ---------- Filtros de la tabla: se aplican sin consultar ---------- */

const search = ref('')
const estadoFiltro = ref('todos')

const ESTADOS = {
  pendiente: { etiqueta: 'Pendiente de pago', clase: 'bg-orange-100 text-orange-700' },
  parcial: { etiqueta: 'Pago parcial', clase: 'bg-yellow-100 text-yellow-800' },
  pagado: { etiqueta: 'Pagado', clase: 'bg-green-100 text-green-700' },
}

// La base traía pagos de antes de este módulo: un estado desconocido se
// muestra tal cual en vez de romper la tabla.
const estado = (clave) => ESTADOS[clave] ?? { etiqueta: clave, clase: 'bg-gray-100 text-gray-600' }

const filasFiltradas = computed(() => {
  const texto = search.value.toLowerCase()

  return props.filas.filter((f) => {
    if (estadoFiltro.value !== 'todos' && f.estado !== estadoFiltro.value) return false

    return `${f.paciente} ${f.servicio} ${f.atiende} ${f.numero_autorizacion ?? ''}`
      .toLowerCase()
      .includes(texto)
  })
})

const quetzales = (n) => `Q${Number(n ?? 0).toFixed(2)}`

/* ---------- Modal ---------- */

const filaEnEdicion = ref(null)
</script>

<template>

  <Head title="Pagos" />
  <div class="bg-white rounded-lg shadow-md p-8 w-full">
    <!-- Encabezado -->
    <div class="flex flex-wrap justify-between items-center gap-3 mb-6">
      <div>
        <h2 class="text-2xl font-bold text-[#2D2B5B]">Pagos</h2>
        <p class="text-sm text-gray-500">Citas del {{ fecha(rango.desde) }} al {{ fecha(rango.hasta) }}</p>
      </div>
      <div class="relative">
        <span class="material-icons absolute left-2 top-1/2 transform -translate-y-1/2 text-gray-400">search</span>
        <input v-model="search" type="text" placeholder="Buscar..."
          class="pl-8 pr-3 py-2 border rounded-md text-md focus:ring-2 focus:ring-[#53C6D3]" />
      </div>
    </div>

    <!-- Rango del cierre: quincenal, por eso se consulta aparte -->
    <div class="flex flex-wrap items-end gap-3 mb-6 p-4 rounded-lg bg-[#FAF9F7] border border-gray-200">
      <div>
        <label class="block text-xs font-medium text-gray-500 mb-1">Desde</label>
        <input v-model="desde" type="date" class="border rounded-md px-3 py-2 focus:ring-2 focus:ring-[#53C6D3]" />
      </div>
      <div>
        <label class="block text-xs font-medium text-gray-500 mb-1">Hasta</label>
        <input v-model="hasta" type="date" class="border rounded-md px-3 py-2 focus:ring-2 focus:ring-[#53C6D3]" />
      </div>
      <button @click="consultar"
        class="inline-flex items-center px-4 py-2 bg-[#2D2B5B] text-white rounded-md hover:opacity-90">
        <span class="material-icons mr-1 text-base">search</span>
        <span>Consultar</span>
      </button>

      <!-- Estos no consultan: filtran lo que ya está en pantalla -->
      <div class="flex flex-wrap gap-2 ml-auto">
        <button v-for="op in ['todos', 'pendiente', 'parcial', 'pagado']" :key="op" @click="estadoFiltro = op"
          class="px-3 py-1.5 rounded-full text-sm border transition" :class="estadoFiltro === op
            ? 'border-[#2D2B5B] bg-[#2D2B5B] text-white'
            : 'border-gray-300 text-gray-600 hover:bg-white'">
          {{ op === 'todos' ? 'Todos' : ESTADOS[op].etiqueta }}
        </button>
      </div>
    </div>

    <!-- Resumen del período -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
      <div class="p-4 rounded-lg border border-gray-200">
        <p class="text-xs text-gray-500">Citas</p>
        <p class="text-xl font-bold text-[#2D2B5B]">{{ totales.sesiones }}</p>
      </div>
      <div class="p-4 rounded-lg border border-gray-200">
        <p class="text-xs text-gray-500">Esperado</p>
        <p class="text-xl font-bold text-[#2D2B5B]">{{ quetzales(totales.esperado) }}</p>
      </div>
      <div class="p-4 rounded-lg border border-gray-200">
        <p class="text-xs text-gray-500">Cobrado</p>
        <p class="text-xl font-bold text-green-700">{{ quetzales(totales.cobrado) }}</p>
      </div>
      <div class="p-4 rounded-lg border border-gray-200">
        <p class="text-xs text-gray-500">Saldo · {{ totales.pendientes }} sin pagar</p>
        <p class="text-xl font-bold text-orange-600">{{ quetzales(totales.saldo) }}</p>
      </div>
    </div>

    <!-- Tabla -->
    <div class="overflow-x-auto">
      <table class="w-full border border-gray-200 text-md rounded-lg">
        <thead class="bg-gray-200 text-[#2D2B5B]">
          <tr>
            <th class="px-2 py-2 text-center w-12"></th>
            <th class="px-4 py-2 text-left">Paciente</th>
            <th class="px-4 py-2 text-left">Sesión</th>
            <th class="px-4 py-2 text-left">Servicio</th>
            <th class="px-4 py-2 text-left">Cita</th>
            <th class="px-4 py-2 text-right">Precio</th>
            <th class="px-4 py-2 text-right">Pagado</th>
            <th class="px-4 py-2 text-left">Tipo de pago</th>
            <th class="px-4 py-2 text-left">Autorización</th>
            <th class="px-4 py-2 text-left">Estado</th>
            <th class="px-4 py-2 text-center">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="f in filasFiltradas" :key="f.cita_id" class="border-t hover:bg-[#FAF9F7] transition">
            <td class="px-2 py-2 text-center">
              <img :src="avatarPaciente(f.genero)" alt="avatar" class="w-8 h-8 rounded-full border inline-block" />
            </td>
            <td class="px-4 py-2 font-medium text-[#2D2B5B]">{{ f.paciente }}</td>
            <td class="px-4 py-2 whitespace-nowrap">
              {{ fecha(f.fecha) }}
              <span class="text-gray-500">{{ f.hora }}</span>
            </td>
            <td class="px-4 py-2">
              {{ f.servicio }}
              <span class="block text-sm text-gray-500">{{ f.atiende }}</span>
            </td>
            <td class="px-4 py-2">
              <span class="text-sm" :class="f.cancelada ? 'text-red-600' : 'text-gray-600'">
                {{ f.estado_cita }}
              </span>
            </td>
            <td class="px-4 py-2 text-right whitespace-nowrap">{{ quetzales(f.precio) }}</td>
            <td class="px-4 py-2 text-right whitespace-nowrap">
              {{ f.monto === null ? '—' : quetzales(f.monto) }}
            </td>
            <td class="px-4 py-2">{{ f.metodo ?? '—' }}</td>
            <td class="px-4 py-2">{{ f.numero_autorizacion || '—' }}</td>
            <td class="px-4 py-2">
              <span class="px-2 py-1 rounded-full text-sm font-medium whitespace-nowrap"
                :class="estado(f.estado).clase">
                {{ estado(f.estado).etiqueta }}
              </span>
            </td>
            <td class="px-4 py-2 text-center">
              <button v-if="puedeRegistrar" @click="filaEnEdicion = f"
                class="inline-flex items-center px-3 py-1 whitespace-nowrap"
                :class="f.pago_id ? 'text-[#53C6D3] hover:text-[#2D2B5B]' : 'text-[#74BE69] hover:text-[#1f1d3f]'">
                <span class="material-icons text-base">{{ f.pago_id ? 'edit' : 'payments' }}</span>
                <span class="ml-1">{{ f.pago_id ? 'Editar' : 'Registrar' }}</span>
              </button>
              <span v-else class="text-gray-400">—</span>
            </td>
          </tr>

          <tr v-if="!filasFiltradas.length">
            <td colspan="11" class="px-4 py-8 text-center text-gray-500">
              No hay citas en este período.
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <RegistrarPagoModal v-if="filaEnEdicion" :fila="filaEnEdicion" :metodos="metodos" @close="filaEnEdicion = null" />
  </div>
</template>

<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

export default {
  layout: AuthenticatedLayout
}
</script>
