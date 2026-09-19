<script setup>
import { computed } from 'vue'
import ModalCapa from '@/Components/ModalCapa.vue'
import BuscadorSelect from '@/Components/BuscadorSelect.vue'

const props = defineProps({
  paciente: Object,
  form: Object,
  generos: Array,
  escolaridades: Array,
  encargados: Array,
})

const emit = defineEmits(['close', 'save'])

// buscar el nombre a mano. Acá se escribe y se filtra.
const nombreEncargado = (e) => [e?.nombres, e?.apellidos].filter(Boolean).join(' ')

const opcionesEncargados = computed(() =>
  (props.encargados ?? []).map((e) => ({ id: e.id, texto: nombreEncargado(e) }))
)

</script>

<template>
  <ModalCapa panel="max-w-md p-6" @close="emit('close')">

    <h2 class="text-lg font-bold text-caine-azul mb-4">
      {{ paciente ? 'Editar Paciente' : 'Nuevo Paciente' }}
    </h2>

    <!-- Nombres -->
    <div class="mb-4">
      <label class="block text-sm font-medium text-gray-700">Nombres</label>
      <input v-model="form.nombres" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
    </div>

    <!-- Apellidos -->
    <div class="mb-4">
      <label class="block text-sm font-medium text-gray-700">Apellidos</label>
      <input v-model="form.apellidos" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
    </div>

    <!-- Género -->
    <div class="mb-4">
      <label class="block text-sm font-medium text-gray-700">Género</label>

      <select v-model="form.genero_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">

        <option value="">Seleccione...</option>

        <option v-for="genero in generos" :key="genero.id" :value="genero.id">
          {{ genero.nombre }}
        </option>

      </select>
    </div>

    <!-- Escolaridad -->
    <div class="mb-4">
      <label class="block text-sm font-medium text-gray-700">Escolaridad</label>

      <select v-model="form.escolaridad_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">

        <option value="">Seleccione...</option>

        <option v-for="escolaridad in escolaridades" :key="escolaridad.id" :value="escolaridad.id">
          {{ escolaridad.nombre }}
        </option>

      </select>
    </div>

    <!-- Encargado: se escribe para filtrar la lista, que es larga. -->
    <BuscadorSelect v-model="form.encargado_id" :opciones="opcionesEncargados"
      etiqueta="Encargado" marcador="Escriba el nombre del encargado..."
      sin-coincidencias="Ningún encargado coincide." texto-quitar="Quitar encargado"
      class="mb-4" />

    <div class="flex justify-end space-x-3 mt-6">
      <button class="px-4 py-2 bg-gray-200 rounded-md" @click="$emit('close')">
        Cancelar
      </button>

      <button class="px-4 py-2 bg-caine-celeste text-white rounded-md" @click="$emit('save')">
        Guardar
      </button>
    </div>
  </ModalCapa>
</template>