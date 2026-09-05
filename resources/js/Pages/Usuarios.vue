<script setup>
import { Head, usePage, useForm, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import UsuarioForm from '@/Components/UsuarioForm.vue'

const { props } = usePage()
const usuarios = props.usuarios
const roles = props.roles

const isOpen = ref(false)
const selectedUser = ref(null)

const form = useForm({
  email: '',
  password: '',
  roles: [],
  tipo_usuario: '',
  nombre: '',
  telefono: '',
  direccion: '',
  fecha_nacimiento: '',
  genero: '',
  especialidad: '',
  numero_colegiado: '',
  experiencia: '',
  formacion: '',
  certificaciones: '',
  relacion: '',
  tipo: '',
})

function openModal(user) {
  selectedUser.value = user
  form.roles = user.roles
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
  if (confirm(`¿Seguro que deseas eliminar a ${user.email}?`)) {
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

    <!-- Botón para crear nuevo usuario -->
    <div class="mb-6 flex justify-end" v-if="$page.props.auth.user.permissions.includes('gestionar usuarios')">
      <button class="bg-caine-celeste text-white px-6 py-3 rounded-lg font-semibold shadow hover:scale-105"
        @click="newUser">
        + Agregar Usuario
      </button>
    </div>

    <!-- Grid de usuarios -->
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
      <div v-for="usuario in usuarios" :key="usuario.id"
        class="bg-white shadow rounded-lg overflow-hidden flex flex-col items-center text-center">
        
        <!-- Avatar -->
                <div class="mt-6">
                    <img
                        :src="
                            usuario.roles.includes('terapeuta')
                                ? '/images/Terapeuta.webp'
                                : usuario.roles.includes('administrador')
                                ? '/images/Admin.webp'
                                : usuario.roles.includes('coordinador')
                                ? '/images/Coordinador.webp'
                                : usuario.roles.includes('encargado')
                                ? '/images/Madre.webp'
                                : '/images/avatar.webp'
                        "
                        alt="Avatar"
                        class="h-20 w-20 rounded-full mx-auto"
                    />
                </div>

        <!-- Nombre y correo -->
        <div class="mt-4">
          <h3 class="text-lg font-semibold text-gray-900">
            {{ usuario.terapeuta?.nombre || usuario.encargado?.nombre || usuario.administrativo?.nombre || '---' }}
          </h3>
          <p class="text-sm text-gray-500">{{ usuario.email }}</p>
        </div>

        <!-- Roles -->
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

        <!-- Acciones -->
        <div class="mt-6 grid grid-cols-2 divide-x divide-gray-200 border-t border-gray-200 w-full">
          <button v-if="$page.props.auth.user.permissions.includes('gestionar usuarios')"
            class="py-3 text-sm font-medium text-caine-celeste hover:bg-gray-50" @click="openModal(usuario)">
            Editar
          </button>
          <button v-if="$page.props.auth.user.permissions.includes('gestionar usuarios')"
            class="py-3 text-sm font-medium text-caine-error hover:bg-gray-50" @click="deleteUser(usuario)">
            Eliminar
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal -->
  <UsuarioForm v-if="isOpen" :user="selectedUser" :form="form" :roles="roles" @close="closeModal" @save="saveChanges" />
</template>

<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

export default {
  layout: AuthenticatedLayout
}
</script>
