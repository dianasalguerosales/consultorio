<script setup>
import { computed, ref } from 'vue'
import { Head, router, Link } from '@inertiajs/vue3'
import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'
import timeGridPlugin from '@fullcalendar/timegrid'
import interactionPlugin from '@fullcalendar/interaction'
import esLocale from '@fullcalendar/core/locales/es'
import CitaModal from '@/Components/CitaModal.vue'
import { fechaCorta } from '@/Utils/fechas'
import SesionModal from '@/Components/SesionModal.vue'
import SolicitarReprogramacionModal from '@/Components/SolicitarReprogramacionModal.vue'
import ResolverReprogramacionModal from '@/Components/ResolverReprogramacionModal.vue'

const props = defineProps({
  citas: { type: Array, default: () => [] },
  rango: { type: Object, default: () => ({}) },
  permisos: { type: Object, default: () => ({}) },
  catalogos: { type: Object, default: null },
  solicitudes: { type: Array, default: () => [] },
  // La define el backend en SolicitudReprogramacion::HORAS_MINIMAS.
  horasMinimasReprogramacion: { type: Number, default: 24 },
})

/* ---------- Estados de la cita ---------- */

// El estado no pinta nada en el calendario: el color está tomado por la
// terapia, y dos códigos de color sobre el mismo evento no se leen. Acá queda
// solo la lista, que es lo que arma el filtro y el resumen del día.
const ESTADOS = [
  'Confirmada',
  'Atendida',
  'Programada',
  'Pendiente',
  'Reprogramada',
  'Cancelada',
  'Vencida',
]

const COLOR_POR_OMISION = { fondo: '#EEF0F4', borde: '#9CA3AF', texto: '#374151' }

/* ---------- Colores por terapia ---------- */

// El relleno del evento dice qué terapia es; es el único código de color del
// calendario. Son ocho tonos porque el catálogo de servicios crece: a partir
// del noveno se repiten.
const COLOR_TERAPIA = [
  { fondo: '#FCE4EE', borde: '#EE518E', texto: '#7A1E45' },
  { fondo: '#E1F4F7', borde: '#53C6D3', texto: '#1F5A62' },
  { fondo: '#E8F4E5', borde: '#74BE69', texto: '#2F5B28' },
  { fondo: '#EDE7FA', borde: '#8B70CD', texto: '#3F2E66' },
  { fondo: '#FDEEDA', borde: '#F4A654', texto: '#7A4E15' },
  { fondo: '#E4E4EF', borde: '#2D2B5B', texto: '#2D2B5B' },
  { fondo: '#DFF1EC', borde: '#3FA88F', texto: '#1E5347' },
  { fondo: '#F7E6F2', borde: '#B45BA6', texto: '#5E2354' },
]

// Las terapias que Diana pidió de un color concreto se fijan por nombre: el id
// cambia entre ambientes, el nombre no. Lo que no está acá toma un tono del
// resto de la paleta, repartido por id para que no se mueva al cambiar de rango.
const COLOR_POR_NOMBRE = {
  'Evaluación Cognitiva': COLOR_TERAPIA[0], // rosado
}

// El color que eligieron en Parámetros es un solo hex y el evento necesita
// tres tonos: el hex pinta el borde, su versión translúcida el relleno, y el
// texto va siempre en el azul de marca — con un color elegido a mano no se
// puede garantizar que un texto del mismo tono se lea.
const desdeHex = (hex) => ({ fondo: `${hex}22`, borde: hex, texto: '#2D2B5B' })

const colorTerapia = (servicioId, nombre = null, hex = null) => {
  if (hex) return desdeHex(hex)
  if (nombre && COLOR_POR_NOMBRE[nombre]) return COLOR_POR_NOMBRE[nombre]

  // Sin servicio no hay terapia que colorear, y el evento queda en gris.
  return servicioId ? COLOR_TERAPIA[Number(servicioId) % COLOR_TERAPIA.length] : COLOR_POR_OMISION
}

