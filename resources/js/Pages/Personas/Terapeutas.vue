<script setup>
import { ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import TerapeutaModalVer from '@/Components/personas/TerapeutaModalVer.vue'
import TerapeutaModalEditar from '@/Components/personas/TerapeutaModalEditar.vue'

defineProps({
  terapeutas: Array,
  especialidades: Array,
  generos: Array
})

const showViewModal = ref(false)
const showEditModal = ref(false)
const selectedTerapeuta = ref(null)

function openViewModal(terapeuta) {
  selectedTerapeuta.value = terapeuta
  showViewModal.value = true
}
function closeViewModal() {
  showViewModal.value = false
  selectedTerapeuta.value = null
}

function openEditModal(terapeuta) {
  selectedTerapeuta.value = terapeuta
  showEditModal.value = true
}
function closeEditModal() {
  showEditModal.value = false
  selectedTerapeuta.value = null
}
</script>

<template>
  <div class="overflow-x-auto bg-white rounded-lg shadow-md">
    <table class="min-w-full border border-gray-200 text-md rounded-lg">
      <!-- Encabezado -->
      <thead class="bg-gray-200 text-[#2D2B5B]">
        <tr>
          <th class="px-4 py-2 text-center">Avatar</th>
          <th class="px-4 py-2 text-left">Nombre</th>
          <th class="px-4 py-2 text-left">Correo</th>
          <th class="px-4 py-2 text-left">Especialidad</th>
          <th class="px-4 py-2 text-center">Acciones</th>
        </tr>
      </thead>

      <!-- Filas -->
      <tbody>
        <tr v-for="t in terapeutas" :key="t.id" class="border-t hover:bg-[#FAF9F7] transition">
          <!-- Avatar -->
          <td class="px-4 py-2 text-center">
            <img :src="t.avatar_url || '/images/avatar.webp'" alt="avatar"
              class="w-10 h-10 rounded-full border inline-block" />
          </td>

          <!-- Nombre -->
          <td class="px-4 py-2 font-medium text-[#2D2B5B]">
            {{ t.nombres }} {{ t.apellidos }}
          </td>

          <!-- Correo -->
          <td class="px-4 py-2 text-gray-700">{{ t.correo }}</td>

          <!-- Especialidad -->
          <td class="px-4 py-2 text-gray-700">{{ t.especialidad?.nombre }}</td>

          <!-- Acciones -->
          <td class="px-4 py-2 text-center">
            <div class="flex justify-center space-x-2">
              <!-- Ver -->
              <button @click="openViewModal(t)"
                class="inline-flex items-center px-3 py-1 text-[#74BE69] hover:text-[#1f1d3f]">
                <span class="material-icons text-base">assignment</span>
                <span class="ml-1">Ver</span>
              </button>

              <!-- Editar -->
              <button @click="openEditModal(t)"
                class="inline-flex items-center px-3 py-1 text-[#53C6D3] hover:text-[#2D2B5B]">
                <span class="material-icons text-base">edit</span>
                <span class="ml-1">Editar</span>
              </button>

              <!-- Eliminar -->
              <Link as="button" method="delete" :href="`/personas/terapeutas/${t.id}`"
                class="inline-flex items-center px-3 py-1 text-red-600 hover:text-red-800">
                <span class="material-icons text-base">delete</span>
                <span class="ml-1">Eliminar</span>
              </Link>

              <!-- Permisos -->
              <Link :href="`/personas/terapeuta/${t.id}/permisos`"
                class="inline-flex items-center px-3 py-1 text-[#2D2B5B] hover:text-[#53C6D3]">
                <span class="material-icons text-base">lock</span>
                <span class="ml-1">Permisos</span>
              </Link>

              <!-- Pacientes -->
              <Link :href="`/personas/terapeuta/${t.id}/pacientes`"
                class="inline-flex items-center px-3 py-1 text-[#2D2B5B] hover:text-[#53C6D3]">
                <span class="material-icons text-base">groups</span>
                <span class="ml-1">Pacientes</span>
              </Link>
            </div>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Modal de perfil -->
    <TerapeutaModalVer
      v-if="showViewModal"
      :terapeuta="selectedTerapeuta"
      @close="closeViewModal"
    />

    <!-- Modal de edición -->
    <TerapeutaModalEditar
      v-if="showEditModal"
      :terapeuta="selectedTerapeuta"
      :especialidades="especialidades"
      :generos="generos"
      @close="closeEditModal"
    />
  </div>
</template>