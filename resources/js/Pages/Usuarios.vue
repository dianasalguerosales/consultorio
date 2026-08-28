<script setup>
import { Head, usePage, useForm, router } from '@inertiajs/vue3'
import { ref } from 'vue'

const { props } = usePage()
const usuarios = props.usuarios
const roles = props.roles

const isOpen = ref(false)
const selectedUser = ref(null)

const form = useForm({
  name: '',
  email: '',
  password: '',
  roles: []
})

function openModal(user) {
  selectedUser.value = user
  form.name = user.name
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
  if (confirm(`¿Seguro que deseas eliminar a ${user.name}?`)) {
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
  form.name = ''
  form.email = ''
  form.password = ''
  form.roles = []
  isOpen.value = true
}

const roleColors = {
  administrador: 'bg-caine-azul text-white',
  coordinador: 'bg-caine-verde text-white',
  terapeuta: 'bg-caine-morado text-white',
  encargado: 'bg-caine-celeste text-white',
  pruebas: 'bg-caine-rosa text-white',
}
</script>

<template>

  <Head title="Gestión de Usuarios" />

  <div class="p-8 max-w-7xl mx-auto">
    <h2 class="text-2xl font-bold text-caine-azul mb-6">Gestión de Usuarios</h2>

    <!-- Botón para crear nuevo usuario -->
    <div class="mb-6 flex justify-end" v-if="$page.props.auth.user.permissions.includes('gestionar usuarios')">
      <button
        class="bg-caine-celeste text-white px-6 py-3 rounded-lg font-semibold shadow transform transition-transform duration-200 hover:scale-105"
        @click="newUser">
        + Agregar Usuario
      </button>
    </div>


    <!-- Grid estilo Contact Cards -->
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
      <div v-for="usuario in usuarios" :key="usuario.id"
        class="bg-white shadow rounded-lg overflow-hidden flex flex-col items-center text-center">
        <!-- Imagen centrada arriba -->
        <div class="mt-6">
          <img :src="`https://ui-avatars.com/api/?name=${usuario.name}&background=random&size=128`" alt="Avatar"
            class="h-20 w-20 rounded-full mx-auto" />

        </div>

        <!-- Nombre y correo -->
        <div class="mt-4">
          <h3 class="text-lg font-semibold text-gray-900">{{ usuario.name }}</h3>
          <p class="text-sm text-gray-500">{{ usuario.email }}</p>
        </div>

        <!-- Roles -->
        <div class="mt-4 flex flex-wrap justify-center gap-2 px-4">
          <span v-for="role in usuario.roles" :key="role"
            :class="['px-3 py-1 rounded-md text-sm font-semibold', roleColors[role] || 'bg-gray-200 text-gray-700']">
            {{ role }}
          </span>
          <span v-if="!usuario.roles.length"
            class="bg-gray-200 text-gray-700 px-3 py-1 rounded-md text-sm font-semibold">
            Sin rol
          </span>
        </div>

        <!-- Botones abajo divididos en dos -->
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
  <div v-if="isOpen" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-30">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
      <h2 class="text-lg font-bold text-caine-azul mb-4">
        {{ selectedUser ? 'Editar Usuario' : 'Nuevo Usuario' }}
      </h2>

      <!-- Nombre -->
      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700">Nombre</label>
        <input v-model="form.name" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
        <div v-if="form.errors.name" class="text-red-500 text-sm">{{ form.errors.name }}</div>
      </div>

      <!-- Correo -->
      <div class="mb-4" v-if="!selectedUser">
        <label class="block text-sm font-medium text-gray-700">Correo</label>
        <input v-model="form.email" type="email" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
        <div v-if="form.errors.email" class="text-red-500 text-sm">{{ form.errors.email }}</div>
      </div>

      <!-- Contraseña -->
      <div class="mb-4" v-if="!selectedUser">
        <label class="block text-sm font-medium text-gray-700">Contraseña</label>
        <input v-model="form.password" type="password" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
        <div v-if="form.errors.password" class="text-red-500 text-sm">{{ form.errors.password }}</div>
      </div>

      <!-- Roles -->
      <label v-for="role in roles" :key="role.id" class="flex items-center space-x-2">
        <input type="checkbox" :value="role.name" v-model="form.roles" class="rounded border-gray-300" />
        <span>{{ role.name }}</span>
      </label>

      <!-- Botones -->
      <div class="flex justify-end space-x-3 mt-6">
        <button class="px-4 py-2 bg-gray-200 rounded-md hover:bg-gray-300" @click="closeModal">
          Cancelar
        </button>
        <button class="px-4 py-2 bg-caine-celeste text-white rounded-md hover:bg-caine-azul" @click="saveChanges">
          Guardar
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

export default {
  layout: AuthenticatedLayout
}
</script>
