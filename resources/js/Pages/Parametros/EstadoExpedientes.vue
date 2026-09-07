<script setup>
import { ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import EstadoExpedienteModalVer from '@/Components/parametros/EstadoExpedienteModalVer.vue'

defineProps({
  estadoExpedientes: Array
})

const showVerModal = ref(false)
const showEditarModal = ref(false)
const selectedEstadoExpediente = ref(null)

function openVerModal(estadoExpediente) {
  selectedEstadoExpediente.value = estadoExpediente
  showVerModal.value = true
}
function closeVerModal() {
  showVerModal.value = false
  selectedEstadoExpediente.value = null
}

function openEditarModal(estadoExpediente) {
  selectedEstadoExpediente.value = estadoExpediente
  showEditarModal.value = true
}
function closeEditarModal() {
  showEditarModal.value = false
  selectedEstadoExpediente.value = null
}
</script>

<template>
  <div class="overflow-x-auto bg-white rounded-lg shadow-md">
    <table class="min-w-full border border-gray-200 text-md rounded-lg">
      <!-- Encabezado -->
      <thead class="bg-gray-200 text-[#2D2B5B]">
        <tr>
          <th class="px-4 py-2 text-left">Nombre</th>
          <th class="px-4 py-2 text-center">Activo</th>
          <th class="px-4 py-2 text-center">Acciones</th>
        </tr>
      </thead>

      <!-- Filas -->
      <tbody>
        <tr v-for="e in estadoExpedientes" :key="e.id" class="border-t hover:bg-[#FAF9F7] transition">
          <!-- Nombre -->
          <td class="px-4 py-2 font-medium text-[#2D2B5B]">
            {{ e.nombre }}
          </td>

          <!-- Activo -->
          <td class="px-4 py-2 text-center">
            <span :class="e.activo ? 'text-green-600' : 'text-red-600'">
              {{ e.activo ? 'Sí' : 'No' }}
            </span>
          </td>

          <!-- Acciones -->
          <td class="px-4 py-2 text-center">
            <div class="flex justify-center space-x-2">
              <!-- Ver -->
              <button @click="openVerModal(e)"
                class="inline-flex items-center px-3 py-1 text-[#74BE69] hover:text-[#1f1d3f]">
                <span class="material-icons text-base">assignment</span>
                <span class="ml-1">Ver</span>
              </button>

              <!-- Editar -->
              <button @click="openEditarModal(e)"
                class="inline-flex items-center px-3 py-1 text-[#53C6D3] hover:text-[#2D2B5B]">
                <span class="material-icons text-base">edit</span>
                <span class="ml-1">Editar</span>
              </button>

              <!-- Eliminar -->
              <Link as="button" method="delete" :href="`/parametros/estado-expedientes/${e.id}`"
                class="inline-flex items-center px-3 py-1 text-red-600 hover:text-red-800">
                <span class="material-icons text-base">delete</span>
                <span class="ml-1">Eliminar</span>
              </Link>
            </div>
          </td>
        </tr>
      </tbody>
    </table>

    <EstadoExpedienteModalVer v-if="showVerModal" :estadoExpediente="selectedEstadoExpediente" @close="closeVerModal" />
  </div>
</template>