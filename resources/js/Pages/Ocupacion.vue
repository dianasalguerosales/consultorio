<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { Head, router, Link } from '@inertiajs/vue3'
import { Chart, Tooltip, CategoryScale } from 'chart.js'
import { MatrixController, MatrixElement } from 'chartjs-chart-matrix'
import { RAMPA, SUPERFICIE, cortesDe, pasoEn } from '@/Utils/paleta'

Chart.register(MatrixController, MatrixElement, Tooltip, CategoryScale)

const props = defineProps({
  semana: { type: Object, default: () => ({}) },
  dias: { type: Array, default: () => [] },
  personal: { type: Array, default: () => [] },
})

/* ---------- Escala secuencial ---------- */

// El máximo real de la semana define los cortes, así la escala usa todo su
// rango en vez de desperdiciar pasos.
const maximo = computed(() => {
  const valores = props.personal.flatMap((p) => p.porDia.map((d) => d.citas))
  return Math.max(1, ...valores)
})

const cortes = computed(() => cortesDe(maximo.value))

// Envoltorio para no repetir los cortes en los cinco puntos que lo usan.
const pasoDe = (citas) => pasoEn(citas, cortes.value)

function estiloCelda(citas) {
  const paso = pasoDe(citas)

  if (!paso) {
    return {
      backgroundColor: SUPERFICIE.fondo,
      color: SUPERFICIE.texto,
      boxShadow: `inset 0 0 0 1px ${SUPERFICIE.borde}`,
    }
  }

  return { backgroundColor: paso.fondo, color: paso.texto }
}

/* ---------- Formato ---------- */

const horas = (minutos) => {
  if (!minutos) return '—'
  const h = Math.floor(minutos / 60)
  const m = minutos % 60
  if (!h) return `${m} min`
  return m ? `${h} h ${m} min` : `${h} h`
}

const DIAS_LARGOS = {
  'Lun.': 'lunes', 'Mar.': 'martes', 'Mié.': 'miércoles', 'Jue.': 'jueves',
  'Vie.': 'viernes', 'Sáb.': 'sábado', 'Dom.': 'domingo',
}

const diaLargo = (etiqueta) => DIAS_LARGOS[etiqueta] ?? etiqueta

/* ---------- Navegación de semana ---------- */

function irA(semana) {
  router.get('/agenda/ocupacion', { semana }, { preserveScroll: true, preserveState: true })
}

/* ---------- Desglose ---------- */

// { persona, dia|null } — con dia en null se listan las citas de toda su semana.
const seleccion = ref(null)

const desglose = computed(() => {
  if (!seleccion.value) return null

  const { persona, dia } = seleccion.value
  const citas = dia
    ? persona.citas.filter((c) => c.fecha === dia.fecha)
    : persona.citas

  return {
    persona,
    dia,
    citas,
    minutos: citas.reduce((suma, c) => suma + c.minutos, 0),
  }
})

function seleccionar(persona, dia) {
  // Volver a tocar la misma celda cierra el desglose.
  const yaEsta = seleccion.value
    && seleccion.value.persona.clave === persona.clave
    && seleccion.value.dia?.fecha === dia?.fecha

  seleccion.value = yaEsta ? null : { persona, dia }
}

const estaSeleccionada = (persona, dia) =>
  seleccion.value?.persona.clave === persona.clave
  && seleccion.value?.dia?.fecha === dia?.fecha

/* ---------- Vista: gráfica o tabla ----------
   El canvas no recibe foco de teclado ni lo leen los lectores de pantalla, así
   que la tabla no es un extra: es el modo accesible equivalente, con los mismos
   datos y las mismas celdas clicables.                                        */

const vista = ref('grafica')

watch(vista, async (modo) => {
  if (modo === 'grafica') {
    await nextTick()
    dibujar()
  }
})

/* ---------- Heatmap con Chart.js ---------- */

const lienzo = ref(null)
let grafica = null

// Chart.js apila las categorías del eje Y de abajo hacia arriba; invertir la
// lista deja a la primera persona arriba, como se lee la tabla.
const nombresEjeY = computed(() => props.personal.map((p) => p.nombre_completo).reverse())

// El eje lleva día y número ("Lun. 7"): el heatmap se lee junto al calendario y
// hay que poder ubicar la fecha sin contar columnas.
const etiquetasEjeX = computed(() => props.dias.map((d) => `${d.etiqueta} ${d.numero}`))

/**
 * Escribe el conteo dentro de cada celda. Sin esto el valor solo viviría en el
 * tooltip, y un tooltip enriquece pero nunca puede ser la única vía para leer
 * un dato; además deja la celda dependiendo solo del color.
 */