// Solo las terapias que aparecen en el rango cargado, igual que el selector
// de abajo: una leyenda con el catálogo entero no explicaría el calendario.
const leyendaTerapias = computed(() => {
  const vistas = new Map()

  for (const cita of props.citas) {
    const nombre = cita.extendedProps?.servicio ?? 'Sin terapia'
    if (vistas.has(nombre)) continue

    vistas.set(nombre, {
      nombre,
      color: colorTerapia(cita.extendedProps?.servicioId, nombre, cita.extendedProps?.servicioColor),
    })
  }

  return [...vistas.values()].sort((a, b) => a.nombre.localeCompare(b.nombre))
})

/* ---------- Filtros ---------- */

// Arrancan todos marcados: el filtro sirve para quitar ruido, no para tener que
// armar la vista desde cero cada vez que se entra.
const estadosVisibles = ref([...ESTADOS])

const todosLosEstados = computed({
  get: () => estadosVisibles.value.length === ESTADOS.length,
  set: (marcar) => { estadosVisibles.value = marcar ? [...ESTADOS] : [] },
})

// Al encargado le sirve filtrar por hijo, no por quién atiende: todas sus
// citas las atiende el personal del consultorio, lo que cambia es el paciente.
const esEncargado = computed(() => Boolean(props.permisos.solicitarReprogramacion))

// Se clasifica por "tipo:id" y no por id a secas: el terapeuta 1 y el auxiliar 1
// son personas distintas.
const claveDe = (cita) =>
  esEncargado.value
    ? `paciente:${cita.extendedProps?.pacienteId ?? '?'}`
    : `${cita.extendedProps?.atiendeTipo ?? '?'}:${cita.extendedProps?.atiendeId ?? '?'}`

// Solo quienes tienen citas en el rango cargado: ofrecer a alguien sin citas
// daría un calendario vacío sin explicar por qué.
const opcionesFiltro = computed(() => {
  const opciones = new Map()

  for (const cita of props.citas) {
    const clave = claveDe(cita)
    if (opciones.has(clave)) continue

    opciones.set(clave, {
      clave,
      nombre: esEncargado.value
        ? cita.extendedProps?.paciente ?? 'Sin paciente'
        : cita.extendedProps?.atiende ?? 'Sin asignar',
    })
  }

  return [...opciones.values()].sort((a, b) => a.nombre.localeCompare(b.nombre))
})

const etiquetaFiltro = computed(() => (esEncargado.value ? 'Hijo' : 'Atiende'))

const atiendeFiltro = ref('')

const citasFiltradas = computed(() =>
  props.citas.filter((cita) => {
    const estado = cita.extendedProps?.estado
    if (estado && !estadosVisibles.value.includes(estado)) return false

    return !atiendeFiltro.value || claveDe(cita) === atiendeFiltro.value
  })
)

function limpiarFiltros() {
  estadosVisibles.value = [...ESTADOS]
  atiendeFiltro.value = ''
}

const hayFiltro = computed(() =>
  Boolean(atiendeFiltro.value) || estadosVisibles.value.length !== ESTADOS.length
)

/* ---------- Eventos para FullCalendar ---------- */

const eventos = computed(() =>
  citasFiltradas.value.map((cita) => {
    const color = colorTerapia(
      cita.extendedProps?.servicioId,
      cita.extendedProps?.servicio,
      cita.extendedProps?.servicioColor,
    )

    return {
      ...cita,
      backgroundColor: color.fondo,
      borderColor: color.borde,
      textColor: color.texto,
    }
  })
)

/* ---------- Modal ---------- */

const modalAbierto = ref(false)
const citaSeleccionada = ref(null)
const slotSeleccionado = ref(null)

function nuevaCita() {
  citaSeleccionada.value = null
  slotSeleccionado.value = null
  modalAbierto.value = true
}

