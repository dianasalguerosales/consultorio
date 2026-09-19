<script setup>
import { ref, computed } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import TablaBase from '@/Components/TablaBase.vue'
import RegistrarPagoModal from '@/Components/RegistrarPagoModal.vue'
import PagarPaqueteModal from '@/Components/PagarPaqueteModal.vue'
import { avatarPaciente } from '@/Utils/avatares'
import { fecha } from '@/Utils/fechas'
import { confirmarEliminacion } from '@/Utils/confirmar'

const props = defineProps({
  filas: { type: Array, default: () => [] },
  paquetes: { type: Array, default: () => [] },
  rango: { type: Object, required: true },
  filtros: { type: Object, default: () => ({}) },
  metodos: { type: Array, default: () => [] },
  totales: { type: Object, required: true },
  puedeRegistrar: { type: Boolean, default: false },
  puedeAutorizar: { type: Boolean, default: false },
})

/* ---------- Filtros que van al servidor ---------- */

// Fecha de la sesión: es el rango que decide qué citas se traen.
const desde = ref(props.rango.desde)
const hasta = ref(props.rango.hasta)

// Fecha del pago y forma de pago: recortan sobre lo anterior. Usar cualquiera
// de los tres deja solo citas ya cobradas — una pendiente no tiene con qué
// compararse.
const pagoDesde = ref(props.filtros.pago_desde ?? '')
const pagoHasta = ref(props.filtros.pago_hasta ?? '')
const metodoFiltro = ref(props.filtros.metodo ?? '')

const hayFiltroDePago = computed(() =>
  Boolean(pagoDesde.value || pagoHasta.value || metodoFiltro.value)
)

function consultar() {
  router.get('/pagos', {
    desde: desde.value,
    hasta: hasta.value,
    pago_desde: pagoDesde.value,
    pago_hasta: pagoHasta.value,
    metodo: metodoFiltro.value,
  }, {
    preserveState: true,
    preserveScroll: true,
  })
}

// Deja el rango de sesión donde está: es la base de la consulta, no un filtro.
function limpiarFiltrosDePago() {
  pagoDesde.value = ''
  pagoHasta.value = ''
  metodoFiltro.value = ''
  consultar()
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

    return `${f.paciente} ${f.servicio} ${f.atiende} ${f.numero_autorizacion ?? ''} ${f.registro ?? ''} ${f.autoriza ?? ''}`
      .toLowerCase()
      .includes(texto)
  })
})

const quetzales = (n) => `Q${Number(n ?? 0).toFixed(2)}`

/* ---------- Desglose por persona ---------- */

// Hay sucursales y oficina central, y lo que las distingue es quién cobró:
// el total por persona es, en la práctica, el total por lugar.
//
// Se calcula sobre `filasFiltradas` y no sobre `filas`, para que el desglose
// diga siempre lo mismo que la tabla que se está viendo.
function desglose(etiquetaDe) {
  const acumulado = new Map()

  for (const f of filasFiltradas.value) {
    // Una cita sin cobrar no suma a nadie.
    if (f.monto === null) continue

    const nombre = etiquetaDe(f)
    const actual = acumulado.get(nombre) ?? { nombre, cobros: 0, monto: 0 }

    actual.cobros += 1
    actual.monto += f.monto
    acumulado.set(nombre, actual)
  }

  return [...acumulado.values()].sort((a, b) => b.monto - a.monto)
}

const porQuienRegistra = computed(() =>
  desglose((f) => f.registro || 'Sin registrar')
)

// Cuánto entró en efectivo, cuánto por transferencia, cuánto con tarjeta.
const porTipoDePago = computed(() =>
  desglose((f) => f.metodo || 'Sin tipo')
)

// Un pago que no pasó por un auxiliar no tiene a quién autorizarlo: se separa
// de los que sí lo esperan, que es el saldo que alguien tiene que revisar.
// Los cobros viejos, de antes de que se guardara quién registraba, no se pueden
// clasificar en ninguno de los dos: quedan aparte en vez de contarse como si no
// necesitaran autorización.
const porQuienAutoriza = computed(() =>
  desglose((f) => {
    if (f.autoriza) return f.autoriza
    if (!f.registro) return 'Sin registrar'

    return f.requiere_autorizacion ? 'Pendiente de autorizar' : 'No requiere autorización'
  })
)

/* ---------- Autorizar ---------- */