const valoresEnCeldas = {
  id: 'valoresEnCeldas',
  afterDatasetsDraw(chart) {
    const meta = chart.getDatasetMeta(0)
    const datos = chart.data.datasets[0]?.data ?? []
    const { ctx } = chart

    ctx.save()
    ctx.textAlign = 'center'
    ctx.textBaseline = 'middle'
    ctx.font = '600 13px ui-sans-serif, system-ui, sans-serif'

    meta.data.forEach((elemento, i) => {
      const punto = datos[i]
      if (!punto?.v) return

      const paso = pasoDe(punto.v)
      if (!paso) return

      const centro = elemento.getCenterPoint()
      ctx.fillStyle = paso.texto
      ctx.fillText(String(punto.v), centro.x, centro.y)
    })

    ctx.restore()
  },
}

// Alto proporcional a la cantidad de personas, más la banda del eje X: si se
// fija un alto cerrado, las etiquetas de abajo quedan cortadas.
const altoLienzo = computed(() => Math.max(160, props.personal.length * 52 + 42))

const puntos = computed(() =>
  props.personal.flatMap((persona) =>
    persona.porDia.map((celda, i) => ({
      x: etiquetasEjeX.value[i],
      y: persona.nombre_completo,
      v: celda.citas,
      minutos: celda.minutos,
      clave: persona.clave,
      indiceDia: i,
    }))
  )
)

function opcionesGrafica() {
  return {
    type: 'matrix',
    plugins: [valoresEnCeldas],
    data: {
      datasets: [{
        label: 'Citas',
        data: puntos.value,

        backgroundColor: (ctx) => {
          const paso = pasoDe(ctx.raw?.v)
          return paso ? paso.fondo : SUPERFICIE.fondo
        },
        // Sin citas la celda lleva un filete; con citas, ninguno: la separación
        // la hace el hueco entre celdas, no un borde dibujado sobre la marca.
        borderColor: (ctx) => (ctx.raw?.v ? 'transparent' : SUPERFICIE.borde),
        borderWidth: (ctx) => (ctx.raw?.v ? 0 : 1),
        borderRadius: 6,

        // Se resta el hueco de 2px de superficie entre celdas.
        width: ({ chart }) => (chart.chartArea?.width ?? 0) / Math.max(1, props.dias.length) - 3,
        height: ({ chart }) => (chart.chartArea?.height ?? 0) / Math.max(1, props.personal.length) - 3,

        hoverBackgroundColor: (ctx) => {
          const paso = pasoDe(ctx.raw?.v)
          return paso ? paso.fondo : SUPERFICIE.fondo
        },
        // El paso más oscuro de la rampa: contrasta contra cualquier celda.
        hoverBorderColor: RAMPA[RAMPA.length - 1].fondo,
        hoverBorderWidth: 2,
      }],
    },

    options: {
      responsive: true,
      maintainAspectRatio: false,
      layout: { padding: { top: 4, right: 4, bottom: 0, left: 0 } },

      scales: {
        x: {
          type: 'category',
          labels: etiquetasEjeX.value,
          position: 'top',
          offset: true,
          grid: { display: false, drawBorder: false },
          border: { display: false },
          ticks: {
            color: '#6b7280',
            font: { size: 12, weight: '500' },
            padding: 6,
          },
        },
        y: {
          type: 'category',
          labels: nombresEjeY.value,
          offset: true,
          grid: { display: false, drawBorder: false },
          border: { display: false },
          ticks: {
            color: '#374151',
            font: { size: 12, weight: '500' },
            padding: 8,
            autoSkip: false,
          },
        },
      },

      plugins: {
        legend: { display: false },
        tooltip: {
          displayColors: false,
          backgroundColor: '#111827',
          padding: 10,
          cornerRadius: 6,
          titleFont: { size: 13, weight: '600' },
          bodyFont: { size: 12 },
          callbacks: {
            // Los nombres vienen de la base: se devuelven como texto, nunca
            // como markup.
            title: (items) => {
              const d = items[0].raw
              const cantidad = `${d.v} ${d.v === 1 ? 'cita' : 'citas'}`
              return d.minutos ? `${cantidad} · ${horas(d.minutos)}` : cantidad
            },
            label: (item) => {
              const d = item.raw
              const dia = props.dias[d.indiceDia]
              return [String(d.y), `${diaLargo(dia.etiqueta)} ${dia.numero}`]
            },
          },
        },
      },

      onHover: (evento, elementos) => {
        evento.native.target.style.cursor = elementos.length ? 'pointer' : 'default'
      },

      onClick: (evento, elementos) => {
        if (!elementos.length) return

        const punto = puntos.value[elementos[0].index]
        const persona = props.personal.find((p) => p.clave === punto.clave)
        if (persona) seleccionar(persona, props.dias[punto.indiceDia])
      },
    },
  }
}

