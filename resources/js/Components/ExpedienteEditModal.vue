<script setup>
import { useForm } from '@inertiajs/vue3'
import { ref } from 'vue'

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
  // Unir todos los items de los módulos
  const allItems = [].concat(
    form.itemsModulo1,
    form.itemsModulo2,
    form.itemsModulo3
  )

  // Asignar al campo "items" que espera tu backend
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

props.criteriosModulo1.forEach(c => {
  form.itemsModulo1.push({ criterio_id: c.id, respuesta: null })
})

props.criteriosModulo2.forEach(c => {
  form.itemsModulo2.push({ criterio_id: c.id, respuesta: null })
})

props.criteriosModulo3.forEach(c => {
  form.itemsModulo3.push({ criterio_id: c.id, respuesta: null })
})

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

        <!-- Paso 2: Módulo 1 -->
        <div v-if="step === 2">
          <h3>Anamnesis - Módulo 1</h3>
          <div v-for="(criterio, index) in criteriosModulo1" :key="criterio.id">
            <label>{{ criterio.area }} - {{ criterio.numero }}. {{ criterio.descripcion }}</label>
            <div>
              <label><input type="radio" value="3" v-model="form.itemsModulo1[index].respuesta" /> Adecuado</label>
              <label><input type="radio" value="2" v-model="form.itemsModulo1[index].respuesta" /> En desarrollo</label>
              <label><input type="radio" value="1" v-model="form.itemsModulo1[index].respuesta" /> Necesita
                observación</label>
            </div>
          </div>
        </div>

        <!-- Paso 3: Módulo 2 -->
        <div v-if="step === 3">
          <h3>Anamnesis - Módulo 2</h3>
          <div v-for="(criterio, index) in criteriosModulo2" :key="criterio.id">
            <label>{{ criterio.area }} - {{ criterio.numero }}. {{ criterio.descripcion }}</label>
            <div>
              <label><input type="radio" value="3" v-model="form.itemsModulo2[index].respuesta" /> Adecuado</label>
              <label><input type="radio" value="2" v-model="form.itemsModulo2[index].respuesta" /> En desarrollo</label>
              <label><input type="radio" value="1" v-model="form.itemsModulo2[index].respuesta" /> Necesita
                observación</label>
            </div>
          </div>
        </div>

        <!-- Paso 4: Módulo 3 -->
        <div v-if="step === 4">
          <h3>Anamnesis - Módulo 3</h3>
          <div v-for="(criterio, index) in criteriosModulo3" :key="criterio.id">
            <label>{{ criterio.area }} - {{ criterio.numero }}. {{ criterio.descripcion }}</label>
            <div>
              <label><input type="radio" value="3" v-model="form.itemsModulo3[index].respuesta" /> Adecuado</label>
              <label><input type="radio" value="2" v-model="form.itemsModulo3[index].respuesta" /> En desarrollo</label>
              <label><input type="radio" value="1" v-model="form.itemsModulo3[index].respuesta" /> Necesita
                observación</label>
            </div>
          </div>
        </div>




        <!-- Paso 3: Módulo 2 Anamnesis -->
        <div v-if="step === 3">
          <h3 class="text-lg font-semibold mb-4">Anamnesis - Módulo 2 (Evaluación Cognitiva)</h3>

          <div v-for="(criterio, index) in criteriosModulo2" :key="criterio.id" class="mt-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">
              {{ criterio.area }} - {{ criterio.numero }}. {{ criterio.descripcion }}
            </label>

            <div class="flex space-x-6">
              <label class="flex items-center space-x-1">
                <input type="radio" :name="'criterio_' + criterio.id" value="3" v-model="form.items[index].respuesta" />
                <span>Adecuado</span>
              </label>

              <label class="flex items-center space-x-1">
                <input type="radio" :name="'criterio_' + criterio.id" value="2" v-model="form.items[index].respuesta" />
                <span>En desarrollo</span>
              </label>

              <label class="flex items-center space-x-1">
                <input type="radio" :name="'criterio_' + criterio.id" value="1" v-model="form.items[index].respuesta" />
                <span>Necesita observación</span>
              </label>
            </div>
          </div>

          <!-- Observaciones -->
          <div class="mt-6">
            <label class="block text-sm font-medium text-gray-700">Observaciones</label>
            <textarea v-model="form.observaciones" class="mt-1 block w-full border rounded-md px-3 py-2"></textarea>
          </div>

          <div class="mt-6 flex justify-between">
            <button @click="prevStep" class="px-4 py-2 bg-gray-300 rounded">Anterior</button>
            <button @click="nextStep" class="px-4 py-2 bg-blue-500 text-white rounded">Siguiente</button>
          </div>
        </div>

        <!-- Paso 4: Módulo 3 Anamnesis -->
        <div v-if="step === 4">
          <h3 class="text-lg font-semibold mb-4">Anamnesis - Módulo 3 (Evaluación Socioemocional)</h3>

          <div v-for="(criterio, index) in criteriosModulo3" :key="criterio.id" class="mt-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">
              {{ criterio.area }} - {{ criterio.numero }}. {{ criterio.descripcion }}
            </label>

            <div class="flex space-x-6">
              <label class="flex items-center space-x-1">
                <input type="radio" :name="'criterio_' + criterio.id" value="3" v-model="form.items[index].respuesta" />
                <span>Adecuado</span>
              </label>

              <label class="flex items-center space-x-1">
                <input type="radio" :name="'criterio_' + criterio.id" value="2" v-model="form.items[index].respuesta" />
                <span>En desarrollo</span>
              </label>

              <label class="flex items-center space-x-1">
                <input type="radio" :name="'criterio_' + criterio.id" value="1" v-model="form.items[index].respuesta" />
                <span>Necesita observación</span>
              </label>
            </div>
          </div>

          <!-- Observaciones -->
          <div class="mt-6">
            <label class="block text-sm font-medium text-gray-700">Observaciones</label>
            <textarea v-model="form.observaciones" class="mt-1 block w-full border rounded-md px-3 py-2"></textarea>
          </div>

          <div class="mt-6 flex justify-between">
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
