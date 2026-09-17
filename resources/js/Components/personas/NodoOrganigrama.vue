<script setup>
import { computed } from 'vue'
import { avatarPaciente, avatarUsuario } from '@/Utils/avatares'

const props = defineProps({
  nodo: { type: Object, required: true },
  nivel: { type: Number, default: 0 },
})

/* ---------- Colores por rol ---------- */
const coloresRol = {
  administrador: '#2D2B5B',
  coordinador: '#53C6D3',
  auxiliar: '#F4A654',
  terapeuta: '#8B70CD',
  paciente: '#FF6B6B'
}

const colorLinea = computed(() => coloresRol[props.nodo.rol] || '#ccc')

// El paciente se distingue por género y el personal por rol. El encargado es
// el caso mixto: entra por rol, pero la imagen sale del género (Madre/Padre).
const avatar = computed(() =>
  props.nodo.rol === 'paciente'
    ? avatarPaciente(props.nodo.genero)
    : avatarUsuario([props.nodo.rol], props.nodo.genero)
)

/* ---------- Hijos ---------- */
const hijos = computed(() => Array.isArray(props.nodo.subalternos) ? props.nodo.subalternos : [])

/* ---------- Travesaño ---------- */

// Con n tarjetas repartidas por igual, el centro de la primera cae a la mitad
// de su ancho, o sea 50%/n del total. Recortar eso por lado deja la línea justo
// entre los centros en vez de sobresalir hasta los bordes exteriores.
function recorteTravesano(n) {
  const recorte = `calc(50% / ${n})`
  return { left: recorte, right: recorte }
}

/* ---------- Agrupar en pares ---------- */
function agruparEnPares(arr) {
  const chunks = []
  for (let i = 0; i < arr.length; i += 2) {
    chunks.push(arr.slice(i, i + 2))
  }
  return chunks
}
</script>

<template>
  <li class="flex flex-col items-center">
    <!-- Card -->
    <div class="flex flex-col rounded-lg shadow-md min-w-[18rem] bg-white border border-gray-200">
      <div class="flex items-center gap-3 px-4 py-3">
        <img :src="avatar" :alt="nodo.cargo ?? ''"
          class="w-12 h-12 rounded-full object-cover shrink-0 border border-gray-300" />
        <div class="text-left">
          <p class="font-semibold text-gray-900">{{ nodo.nombre }}</p>
          <p class="text-sm text-gray-600">{{ nodo.cargo ?? 'Sin cargo' }}</p>
          <p v-if="nodo.correo" class="text-xs text-gray-500">{{ nodo.correo }}</p>
        </div>
      </div>
      <!-- Línea inferior de color -->
      <div class="h-2 w-full rounded-b-lg" :style="{ backgroundColor: colorLinea }"></div>
    </div>

    <!-- Conexión hacia hijos -->
    <template v-if="hijos.length">
      <div class="w-px h-6 bg-gray-300"></div>

      <!-- Renderizar hijos en pares -->
      <div v-for="(par, idx) in agruparEnPares(hijos)" :key="idx" class="flex flex-col items-center">
        <ul class="flex justify-center gap-8 relative">
          <div v-if="par.length > 1" :style="recorteTravesano(par.length)"
            class="absolute top-0 border-t border-gray-300"></div>

          <li v-for="hijo in par" :key="hijo.id" class="flex flex-col items-center">
            <div class="w-px h-6 bg-gray-300"></div>
            <NodoOrganigrama :nodo="hijo" :nivel="nivel + 1" />
          </li>
        </ul>
      </div>
    </template>
  </li>
</template>
