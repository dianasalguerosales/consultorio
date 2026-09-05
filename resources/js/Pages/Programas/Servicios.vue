<script setup>
import { ref, computed } from 'vue'
import { useForm, router } from '@inertiajs/vue3'

const props = defineProps({
  servicios: { type: Array, default: () => [] }
})

const search = ref('')
const editando = ref(null)
const form = useForm({ nombre: '' })

function submit() {
  form.post('/servicios', {
    onSuccess: () => {
      form.reset() // ✅ limpia el campo después de guardar
    }
  })
}

function startEdit(servicio) {
  editando.value = servicio.id
  form.nombre = servicio.nombre
}

function update(servicio) {
  form.put(`/servicios/${servicio.id}`, {
    onSuccess: () => {
      editando.value = null
      form.reset()
    }
  })
}

function deleteServicio(servicio) {
  if (confirm(`¿Seguro que deseas eliminar el servicio "${servicio.nombre}"?`)) {
    router.delete(`/servicios/${servicio.id}`)
  }
}

const serviciosFiltrados = computed(() => {
  return props.servicios.filter(s => {
    const texto = `${s.id} ${s.nombre}`.toLowerCase()
    return texto.includes(search.value.toLowerCase())
  })
})
</script>

<template>
  <div class="bg-white rounded-lg shadow-md p-6 w-full">
    <!-- Encabezado -->
    <div class="flex justify-between items-center mb-6">
      <div class="relative">
        <span class="material-icons absolute left-2 top-1/2 transform -translate-y-1/2 text-gray-400">search</span>
        <input v-model="search" type="text" placeholder="Buscar..."
          class="pl-8 pr-3 py-2 border rounded-md text-md focus:ring-2 focus:ring-[#53C6D3]" />
      </div>
      <form @submit.prevent="submit">
        <div class="flex gap-2">
          <input v-model="form.nombre" type="text" placeholder="Nuevo servicio"
                 class="border rounded px-3 py-2 focus:ring-2 focus:ring-[#2D2B5B]" />
          <button type="submit"
                  class="inline-flex items-center px-4 py-2 bg-[#2D2B5B] text-white rounded-md hover:bg-green-700">
            <span class="material-icons mr-1">add_circle</span>
            <span>Agregar</span>
          </button>
        </div>
      </form>
    </div>

    <!-- Tabla -->
    <div class="overflow-x-auto">
      <table class="w-full border border-gray-200 text-md rounded-lg">
        <thead class="bg-gray-200 text-[#2D2B5B]">
          <tr>
            <th class="px-4 py-2 text-left">#</th> <!-- ✅ correlativo -->
            <th class="px-4 py-2 text-left">Nombre</th>
            <th class="px-4 py-2 text-center">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(servicio, index) in serviciosFiltrados" :key="servicio.id"
              class="border-t hover:bg-[#FAF9F7] transition">
            <td class="px-4 py-2 font-medium text-[#2D2B5B]">{{ index + 1 }}</td> <!-- correlativo -->
            <td class="px-4 py-2">
              <template v-if="editando === servicio.id">
                <input v-model="form.nombre" type="text"
                       class="border rounded px-2 py-1 focus:ring-2 focus:ring-[#2D2B5B]" />
              </template>
              <template v-else>
                {{ servicio.nombre }}
              </template>
            </td>
            <td class="px-4 py-2 text-center">
              <div class="flex justify-center space-x-2">
                <template v-if="editando === servicio.id">
                  <button @click="update(servicio)"
                          class="inline-flex items-center px-3 py-1 text-green-600 hover:text-green-800">
                    <span class="material-icons text-base">check</span>
                    <span class="ml-1">Guardar</span>
                  </button>
                  <button @click="editando = null"
                          class="inline-flex items-center px-3 py-1 text-gray-600 hover:text-gray-800">
                    <span class="material-icons text-base">close</span>
                    <span class="ml-1">Cancelar</span>
                  </button>
                </template>
                <template v-else>
                  <button @click="startEdit(servicio)"
                          class="inline-flex items-center px-3 py-1 text-[#53C6D3] hover:text-[#2D2B5B]">
                    <span class="material-icons text-base">edit</span>
                    <span class="ml-1">Editar</span>
                  </button>
                  <button @click="deleteServicio(servicio)"
                          class="inline-flex items-center px-3 py-1 text-red-600 hover:text-red-800">
                    <span class="material-icons text-base">delete</span>
                    <span class="ml-1">Eliminar</span>
                  </button>
                </template>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
