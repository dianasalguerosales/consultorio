<script setup>
import { computed, ref } from 'vue'
import ModalCapa from '@/Components/ModalCapa.vue'
import { fechaLarga } from '@/Utils/fechas'
import {
  NIVELES,
  OBSERVACION,
  VALORES,
  contarObservacion,
  esDeficiente,
  nivelDe,
} from '@/Utils/anamnesis'

const props = defineProps({
  expediente: { type: Object, required: true },
})

const emit = defineEmits(['close'])

// La escala vive en @/Utils/anamnesis, compartida con Historia Clínica e
// /indicadores.

const items = computed(() => props.expediente?.anamnesis?.items ?? [])

const tieneAnamnesis = computed(() =>
  Boolean(props.expediente?.anamnesis) && items.value.length > 0
)

/* ---------- Agrupado por módulo y área ---------- */

// Solo los deficientes, para revisar rápido sin recorrer los 86 criterios.
const soloDeficientes = ref(false)

const itemsVisibles = computed(() =>
  soloDeficientes.value
    ? items.value.filter(esDeficiente)
    : items.value
)

const modulos = computed(() => {
  const mapa = new Map()

  for (const item of itemsVisibles.value) {
    if (!item.criterio) continue

    const { modulo, area } = item.criterio

    if (!mapa.has(modulo)) mapa.set(modulo, new Map())
    const areas = mapa.get(modulo)

    if (!areas.has(area)) areas.set(area, [])
    areas.get(area).push(item)
  }

  // A array, con los criterios de cada área en su orden original.
  return [...mapa.entries()].map(([nombre, areas]) => ({
    nombre,
    areas: [...areas.entries()].map(([area, lista]) => ({
      nombre: area,
      items: [...lista].sort((a, b) => (a.criterio?.numero ?? 0) - (b.criterio?.numero ?? 0)),
      // Cuántos de esta área requieren atención.
      deficientes: contarObservacion(lista),
    })),
  }))
})

/* ---------- Conteos ---------- */

const conteo = computed(() => {
  const c = Object.fromEntries(VALORES.map((valor) => [valor, 0]))
  for (const item of items.value) {
    const valor = nivelDe(item.respuesta).valor
    c[valor] = (c[valor] ?? 0) + 1
  }
  return c
})

/* ---------- Datos de cabecera ---------- */

const paciente = computed(() => {
  const p = props.expediente?.paciente
  if (p?.nombre_completo) return p.nombre_completo
  if (p) return `${p.nombres ?? ''} ${p.apellidos ?? ''}`.trim()
  return `${props.expediente?.nombres ?? ''} ${props.expediente?.apellidos ?? ''}`.trim() || '—'
})

const diagnosticos = computed(() =>
  (props.expediente?.diagnosticos ?? []).map((d) => d.nombre).join(', ') || 'Sin diagnóstico'
)

const hoy = fechaLarga(new Date().toLocaleDateString('sv-SE'))

function imprimir() {
  // Al imprimir se muestran todos los criterios, no solo los filtrados: el
  // documento físico debe quedar completo.
  const filtroPrevio = soloDeficientes.value
  soloDeficientes.value = false

  // Se espera un ciclo para que el DOM ya tenga la lista completa.
  requestAnimationFrame(() => {
    window.print()
    soloDeficientes.value = filtroPrevio
  })
}
</script>

