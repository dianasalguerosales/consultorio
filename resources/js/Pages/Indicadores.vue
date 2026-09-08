<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch, nextTick } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import cytoscape from 'cytoscape'

const props = defineProps({
  grafo: { type: Object, default: () => ({ nodos: [], aristas: [] }) },
  nivel: { type: String, default: 'observacion' },
  resumen: { type: Object, default: () => ({}) },
})

/* ---------- Colores ----------
   Los nodos son de dos tipos y eso sí es identidad, así que van dos hues
   categóricos: el azul de marca y el naranja caine bajado a la banda de
   luminosidad. Validados en todos los pares: ΔE 27.4 bajo protanopia y
   contraste ≥ 3:1 sobre la superficie.                                       */

const COLOR = {
  diagnostico: '#48468a',
  area: '#c17924',
  // Las aristas son el dato aquí, no decoración de fondo: van a un gris que
  // deja distinguir los grosores entre sí.
  arista: '#aab4c2',
  aristaActiva: '#48468a',
  texto: '#374151',
  textoSuave: '#6b7280',
}

/* ---------- Filtro de nivel ---------- */

function cambiarNivel(nivel) {
  router.get('/indicadores', { nivel }, {
    preserveScroll: true,
    preserveState: true,
    only: ['grafo', 'nivel', 'resumen'],
  })
}

/* ---------- Selección ---------- */

const seleccionado = ref(null)

const detalle = computed(() => {
  if (!seleccionado.value) return null

  const nodo = props.grafo.nodos.find((n) => n.id === seleccionado.value)
  if (!nodo) return null

  // Vecinos: para un diagnóstico son sus áreas deficientes; para un área, los
  // diagnósticos en que aparece.
  const vecinos = props.grafo.aristas
    .filter((a) => a.origen === nodo.id || a.destino === nodo.id)
    .map((a) => {
      const otroId = a.origen === nodo.id ? a.destino : a.origen
      const otro = props.grafo.nodos.find((n) => n.id === otroId)
      return { nodo: otro, peso: a.peso, pacientes: a.pacientes }
    })
    .filter((v) => v.nodo)
    .sort((a, b) => b.peso - a.peso)

  return { nodo, vecinos }
})

/* ---------- Grafo ---------- */

const contenedor = ref(null)
let cy = null

// El tamaño del nodo crece con la cantidad de expedientes, pero acotado: sin
// tope un nodo con muchos expedientes se come el lienzo.
const maxExpedientes = computed(() =>
  Math.max(1, ...props.grafo.nodos.map((n) => n.expedientes))
)

function tamanoDe(expedientes) {
  const t = expedientes / maxExpedientes.value
  // En dos columnas el nodo no necesita ser grande: la etiqueta va al lado y
  // un nodo gordo solo se come el espacio de las líneas.
  return 18 + Math.round(t * 16)
}

/* ---------- Posiciones ----------
   El grafo es bipartito (diagnóstico ↔ área) y las etiquetas son largas, así
   que se colocan en dos columnas en vez de dejar que un layout de fuerzas los
   acomode: con 'cose' los nodos se encimaban y las etiquetas quedaban
   ilegibles unas sobre otras.                                                */

const ANCHO = 940
const COL_IZQ = 150
const COL_DER = ANCHO - 150
const PASO_Y = 54

