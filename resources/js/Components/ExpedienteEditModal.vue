<script setup>
import { useForm } from '@inertiajs/vue3'
import { ref } from 'vue'

// Importar subcomponentes
import DatosGenerales from './expedientes/DatosGenerales.vue'
import Modulo1 from './expedientes/Modulo1.vue'
import Modulo2 from './expedientes/Modulo2.vue'
import Modulo3 from './expedientes/Modulo3.vue'
import HistoriaClinica from './expedientes/HistoriaClinica.vue'
import Terapias from './expedientes/Terapias.vue'
import Evaluaciones from './expedientes/Evaluaciones.vue'

const props = defineProps({
  expediente: { type: Object, required: false, default: null },
  pacienteId: { type: Number, required: false, default: null },
  diagnosticosList: { type: Array, default: () => [] },
  terapiasList: { type: Array, default: () => [] },
  evaluacionesList: { type: Array, default: () => [] },
  escolaridadesList: { type: Array, default: () => [] },
  criteriosModulo1: { type: Array, default: () => [] },
  criteriosModulo2: { type: Array, default: () => [] },
  criteriosModulo3: { type: Array, default: () => [] }
})

const emit = defineEmits(['close'])
const step = ref(1)

const form = useForm({
  paciente_id: props.pacienteId || null,
  nombre_pila: props.expediente?.nombre_pila || '',
  estado: props.expediente?.estado || 'activo',
  motivo_consulta: props.expediente?.motivo_consulta || '',
  fecha_apertura: props.expediente?.fecha_apertura || '',
  modalidad: props.expediente?.modalidad || '',
  escolaridad_id: props.expediente?.escolaridad_id || null,
  observaciones_administrativas: props.expediente?.observaciones_administrativas || '',
  diagnosticos: props.expediente?.diagnosticos?.map(d => d.id) || [],
  terapias: props.expediente?.terapias?.map(t => t.id) || [],
  evaluaciones: props.expediente?.evaluaciones?.map(e => e.id) || [],
  observaciones: '',
  itemsModulo1: [],
  itemsModulo2: [],
  itemsModulo3: []
})

function nextStep() { step.value++ }
function prevStep() { step.value-- }

function saveChanges() {
  const allItems = [].concat(
    form.itemsModulo1,
    form.itemsModulo2,
    form.itemsModulo3
  )
  form.items = allItems

  if (props.expediente) {
    form.put(route('expedientes.update', props.expediente.id), {
      onSuccess: () => emit('close'),
      onError: (errors) => console.log(errors)
    })
  } else {
    form.post(route('expedientes.store'), {
      onSuccess: () => emit('close'),
      onError: (errors) => console.log(errors)
    })
  }
}

// Inicializar criterios
props.criteriosModulo1.forEach(c => form.itemsModulo1.push({ criterio_id: c.id, respuesta: null }))
props.criteriosModulo2.forEach(c => form.itemsModulo2.push({ criterio_id: c.id, respuesta: null }))
props.criteriosModulo3.forEach(c => form.itemsModulo3.push({ criterio_id: c.id, respuesta: null }))
</script>

<template>
  <div class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl w-11/12 max-w-6xl h-5/6 flex flex-col relative">
      <!-- Header -->
      <div class="flex justify-between items-center border-b p-4">
        <h2 class="text-xl font-bold text-caine-azul">
          {{ props.expediente ? 'Editar Expediente' : 'Nuevo Expediente' }}
        </h2>
        <button @click="$emit('close')" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
      </div>

      <!-- Barra de progreso -->
      <div class="px-6 pt-4">
        <div class="w-full bg-gray-200 rounded-full h-2.5 mb-2">
          <div class="bg-blue-600 h-2.5 rounded-full" :style="{ width: (step / 7 * 100) + '%' }"></div>
        </div>
        <p class="text-sm text-gray-600">Paso {{ step }} de 7</p>
      </div>

      <!-- Body dinámico -->
      <div class="p-6 flex-1 overflow-y-auto">
        <component
          :is="{
            1: DatosGenerales,
            2: Modulo1,
            3: Modulo2,
            4: Modulo3,
            5: HistoriaClinica,
            6: Terapias,
            7: Evaluaciones
          }[step]"
          :form="form"
          :criteriosModulo1="criteriosModulo1"
          :criteriosModulo2="criteriosModulo2"
          :criteriosModulo3="criteriosModulo3"
          :escolaridadesList="escolaridadesList"
          :diagnosticosList="diagnosticosList"
          :terapiasList="terapiasList"
          :evaluacionesList="evaluacionesList"
          @next="nextStep"
          @prev="prevStep"
          @save="saveChanges"
        />
      </div>
    </div>
  </div>
</template>
