<script setup>
import { useForm } from '@inertiajs/vue3'
import { ref } from 'vue'

const props = defineProps({
  expediente: { type: Object, required: false, default: null },
  pacienteId: { type: Number, required: false, default: null },
  diagnosticosList: { type: Array, default: () => [] },
  terapiasList: { type: Array, default: () => [] },
  evaluacionesList: { type: Array, default: () => [] },
  escolaridadesList: { type: Array, default: () => [] }
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
  anamnesis: {
    modulo1: [],
    modulo2: [],
    modulo3: [],
    observaciones: ''
  }
})

function nextStep() { step.value++ }
function prevStep() { step.value-- }

function saveChanges() {
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
  <div class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl w-11/12 max-w-6xl h-5/6 flex flex-col relative">
      <!-- Header -->
      <div class="flex justify-between items-center border-b p-4">
        <h2 class="text-xl font-bold text-caine-azul">
          {{ props.expediente ? 'Editar Expediente' : 'Nuevo Expediente' }}
        </h2>
        <button @click="$emit('close')" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
      </div>

      <div class="px-6 pt-4">
        <div class="w-full bg-gray-200 rounded-full h-2.5 mb-2">
          <div class="bg-blue-600 h-2.5 rounded-full" :style="{ width: (step / 7 * 100) + '%' }"></div>
        </div>
        <p class="text-sm text-gray-600">Paso {{ step }} de 7</p>
      </div>



      <!-- Body -->
      <div class="p-6">
        <!-- Paso 1: Datos Generales -->
        <div v-if="step === 1" class="grid grid-cols-2 gap-6">
          <h3 class="col-span-2 text-lg font-semibold mb-4">Datos Generales</h3>

          <!-- Estado -->
          <div>
            <label class="block text-sm font-medium text-[#2D2B5B]">Estado</label>
            <select v-model="form.estado" class="mt-1 block w-full border rounded-md px-3 py-2">
              <option value="activo">Activo</option>
              <option value="archivado">Archivado</option>
              <option value="pendiente">Pendiente</option>
              <option value="inactivo">Inactivo</option>
            </select>
          </div>

          <!-- Fecha apertura -->
          <div>
            <label class="block text-sm font-medium text-[#2D2B5B]">Fecha inicio</label>
            <input v-model="form.fecha_apertura" type="date" class="mt-1 block w-full border rounded-md px-3 py-2" />
          </div>

          <!-- Nombre -->
          <div class="col-span-2">
            <label class="block text-sm font-medium text-[#2D2B5B]">Nombre pila</label>
            <input v-model="form.nombre_pila" type="text" class="mt-1 block w-full border rounded-md px-3 py-2" />
          </div>

          <!-- Motivo consulta -->
          <div class="col-span-2">
            <label class="block text-sm font-medium text-[#2D2B5B]">Motivo de consulta</label>
            <textarea v-model="form.motivo_consulta" rows="2"
              class="mt-1 block w-full border rounded-md px-3 py-2"></textarea>
          </div>

          <!-- Escolaridad -->
          <div>
            <label class="block text-sm font-medium text-[#2D2B5B]">Escolaridad</label>
            <select v-model="form.escolaridad_id" class="mt-1 block w-full border rounded-md px-3 py-2">
              <option :value="null">N/D</option>
              <option v-for="esc in escolaridadesList" :key="esc.id" :value="esc.id">
                {{ esc.grado }}
              </option>
            </select>
          </div>

          <!-- Modalidad -->
          <div>
            <label class="block text-sm font-medium text-[#2D2B5B]">Modalidad</label>
            <select v-model="form.modalidad" class="mt-1 block w-full border rounded-md px-3 py-2">
              <option value="presencial">Presencial</option>
              <option value="virtual">Virtual</option>
            </select>
          </div>

          <!-- Observaciones -->
          <!-- <div class="col-span-2">
            <label class="block text-sm font-medium text-[#2D2B5B]">Observaciones</label>
            <textarea v-model="form.observaciones_administrativas" rows="3"
              class="mt-1 block w-full border rounded-md px-3 py-2"></textarea>
          </div> -->


          <!-- Flecha derecha -->
          <div class="absolute inset-y-0 -right-12 flex items-center">
            <button @click="nextStep" class="bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-full p-2 shadow">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </button>
          </div>
        </div>

        <!-- Paso 2: Módulo 1 Anamnesis -->
<div v-if="step === 2" class="space-y-6">
  <h3 class="text-lg font-semibold mb-4">Anamnesis - Módulo 1 (Desarrollo Infantil)</h3>

  <!-- Ejemplo: Desarrollo motor grueso -->
  <div>
    <h4 class="font-semibold text-caine-azul">Desarrollo motor grueso</h4>
    <div v-for="(criterio, index) in [
      'Equilibrio y coordinación.',
      'Corre y salta adecuadamente.',
      'Sube y baja escaleras.',
      'Lanza y atrapa objetos.',
      'Postura corporal adecuada.'
    ]" :key="index" class="mt-2">
      <label class="block text-sm mb-1">{{ criterio }}</label>
      <div class="flex space-x-4">
        <label>
          <input type="radio" :name="'motor_grueso_'+index" value="3"
                 v-model="form.anamnesis.modulo1[criterio]" />
          Adecuado
        </label>
        <label>
          <input type="radio" :name="'motor_grueso_'+index" value="2"
                 v-model="form.anamnesis.modulo1[criterio]" />
          En desarrollo
        </label>
        <label>
          <input type="radio" :name="'motor_grueso_'+index" value="1"
                 v-model="form.anamnesis.modulo1[criterio]" />
          Necesita observación
        </label>
      </div>
    </div>
  </div>