const posiciones = computed(() => {
  const diagnosticos = props.grafo.nodos.filter((n) => n.tipo === 'diagnostico')
  const areas = props.grafo.nodos.filter((n) => n.tipo === 'area')

  // Los diagnósticos van ordenados por cuántos expedientes tocan.
  const dxOrden = [...diagnosticos].sort((a, b) => b.expedientes - a.expedientes)
  const filaDx = new Map(dxOrden.map((n, i) => [n.id, i]))

  // Baricentro: cada área se coloca a la altura promedio de los diagnósticos a
  // los que se conecta. Es la heurística estándar para cruzar menos líneas.
  const conPeso = areas.map((area) => {
    const vecinas = props.grafo.aristas
      .filter((a) => a.destino === area.id)
      .map((a) => filaDx.get(a.origen))
      .filter((f) => f !== undefined)

    const centro = vecinas.length
      ? vecinas.reduce((s, f) => s + f, 0) / vecinas.length
      : dxOrden.length / 2

    return { area, centro }
  })

  conPeso.sort((a, b) => a.centro - b.centro || b.area.expedientes - a.area.expedientes)

  const filas = Math.max(dxOrden.length, conPeso.length)
  const alto = filas * PASO_Y

  const mapa = {}

  // Cada columna se reparte sobre todo el alto en vez de centrarse: si la
  // columna corta se agrupa en el medio, sus líneas salen muy empinadas y
  // cuesta seguirlas.
  const repartir = (lista, x, leerId) => {
    const paso = alto / Math.max(1, lista.length)

    lista.forEach((item, i) => {
      mapa[leerId(item)] = { x, y: i * paso + paso / 2 }
    })
  }

  repartir(dxOrden, COL_IZQ, (n) => n.id)
  repartir(conPeso, COL_DER, ({ area }) => area.id)

  return { mapa, alto }
})

const altoLienzo = computed(() => Math.max(420, posiciones.value.alto + 40))

function elementos() {
  return [
    ...props.grafo.nodos.map((n) => ({
      data: {
        id: n.id,
        etiqueta: n.etiqueta,
        tipo: n.tipo,
        expedientes: n.expedientes,
        tamano: tamanoDe(n.expedientes),
      },
      position: { ...(posiciones.value.mapa[n.id] ?? { x: 0, y: 0 }) },
    })),
    ...props.grafo.aristas.map((a) => ({
      data: {
        id: a.id,
        source: a.origen,
        target: a.destino,
        peso: a.peso,
        // El grosor sale del peso; el mínimo mantiene visible una coincidencia
        // de un solo expediente.
        grosor: 1.5 + (a.peso - 1) * 1.6,
      },
    })),
  ]
}

function estilos() {
  return [
    {
      selector: 'node',
      style: {
        'background-color': (el) => COLOR[el.data('tipo')] ?? COLOR.area,
        width: 'data(tamano)',
        height: 'data(tamano)',
        label: 'data(etiqueta)',
        'font-size': 11,
        'font-weight': 500,
        // El texto va en tinta, nunca en el color del dato: la identidad la
        // carga el nodo de al lado.
        color: COLOR.texto,
        'text-valign': 'center',
        'text-wrap': 'wrap',
        'text-max-width': 130,
        // Anillo del color de la superficie, para que un nodo cruzado por una
        // línea siga leyéndose separado.
        'border-width': 2,
        'border-color': '#ffffff',
        'transition-property': 'opacity',
        'transition-duration': '120ms',
      },
    },
    {
      // Los diagnósticos son el eje del grafo: rombo para distinguirlos por
      // forma y no solo por color. Su etiqueta va hacia afuera, a la
      // izquierda, así ninguna cae sobre las líneas del centro.
      selector: 'node[tipo = "diagnostico"]',
      style: {
        shape: 'diamond',
        'font-weight': 600,
        'font-size': 12,
        'text-halign': 'left',
        'text-margin-x': -6,
      },
    },
    {
      selector: 'node[tipo = "area"]',
      style: {
        'text-halign': 'right',
        'text-margin-x': 6,
      },
    },
    {
      selector: 'edge',
      style: {
        width: 'data(grosor)',
        'line-color': COLOR.arista,
        'curve-style': 'bezier',
        opacity: 0.75,
        'transition-property': 'line-color opacity',
        'transition-duration': '120ms',
      },
    },
    {
      selector: 'node.atenuado',
      style: { opacity: 0.22 },
    },
    {
      selector: 'edge.atenuado',
      style: { opacity: 0.08 },
    },
    {
      selector: 'node.activo',
      style: {
        'border-width': 3,
        'border-color': COLOR.aristaActiva,
      },
    },
    {
      selector: 'edge.activa',
      style: {
        'line-color': COLOR.aristaActiva,
        opacity: 1,
      },
    },
  ]
}

