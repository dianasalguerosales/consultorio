<script setup>
import { ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import AdministrativosModalVer from '@/Components/personas/AdministrativoModalVer.vue'
import AdministrativoModalEditar from '@/Components/personas/AdministrativoModalEditar.vue'

defineProps({
  administrativos: Array,
  cargos: Array,
  especialidades: Array,
  generos: Array
})

const showVerModal = ref(false)
const showEditarModal = ref(false)
const selectedAdministrativo = ref(null)

function openVerModal(administrativo) {
  selectedAdministrativo.value = administrativo
  showVerModal.value = true
}
function closeVerModal() {
  showVerModal.value = false
  selectedAdministrativo.value = null
}

function openEditarModal(administrativo) {
  selectedAdministrativo.value = administrativo
  showEditarModal.value = true
}
function closeEditarModal() {
  showEditarModal.value = false
  selectedAdministrativo.value = null
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
          <th class="px-4 py-2 text-left">Cargo</th>
          <th class="px-4 py-2 text-center">Acciones</th>
        </tr>
      </thead>

      <!-- Filas -->
      <tbody>
        <tr v-for="a in administrativos" :key="a.id" class="border-t hover:bg-[#FAF9F7] transition">
          <!-- Avatar -->
          <td class="px-4 py-2 text-center">
            <img :src="a.avatar_url || '/images/avatar.webp'" alt="avatar"
              class="w-10 h-10 rounded-full border inline-block" />
          </td>

          <!-- Nombre -->
          <td class="px-4 py-2 font-medium text-[#2D2B5B]">
            {{ a.nombres }} {{ a.apellidos }}
          </td>

          <!-- Correo -->
          <td class="px-4 py-2 text-gray-700">{{ a.correo }}</td>

          <!-- Cargo -->
          <td class="px-4 py-2 text-gray-700">{{ a.cargo?.nombre }}</td>

          <!-- Acciones -->
          <td class="px-4 py-2 text-center">
            <div class="flex justify-center space-x-2">
              <!-- Ver -->
              <button @click="openVerModal(a)"
                class="inline-flex items-center px-3 py-1 text-[#74BE69] hover:text-[#1f1d3f]">
                <span class="material-icons text-base">assignment</span>
                <span class="ml-1">Ver</span>
              </button>

              <!-- Editar -->
              <button @click="openEditarModal(a)"
                class="inline-flex items-center px-3 py-1 text-[#53C6D3] hover:text-[#2D2B5B]">
                <span class="material-icons text-base">edit</span>
                <span class="ml-1">Editar</span>
              </button>

              <!-- Eliminar -->
              <Link as="button" method="delete" :href="`/personas/administrativos/${a.id}`"
                class="inline-flex items-center px-3 py-1 text-red-600 hover:text-red-800">
                <span class="material-icons text-base">delete</span>
                <span class="ml-1">Eliminar</span>
              </Link>

              <!-- Permisos -->
              <Link :href="`/personas/administrativo/${a.id}/permisos`"
                class="inline-flex items-center px-3 py-1 text-[#2D2B5B] hover:text-[#53C6D3]">
                <span class="material-icons text-base">lock</span>
                <span class="ml-1">Permisos</span>
              </Link>

              <!-- Subalternos -->
              <Link :href="`/personas/administrativo/${a.id}/subalternos`"
                class="inline-flex items-center px-3 py-1 text-[#2D2B5B] hover:text-[#53C6D3]">
                <span class="material-icons text-base">group</span>
                <span class="ml-1">Subalternos</span>
              </Link>
            </div>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Modal Ver -->
    <AdministrativosModalVer
      v-if="showVerModal"
      :administrativo="selectedAdministrativo"
      @close="closeVerModal"
    />

    <!-- Modal Editar -->
    <AdministrativoModalEditar
      v-if="showEditarModal"
      :administrativo="selectedAdministrativo"
      :cargos="cargos"
      :especialidades="especialidades"
      :generos="generos"
      @close="closeEditarModal"
    />
  </div>
</template>