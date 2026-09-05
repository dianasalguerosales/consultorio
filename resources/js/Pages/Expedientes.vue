<script setup>
import { ref, computed } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import ExpedienteModal from '@/Components/ExpedienteModal.vue'
import ExpedienteEditModal from '@/Components/ExpedienteEditModal.vue'

const props = defineProps({
  expedientes: Array,
  diagnosticosList: Array,
  terapiasList: Array,
  evaluacionesList: Array,
  escolaridadesList: Array,
  criteriosModulo1: { type: Array, default: () => [] },
  criteriosModulo2: { type: Array, default: () => [] },
  criteriosModulo3: { type: Array, default: () => [] }
})

const search = ref('')
const showModal = ref(false)
const selectedExpediente = ref(null)

const showEditModal = ref(false)
const expedienteEditData = ref(null)

const expedientesFiltrados = computed(() => {
  return props.expedientes.filter(exp => {
    const texto = `${exp.id} ${exp.paciente?.nombre ?? exp.nombre_pila} ${exp.fecha_apertura} ${exp.estado}`.toLowerCase()
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

function openEditModal(exp = null) {
  expedienteEditData.value = exp
  showEditModal.value = true
}
function closeEditModal() {
  showEditModal.value = false
  expedienteEditData.value = null
}

function deleteExpediente(exp) {
  if (confirm(`¿Seguro que deseas eliminar el expediente #${exp.id}?`)) {
    router.delete(route('expedientes.destroy', exp.id), {
      onSuccess: () => {
        console.log('Expediente eliminado correctamente')
      },
      onError: (errors) => {
        console.error(errors)
      }
    })
  }
}
</script>

<template>
  <Head title="Expedientes" />
  <div class="bg-white rounded-lg shadow-md p-8 w-full">
    <!-- Encabezado -->
    <div class="flex justify-between items-center mb-16">
      <h2 class="text-2xl font-bold text-[#2D2B5B]">Expedientes</h2>
      <div class="flex items-center space-x-3">
        <div class="relative">
          <span class="material-icons absolute left-2 top-1/2 transform -translate-y-1/2 text-gray-400">search</span>
          <input v-model="search" type="text" placeholder="Buscar..."
            class="pl-8 pr-3 py-2 border rounded-md text-md focus:ring-2 focus:ring-[#53C6D3]" />
        </div>
        <!-- Botón agregar -->
        <button @click="openEditModal()"
          class="inline-flex items-center px-4 py-2 bg-[#2D2B5B] text-white rounded-md hover:bg-green-700">
          <span class="material-icons mr-1">add_circle</span>
          <span>Nuevo</span>
        </button>
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
          <tr v-for="exp in expedientesFiltrados" :key="exp.id" class="border-t hover:bg-[#FAF9F7] transition">
            <td class="px-4 py-2 font-medium text-[#2D2B5B]">{{ exp.id }}</td>
            <td class="px-2 py-2 text-center">
              <img :src="exp.paciente?.genero === 'femenino'
                ? '/images/Femenino.webp'
                : exp.paciente?.genero === 'masculino'
                  ? '/images/Masculino.webp'
                  : '/images/avatar.webp'" alt="avatar" class="w-8 h-8 rounded-full border inline-block" />
            </td>
            <td class="px-4 py-2">
              {{ exp.paciente ? exp.paciente.nombre : exp.nombre_pila }}
            </td>
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
                <button @click="openEditModal(exp)"
                  class="inline-flex items-center px-3 py-1 text-[#53C6D3] hover:text-[#2D2B5B]">
                  <span class="material-icons text-base">edit</span>
                  <span class="ml-1">Editar</span>
                </button>
                <!-- Botón eliminar -->
                <button @click="deleteExpediente(exp)"
                  class="inline-flex items-center px-3 py-1 text-red-600 hover:text-red-800">
                  <span class="material-icons text-base">delete</span>
                  <span class="ml-1">Eliminar</span>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal detalle -->
    <ExpedienteModal v-if="showModal" :expediente="selectedExpediente" @close="closeModal" />

    <!-- Modal crear/editar -->
    <ExpedienteEditModal v-if="showEditModal" :expediente="expedienteEditData" :diagnosticos-list="diagnosticosList"
      :terapias-list="terapiasList" :evaluaciones-list="evaluacionesList" :escolaridades-list="escolaridadesList"
      :criterios-modulo1="props.criteriosModulo1" :criterios-modulo2="props.criteriosModulo2"
      :criterios-modulo3="props.criteriosModulo3" @close="closeEditModal" />
  </div>
</template>

<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

export default {
    layout: AuthenticatedLayout
}
</script>