// Clic en un hueco libre: precarga fecha y hora de ese hueco.
function alSeleccionarHueco(info) {
  if (!props.permisos.agendar) return

  citaSeleccionada.value = null
  slotSeleccionado.value = {
    fecha: info.startStr.slice(0, 10),
    horaInicio: info.startStr.slice(11, 16),
    horaFin: info.endStr.slice(11, 16),
  }
  modalAbierto.value = true
  calendario.value?.getApi()?.unselect()
}

function alClicEvento(info) {
  if (!props.permisos.agendar) return

  const cita = props.citas.find((c) => String(c.id) === String(info.event.id))
  if (!cita) return

  citaSeleccionada.value = cita
  slotSeleccionado.value = null
  modalAbierto.value = true
}

function cerrarModal() {
  modalAbierto.value = false
  citaSeleccionada.value = null
  slotSeleccionado.value = null
}

/* ---------- Rango visible ---------- */

const calendario = ref(null)
const tituloRango = ref('')

// Al navegar de mes o semana se le pide al backend solo ese rango, en vez de
// traer todas las citas históricas de una sola vez.
function alCambiarRango(info) {
  tituloRango.value = info.view.title

  const desde = info.startStr.slice(0, 10)
  const hasta = info.endStr.slice(0, 10)

  if (desde === props.rango.desde && hasta === props.rango.hasta) return

  router.get('/agenda', { desde, hasta }, {
    preserveState: true,
    preserveScroll: true,
    replace: true,
    only: ['citas', 'rango'],
  })
}

/* ---------- Paneles laterales ---------- */

const hoy = new Date().toISOString().slice(0, 10)

const citasDeHoy = computed(() =>
  citasFiltradas.value.filter((c) => c.start?.slice(0, 10) === hoy)
)

// Conteo por estado para el "Resumen del día".
const resumenDeHoy = computed(() => {
  const conteo = {}
  for (const estado of ESTADOS) conteo[estado] = 0

  for (const cita of citasDeHoy.value) {
    const estado = cita.extendedProps?.estado
    if (estado in conteo) conteo[estado]++
  }
  return conteo
})

// Al encargado el panel es su forma de pedir reprogramaciones, así que ve
// todas sus citas futuras; al personal le alcanzan las próximas cinco porque
// trabaja sobre el calendario.
const proximasCitas = computed(() => {
  const futuras = citasFiltradas.value
    .filter((c) => c.start >= hoy)
    .sort((a, b) => a.start.localeCompare(b.start))

  return props.permisos.solicitarReprogramacion ? futuras : futuras.slice(0, 5)
})

/* ---------- Atender una cita ---------- */

const citaAtendiendo = ref(null)

// Un terapeuta no escribe las observaciones de la sesión de otro.
const puedeAtender = (cita) => {
  const yo = props.permisos.yoAtiendo

  return Boolean(props.permisos.atender)
    && yo?.tipo === 'terapeuta'
    && cita.extendedProps?.atiendeTipo === 'terapeuta'
    && Number(cita.extendedProps?.atiendeId) === Number(yo.id)
}

function atender(cita) {
  citaAtendiendo.value = cita
}

function cerrarSesionModal() {
  citaAtendiendo.value = null
}

/* ---------- Reprogramación ---------- */

const citaAReprogramar = ref(null)
const solicitudAResolver = ref(null)

// El encargado pide con 24 horas de anticipación. El backend lo valida igual:
// esto solo evita ofrecer un botón que iba a fallar.
const horasHasta = (cita) => (new Date(cita.start) - new Date()) / 36e5

const puedeSolicitar = (cita) =>
  Boolean(props.permisos.solicitarReprogramacion)
  && !cita.extendedProps?.tieneSolicitud
  && horasHasta(cita) >= props.horasMinimasReprogramacion