</div>



        <!-- Paso 3: Módulo 2 Anamnesis -->
        <div v-if="step === 3">
          <h3 class="text-lg font-semibold mb-4">Anamnesis - Módulo 2</h3>
          <!-- preguntas módulo 2 -->
          <div class="mt-4 flex justify-between">
            <button @click="prevStep" class="px-4 py-2 bg-gray-300 rounded">Anterior</button>
            <button @click="nextStep" class="px-4 py-2 bg-blue-500 text-white rounded">Siguiente</button>
          </div>
        </div>

        <!-- Paso 4: Módulo 3 Anamnesis -->
        <div v-if="step === 4">
          <h3 class="text-lg font-semibold mb-4">Anamnesis - Módulo 3</h3>
          <!-- preguntas módulo 3 -->
          <div class="mt-4 flex justify-between">
            <button @click="prevStep" class="px-4 py-2 bg-gray-300 rounded">Anterior</button>
            <button @click="nextStep" class="px-4 py-2 bg-blue-500 text-white rounded">Siguiente</button>
          </div>
        </div>

        <!-- Paso 5: Historia Clínica -->
        <div v-if="step === 5">
          <h3 class="text-lg font-semibold mb-4">Historia Clínica</h3>
          <!-- campos historia clínica -->
          <div class="mt-4 flex justify-between">
            <button @click="prevStep" class="px-4 py-2 bg-gray-300 rounded">Anterior</button>
            <button @click="nextStep" class="px-4 py-2 bg-blue-500 text-white rounded">Siguiente</button>
          </div>
        </div>

        <!-- Paso 6: Atención Terapéutica -->
        <div v-if="step === 6">
          <h3 class="text-lg font-semibold mb-4">Atención Terapéutica</h3>
          <!-- campos terapias -->
          <div class="mt-4 flex justify-between">
            <button @click="prevStep" class="px-4 py-2 bg-gray-300 rounded">Anterior</button>
            <button @click="nextStep" class="px-4 py-2 bg-blue-500 text-white rounded">Siguiente</button>
          </div>
        </div>

        <!-- Paso 7: Evaluaciones -->
        <div v-if="step === 7">
          <h3 class="text-lg font-semibold mb-4">Evaluaciones</h3>
          <!-- campos evaluaciones -->
          <div class="mt-4 flex justify-between">
            <button @click="prevStep" class="px-4 py-2 bg-gray-300 rounded">Anterior</button>
            <button @click="saveChanges" class="px-4 py-2 bg-green-500 text-white rounded">Guardar Expediente</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