<template>
  <ModalCapa clase="anamnesis-modal" @close="emit('close')"
    panel="max-w-4xl max-h-[92vh] flex flex-col anamnesis-hoja">

      <!-- Encabezado -->
      <div class="flex items-start justify-between gap-4 px-6 py-4 border-b border-gray-200">
        <div>
          <h3 class="text-lg font-bold text-caine-azul">Anamnesis</h3>
          <!-- En papel esto lo cubre la ficha de datos de abajo, así que se
               oculta para no repetir el nombre dos veces. -->
          <p class="text-sm text-gray-500 no-imprimir">
            {{ paciente }}
            <template v-if="expediente?.codigo"> · Expediente {{ expediente.codigo }}</template>
          </p>
        </div>

        <div class="flex items-center gap-2 no-imprimir">
          <button v-if="tieneAnamnesis" type="button" @click="imprimir"
            class="inline-flex items-center gap-1 px-3 py-2 rounded-md bg-caine-azul text-white
                   text-sm font-medium hover:opacity-90 transition">
            <span class="material-icons text-base">print</span>
            Imprimir
          </button>

          <button type="button" @click="emit('close')"
            class="text-gray-400 hover:text-gray-600" aria-label="Cerrar">
            <span class="material-icons">close</span>
          </button>
        </div>
      </div>

      <!-- Datos que solo aparecen en el papel -->
      <div class="hidden solo-imprimir px-6 pt-4 text-sm">
        <p><strong>Paciente:</strong> {{ paciente }}</p>
        <p><strong>Expediente:</strong> {{ expediente?.codigo ?? '—' }}</p>
        <p><strong>Diagnósticos:</strong> {{ diagnosticos }}</p>
        <p><strong>Motivo de consulta:</strong> {{ expediente?.motivo_consulta || '—' }}</p>
        <p><strong>Impreso el:</strong> {{ hoy }}</p>
      </div>

      <div class="overflow-y-auto px-6 py-4 anamnesis-cuerpo">
        <p v-if="!tieneAnamnesis" class="py-10 text-center text-sm text-gray-400">
          Este expediente todavía no tiene una anamnesis registrada.
        </p>

        <template v-else>
          <!-- Resumen y filtro -->
          <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
            <div class="flex flex-wrap items-center gap-2 text-xs">
              <span v-for="valor in VALORES" :key="valor"
                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full font-medium"
                :class="nivelDe(valor).clase">
                <span class="inline-block w-2 h-2 rounded-full"
                  :style="{ backgroundColor: nivelDe(valor).punto }"></span>
                {{ conteo[valor] }} {{ nivelDe(valor).etiqueta }}
              </span>
              <span class="text-gray-400">de {{ items.length }} criterios</span>
            </div>

            <label class="inline-flex items-center gap-2 text-sm text-gray-600 no-imprimir">
              <input type="checkbox" v-model="soloDeficientes"
                class="rounded border-gray-300 text-caine-azul focus:ring-caine-azul" />
              Ver solo lo que requiere atención
            </label>
          </div>

          <p v-if="soloDeficientes && !modulos.length"
            class="py-8 text-center text-sm text-gray-400">
            No hay criterios en Observación ni En desarrollo.
          </p>

          <!-- Módulos -->
          <div v-for="modulo in modulos" :key="modulo.nombre" class="mb-7 anamnesis-modulo">
            <h4 class="text-sm font-bold text-caine-azul pb-2 mb-3 border-b border-gray-200">
              {{ modulo.nombre }}
            </h4>

            <div v-for="area in modulo.areas" :key="area.nombre" class="mb-4 anamnesis-area">
              <div class="flex items-baseline justify-between gap-3 mb-1.5">
                <h5 class="text-xs font-semibold text-gray-500 uppercase tracking-wide">
                  {{ area.nombre }}
                </h5>
                <span v-if="area.deficientes" class="text-xs shrink-0"
                  :style="{ color: NIVELES[OBSERVACION].texto }">
                  {{ area.deficientes }} en observación
                </span>
              </div>

              <table class="w-full text-sm">
                <tbody>
                  <tr v-for="item in area.items" :key="item.id"
                    class="border-b border-gray-50 last:border-0">
                    <td class="py-1.5 pr-3 align-top text-gray-400 tabular-nums w-8">
                      {{ item.criterio?.numero }}.
                    </td>
                    <td class="py-1.5 pr-3 align-top text-gray-700">
                      {{ item.criterio?.descripcion }}
                    </td>
                    <td class="py-1.5 align-top text-right whitespace-nowrap w-32">
                      <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-xs font-medium"
                        :class="nivelDe(item.respuesta).clase">
                        <span class="inline-block w-1.5 h-1.5 rounded-full"
                          :style="{ backgroundColor: nivelDe(item.respuesta).punto }"></span>
                        {{ nivelDe(item.respuesta).etiqueta }}
                      </span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Observaciones -->
          <div v-if="expediente?.anamnesis?.observaciones"
            class="pt-4 border-t border-gray-200 anamnesis-area">
            <h4 class="text-sm font-bold text-caine-azul mb-2">Observaciones</h4>
            <p class="text-sm text-gray-700 whitespace-pre-line">
              {{ expediente.anamnesis.observaciones }}
            </p>
          </div>

          <!-- Firma, solo en papel -->
          <div class="hidden solo-imprimir mt-10 pt-8">
            <div class="flex justify-between gap-12">
              <div class="flex-1 border-t border-gray-400 pt-1 text-xs text-gray-600">
                Firma del terapeuta
              </div>
              <div class="flex-1 border-t border-gray-400 pt-1 text-xs text-gray-600">
                Firma del encargado
              </div>
            </div>
          </div>
        </template>
      </div>
  </ModalCapa>
</template>

<style>
/* En pantalla el modal es una capa flotante; en papel es la única hoja, así que
   se saca del flujo modal y se deja fluir para que pagine bien. */
@media print {
  body * {
    visibility: hidden;
  }

  .anamnesis-modal,
  .anamnesis-modal * {
    visibility: visible;
  }

  .anamnesis-modal {
    position: absolute !important;
    inset: 0 !important;
    display: block !important;
    padding: 0 !important;
    z-index: auto;
  }

  .anamnesis-hoja {
    position: static !important;
    max-width: none !important;
    max-height: none !important;
    box-shadow: none !important;
    border-radius: 0 !important;
    display: block !important;
  }

  /* Sin scroll interno: si queda, el navegador imprime solo la parte visible. */
  .anamnesis-cuerpo {
    overflow: visible !important;
    max-height: none !important;
  }

  .no-imprimir,
  .modal-fondo {
    display: none !important;
  }

  .solo-imprimir {
    display: block !important;
  }

  /* Un módulo o un área no se parte a la mitad entre dos páginas. */
  .anamnesis-modulo {
    break-inside: avoid;
    page-break-inside: avoid;
  }

  .anamnesis-area {
    break-inside: avoid;
    page-break-inside: avoid;
  }

  /* Los fondos de color se pierden al imprimir, así que la etiqueta de nivel
     necesita su propio borde para seguir distinguiéndose. */
  .anamnesis-modal [class*="rounded"] {
    print-color-adjust: exact;
    -webkit-print-color-adjust: exact;
  }

  @page {
    margin: 14mm;
  }
}
</style>