// Solo lo pide el cobro que registró un auxiliar; el resto no pasa por aquí.
const autorizando = ref(null)

function autorizar(fila) {
  if (!confirm(`¿Autorizar el pago de ${fila.paciente} por ${quetzales(fila.monto)}?`)) return

  autorizando.value = fila.pago_id

  router.post(`/pagos/${fila.pago_id}/autorizar`, {}, {
    preserveScroll: true,
    onFinish: () => (autorizando.value = null),
  })
}

/* ---------- Paquetes ---------- */

// El cliente paga el programa completo, no sesión por sesión: el reparto entre
// las citas lo hace el backend.
const paqueteACobrar = ref(null)

// Un paquete ya cobrado se sigue mostrando, para poder anularlo.
const paquetesConSaldo = computed(() => props.paquetes.filter((p) => p.saldo > 0))

function anularPaquete(paquete) {
  if (!confirmarEliminacion(
    `los cobros del paquete ${paquete.programa} de ${paquete.paciente}`
  )) return

  router.delete(`/pagos/paquetes/${paquete.id}`, { preserveScroll: true })
}

function autorizarPaquete(paquete) {
  router.post(`/pagos/paquetes/${paquete.id}/autorizar`, {}, { preserveScroll: true })
}

/* ---------- Modal ---------- */

const filaEnEdicion = ref(null)
</script>

