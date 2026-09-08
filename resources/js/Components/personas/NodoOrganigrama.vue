<script setup>
import { computed } from 'vue'
import { avatarUsuario } from '@/Utils/avatares'
import { RAMPA } from '@/Utils/paleta'

const props = defineProps({
  nodo: { type: Object, required: true },
  // Profundidad desde la raíz: define el color del nodo.
  nivel: { type: Number, default: 0 },
})

/* ---------- Color por nivel ---------- */

// El rango es ordinal, así que va la rampa secuencial: mientras más arriba en
// el organigrama, más oscuro. El cargo va escrito en el nodo, así que la
// identidad nunca depende solo del color.
const paso = computed(() => RAMPA[Math.max(0, RAMPA.length - 1 - props.nivel)])

const hijos = computed(() => props.nodo.subalternos ?? [])
</script>

<template>
  <li class="flex flex-col items-center">
    <!-- Tarjeta -->
    <div class="flex items-center gap-3 rounded-lg px-4 py-3 shadow-sm min-w-[15rem]"
      :style="{ backgroundColor: paso.fondo, color: paso.texto }">
      <img :src="avatarUsuario([nodo.rol])" :alt="nodo.cargo ?? ''"
        class="w-10 h-10 rounded-full bg-white/80 object-cover shrink-0" />

      <div class="text-left">
        <p class="font-semibold leading-tight">{{ nodo.nombre }}</p>
        <p class="text-xs opacity-80">{{ nodo.cargo ?? 'Sin cargo' }}</p>
        <p v-if="nodo.correo" class="text-xs opacity-70">{{ nodo.correo }}</p>
      </div>
    </div>

    <template v-if="hijos.length">
      <!-- Bajada desde el padre hacia la fila de hijos -->
      <span class="w-px h-6 bg-gray-300"></span>

      <ul class="flex justify-center gap-8 relative">
        <!-- Travesaño horizontal. Se recorta a la mitad de la primera y la
             última tarjeta para no sobresalir de la fila. -->
        <span v-if="hijos.length > 1"
          class="absolute top-0 left-[calc(50%/var(--n))] right-[calc(50%/var(--n))] h-px bg-gray-300"
          :style="{ '--n': hijos.length }"></span>

        <li v-for="hijo in hijos" :key="hijo.id" class="flex flex-col items-center">
          <span class="w-px h-6 bg-gray-300"></span>
          <NodoOrganigrama :nodo="hijo" :nivel="nivel + 1" />
        </li>
      </ul>
    </template>
  </li>
</template>
