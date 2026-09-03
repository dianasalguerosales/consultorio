<script setup>
import { useForm } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

const props = defineProps({
  expedienteId: {
    type: Number,
    required: true
  }
})

const step = ref(1)
const observaciones = ref('')

// Preguntas por área (ejemplo inicial, luego puedes cargar dinámicamente desde BD)
const desarrolloPreguntas = ref([
  { criterio: 'Lenguaje expresivo', respuesta: null },
  { criterio: 'Motricidad fina', respuesta: null },
])

const cognitivaPreguntas = ref([
  { criterio: 'Memoria de trabajo', respuesta: null },
  { criterio: 'Atención sostenida', respuesta: null },
])

const emocionalPreguntas = ref([
  { criterio: 'Regulación emocional', respuesta: null },
  { criterio: 'Interacción social', respuesta: null },
])

// Totales por área
const totalDesarrollo = computed(() =>
  desarrolloPreguntas.value.reduce((sum, q) => sum + Number(q.respuesta || 0), 0)
)
const totalCognitiva = computed(() =>
  cognitivaPreguntas.value.reduce((sum, q) => sum + Number(q.respuesta || 0), 0)
)
const totalEmocional = computed(() =>
  emocionalPreguntas.value.reduce((sum, q) => sum + Number(q.respuesta || 0), 0)
)

// Formulario Inertia
const form = useForm({
  expediente_id: props.expedienteId,
  observaciones: observaciones.value,
  items: []
})

function guardarAnamnesis() {
  form.items = [
    ...desarrolloPreguntas.value.map(q => ({ area: 'desarrollo', criterio: q.criterio, respuesta: q.respuesta })),
    ...cognitivaPreguntas.value.map(q => ({ area: 'cognitiva', criterio: q.criterio, respuesta: q.respuesta })),
    ...emocionalPreguntas.value.map(q => ({ area: 'emocional', criterio: q.criterio, respuesta: q.respuesta })),
  ]
  form.observaciones = observaciones.value

  form.post(route('anamnesis.store'), {
    onSuccess: () => alert('Anamnesis guardada correctamente')
  })
}
</script>

<template>
  <div class="p-6">
    <!-- Paso 1 -->
    <div v-if="step === 1">
      <h2 class="text-xl font-bold">Evaluación del Desarrollo Infantil</h2>
      <div v-for="(q, i) in desarrolloPreguntas" :key="i" class="mt-2">
        <label>{{ q.criterio }}</label>
        <select v-model="q.respuesta" class="ml-2 border rounded px-2 py-1">
          <option :value="3">Adecuado</option>
          <option :value="2">En desarrollo</option>
          <option :value="1">Necesita observación</option>
        </select>
      </div>
      <button @click="step++" class="mt-4 px-4 py-2 bg-blue-500 text-white rounded">Siguiente</button>
    </div>

    <!-- Paso 2 -->
    <div v-if="step === 2">
      <h2 class="text-xl font-bold">Evaluación Cognitiva</h2>
      <div v-for="(q, i) in cognitivaPreguntas" :key="i" class="mt-2">
        <label>{{ q.criterio }}</label>
        <select v-model="q.respuesta" class="ml-2 border rounded px-2 py-1">
          <option :value="3">Adecuado</option>
          <option :value="2">En desarrollo</option>
          <option :value="1">Necesita observación</option>
        </select>
      </div>
      <div class="mt-4 flex justify-between">
        <button @click="step--" class="px-4 py-2 bg-gray-300 rounded">Anterior</button>
        <button @click="step++" class="px-4 py-2 bg-blue-500 text-white rounded">Siguiente</button>
      </div>
    </div>

    <!-- Paso 3 -->
    <div v-if="step === 3">
      <h2 class="text-xl font-bold">Evaluación Emocional y Conductual</h2>
      <div v-for="(q, i) in emocionalPreguntas" :key="i" class="mt-2">
        <label>{{ q.criterio }}</label>
        <select v-model="q.respuesta" class="ml-2 border rounded px-2 py-1">
          <option :value="3">Adecuado</option>
          <option :value="2">En desarrollo</option>
          <option :value="1">Necesita observación</option>
        </select>
      </div>
      <div class="mt-4 flex justify-between">
        <button @click="step--" class="px-4 py-2 bg-gray-300 rounded">Anterior</button>
        <button @click="step++" class="px-4 py-2 bg-blue-500 text-white rounded">Finalizar</button>
      </div>
    </div>

    <!-- Resumen -->
    <div v-if="step === 4">
      <h2 class="text-xl font-bold">Resumen</h2>
      <p>Total Desarrollo: {{ totalDesarrollo }}</p>
      <p>Total Cognitiva: {{ totalCognitiva }}</p>
      <p>Total Emocional: {{ totalEmocional }}</p>

      <label class="block mt-4">Observaciones generales</label>
      <textarea v-model="observaciones" rows="3" class="w-full border rounded px-3 py-2"></textarea>

      <div class="mt-4 flex justify-between">
        <button @click="step--" class="px-4 py-2 bg-gray-300 rounded">Anterior</button>
        <button @click="guardarAnamnesis" class="px-4 py-2 bg-green-500 text-white rounded">Guardar</button>
      </div>
    </div>
  </div>
</template>
