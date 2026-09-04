<script setup>
import { Head, usePage, useForm, router } from '@inertiajs/vue3'
import { ref } from 'vue'

const { props } = usePage()
const terapeutas = props.terapeutas

const isOpen = ref(false)
const selectedTerapeuta = ref(null)

const form = useForm({
  nombre: '',
  fecha_nacimiento: '',
  telefono: '',
  correo: '',
  numero_colegiado: '',
  especialidad: '',
  formacion: '',
  certificaciones: '',
})

function openModal(terapeuta) {
  selectedTerapeuta.value = terapeuta
  form.nombre = terapeuta.nombre
  form.fecha_nacimiento = terapeuta.fecha_nacimiento
  form.telefono = terapeuta.telefono
  form.correo = terapeuta.correo
  form.numero_colegiado = terapeuta.numero_colegiado
  form.especialidad = terapeuta.especialidad
  form.formacion = terapeuta.formacion
  form.certificaciones = terapeuta.certificaciones
  isOpen.value = true
}

function closeModal() {
  isOpen.value = false
}

function saveChanges() {
  if (selectedTerapeuta.value) {
    form.put(route('terapeutas.update', selectedTerapeuta.value.id), {
      onSuccess: () => {
        isOpen.value = false
        router.visit(route('terapeutas.index'), { only: ['terapeutas'] })
      }
    })
  } else {
    form.post(route('terapeutas.store'), {
      onSuccess: () => {
        isOpen.value = false
        router.visit(route('terapeutas.index'), { only: ['terapeutas'] })
      }
    })
  }
}

function deleteTerapeuta(terapeuta) {
  if (confirm(`¿Seguro que deseas eliminar a ${terapeuta.nombre}?`)) {
    form.delete(route('terapeutas.destroy', terapeuta.id), {
      onSuccess: () => {
        router.visit(route('terapeutas.index'), { only: ['terapeutas'] })
      }
    })
  }
}

function newTerapeuta() {
  selectedTerapeuta.value = null
  form.reset()
  isOpen.value = true
}
</script>

<template>
  <Head title="Gestión de Terapeutas" />

  <div class="p-8 max-w-7xl mx-auto">
    <h2 class="text-2xl font-bold text-caine-azul mb-6">Gestión de Terapeutas</h2>

    <!-- Botón para crear nuevo terapeuta -->
    <div class="mb-6 flex justify-end">
      <button class="bg-caine-celeste text-white px-6 py-3 rounded-lg font-semibold shadow hover:scale-105"
        @click="newTerapeuta">
        + Agregar Terapeuta
      </button>
    </div>

    <!-- Grid de terapeutas -->
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
      <div v-for="terapeuta in terapeutas" :key="terapeuta.id"
        class="bg-white shadow rounded-lg overflow-hidden flex flex-col items-center text-center">

        <!-- Avatar -->
        <div class="mt-6">
          <img :src="`https://ui-avatars.com/api/?name=${terapeuta.nombre}&background=random&size=128`"
            alt="Avatar" class="h-20 w-20 rounded-full mx-auto" />
        </div>

        <!-- Nombre y correo -->
        <div class="mt-4">
          <h3 class="text-lg font-semibold text-gray-900">{{ terapeuta.nombre }}</h3>
          <p class="text-sm text-gray-500">{{ terapeuta.correo }}</p>
        </div>

        <!-- Especialidad -->
        <div class="mt-2 text-sm text-gray-700">
          <p><strong>Especialidad:</strong> {{ terapeuta.especialidad || '---' }}</p>
          <p><strong>Colegiado:</strong> {{ terapeuta.numero_colegiado || '---' }}</p>
        </div>

        <!-- Acciones -->
        <div class="mt-6 grid grid-cols-2 divide-x divide-gray-200 border-t border-gray-200 w-full">
          <button
            class="py-3 text-sm font-medium text-caine-celeste hover:bg-gray-50" @click="openModal(terapeuta)">
            Editar
          </button>
          <button
            class="py-3 text-sm font-medium text-caine-error hover:bg-gray-50" @click="deleteTerapeuta(terapeuta)">
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
        {{ selectedTerapeuta ? 'Editar Terapeuta' : 'Nuevo Terapeuta' }}
      </h2>

      <!-- Formulario -->
      <form @submit.prevent="saveChanges" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700">Nombre</label>
          <input v-model="form.nombre" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Fecha Nacimiento</label>
          <input v-model="form.fecha_nacimiento" type="date" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Teléfono</label>
          <input v-model="form.telefono" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Correo</label>
          <input v-model="form.correo" type="email" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Número Colegiado</label>
          <input v-model="form.numero_colegiado" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Especialidad</label>
          <input v-model="form.especialidad" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Formación</label>
          <textarea v-model="form.formacion" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"></textarea>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Certificaciones</label>
          <textarea v-model="form.certificaciones" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"></textarea>
        </div>

        <!-- Botones -->
        <div class="flex justify-end space-x-3 mt-6">
          <button type="button" class="px-4 py-2 bg-gray-200 rounded-md hover:bg-gray-300" @click="closeModal">
            Cancelar
          </button>
          <button type="submit" class="px-4 py-2 bg-caine-celeste text-white rounded-md hover:bg-caine-azul">
            Guardar
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

export default {
  layout: AuthenticatedLayout
}
</script> 