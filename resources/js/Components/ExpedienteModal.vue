<script setup>
import { computed } from 'vue'

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
</script>

<template>
  <div class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl w-2/3 max-w-4xl h-5/6 overflow-y-auto relative">
      <!-- Header -->
      <div class="flex justify-between items-center border-b p-4">
        <h2 class="text-xl font-bold text-caine-azul">
          Expediente de {{ expediente?.paciente?.nombre }}
        </h2>
        <button @click="$emit('close')" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
      </div>

      <!-- Body -->
      <div class="p-6 grid grid-cols-2 gap-6">
        <p><strong>Código:</strong> {{ expediente?.id }}</p>
        <p><strong>Fecha de nacimiento:</strong> {{ expediente?.paciente?.fecha_nacimiento }}</p>
        <p><strong>Edad:</strong> {{ edad || 'N/D' }}</p>
        <p><strong>Nombre del encargado:</strong> {{ expediente?.paciente?.encargados?.[0]?.nombre || 'N/D' }}</p>
        <p><strong>Teléfono del encargado:</strong> {{ expediente?.paciente?.encargados?.[0]?.telefono || 'N/D' }}</p>
        <p><strong>Dirección:</strong> {{ expediente?.paciente?.encargados?.[0]?.direccion || 'N/D' }}</p>
        <p class="col-span-2"><strong>Motivo de consulta:</strong> {{ expediente?.motivo_consulta || 'Ninguno' }}</p>
        <p><strong>Fecha de inicio:</strong> {{ expediente?.fecha_inicio }}</p>
        <p><strong>Correo del encargado:</strong> {{ expediente?.paciente?.encargados?.[0]?.correo || 'N/D' }}</p>
        <p><strong>Diagnóstico:</strong> {{ expediente?.diagnostico || 'N/D' }}</p>
        <p><strong>Escolaridad:</strong> {{ expediente?.escolaridad || 'N/D' }}</p>
        <p><strong>Modalidad:</strong> {{ expediente?.modalidad || 'N/D' }}</p>
        <p class="col-span-2"><strong>Observaciones:</strong> {{ expediente?.observaciones_administrativas || 'Ninguna' }}</p>
      </div>

      <!-- Footer -->
      <div class="flex justify-end border-t p-4">
        <button @click="$emit('close')" class="px-4 py-2 bg-caine-celeste text-white rounded-md hover:bg-caine-azul">
          Cerrar
        </button>
      </div>
    </div>
  </div>
</template>