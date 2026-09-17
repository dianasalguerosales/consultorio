<script setup>
import { useForm } from '@inertiajs/vue3'
import { ref } from 'vue'
import ModalCapa from '@/Components/ModalCapa.vue'

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
  modalidadesList: { type: Array, default: () => [] },
  diagnosticosList: { type: Array, default: () => [] },
  escolaridadesList: { type: Array, default: () => [] },
  criteriosModulo1: { type: Array, default: () => [] },
  criteriosModulo2: { type: Array, default: () => [] },
  criteriosModulo3: { type: Array, default: () => [] },
  serviciosList: { type: Array, default: () => [] },
  evaluacionesList: { type: Array, default: () => [] },
  estadoExpedientes: { type: Array, default: () => [] }
})

const emit = defineEmits(['close'])
const step = ref(1)

// Una fecha puede venir como '2026-09-09' o con hora; el <input type="date">
// solo acepta lo primero.
const soloFecha = (valor) => (valor ? String(valor).slice(0, 10) : '')

// Lo que ya está respondido en la anamnesis, por criterio.
const respuestasGuardadas = new Map(
  (props.expediente?.anamnesis?.items ?? []).map(i => [i.criterio_id, i.respuesta])
)

// Un renglón por criterio, con su respuesta si el expediente ya la tenía.
const itemsDe = (criterios) =>
  criterios.map(c => ({
    criterio_id: c.id,
    respuesta: respuestasGuardadas.get(c.id) ?? null,
  }))

const form = useForm({
  paciente_id: props.pacienteId || props.expediente?.paciente_id || null,
  nombres: props.expediente?.nombres || '',
  apellidos: props.expediente?.apellidos || '',
  fecha_nacimiento: soloFecha(props.expediente?.fecha_nacimiento),
  estado_expediente_id: props.expediente?.estado_expediente_id || null,
  modalidad_id: props.expediente?.modalidad_id || null,
  anamnesis_id: props.expediente?.anamnesis_id || null,
  diagnosticos: props.expediente?.diagnosticos?.map(d => d.id) || [],
  // La relación se llama `servicios`; con el nombre viejo el formulario mandaba
  // la lista vacía y cada guardado borraba las terapias del expediente.
  servicios: props.expediente?.servicios?.map(s => s.id) || [],
  evaluaciones: props.expediente?.evaluaciones?.map(e => e.id) || [],
  // La escolaridad es del paciente, no del expediente.
  escolaridad_id: props.expediente?.paciente?.escolaridad_id || null,
  motivo_consulta: props.expediente?.motivo_consulta || '',
  fecha_inicio: soloFecha(props.expediente?.fecha_inicio),
  consentimiento: Boolean(props.expediente?.consentimiento),
  observaciones: props.expediente?.observaciones || '',
  itemsModulo1: itemsDe(props.criteriosModulo1),
  itemsModulo2: itemsDe(props.criteriosModulo2),
  itemsModulo3: itemsDe(props.criteriosModulo3)
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

</script>

<template>
  <ModalCapa panel="max-w-6xl h-5/6 flex flex-col" @close="emit('close')">
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
        <component :is="{
          1: DatosGenerales,
          2: Modulo1,
          3: Modulo2,
          4: Modulo3,
          5: HistoriaClinica,
          6: Terapias,
          7: Evaluaciones
        }[step]" :form="form" :criteriosModulo1="criteriosModulo1" :criteriosModulo2="criteriosModulo2"
          :criteriosModulo3="criteriosModulo3" :escolaridadesList="escolaridadesList"
          :diagnosticosList="diagnosticosList" :serviciosList="serviciosList" :evaluacionesList="evaluacionesList"
          :modalidadesList="modalidadesList" :estadoExpedientes="estadoExpedientes" @next="nextStep" @prev="prevStep"
          @save="saveChanges" />
      </div>
  </ModalCapa>
</template>
