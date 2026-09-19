<script setup>
import { Head, usePage, useForm, router } from '@inertiajs/vue3'
import TarjetaFicha from '@/Components/TarjetaFicha.vue'
import { ref } from 'vue'
import UsuarioForm from '@/Components/UsuarioForm.vue'
import InfoModal from '@/Components/InfoModal.vue'
import { avatarUsuario } from '@/Utils/avatares'
import { fechaHora } from '@/Utils/fechas'
import { confirmarEliminacion } from '@/Utils/confirmar'

const { props } = usePage()
const usuarios = props.usuarios
const roles = props.roles

const isOpen = ref(false)
const selectedUser = ref(null)
const infoModalVisible = ref(false) // información modal generico 

const form = useForm({
  email: '',
  password: '',
  status: 'active',
  roles: [],
})

function openModal(user) {
  selectedUser.value = user
  form.email = user.email
  form.status = user.status
  form.roles = user.roles || []
  isOpen.value = true
}

function closeModal() {
  isOpen.value = false
}

function saveChanges() {
  if (selectedUser.value) {
    form.put(route('usuarios.update', selectedUser.value.id), {
      onSuccess: () => {
        isOpen.value = false
        router.visit(route('usuarios'), { only: ['usuarios'] })
      }
    })
  } else {
    form.post(route('usuarios.store'), {
      onSuccess: () => {
        isOpen.value = false
        router.visit(route('usuarios'), { only: ['usuarios'] })
      }
    })
  }
}

function deleteUser(user) {
  if (confirmarEliminacion(`el usuario ${user.email}`)) {
    form.delete(route('usuarios.destroy', user.id), {
      onSuccess: () => {
        router.visit(route('usuarios'), { only: ['usuarios'] })
      }
    })
  }
}

function newUser() {
  selectedUser.value = null
  form.reset()
  isOpen.value = true
}

//función para abrir el modal de información
function openInfoModal(user) {
  selectedUser.value = user
  infoModalVisible.value = true
}

const roleColors = {
  administrador: 'bg-caine-azul text-white',
  coordinador: 'bg-caine-verde text-white',
  terapeuta: 'bg-caine-morado text-white',
  encargado: 'bg-caine-celeste text-white',
  pruebas: 'bg-caine-rosa text-white',
}

const capitalize = (str) => str.charAt(0).toUpperCase() + str.slice(1)
</script>

<template>
  <Head title="Gestión de Usuarios" />

  <div class="p-8 max-w-7xl mx-auto">
    <h2 class="text-2xl font-bold text-caine-azul mb-6">Gestión de Usuarios</h2>

    <div class="mb-6 flex justify-end" v-if="$page.props.auth.user.permissions.includes('gestionar usuarios')">
      <button class="bg-caine-celeste text-white px-6 py-3 rounded-lg font-semibold shadow hover:scale-105"
        @click="newUser">
        + Agregar Usuario
      </button>
    </div>

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
      <TarjetaFicha v-for="usuario in usuarios" :key="usuario.id"
        :titulo="usuario.terapeuta?.nombre_completo
          || usuario.encargado?.nombre_completo
          || usuario.administrativo?.nombre_completo
          || '---'">

        <template #imagen>
          <img :src="avatarUsuario(usuario.roles, usuario.encargado?.genero)" alt="Avatar"
            class="h-20 w-20 rounded-full mx-auto" />
        </template>

        <template #datos>
          <p class="text-sm text-gray-500">{{ usuario.email }}</p>
        </template>

        <div class="mt-4 flex flex-wrap justify-center gap-2 px-4">
          <span v-for="role in usuario.roles" :key="role"
            :class="['px-3 py-1 rounded-md text-sm font-semibold', roleColors[role] || 'bg-gray-200 text-gray-700']">
            {{ capitalize(role) }}
          </span>
          <span v-if="!usuario.roles.length"
            class="bg-gray-200 text-gray-700 px-3 py-1 rounded-md text-sm font-semibold">
            Sin rol
          </span>
        </div>

        <template #acciones>
          <!-- Botón Ver (nuevo modal de información) -->
          <button v-if="$page.props.auth.user.permissions.includes('gestionar usuarios')"
            class="py-3 text-sm font-medium text-caine-celeste hover:bg-gray-50" @click="openInfoModal(usuario)">
            Ver
          </button>

          <!-- Botón Eliminar -->
          <button v-if="$page.props.auth.user.permissions.includes('gestionar usuarios')"
            class="py-3 text-sm font-medium text-caine-error hover:bg-gray-50" @click="deleteUser(usuario)">
            Eliminar
          </button>
        </template>

        <template #pie>
          <div class="border-t border-gray-200 w-full">
            <!-- Botón Gestionar permisos ocupa toda la parte inferior -->
            <button v-if="$page.props.auth.user.permissions.includes('gestionar usuarios')"
              class="py-3 w-full text-sm font-medium text-white bg-caine-verde hover:bg-caine-celeste/80 rounded-b-md"
              @click="openModal(usuario)">
              Editar
            </button>
          </div>
        </template>
      </TarjetaFicha>
    </div>
  </div>

  <!-- Modal -->
  <UsuarioForm v-if="isOpen" :user="selectedUser" :form="form" :roles="roles" @close="closeModal" @save="saveChanges" />

  <!-- Modal genérico de información -->
  <InfoModal v-if="infoModalVisible" :visible="infoModalVisible" title="Información del Usuario" :data="{
    'Email': selectedUser.email,
    'Estado': selectedUser.status,
    'Última vez': fechaHora(selectedUser.last_login_at, 'Nunca')
  }" @close="infoModalVisible = false" />
</template>

<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

export default {
  layout: AuthenticatedLayout
}
</script>