function dibujar() {
  if (!lienzo.value) return

  grafica?.destroy()
  grafica = new Chart(lienzo.value, opcionesGrafica())
}

onMounted(() => {
  if (vista.value === 'grafica') dibujar()
})

// Al cambiar de semana llegan datos nuevos por Inertia sin remontar la página.
watch(() => props.personal, () => {
  if (vista.value === 'grafica') dibujar()
}, { deep: true })

onBeforeUnmount(() => {
  grafica?.destroy()
  grafica = null
})

/* ---------- Totales de la semana ---------- */

const totalSemana = computed(() => ({
  citas: props.personal.reduce((s, p) => s + p.totales.citas, 0),
  minutos: props.personal.reduce((s, p) => s + p.totales.minutos, 0),
  personas: props.personal.filter((p) => p.totales.citas > 0).length,
}))

// Quien tiene el promedio por cita más alto: el dato que la tabla de horas
// existe para revelar.
const masLargas = computed(() => {
  const conCitas = props.personal.filter((p) => p.totales.citas > 0)
  if (!conCitas.length) return null
  return conCitas.reduce((a, b) => (b.totales.promedioMinutos > a.totales.promedioMinutos ? b : a))
})
</script>

<template>
  <Head title="Ocupación de personal" />

  <div class="p-8 max-w-7xl mx-auto">
    <div class="flex items-center justify-between mb-6">
      <h2 class="text-2xl font-bold text-caine-azul">Ocupación de personal</h2>

      <Link href="/agenda"
        class="inline-flex items-center gap-1 text-sm font-medium text-caine-azul hover:underline">
        <span class="material-icons text-base">arrow_back</span>
        Volver a la agenda
      </Link>
    </div>

    <!-- Filtros: una sola fila, arriba de todo lo que condicionan -->
    <div class="flex flex-wrap items-center gap-3 mb-6">
      <div class="inline-flex items-center rounded-lg border border-gray-200 bg-white overflow-hidden">
        <button type="button" @click="irA(semana.anterior)"
          class="px-3 py-2 text-gray-500 hover:bg-gray-50 hover:text-caine-azul transition"
          aria-label="Semana anterior">
          <span class="material-icons text-lg align-middle">chevron_left</span>
        </button>
        <span class="px-4 py-2 text-sm font-semibold text-caine-azul border-x border-gray-200 min-w-[13rem] text-center">
          {{ semana.etiqueta }}
        </span>
        <button type="button" @click="irA(semana.siguiente)"
          class="px-3 py-2 text-gray-500 hover:bg-gray-50 hover:text-caine-azul transition"
          aria-label="Semana siguiente">
          <span class="material-icons text-lg align-middle">chevron_right</span>
        </button>
      </div>

      <button v-if="!semana.esActual" type="button" @click="irA('')"
        class="px-3 py-2 text-sm font-medium rounded-lg border border-gray-200 bg-white text-gray-600 hover:text-caine-azul hover:bg-gray-50 transition">
        Esta semana
      </button>

      <p class="text-sm text-gray-500 ml-auto">
        {{ totalSemana.citas }} citas · {{ horas(totalSemana.minutos) }} ·
        {{ totalSemana.personas }} de {{ personal.length }} con agenda
      </p>
    </div>

    <!-- Heatmap -->
    <div class="bg-white shadow rounded-lg p-6 mb-6">
      <div class="flex flex-wrap items-start justify-between gap-4 mb-5">
        <div>
          <h3 class="font-bold text-caine-azul">Citas por día</h3>
          <p class="text-sm text-gray-500">
            Toque una celda para ver el detalle de ese día.
          </p>
        </div>

        <div class="flex items-center gap-4">
          <!-- Leyenda de escala: el color codifica cantidad, no identidad -->
          <div class="flex items-center gap-2">
            <span class="text-xs text-gray-400">menos</span>
            <div class="flex gap-[2px]">
              <span class="w-5 h-4 rounded-sm"
                :style="{ backgroundColor: SUPERFICIE.fondo, boxShadow: `inset 0 0 0 1px ${SUPERFICIE.borde}` }"
                title="sin citas"></span>
              <span v-for="(corte, i) in cortes" :key="i" class="w-5 h-4 rounded-sm"
                :style="{ backgroundColor: RAMPA[Math.min(i, RAMPA.length - 1)].fondo }"
                :title="corte.min === corte.max ? `${corte.min} citas` : `${corte.min} a ${corte.max} citas`"></span>
            </div>
            <span class="text-xs text-gray-400">más</span>
          </div>

          <!-- La tabla es el equivalente accesible del canvas, no un extra -->
          <div class="inline-flex rounded-md border border-gray-200 overflow-hidden text-xs font-medium">
            <button type="button" @click="vista = 'grafica'"
              :class="vista === 'grafica' ? 'bg-caine-azul text-white' : 'bg-white text-gray-500 hover:bg-gray-50'"
              class="px-3 py-1.5 transition">
              Gráfica
            </button>
            <button type="button" @click="vista = 'tabla'"
              :class="vista === 'tabla' ? 'bg-caine-azul text-white' : 'bg-white text-gray-500 hover:bg-gray-50'"
              class="px-3 py-1.5 border-l border-gray-200 transition">
              Tabla
            </button>
          </div>
        </div>
      </div>

      <div v-if="!personal.length" class="py-10 text-center text-sm text-gray-400">
        No hay terapeutas ni auxiliares registrados.
      </div>

      <template v-else>
        <!-- Gráfica -->
        <div v-show="vista === 'grafica'">
          <div :style="{ height: altoLienzo + 'px' }">
            <canvas ref="lienzo" role="img"
              :aria-label="`Mapa de calor de citas por persona y día, semana del ${semana.desde} al ${semana.hasta}. Cambie a la vista de tabla para leer los valores.`"></canvas>
          </div>
        </div>

        <!-- Tabla: mismos datos, celdas enfocables por teclado -->
        <div v-show="vista === 'tabla'" class="overflow-x-auto">
          <table class="w-full border-separate" style="border-spacing: 2px">
            <caption class="sr-only">
              Cantidad de citas por persona y día, semana del {{ semana.desde }} al {{ semana.hasta }}
            </caption>

            <thead>
              <tr>
                <th scope="col" class="text-left text-xs font-medium text-gray-400 pb-1 pr-4 w-56">
                  Persona
                </th>
                <th v-for="dia in dias" :key="dia.fecha" scope="col"
                  class="text-center text-xs font-medium pb-1"
                  :class="dia.esFinDeSemana ? 'text-gray-300' : 'text-gray-500'">
                  <span class="block">{{ dia.etiqueta }}</span>
                  <span class="block font-normal text-gray-400">{{ dia.numero }}</span>
                </th>
                <th scope="col" class="text-right text-xs font-medium text-gray-400 pb-1 pl-4 w-24">
                  Semana
                </th>
              </tr>
            </thead>

            <tbody>
              <tr v-for="persona in personal" :key="persona.clave">
                <th scope="row" class="text-left pr-4 align-middle">
                  <span class="block text-sm font-medium text-gray-700 leading-tight">
                    {{ persona.nombre_completo }}
                  </span>
                  <span class="block text-xs text-gray-400">{{ persona.rol }}</span>
                </th>

                <td v-for="(celda, i) in persona.porDia" :key="celda.fecha" class="p-0">
                  <button type="button"
                    class="w-full h-11 rounded-md text-sm font-semibold transition
                           focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-1 focus-visible:ring-caine-azul
                           hover:brightness-110"
                    :class="estaSeleccionada(persona, dias[i]) ? 'ring-2 ring-offset-1 ring-caine-azul' : ''"
                    :style="estiloCelda(celda.citas)"
                    :aria-label="`${persona.nombre_completo}, ${diaLargo(dias[i].etiqueta)} ${dias[i].numero}: ${celda.citas} citas, ${horas(celda.minutos)}`"
                    @click="seleccionar(persona, dias[i])">
                    {{ celda.citas || '' }}
                  </button>
                </td>

                <td class="pl-4 text-right align-middle">
                  <button type="button" @click="seleccionar(persona, null)"
                    class="text-sm font-semibold text-caine-azul hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-caine-azul rounded px-1"
                    :aria-label="`Ver las ${persona.totales.citas} citas de ${persona.nombre_completo} en toda la semana`">
                    {{ persona.totales.citas }}
                  </button>
                  <span class="block text-xs text-gray-400">{{ horas(persona.totales.minutos) }}</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </template>
    </div>

    <!-- Desglose del día seleccionado -->
    <div v-if="desglose" class="bg-white shadow rounded-lg p-6 mb-6">
      <div class="flex items-start justify-between gap-4 mb-4">
        <div>
          <h3 class="font-bold text-caine-azul">
            {{ desglose.persona.nombre_completo }}
          </h3>
          <p class="text-sm text-gray-500">
            <template v-if="desglose.dia">
              {{ diaLargo(desglose.dia.etiqueta) }} {{ desglose.dia.numero }} ·
            </template>
            <template v-else>
              Toda la semana ·
            </template>
            {{ desglose.citas.length }} citas · {{ horas(desglose.minutos) }}
          </p>
        </div>

        <button type="button" @click="seleccion = null"
          class="text-gray-400 hover:text-gray-600" aria-label="Cerrar detalle">
          <span class="material-icons">close</span>
        </button>
      </div>

      <p v-if="!desglose.citas.length" class="text-sm text-gray-400 py-4">
        No tiene citas en este día.
      </p>

      <div v-else class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="text-left text-xs font-medium text-gray-400 border-b border-gray-100">
              <th scope="col" class="py-2 pr-4" v-if="!desglose.dia">Fecha</th>
              <th scope="col" class="py-2 pr-4">Horario</th>
              <th scope="col" class="py-2 pr-4">Duración</th>
              <th scope="col" class="py-2 pr-4">Paciente</th>
              <th scope="col" class="py-2 pr-4">Servicio</th>
              <th scope="col" class="py-2 pr-4">Modalidad</th>
              <th scope="col" class="py-2">Estado</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="cita in desglose.citas" :key="cita.id"
              class="border-b border-gray-50 last:border-0">
              <td class="py-2 pr-4 text-gray-500 tabular-nums" v-if="!desglose.dia">{{ cita.fecha }}</td>
              <td class="py-2 pr-4 font-medium text-caine-azul tabular-nums">
                {{ cita.horaInicio }}<template v-if="cita.horaFin"> – {{ cita.horaFin }}</template>
              </td>
              <td class="py-2 pr-4 text-gray-500 tabular-nums">{{ cita.minutos }} min</td>
              <td class="py-2 pr-4 text-gray-700">{{ cita.paciente ?? '—' }}</td>
              <td class="py-2 pr-4 text-gray-500">{{ cita.servicio ?? '—' }}</td>
              <td class="py-2 pr-4 text-gray-500">{{ cita.modalidad ?? '—' }}</td>
              <td class="py-2 text-gray-500">{{ cita.estado ?? '—' }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Comparación de horas -->
    <div class="bg-white shadow rounded-lg p-6">
      <h3 class="font-bold text-caine-azul">Comparación de horas</h3>
      <p class="text-sm text-gray-500 mb-4">
        El conteo de citas no dice cuánto pesa cada agenda: aquí se ve quién lleva
        citas más largas.
        <template v-if="masLargas">
          <span class="text-gray-600 font-medium">
            {{ masLargas.nombre_completo }}
          </span>
          promedia {{ masLargas.totales.promedioMinutos }} min por cita.
        </template>
      </p>

      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="text-xs font-medium text-gray-400 border-b border-gray-100">
              <th scope="col" class="text-left py-2 pr-4">Persona</th>
              <th scope="col" class="text-left py-2 pr-4">Rol</th>
              <th scope="col" class="text-right py-2 pr-4">Citas</th>
              <th scope="col" class="text-right py-2 pr-4">Horas</th>
              <th scope="col" class="text-right py-2 pr-4">Promedio</th>
              <th scope="col" class="text-right py-2 pr-4">Más larga</th>
              <th scope="col" class="text-right py-2">Días activos</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="persona in personal" :key="persona.clave"
              class="border-b border-gray-50 last:border-0"
              :class="persona.totales.citas ? '' : 'text-gray-300'">
              <td class="py-2 pr-4 font-medium text-gray-700">{{ persona.nombre_completo }}</td>
              <td class="py-2 pr-4 text-gray-500">{{ persona.rol }}</td>
              <td class="py-2 pr-4 text-right tabular-nums text-gray-700">{{ persona.totales.citas }}</td>
              <td class="py-2 pr-4 text-right tabular-nums text-gray-700">{{ horas(persona.totales.minutos) }}</td>
              <td class="py-2 pr-4 text-right tabular-nums"
                :class="masLargas && persona.clave === masLargas.clave
                  ? 'font-semibold text-caine-azul'
                  : 'text-gray-500'">
                {{ persona.totales.promedioMinutos ? persona.totales.promedioMinutos + ' min' : '—' }}
              </td>
              <td class="py-2 pr-4 text-right tabular-nums text-gray-500">
                {{ persona.totales.maxMinutos ? persona.totales.maxMinutos + ' min' : '—' }}
              </td>
              <td class="py-2 text-right tabular-nums text-gray-500">{{ persona.totales.diasConCitas }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

export default {
  layout: AuthenticatedLayout,
}
</script>
