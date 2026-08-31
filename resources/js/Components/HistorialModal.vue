<script setup>
defineProps({
  paciente: Object,
})
defineEmits(['close'])
</script>

<template>
  <div class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl w-2/3 max-w-3xl h-5/6 overflow-y-auto relative">
      <!-- Header -->
      <div class="flex justify-between items-center border-b p-4">
        <h2 class="text-xl font-bold text-caine-verde">
          Historial de citas de {{ paciente?.nombre }}
        </h2>
        <button @click="$emit('close')" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
      </div>

      <!-- Body -->
      <div class="p-6 space-y-4">
        <table class="w-full border-collapse">
          <thead>
            <tr class="bg-gray-100">
              <th class="p-2 border">Fecha</th>
              <th class="p-2 border">Hora</th>
              <th class="p-2 border">Terapeuta</th>
              <th class="p-2 border">Servicio</th>
              <th class="p-2 border">Estado</th>
              <th class="p-2 border">Modalidad</th>
              <th class="p-2 border">Observaciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="cita in paciente?.citas || []" :key="cita.id" class="hover:bg-gray-50">
              <td class="p-2 border">{{ cita.fecha }}</td>
              <td class="p-2 border">{{ cita.hora_inicio }} - {{ cita.hora_fin }}</td>
              <td class="p-2 border">{{ cita.terapeuta?.nombre }}</td>
              <td class="p-2 border">{{ cita.servicio?.nombre }}</td>
              <td class="p-2 border">{{ cita.estado_cita?.nombre }}</td>
              <td class="p-2 border">{{ cita.modalidad }}</td>
              <td class="p-2 border">{{ cita.observaciones }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Footer -->
      <div class="flex justify-end border-t p-4">
        <button @click="$emit('close')" class="px-4 py-2 bg-caine-verde text-white rounded-md hover:bg-caine-azul">
          Cerrar
        </button>
      </div>
    </div>
  </div>
</template>