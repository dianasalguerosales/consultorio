<script setup>
import { computed, ref } from 'vue'

/**
 * Un selector que se escribe para filtrar, en vez de un <select> que obliga a
 * buscar la opción a mano en una lista larga.
 *
 * Las opciones llegan ya normalizadas: `{ id, texto }`, y `texto` es a la vez
 * lo que se muestra y lo que se filtra. Así el que lo usa decide qué entra en
 * la búsqueda — en evaluaciones, el nombre del niño **y** su expediente, para
 * que se pueda llegar por cualquiera de los dos.
 */
const props = defineProps({
  modelValue: { type: [String, Number], default: '' },
  opciones: { type: Array, default: () => [] },
  etiqueta: { type: String, default: '' },
  marcador: { type: String, default: 'Escriba para buscar...' },
  sinCoincidencias: { type: String, default: 'Ningún resultado coincide.' },
  textoQuitar: { type: String, default: 'Quitar selección' },
  error: { type: String, default: '' },
  // Bloqueado se lee pero no se cambia: al corregir un grupo de objetivos, el
  // paciente ya no se toca — sería asignárselo a otro niño.
  deshabilitado: { type: Boolean, default: false },
})

const emit = defineEmits(['update:modelValue'])

const busqueda = ref('')
const listaAbierta = ref(false)

const elegida = computed(() =>
  props.opciones.find((o) => String(o.id) === String(props.modelValue)) ?? null
)

// Mientras no se esté escribiendo, el campo muestra lo que ya está elegido.
const texto = computed({
  get: () => (listaAbierta.value ? busqueda.value : elegida.value?.texto ?? ''),
  set: (valor) => { busqueda.value = valor },
})

const filtradas = computed(() => {
  const buscado = busqueda.value.trim().toLowerCase()
  if (!buscado) return props.opciones

  return props.opciones.filter((o) => String(o.texto).toLowerCase().includes(buscado))
})

function abrirLista() {
  if (props.deshabilitado) return

  busqueda.value = ''
  listaAbierta.value = true
}

function elegir(opcion) {
  emit('update:modelValue', opcion.id)
  busqueda.value = ''
  listaAbierta.value = false
}

function limpiar() {
  emit('update:modelValue', '')
  busqueda.value = ''
}
</script>

<template>
  <div class="relative">
    <label v-if="etiqueta" class="block text-sm font-medium text-caine-azul mb-1">{{ etiqueta }}</label>

    <!-- keydown.esc.stop: sin el .stop, Escape lo agarra el EscClose del modal
         y cierra el modal entero en vez de la lista. -->
    <input v-model="texto" type="text" :placeholder="marcador" :disabled="deshabilitado"
      class="block w-full border-gray-300 rounded-md shadow-sm disabled:bg-gray-100
             focus:ring-caine-celeste focus:border-caine-celeste"
      @focus="abrirLista" @blur="listaAbierta = false" @keydown.esc.stop="listaAbierta = false" />

    <!-- mousedown.prevent: con click, el blur cierra la lista antes de que el
         clic alcance la opción. -->
    <ul v-if="listaAbierta"
      class="absolute z-10 mt-1 w-full max-h-52 overflow-y-auto bg-white border border-gray-200 rounded-md shadow-lg">
      <li v-for="opcion in filtradas" :key="opcion.id" @mousedown.prevent="elegir(opcion)"
        class="px-3 py-2 text-sm cursor-pointer hover:bg-caine-celeste hover:text-white"
        :class="{ 'bg-gray-100': String(opcion.id) === String(modelValue) }">
        {{ opcion.texto }}
      </li>
      <li v-if="!filtradas.length" class="px-3 py-2 text-sm text-gray-400">
        {{ sinCoincidencias }}
      </li>
    </ul>

    <button v-if="modelValue && !deshabilitado" type="button" @click="limpiar"
      class="mt-1 text-xs text-gray-500 hover:text-caine-error">
      {{ textoQuitar }}
    </button>

    <p v-if="error" class="mt-1 text-sm text-caine-error">{{ error }}</p>
  </div>
</template>