function dibujar() {
  if (!contenedor.value) return

  cy?.destroy()

  cy = cytoscape({
    container: contenedor.value,
    elements: elementos(),
    style: estilos(),
    // Posiciones ya calculadas: dos columnas, ordenadas por baricentro.
    layout: { name: 'preset', fit: false, padding: 20 },
    minZoom: 0.4,
    maxZoom: 2.5,
    wheelSensitivity: 0.2,
  })

  // El encuadre se hace a mano para poder topar el zoom: 'fit' solo agrandaba
  // los nodos hasta encimarlos cuando hay pocos.
  cy.fit(undefined, 24)
  if (cy.zoom() > 1) cy.zoom({ level: 1, position: { x: ANCHO / 2, y: posiciones.value.alto / 2 } })
  cy.center()

  cy.on('tap', 'node', (evento) => {
    const id = evento.target.id()
    seleccionado.value = seleccionado.value === id ? null : id
  })

  // Tocar el fondo limpia la selección.
  cy.on('tap', (evento) => {
    if (evento.target === cy) seleccionado.value = null
  })

  resaltar()
}

/** Atenúa lo que no toca al nodo seleccionado, sin ocultarlo. */
function resaltar() {
  if (!cy) return

  cy.elements().removeClass('atenuado activo activa')

  if (!seleccionado.value) return

  const nodo = cy.getElementById(seleccionado.value)
  if (!nodo || nodo.empty()) return

  const vecindario = nodo.closedNeighborhood()

  cy.elements().difference(vecindario).addClass('atenuado')
  nodo.addClass('activo')
  nodo.connectedEdges().addClass('activa')
}

watch(seleccionado, resaltar)

watch(() => props.grafo, async () => {
  seleccionado.value = null
  await nextTick()
  dibujar()
}, { deep: true })

onMounted(dibujar)

onBeforeUnmount(() => {
  cy?.destroy()
  cy = null
})

function reencuadrar() {
  cy?.fit(undefined, 24)
}

/* ---------- Vista de tabla (equivalente accesible del lienzo) ---------- */

const vista = ref('grafo')

watch(vista, async (modo) => {
  if (modo === 'grafo') {
    await nextTick()
    dibujar()
  }
})

// Una fila por vínculo diagnóstico → área, que es lo que el grafo dibuja.
const filas = computed(() =>
  props.grafo.aristas
    .map((a) => {
      const dx = props.grafo.nodos.find((n) => n.id === a.origen)
      const area = props.grafo.nodos.find((n) => n.id === a.destino)
      return {
        id: a.id,
        diagnostico: dx?.etiqueta ?? '—',
        area: area?.etiqueta ?? '—',
        modulo: area?.modulo ?? '—',
        peso: a.peso,
        pacientes: a.pacientes,
      }
    })
    .sort((a, b) =>
      a.diagnostico.localeCompare(b.diagnostico) || b.peso - a.peso
    )
)

const etiquetaNivel = computed(() =>
  props.nivel === 'ambos'
    ? 'Observación y En desarrollo'
    : 'solo Observación'
)
</script>

