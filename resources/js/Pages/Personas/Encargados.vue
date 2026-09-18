<script setup>
import { ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import EncargadoModalVer from '@/Components/personas/EncargadoModalVer.vue'
import EncargadoModalEditar from '@/Components/personas/EncargadoModalEditar.vue'
import { avatarUsuario } from '@/Utils/avatares'
import RolesModal from '@/Components/personas/RolesModal.vue'
import { confirmarEliminacion } from '@/Utils/confirmar'
import TablaBase from '@/Components/TablaBase.vue'

const props = defineProps({
  encargados: Array,
  roles: Array,
  generos: Array,
  estadosCiviles: Array,
  relacionesPaciente: Array,
  usuariosDisponibles: Array
})

const showViewModal = ref(false)
const showEditModal = ref(false)
const enRoles = ref(null)
const selectedEncargado = ref(null)

function openViewModal(encargado) {
  selectedEncargado.value = encargado
  showViewModal.value = true
}
function closeViewModal() {
  showViewModal.value = false
  selectedEncargado.value = null
}

function openEditModal(encargado) {
  selectedEncargado.value = encargado
  showEditModal.value = true
}
function closeEditModal() {
  showEditModal.value = false
  selectedEncargado.value = null
}

function abrirRoles(persona) {
  enRoles.value = persona
}
</script>

<template>
  <div>
  <TablaBase>
      <!-- Encabezado -->
      <thead class="bg-gray-200 text-[#2D2B5B]">
        <tr>
          <th class="px-4 py-2 text-center">Avatar</th>
          <th class="px-4 py-2 text-left">Nombre</th>
          <th class="px-4 py-2 text-left">Correo</th>
          <th class="px-4 py-2 text-left">Relación Paciente</th>
          <th class="px-4 py-2 text-center">Acciones</th>
        </tr>
      </thead>

      <!-- Filas -->
      <tbody>
        <tr v-for="e in encargados" :key="e.id" class="border-t hover:bg-[#FAF9F7] transition">
          <!-- Avatar -->
          <td class="px-4 py-2 text-center">
            <img :src="avatarUsuario(['encargado'], e.genero)" :alt="e.relacionPaciente?.nombre ?? 'Encargado'"
              class="w-10 h-10 rounded-full border inline-block" />
          </td>

          <!-- Nombre -->
          <td class="px-4 py-2 font-medium text-[#2D2B5B]">
            {{ e.nombres }} {{ e.apellidos }}
          </td>

          <!-- Correo -->
          <td class="px-4 py-2 text-gray-700">{{ e.correo }}</td>

          <!-- Relación Paciente -->
          <td class="px-4 py-2 text-gray-700">{{ e.relacion_paciente?.nombre }}</td>

          <!-- Acciones -->
          <td class="px-4 py-2 text-center">
            <div class="flex justify-center space-x-2">
              <!-- Ver -->
              <button @click="openViewModal(e)"
                class="inline-flex items-center px-3 py-1 text-[#74BE69] hover:text-[#1f1d3f]">
                <span class="material-icons text-base">assignment</span>
                <span class="ml-1">Ver</span>
              </button>

              <!-- Editar -->
              <button @click="openEditModal(e)"
                class="inline-flex items-center px-3 py-1 text-[#53C6D3] hover:text-[#2D2B5B]">
                <span class="material-icons text-base">edit</span>
                <span class="ml-1">Editar</span>
              </button>

              <!-- Eliminar -->
              <Link as="button" method="delete" :href="`/personas/encargados/${e.id}`"
                @before="confirmarEliminacion(`al encargado ${e.nombres} ${e.apellidos}`)"
                class="inline-flex items-center px-3 py-1 text-red-600 hover:text-red-800">
                <span class="material-icons text-base">delete</span>
                <span class="ml-1">Eliminar</span>
              </Link>

              <!-- Permisos: se gestionan sobre el usuario ligado a la persona. -->
              <button type="button" @click="abrirRoles(e)"
                class="inline-flex items-center px-3 py-1 text-[#2D2B5B] hover:text-[#53C6D3]">
                <span class="material-icons text-base">lock</span>
                <span class="ml-1">Permisos</span>
              </button>

              <!-- Pacientes -->
              <Link :href="`/personas/encargado/${e.id}/pacientes`"
                class="inline-flex items-center px-3 py-1 text-[#2D2B5B] hover:text-[#53C6D3]">
                <span class="material-icons text-base">groups</span>
                <span class="ml-1">Pacientes</span>
              </Link>
            </div>
          </td>
        </tr>
      </tbody>
    </TablaBase>

    <!-- Modal de perfil -->
    <EncargadoModalVer v-if="showViewModal" :encargado="selectedEncargado" @close="closeViewModal" />

    <!-- Modal de edición -->
    <EncargadoModalEditar v-if="showEditModal" :encargado="selectedEncargado" :generos="generos"
      :estadosCiviles="estadosCiviles" :relacionesPaciente="relacionesPaciente"
      :usuariosDisponibles="usuariosDisponibles" @close="closeEditModal" />
    <RolesModal v-if="enRoles" :persona="enRoles" tipo="encargado" :roles="roles ?? []" @close="enRoles = null" />
  </div>
</template>