<template>

  <Head title="Pagos" />
  <div class="bg-white rounded-lg shadow-md p-4 sm:p-8 w-full">
    <!-- Encabezado -->
    <div class="flex flex-wrap justify-between items-center gap-3 mb-6">
      <div>
        <h2 class="text-2xl font-bold text-[#2D2B5B]">Pagos</h2>
        <p class="text-sm text-gray-500">Citas del {{ fecha(rango.desde) }} al {{ fecha(rango.hasta) }}</p>
      </div>
      <div class="relative w-full sm:w-auto">
        <span class="material-icons absolute left-2 top-1/2 transform -translate-y-1/2 text-gray-400">search</span>
        <input v-model="search" type="text" placeholder="Buscar paciente, servicio o persona..."
          class="w-full sm:w-72 pl-8 pr-3 py-2 border rounded-md text-md focus:ring-2 focus:ring-[#53C6D3]" />
      </div>
    </div>

    <!-- Rango del cierre: quincenal, por eso se consulta aparte -->
    <div class="mb-6 p-4 rounded-lg bg-[#FAF9F7] border border-gray-200 space-y-4">
      <div class="flex flex-wrap items-end gap-3">
        <div class="flex-1 min-w-[9.5rem]">
          <label class="block text-xs font-medium text-gray-500 mb-1">Sesión desde</label>
          <input v-model="desde" type="date" class="w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-[#53C6D3]" />
        </div>
        <div class="flex-1 min-w-[9.5rem]">
          <label class="block text-xs font-medium text-gray-500 mb-1">Sesión hasta</label>
          <input v-model="hasta" type="date" class="w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-[#53C6D3]" />
        </div>

        <span class="hidden lg:block w-px h-10 bg-gray-300"></span>

        <div class="flex-1 min-w-[9.5rem]">
          <label class="block text-xs font-medium text-gray-500 mb-1">Pago desde</label>
          <input v-model="pagoDesde" type="date" class="w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-[#53C6D3]" />
        </div>
        <div class="flex-1 min-w-[9.5rem]">
          <label class="block text-xs font-medium text-gray-500 mb-1">Pago hasta</label>
          <input v-model="pagoHasta" type="date" class="w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-[#53C6D3]" />
        </div>
        <div class="flex-1 min-w-[9.5rem]">
          <label class="block text-xs font-medium text-gray-500 mb-1">Tipo de pago</label>
          <select v-model="metodoFiltro" class="w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-[#53C6D3]">
            <option value="">Todos</option>
            <option v-for="m in metodos" :key="m" :value="m">{{ m }}</option>
          </select>
        </div>

        <button @click="consultar"
          class="inline-flex items-center justify-center w-full sm:w-auto px-4 py-2 bg-[#2D2B5B] text-white rounded-md hover:opacity-90">
          <span class="material-icons mr-1 text-base">search</span>
          <span>Consultar</span>
        </button>
      </div>

      <!-- Con un filtro de pago puesto la tabla ya no muestra lo pendiente:
           se avisa para que nadie lea el saldo como si fuera el del período. -->
      <p v-if="hayFiltroDePago" class="flex flex-wrap items-center gap-2 text-xs text-gray-500">
        <span class="material-icons text-sm text-[#F4A654]">info</span>
        Filtrando por pago: solo se ven las citas ya cobradas dentro del rango de sesión.
        <button type="button" @click="limpiarFiltrosDePago"
          class="font-medium text-[#53C6D3] hover:underline">
          Quitar filtros de pago
        </button>
      </p>

      <!-- Estos no consultan: filtran lo que ya está en pantalla -->
      <div class="flex flex-wrap gap-2">
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

    <!-- Cuánto entró por cada quien. Sigue lo que muestra la tabla, así que
         cambia con el buscador y con los botones de estado. -->
    <div v-if="porQuienRegistra.length" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 mb-6">
      <div v-for="grupo in [
        { titulo: 'Cobrado por tipo de pago', filas: porTipoDePago },
        { titulo: 'Cobrado por quien registra', filas: porQuienRegistra },
        { titulo: 'Cobrado por quien autoriza', filas: porQuienAutoriza },
      ]" :key="grupo.titulo" class="p-4 rounded-lg border border-gray-200">
        <h3 class="text-sm font-bold text-[#2D2B5B] mb-3">{{ grupo.titulo }}</h3>

        <table class="w-full text-sm">
          <tbody>
            <tr v-for="g in grupo.filas" :key="g.nombre" class="border-b border-gray-100 last:border-0">
              <td class="py-1.5 pr-2 text-gray-700">{{ g.nombre }}</td>
              <td class="py-1.5 px-2 text-right text-gray-400 whitespace-nowrap">
                {{ g.cobros }} {{ g.cobros === 1 ? 'cobro' : 'cobros' }}
              </td>
              <td class="py-1.5 pl-2 text-right font-semibold text-[#2D2B5B] whitespace-nowrap">
                {{ quetzales(g.monto) }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Paquetes del período. Va antes de la tabla porque es como se cobra en
         el mostrador: primero el programa, y la tabla de abajo es el detalle. -->
    <div v-if="paquetes.length" class="mb-6 rounded-lg border border-gray-200 overflow-hidden">
      <div class="flex flex-wrap items-baseline justify-between gap-2 px-4 py-3 bg-[#FAF9F7] border-b border-gray-200">
        <h3 class="font-bold text-[#2D2B5B]">Paquetes del período</h3>
        <p class="text-sm text-gray-500">
          {{ paquetesConSaldo.length }} con saldo de {{ paquetes.length }}
        </p>
      </div>

      <ul class="divide-y divide-gray-100">
        <li v-for="p in paquetes" :key="p.id"
          class="flex flex-wrap items-center justify-between gap-3 px-4 py-3">

          <div class="flex items-center gap-3 min-w-0">
            <img :src="avatarPaciente(p.genero)" alt="avatar" class="w-9 h-9 rounded-full border shrink-0" />
            <div class="min-w-0">
              <p class="font-medium text-[#2D2B5B] truncate">{{ p.paciente }}</p>
              <p class="text-sm text-gray-500 truncate">
                {{ p.programa }} · {{ p.citas }} citas a {{ quetzales(p.por_cita) }}
              </p>
            </div>
          </div>

          <dl class="flex items-center gap-4 text-sm">
            <div class="text-right">
              <dt class="text-xs text-gray-500">Paquete</dt>
              <dd class="font-medium text-[#2D2B5B] whitespace-nowrap">{{ quetzales(p.precio) }}</dd>
            </div>
            <div class="text-right">
              <dt class="text-xs text-gray-500">Cobrado</dt>
              <dd class="text-green-700 whitespace-nowrap">{{ quetzales(p.cobrado) }}</dd>
            </div>
            <div class="text-right">
              <dt class="text-xs text-gray-500">Saldo</dt>
              <dd class="font-semibold whitespace-nowrap"
                :class="p.saldo > 0 ? 'text-orange-600' : 'text-gray-400'">
                {{ quetzales(p.saldo) }}
              </dd>
            </div>
          </dl>

          <div class="flex flex-wrap items-center gap-2">
            <button v-if="puedeRegistrar && p.saldo > 0" type="button" @click="paqueteACobrar = p"
              class="inline-flex items-center gap-1 rounded-md bg-[#2D2B5B] px-3 py-2 text-sm
                     font-medium text-white transition hover:opacity-90">
              <span class="material-icons text-base">payments</span>
              Cobrar paquete
            </button>

            <span v-else-if="p.saldo <= 0"
              class="inline-flex items-center gap-1 rounded-full bg-green-100 px-3 py-1 text-sm font-medium text-green-700">
              <span class="material-icons text-base">check</span>
              Pagado
            </span>

            <button v-if="puedeAutorizar && p.cobrado > 0" type="button" @click="autorizarPaquete(p)"
              class="inline-flex items-center gap-1 rounded-md border border-[#74BE69] px-3 py-2
                     text-sm font-medium text-[#74BE69] transition hover:bg-[#74BE69] hover:text-white">
              <span class="material-icons text-base">verified</span>
              Autorizar
            </button>

            <button v-if="puedeRegistrar && p.cobrado > 0" type="button" @click="anularPaquete(p)"
              class="inline-flex items-center gap-1 rounded-md px-2 py-2 text-sm text-gray-400
                     transition hover:text-caine-error" title="Anular el cobro del paquete">
              <span class="material-icons text-base">delete</span>
            </button>
          </div>
        </li>
      </ul>
    </div>

    <!-- Tabla. De lg para abajo son catorce columnas que no entran ni
         scrolleando, así que ahí se cambia por las tarjetas de más abajo. -->
    <TablaBase class="hidden lg:block">
        <thead class="bg-gray-200 text-[#2D2B5B]">
          <tr>
            <th class="px-2 py-2 text-center w-12"></th>
            <th class="px-4 py-2 text-left">Paciente</th>
            <th class="px-4 py-2 text-left">Sesión</th>
            <th class="px-4 py-2 text-left">Servicio</th>
            <th class="px-4 py-2 text-left">Cita</th>
            <th class="px-4 py-2 text-right">Precio</th>
            <th class="px-4 py-2 text-right">Pagado</th>
            <th class="px-4 py-2 text-left">Fecha de pago</th>
            <th class="px-4 py-2 text-left">Tipo de pago</th>
            <th class="px-4 py-2 text-left">N.° de documento</th>
            <th class="px-4 py-2 text-left">Registró</th>
            <th class="px-4 py-2 text-left">Autorizó</th>
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
            <td class="px-4 py-2 whitespace-nowrap">
              {{ f.fecha_pago ? fecha(f.fecha_pago) : '—' }}
            </td>
            <td class="px-4 py-2">{{ f.metodo ?? '—' }}</td>
            <td class="px-4 py-2">{{ f.numero_autorizacion || '—' }}</td>
            <td class="px-4 py-2">{{ f.registro || '—' }}</td>
            <td class="px-4 py-2 whitespace-nowrap">
              <!-- Autorizado: queda el nombre y, de título, cuándo fue. -->
              <span v-if="f.autoriza" :title="f.autorizado_en">{{ f.autoriza }}</span>

              <!-- Lo registró un auxiliar y todavía nadie lo respalda. -->
              <template v-else-if="f.requiere_autorizacion">
                <button v-if="puedeAutorizar" type="button" @click="autorizar(f)"
                  :disabled="autorizando === f.pago_id"
                  class="inline-flex items-center gap-1 rounded-md border border-[#74BE69] px-2 py-1
                         text-sm font-medium text-[#74BE69] transition
                         hover:bg-[#74BE69] hover:text-white disabled:opacity-40">
                  <span class="material-icons text-base">verified</span>
                  {{ autorizando === f.pago_id ? 'Autorizando...' : 'Autorizar' }}
                </button>

                <span v-else class="text-orange-600">Pendiente</span>
              </template>

              <!-- Lo cobró quien no necesita que lo autoricen. -->
              <span v-else class="text-gray-400">—</span>
            </td>
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
            <td colspan="14" class="px-4 py-8 text-center text-gray-500">
              No hay citas en este período.
            </td>
          </tr>
        </tbody>
    </TablaBase>

    <!-- Lo mismo en pantalla chica: una tarjeta por cita, con todos los datos
         de la fila a la vista. Nada queda fuera del borde. -->
    <div class="lg:hidden space-y-3">
      <article v-for="f in filasFiltradas" :key="f.cita_id"
        class="rounded-lg border border-gray-200 p-4">

        <!-- Quién y en qué estado -->
        <div class="flex items-start justify-between gap-3">
          <div class="flex items-center gap-3 min-w-0">
            <img :src="avatarPaciente(f.genero)" alt="avatar" class="w-9 h-9 rounded-full border shrink-0" />
            <div class="min-w-0">
              <p class="font-medium text-[#2D2B5B] truncate">{{ f.paciente }}</p>
              <p class="text-sm text-gray-500 truncate">{{ f.servicio }} · {{ f.atiende }}</p>
            </div>
          </div>

          <span class="px-2 py-1 rounded-full text-xs font-medium whitespace-nowrap shrink-0"
            :class="estado(f.estado).clase">
            {{ estado(f.estado).etiqueta }}
          </span>
        </div>

        <!-- La sesión y el dinero -->
        <dl class="grid grid-cols-2 gap-x-4 gap-y-2 mt-4 text-sm">
          <div>
            <dt class="text-xs text-gray-500">Sesión</dt>
            <dd class="text-gray-700">{{ fecha(f.fecha) }} · {{ f.hora }}</dd>
          </div>
          <div>
            <dt class="text-xs text-gray-500">Cita</dt>
            <dd :class="f.cancelada ? 'text-red-600' : 'text-gray-700'">{{ f.estado_cita }}</dd>
          </div>
          <div>
            <dt class="text-xs text-gray-500">Precio</dt>
            <dd class="text-gray-700">{{ quetzales(f.precio) }}</dd>
          </div>
          <div>
            <dt class="text-xs text-gray-500">Pagado</dt>
            <dd class="font-semibold text-[#2D2B5B]">{{ f.monto === null ? '—' : quetzales(f.monto) }}</dd>
          </div>
          <div>
            <dt class="text-xs text-gray-500">Fecha de pago</dt>
            <dd class="text-gray-700">{{ f.fecha_pago ? fecha(f.fecha_pago) : '—' }}</dd>
          </div>
          <div>
            <dt class="text-xs text-gray-500">Tipo de pago</dt>
            <dd class="text-gray-700">{{ f.metodo ?? '—' }}</dd>
          </div>
          <div>
            <dt class="text-xs text-gray-500">N.° de documento</dt>
            <dd class="text-gray-700 break-all">{{ f.numero_autorizacion || '—' }}</dd>
          </div>
          <div>
            <dt class="text-xs text-gray-500">Registró</dt>
            <dd class="text-gray-700">{{ f.registro || '—' }}</dd>
          </div>

          <div class="col-span-2">
            <dt class="text-xs text-gray-500">Autorizó</dt>
            <dd>
              <span v-if="f.autoriza" class="text-gray-700" :title="f.autorizado_en">{{ f.autoriza }}</span>

              <template v-else-if="f.requiere_autorizacion">
                <button v-if="puedeAutorizar" type="button" @click="autorizar(f)"
                  :disabled="autorizando === f.pago_id"
                  class="inline-flex items-center gap-1 rounded-md border border-[#74BE69] px-2 py-1
                         text-sm font-medium text-[#74BE69] transition
                         hover:bg-[#74BE69] hover:text-white disabled:opacity-40">
                  <span class="material-icons text-base">verified</span>
                  {{ autorizando === f.pago_id ? 'Autorizando...' : 'Autorizar' }}
                </button>

                <span v-else class="text-orange-600">Pendiente</span>
              </template>

              <span v-else class="text-gray-400">—</span>
            </dd>
          </div>
        </dl>

        <button v-if="puedeRegistrar" @click="filaEnEdicion = f"
          class="mt-4 w-full inline-flex items-center justify-center gap-1 rounded-md border px-3 py-2
                 text-sm font-medium transition"
          :class="f.pago_id
            ? 'border-[#53C6D3] text-[#53C6D3] hover:bg-[#53C6D3] hover:text-white'
            : 'border-[#74BE69] text-[#74BE69] hover:bg-[#74BE69] hover:text-white'">
          <span class="material-icons text-base">{{ f.pago_id ? 'edit' : 'payments' }}</span>
          {{ f.pago_id ? 'Editar pago' : 'Registrar pago' }}
        </button>
      </article>

      <p v-if="!filasFiltradas.length" class="py-8 text-center text-gray-500">
        No hay citas en este período.
      </p>
    </div>

    <RegistrarPagoModal v-if="filaEnEdicion" :fila="filaEnEdicion" :metodos="metodos" @close="filaEnEdicion = null" />

    <PagarPaqueteModal v-if="paqueteACobrar" :paquete="paqueteACobrar" :metodos="metodos"
      @close="paqueteACobrar = null" />
  </div>
</template>

<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

export default {
  layout: AuthenticatedLayout
}
</script>