// Un botón que no está no explica nada: al encargado se le dice por qué.
const motivoSinReprogramar = (cita) => {
  if (!props.permisos.solicitarReprogramacion || puedeSolicitar(cita)) return null
  if (cita.extendedProps?.tieneSolicitud) return 'Reprogramación solicitada'

  return `Faltan menos de ${props.horasMinimasReprogramacion} h`
}

const opcionesCalendario = computed(() => ({
  plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
  initialView: 'timeGridWeek',
  locale: esLocale,
  headerToolbar: {
    left: 'timeGridDay,timeGridWeek,dayGridMonth',
    center: 'title',
    right: 'prev,next today',
  },
  buttonText: { today: 'Hoy', day: 'Día', week: 'Semana', month: 'Mes' },
  slotMinTime: '07:00:00',
  slotMaxTime: '19:00:00',
  allDaySlot: false,
  height: 'auto',
  expandRows: true,
  nowIndicator: true,
  weekends: true,
  firstDay: 1,
  selectable: props.permisos.agendar,
  selectMirror: true,
  events: eventos.value,
  select: alSeleccionarHueco,
  eventClick: alClicEvento,
  datesSet: alCambiarRango,
}))
</script>

<template>

  <Head title="Agenda" />

  <div class="p-8 max-w-7xl mx-auto">
    <div class="flex items-center justify-between mb-6">
      <h2 class="text-2xl font-bold text-caine-azul">Agenda</h2>

      <div class="flex items-center gap-2">
        <button v-if="permisos.agendar" type="button" @click="nuevaCita"
          class="bg-caine-celeste text-white px-6 py-3 rounded-lg font-semibold shadow hover:scale-105 transition">
          + Nueva cita
        </button>
      </div>
    </div>

    <!-- El calendario ocupa el ancho completo; los paneles bajan debajo, así
         las columnas de la semana no quedan amontonadas. -->
    <div class="space-y-6">

      <!-- Calendario -->
      <div class="bg-white shadow rounded-lg p-4">
        <!-- El estado solo filtra: el color del calendario es el de la
                 terapia, y la leyenda de abajo es la que lo explica. -->
        <div class="mb-4 pb-4 border-b border-gray-100 space-y-3">
          <div class="flex flex-wrap items-center gap-x-4 gap-y-2">
            <span class="text-xs font-semibold text-caine-azul">Estado</span>

            <label class="flex items-center gap-2 cursor-pointer">
              <input type="checkbox" v-model="todosLosEstados"
                class="rounded border-gray-300 text-caine-azul focus:ring-caine-azul" />
              <span class="text-xs font-medium text-caine-azul">Todos</span>
            </label>

            <span class="w-px h-4 bg-gray-200"></span>

            <label v-for="estado in ESTADOS" :key="estado" class="flex items-center gap-2 cursor-pointer">
              <input type="checkbox" :value="estado" v-model="estadosVisibles"
                class="rounded border-gray-300 text-caine-azul focus:ring-caine-azul" />
              <span class="text-xs text-gray-600">{{ estado }}</span>
            </label>
          </div>
          
          <div v-if="leyendaTerapias.length" class="flex flex-wrap items-center gap-x-3 gap-y-2">
            <span class="text-sm font-semibold text-caine-azul">Terapia</span>

            <!-- Cada entrada se pinta como el evento del calendario: mismo relleno
             y mismo borde, para que la equivalencia se vea sin explicarla. -->
            <span v-for="t in leyendaTerapias" :key="t.nombre"
              class="inline-flex items-center gap-2 px-3 py-1 rounded-full border text-sm font-medium"
              :style="{ backgroundColor: t.color.fondo, borderColor: t.color.borde, color: t.color.texto }">
              <span class="w-2.5 h-2.5 rounded-full shrink-0" :style="{ backgroundColor: t.color.borde }"></span>
              {{ t.nombre }}
            </span>
          </div>

          <div class="flex flex-wrap items-center gap-3">
            <!-- Con una sola opción el selector no filtra nada. -->
            <label v-if="opcionesFiltro.length > 1" class="flex items-center gap-2">
              <span class="text-xs font-medium text-caine-azul">{{ etiquetaFiltro }}</span>
              <select v-model="atiendeFiltro"
                class="text-xs rounded-md border-gray-300 py-1 focus:ring-caine-celeste focus:border-caine-celeste">
                <option value="">Todos</option>
                <option v-for="o in opcionesFiltro" :key="o.clave" :value="o.clave">
                  {{ o.nombre }}
                </option>
              </select>
            </label>

            <span class="text-xs text-gray-400">
              {{ citasFiltradas.length }} de {{ citas.length }} citas
            </span>

            <button v-if="hayFiltro" type="button" @click="limpiarFiltros"
              class="text-xs font-medium text-caine-celeste hover:underline">
              Limpiar filtros
            </button>
          </div>
        </div>

        <FullCalendar ref="calendario" :options="opcionesCalendario" />
      </div>

      <!-- Solicitudes de reprogramación esperando respuesta -->
      <div v-if="permisos.resolverReprogramacion && solicitudes.length" class="bg-white shadow rounded-lg p-5">
        <h3 class="font-bold text-[#2D2B5B] mb-1">Solicitudes de reprogramación</h3>
        <p class="text-sm text-gray-400 mb-4">{{ solicitudes.length }} esperando respuesta</p>

        <ul class="space-y-3">
          <li v-for="s in solicitudes" :key="s.id"
            class="flex flex-wrap items-center justify-between gap-3 border-l-2 border-caine-naranja pl-3">
            <div class="text-sm">
              <p class="font-medium text-[#2D2B5B]">{{ s.paciente }}</p>
              <p class="text-gray-500">{{ s.fecha }} · {{ s.hora }} · {{ s.servicio }}</p>
              <p v-if="s.motivo" class="text-gray-500 italic">«{{ s.motivo }}»</p>
            </div>

            <button type="button" @click="solicitudAResolver = s" class="inline-flex items-center gap-1 rounded-md border border-caine-azul px-3 py-1
                         text-xs font-medium text-caine-azul transition hover:bg-caine-azul hover:text-white">
              <span class="material-icons text-sm">event_available</span>
              Resolver
            </button>
          </li>
        </ul>
      </div>

      <!-- Paneles, ahora debajo del calendario y en dos columnas -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Resumen del día -->
        <div class="bg-white shadow rounded-lg p-5">
          <h3 class="font-bold text-[#2D2B5B]">Resumen del día</h3>
          <p class="text-sm text-gray-400 mb-4">{{ fechaCorta(hoy) }}</p>

          <dl class="space-y-2">
            <div v-for="estado in ESTADOS" :key="estado" class="flex items-center justify-between text-sm">
              <dt class="text-gray-600">{{ estado }}s</dt>
              <dd class="font-semibold text-[#2D2B5B]">{{ resumenDeHoy[estado] }}</dd>
            </div>
          </dl>

          <p v-if="!citasDeHoy.length" class="mt-4 text-sm text-gray-400">
            No hay citas para hoy.
          </p>
        </div>

        <!-- Próximas citas -->
        <div class="bg-white shadow rounded-lg p-5">
          <h3 class="font-bold text-[#2D2B5B] mb-4">Próximas citas</h3>

          <ul v-if="proximasCitas.length" class="divide-y divide-gray-200">
            <li v-for="cita in proximasCitas" :key="cita.id" class="text-sm border-l-2 pl-3 py-4 first:pt-0 last:pb-0"
              :style="{ borderColor: colorTerapia(cita.extendedProps?.servicioId, cita.extendedProps?.servicio, cita.extendedProps?.servicioColor).borde }">
              <!-- Fecha y acción en la misma línea -->
              <div class="flex items-center justify-between gap-2">
                <p class="text-gray-500">
                  {{ fechaCorta(cita.start) }} · {{ cita.extendedProps?.horaInicio }}
                </p>

                <button v-if="puedeSolicitar(cita)" type="button" @click="citaAReprogramar = cita" class="inline-flex shrink-0 items-center gap-1 rounded-md border
                             border-caine-celeste px-2 py-1 text-xs font-medium text-caine-celeste
                             transition hover:bg-caine-celeste hover:text-white">
                  <span class="material-icons text-sm">event_repeat</span>
                  Reprogramar
                </button>

                <span v-else-if="motivoSinReprogramar(cita)" class="shrink-0 text-xs text-gray-400">
                  {{ motivoSinReprogramar(cita) }}
                </span>

                <button v-if="puedeAtender(cita)" type="button" @click="atender(cita)" class="inline-flex shrink-0 items-center gap-1 rounded-md border
                             border-caine-azul px-2 py-1 text-xs font-medium text-caine-azul
                             transition hover:bg-caine-azul hover:text-white">
                  <span class="material-icons text-sm">edit_note</span>
                  {{ cita.extendedProps?.sesion ? 'Ver observaciones' : 'Atender' }}
                </button>
              </div>

              <p class="text-[#2D2B5B] font-medium">
                {{ cita.extendedProps?.paciente }}

                <!-- Atendida = ya tiene sesión registrada. -->
                <span v-if="cita.extendedProps?.sesion" class="ml-1 align-middle inline-flex items-center gap-0.5 rounded
                             bg-caine-verde/15 px-1.5 py-0.5 text-xs font-medium text-[#2F5B28]">
                  <span class="material-icons text-xs">check</span>
                  Atendida
                </span>
              </p>
              <p class="text-gray-500">{{ cita.extendedProps?.servicio }}</p>
              <p class="text-xs text-gray-400">{{ cita.extendedProps?.atiende }}</p>
            </li>
          </ul>

          <p v-else class="text-sm text-gray-400">No hay citas próximas.</p>
        </div>
      </div>
    </div>

    <CitaModal v-if="permisos.agendar" :show="modalAbierto" :catalogos="catalogos" :cita="citaSeleccionada"
      :hueco="slotSeleccionado" :permisos="permisos" @close="cerrarModal" />

    <SesionModal v-if="citaAtendiendo" :cita="citaAtendiendo" @close="cerrarSesionModal" />

    <SolicitarReprogramacionModal v-if="citaAReprogramar" :cita="citaAReprogramar" @close="citaAReprogramar = null" />

    <ResolverReprogramacionModal v-if="solicitudAResolver" :solicitud="solicitudAResolver"
      @close="solicitudAResolver = null" />
  </div>
</template>

<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

export default {
  layout: AuthenticatedLayout,
}
</script>

<style>
/* FullCalendar trae su propio look; aquí solo se alinea con la paleta caine. */
.fc .fc-button-primary {
  background-color: #fff;
  border-color: #d1d5db;
  color: #2d2b5b;
  font-weight: 500;
  text-transform: capitalize;
}

.fc .fc-button-primary:not(:disabled):hover {
  background-color: #f4f6f9;
  border-color: #2d2b5b;
}

.fc .fc-button-primary:not(:disabled).fc-button-active {
  background-color: #2d2b5b;
  border-color: #2d2b5b;
  color: #fff;
}

.fc .fc-toolbar-title {
  font-size: 1rem;
  font-weight: 600;
  color: #2d2b5b;
}

.fc .fc-col-header-cell-cushion,
.fc .fc-timegrid-axis-cushion,
.fc .fc-timegrid-slot-label-cushion {
  color: #6b7280;
  font-size: 0.75rem;
  text-decoration: none;
}

.fc .fc-event {
  border-left-width: 3px;
  font-size: 0.75rem;
  padding: 1px 3px;
  cursor: pointer;
}

.fc .fc-day-today {
  background-color: #fafaf7 !important;
}
</style>
