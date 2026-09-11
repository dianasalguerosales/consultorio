<script setup>
import { computed } from 'vue'
import { Head } from '@inertiajs/vue3'
import { fechaCorta } from '@/Utils/fechas'

const props = defineProps({
  cumpleanos: { type: Object, default: () => ({ hoy: [], manana: [] }) },
})

const hay = computed(() => props.cumpleanos.hoy.length + props.cumpleanos.manana.length > 0)

const COLOR_TIPO = {
  Paciente: 'bg-caine-celeste text-white',
  Terapeuta: 'bg-caine-morado text-white',
  Personal: 'bg-caine-azul text-white',
  Encargado: 'bg-caine-verde text-white',
}
</script>

<template>

  <Head title="Panel administrador" />

  <div class="p-4 sm:p-6">
    <h2 class="text-2xl font-bold text-[#2D2B5B] mb-4">Bienvenido al Panel</h2>
    <p class="text-[#2B2B41] mb-6">Selecciona una opción del menú lateral para comenzar.</p>

    <!-- Cumpleaños: para saludar a quien llega hoy y preparar lo de mañana -->
    <div v-if="hay" class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div v-for="grupo in [
        { clave: 'hoy', titulo: 'Cumpleaños de hoy', icono: 'cake', lista: cumpleanos.hoy },
        { clave: 'manana', titulo: 'Cumpleaños de mañana', icono: 'event', lista: cumpleanos.manana },
      ]" :key="grupo.clave" class="bg-white rounded-lg shadow-md p-5">
        <h3 class="flex items-center gap-2 text-lg font-bold text-[#2D2B5B] mb-3">
          <span class="material-icons text-caine-naranja">{{ grupo.icono }}</span>
          {{ grupo.titulo }}
        </h3>

        <ul v-if="grupo.lista.length" class="divide-y divide-gray-100">
          <li v-for="p in grupo.lista" :key="`${p.tipo}-${p.nombre}`"
            class="flex flex-wrap items-center justify-between gap-2 py-2">
            <div class="min-w-0">
              <p class="font-medium text-[#2D2B5B] truncate">{{ p.nombre }}</p>
              <p class="text-sm text-gray-500">
                Cumple {{ p.edad }} {{ p.edad === 1 ? 'año' : 'años' }} · {{ fechaCorta(p.nacimiento) }}
              </p>
            </div>
            <span class="px-2 py-1 rounded-full text-xs font-semibold whitespace-nowrap"
              :class="COLOR_TIPO[p.tipo] ?? 'bg-gray-200 text-gray-700'">
              {{ p.tipo }}
            </span>
          </li>
        </ul>

        <p v-else class="text-sm text-gray-400 py-2">Nadie cumple años.</p>
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
