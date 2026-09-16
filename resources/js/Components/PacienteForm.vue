<script setup>
import { computed, ref } from 'vue'
import ModalCapa from '@/Components/ModalCapa.vue'

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

const busquedaEncargado = ref('')
const listaAbierta = ref(false)

const encargadoElegido = computed(() =>
  (props.encargados ?? []).find((e) => String(e.id) === String(props.form.encargado_id)) ?? null
)

// Mientras no se esté escribiendo, el input muestra a quien ya está elegido.
const textoEncargado = computed({
  get: () => (listaAbierta.value ? busquedaEncargado.value : nombreEncargado(encargadoElegido.value)),
  set: (valor) => { busquedaEncargado.value = valor },
})

const encargadosFiltrados = computed(() => {
  const texto = busquedaEncargado.value.trim().toLowerCase()
  const lista = props.encargados ?? []
  if (!texto) return lista

  return lista.filter((e) => nombreEncargado(e).toLowerCase().includes(texto))
})

function abrirLista() {
  busquedaEncargado.value = ''
  listaAbierta.value = true
}

function elegirEncargado(encargado) {
  props.form.encargado_id = encargado.id
  busquedaEncargado.value = ''
  listaAbierta.value = false
}

function limpiarEncargado() {
  props.form.encargado_id = ''
  busquedaEncargado.value = ''
}

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

    <!-- Encargado -->
    <!-- se escribe para filtrar la lista, que es larga. -->
    <div class="mb-4 relative">
      <label class="block text-sm font-medium text-gray-700">Encargado</label>

      <!-- keydown.esc.stop: sin el .stop, Escape lo agarra EscClose de ModalCapa y cierra el modal entero en vez de la lista. -->
      <input v-model="textoEncargado" type="text" placeholder="Escriba el nombre del encargado..."
        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-caine-celeste focus:border-caine-celeste"
        @focus="abrirLista" @blur="listaAbierta = false" @keydown.esc.stop="listaAbierta = false" />

      <!-- mousedown.prevent: con click, el blur cierra la lista antes de que el clic llegue a la opción. -->
      <ul v-if="listaAbierta"
        class="absolute z-10 mt-1 w-full max-h-52 overflow-y-auto bg-white border border-gray-200 rounded-md shadow-lg">
        <li v-for="encargado in encargadosFiltrados" :key="encargado.id" @mousedown.prevent="elegirEncargado(encargado)"
          class="px-3 py-2 text-sm cursor-pointer hover:bg-caine-celeste hover:text-white"
          :class="{ 'bg-gray-100': String(encargado.id) === String(form.encargado_id) }">
          {{ nombreEncargado(encargado) }}
        </li>
        <li v-if="!encargadosFiltrados.length" class="px-3 py-2 text-sm text-gray-400">
          Ningún encargado coincide.
        </li>
      </ul>

      <button v-if="form.encargado_id" type="button" @click="limpiarEncargado"
        class="mt-1 text-xs text-gray-500 hover:text-caine-error">
        Quitar encargado
      </button>
    </div>

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