<script setup>
import { ref, computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import ExpedienteModal from '@/Components/ExpedienteModal.vue'

const props = defineProps({
  expedientes: Array
})

const search = ref('')
const showModal = ref(false)
const selectedExpediente = ref(null)

const expedientesFiltrados = computed(() => {
  return props.expedientes.filter(exp => {
    const texto = `${exp.id} ${exp.paciente?.nombre} ${exp.fecha_apertura} ${exp.estado}`.toLowerCase()
    return texto.includes(search.value.toLowerCase())
  })
})

function capitalizeEstado(estado) {
  if (!estado) return ''
  return estado.charAt(0).toUpperCase() + estado.slice(1)
}

function openModal(exp) {
  selectedExpediente.value = exp
  showModal.value = true
}
function closeModal() {
  showModal.value = false
  selectedExpediente.value = null
}
</script>

<template>
  <div class="bg-white rounded-lg shadow-md p-8 w-full">
    <div class="flex justify-between items-center mb-16">
      <h2 class="text-2xl font-bold text-[#2D2B5B]">Expedientes</h2>
      <div class="flex items-center space-x-3">
        <div class="relative">
          <span class="material-icons absolute left-2 top-1/2 transform -translate-y-1/2 text-gray-400">search</span>
          <input v-model="search" type="text" placeholder="Buscar..." 
                 class="pl-8 pr-3 py-2 border rounded-md text-md focus:ring-2 focus:ring-[#53C6D3]" />
        </div>
        <!-- Botón agregar -->
        <Link href="/expedientes/create" 
              class="inline-flex items-center px-4 py-2 bg-[#2D2B5B] text-white rounded-md hover:bg-green-700">
          <span class="material-icons mr-1">add_circle</span>
          <span>Nuevo</span>
        </Link>
      </div>
    </div>

    <!-- Tabla -->
    <div class="overflow-x-auto">
      <table class="w-full border border-gray-200 text-md rounded-lg">
        <thead class="bg-gray-200 text-[#2D2B5B]">
          <tr>
            <th class="px-4 py-2 text-left">Código</th>
            <th class="px-2 py-2 text-center w-12"></th>
            <th class="px-4 py-2 text-left">Paciente</th>
            <th class="px-4 py-2 text-left">Fecha inicio</th>
            <th class="px-4 py-2 text-left">Estado</th>
            <th class="px-4 py-2 text-center">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="exp in expedientesFiltrados" :key="exp.id" 
              class="border-t hover:bg-[#FAF9F7] transition">
            <td class="px-4 py-2 font-medium text-[#2D2B5B]">{{ exp.id }}</td>
            <!-- Avatar columna pequeña -->
            <td class="px-2 py-2 text-center">
              <img src="/images/avatar.png" alt="avatar" class="w-8 h-8 rounded-full border inline-block" />
            </td>
            <!-- Nombre del paciente -->
            <td class="px-4 py-2">{{ exp.paciente?.nombre }}</td>
            <td class="px-4 py-2">{{ exp.fecha_apertura }}</td>
            <td class="px-4 py-2">
              <span :class="{
                'text-green-600 font-semibold': exp.estado === 'activo',
                'text-blue-600 font-semibold': exp.estado === 'archivado',
                'text-orange-600 font-semibold': exp.estado === 'pendiente',
                'text-gray-600 font-semibold': exp.estado === 'inactivo'
              }">
                {{ capitalizeEstado(exp.estado) }}
              </span>
            </td>
            <td class="px-4 py-2 text-center">
              <div class="flex justify-center space-x-2">
                <button @click="openModal(exp)" 
                        class="inline-flex items-center px-3 py-1 text-[#74BE69] hover:text-[#1f1d3f]">
                  <span class="material-icons text-base">assignment</span>
                  <span class="ml-1">Ver</span>
                </button>
                <!-- Botón Editar -->
                <Link :href="`/expedientes/${exp.id}/edit`" 
                      class="inline-flex items-center px-3 py-1 text-[#53C6D3] hover:text-[#2D2B5B]">
                  <span class="material-icons text-base">edit</span>
                  <span class="ml-1">Editar</span>
                </Link>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    
    <ExpedienteModal v-if="showModal" :expediente="selectedExpediente" @close="closeModal" />
  </div>
</template>

<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

export default {
  layout: AuthenticatedLayout
}
</script>