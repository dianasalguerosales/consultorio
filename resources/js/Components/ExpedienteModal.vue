<script setup>
import { computed, ref } from 'vue'
import DatosGenerales from './tabs/DatosGenerales.vue'
import HistoriaClinica from './tabs/HistoriaClinica.vue'
import AtencionTerapeutica from './tabs/AtencionTerapeutica.vue'
import Evaluaciones from './tabs/Evaluaciones.vue'

const props = defineProps({
  expediente: {
    type: Object,
    required: true
  }
})
defineEmits(['close'])

const edad = computed(() => {
  if (!props.expediente?.paciente?.fecha_nacimiento) return null
  const nacimiento = new Date(props.expediente.paciente.fecha_nacimiento)
  const hoy = new Date()

  let years = hoy.getFullYear() - nacimiento.getFullYear()
  let months = hoy.getMonth() - nacimiento.getMonth()

  if (months < 0) {
    years--
    months += 12
  }

  return `${years} años ${months} meses`
})

// Tabs reducidos
const tabs = [
  'Datos generales',
  'Historia Clínica',
  'Atención terapéutica',
  'Evaluaciones'
]

const activeTab = ref(tabs[0])

const getComponent = (tab) => {
  switch (tab) {
    case 'Datos generales': return DatosGenerales
    case 'Historia Clínica': return HistoriaClinica
    case 'Atención terapéutica': return AtencionTerapeutica
    case 'Evaluaciones': return Evaluaciones
  }
}
</script>

<template>
  <div class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl w-11/12 max-w-6xl h-5/6 flex flex-col">

      <!-- Header -->
      <div class="flex justify-between items-center border-b p-4">
        <h2 class="text-xl font-bold text-caine-azul">
          Expediente de {{ expediente?.paciente?.nombre ?? expediente?.nombre_pila }}
        </h2>
        <button @click="$emit('close')" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
      </div>

      <!-- Tabs estilo folder -->
      <div class="flex bg-gray-100 px-4">
        <button v-for="tab in tabs" :key="tab" @click="activeTab = tab" :class="[
          'px-4 py-2 text-sm font-medium relative',
          activeTab === tab
            ? 'bg-white border-t-2 border-l border-r text-caine-azul rounded-t-md'
            : 'text-gray-600 hover:text-caine-azul'
        ]">
          {{ tab }}
        </button>
      </div>

      <!-- Contenedor unido a la pestaña -->
      <div class="flex-1 overflow-y-auto px-4 bg-gray-50">
        <div class="bg-white border-l border-r border-b  l rounded-b-md  shadow-sm p-4">
          <component :is="getComponent(activeTab)" :expediente="expediente" />
        </div>
      </div>

      <!-- Footer -->
      <div class="flex justify-end border-t p-4 bg-gray-50">
        <button @click="$emit('close')" class="px-4 py-2 bg-caine-celeste text-white rounded-md hover:bg-caine-azul">
          Cerrar
        </button>
      </div>
    </div>
  </div>
</template>