<template>
  <Head title="Indicadores" />

  <div class="p-8 max-w-7xl mx-auto">
    <h2 class="text-2xl font-bold text-caine-azul mb-6">Indicadores</h2>

    <!-- Filtros: una sola fila, arriba de todo lo que condicionan -->
    <div class="flex flex-wrap items-center gap-3 mb-6">
      <div class="inline-flex rounded-lg border border-gray-200 bg-white overflow-hidden text-sm font-medium">
        <button type="button" @click="cambiarNivel('observacion')"
          :class="nivel === 'observacion' ? 'bg-caine-azul text-white' : 'bg-white text-gray-600 hover:bg-gray-50'"
          class="px-4 py-2 transition">
          Solo Observación
        </button>
        <button type="button" @click="cambiarNivel('ambos')"
          :class="nivel === 'ambos' ? 'bg-caine-azul text-white' : 'bg-white text-gray-600 hover:bg-gray-50'"
          class="px-4 py-2 border-l border-gray-200 transition">
          Incluir En desarrollo
        </button>
      </div>

      <p class="text-sm text-gray-500 ml-auto">
        {{ resumen.expedientes }} expedientes · {{ resumen.diagnosticos }} diagnósticos ·
        {{ resumen.areas }} áreas · {{ resumen.vinculos }} vínculos
      </p>
    </div>

    <!-- Grafo -->
    <div class="bg-white shadow rounded-lg p-6 mb-6">
      <div class="flex flex-wrap items-start justify-between gap-4 mb-4">
        <div>
          <h3 class="font-bold text-caine-azul">Puntos deficientes por diagnóstico</h3>
          <p class="text-sm text-gray-500">
            Cada línea une un diagnóstico con un área que salió deficiente en la
            anamnesis; entre más gruesa, en más expedientes coinciden.
            Mostrando {{ etiquetaNivel }}.
          </p>
        </div>

        <div class="flex items-center gap-4">
          <!-- Leyenda: forma y color, nunca color solo -->
          <div class="flex items-center gap-4 text-xs">
            <span class="flex items-center gap-1.5">
              <span class="inline-block w-3 h-3 rotate-45"
                :style="{ backgroundColor: COLOR.diagnostico }"></span>
              <span class="text-gray-600">Diagnóstico</span>
            </span>
            <span class="flex items-center gap-1.5">
              <span class="inline-block w-3 h-3 rounded-full"
                :style="{ backgroundColor: COLOR.area }"></span>
              <span class="text-gray-600">Área deficiente</span>
            </span>
          </div>

          <div class="inline-flex rounded-md border border-gray-200 overflow-hidden text-xs font-medium">
            <button type="button" @click="vista = 'grafo'"
              :class="vista === 'grafo' ? 'bg-caine-azul text-white' : 'bg-white text-gray-500 hover:bg-gray-50'"
              class="px-3 py-1.5 transition">
              Grafo
            </button>
            <button type="button" @click="vista = 'tabla'"
              :class="vista === 'tabla' ? 'bg-caine-azul text-white' : 'bg-white text-gray-500 hover:bg-gray-50'"
              class="px-3 py-1.5 border-l border-gray-200 transition">
              Tabla
            </button>
          </div>
        </div>
      </div>

      <div v-if="!grafo.nodos.length" class="py-12 text-center text-sm text-gray-400">
        No hay expedientes con diagnóstico y anamnesis registrados.
      </div>

      <template v-else>
        <!-- Lienzo -->
        <div v-show="vista === 'grafo'">
          <div class="relative">
            <div ref="contenedor" class="w-full rounded-md border border-gray-100"
              :style="{ height: altoLienzo + 'px', background: '#fcfcfb' }"></div>

            <button type="button" @click="reencuadrar"
              class="absolute top-3 right-3 px-2.5 py-1.5 rounded-md bg-white/90 border border-gray-200
                     text-xs font-medium text-gray-600 hover:text-caine-azul transition">
              Reencuadrar
            </button>
          </div>

          <p class="mt-2 text-xs text-gray-400">
            Toque un nodo para aislar sus conexiones. Arrastre para mover, rueda
            para acercar. Los valores exactos están en la vista de tabla.
          </p>
        </div>

        <!-- Tabla: el lienzo no recibe foco ni lo leen los lectores de pantalla -->
        <div v-show="vista === 'tabla'" class="overflow-x-auto">
          <table class="w-full text-sm">
            <caption class="sr-only">
              Vínculos entre diagnóstico y área deficiente, mostrando {{ etiquetaNivel }}
            </caption>
            <thead>
              <tr class="text-left text-xs font-medium text-gray-400 border-b border-gray-100">
                <th scope="col" class="py-2 pr-4">Diagnóstico</th>
                <th scope="col" class="py-2 pr-4">Área deficiente</th>
                <th scope="col" class="py-2 pr-4">Módulo</th>
                <th scope="col" class="py-2 pr-4 text-right">Expedientes</th>
                <th scope="col" class="py-2">Pacientes</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="fila in filas" :key="fila.id"
                class="border-b border-gray-50 last:border-0">
                <td class="py-2 pr-4 font-medium text-gray-700">{{ fila.diagnostico }}</td>
                <td class="py-2 pr-4 text-gray-700">{{ fila.area }}</td>
                <td class="py-2 pr-4 text-gray-500">{{ fila.modulo }}</td>
                <td class="py-2 pr-4 text-right tabular-nums font-semibold text-caine-azul">
                  {{ fila.peso }}
                </td>
                <td class="py-2 text-gray-500">{{ fila.pacientes.join(', ') }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </template>
    </div>

    <!-- Detalle del nodo seleccionado -->
    <div v-if="detalle" class="bg-white shadow rounded-lg p-6">
      <div class="flex items-start justify-between gap-4 mb-4">
        <div>
          <div class="flex items-center gap-2">
            <span v-if="detalle.nodo.tipo === 'diagnostico'"
              class="inline-block w-3 h-3 rotate-45"
              :style="{ backgroundColor: COLOR.diagnostico }"></span>
            <span v-else class="inline-block w-3 h-3 rounded-full"
              :style="{ backgroundColor: COLOR.area }"></span>

            <h3 class="font-bold text-caine-azul">{{ detalle.nodo.etiqueta }}</h3>
          </div>

          <p class="text-sm text-gray-500 mt-1">
            <template v-if="detalle.nodo.tipo === 'diagnostico'">
              Diagnóstico · {{ detalle.nodo.expedientes }} expedientes ·
              {{ detalle.vecinos.length }} áreas deficientes
            </template>
            <template v-else>
              {{ detalle.nodo.modulo }} · {{ detalle.nodo.expedientes }} expedientes ·
              {{ detalle.vecinos.length }} diagnósticos
            </template>
          </p>
        </div>

        <button type="button" @click="seleccionado = null"
          class="text-gray-400 hover:text-gray-600" aria-label="Cerrar detalle">
          <span class="material-icons">close</span>
        </button>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Vecinos -->
        <div>
          <h4 class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3">
            {{ detalle.nodo.tipo === 'diagnostico' ? 'Áreas deficientes' : 'Diagnósticos asociados' }}
          </h4>

          <ul class="space-y-2">
            <li v-for="vecino in detalle.vecinos" :key="vecino.nodo.id"
              class="flex items-start justify-between gap-3 text-sm">
              <button type="button" @click="seleccionado = vecino.nodo.id"
                class="text-left text-gray-700 hover:text-caine-azul hover:underline">
                {{ vecino.nodo.etiqueta }}
                <span class="block text-xs text-gray-400">
                  {{ vecino.pacientes.join(', ') }}
                </span>
              </button>
              <span class="shrink-0 tabular-nums text-xs font-semibold text-caine-azul
                           bg-gray-50 rounded px-2 py-0.5">
                {{ vecino.peso }}
              </span>
            </li>
          </ul>
        </div>

        <!-- Criterios concretos, solo aplica a un área -->
        <div v-if="detalle.nodo.tipo === 'area' && detalle.nodo.criterios?.length">
          <h4 class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3">
            Criterios que fallaron en esta área
          </h4>

          <ul class="space-y-3">
            <li v-for="criterio in detalle.nodo.criterios" :key="criterio.numero"
              class="text-sm">
              <p class="text-gray-700">
                {{ criterio.numero }}. {{ criterio.descripcion }}
              </p>
              <ul class="mt-1 space-y-0.5">
                <li v-for="p in criterio.pacientes" :key="p.nombre + p.respuesta"
                  class="text-xs text-gray-500 flex items-center gap-1.5">
                  <span class="inline-block w-1.5 h-1.5 rounded-full"
                    :style="{ backgroundColor: p.respuesta === 1 ? '#c17924' : '#d6b48a' }"></span>
                  {{ p.nombre }}
                  <span class="text-gray-400">
                    ({{ p.respuesta === 1 ? 'Observación' : 'En desarrollo' }})
                  </span>
                </li>
              </ul>
            </li>
          </ul>
        </div>

        <!-- Pacientes, cuando es un diagnóstico -->
        <div v-else-if="detalle.nodo.tipo === 'diagnostico'">
          <h4 class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3">
            Pacientes con este diagnóstico
          </h4>
          <ul class="space-y-1">
            <li v-for="paciente in detalle.nodo.pacientes" :key="paciente"
              class="text-sm text-gray-700">
              {{ paciente }}
            </li>
          </ul>
        </div>
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
