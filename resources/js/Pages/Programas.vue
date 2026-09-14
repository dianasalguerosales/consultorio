<script setup>
import { ref, computed } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import GenerarPaqueteModal from '@/Components/GenerarPaqueteModal.vue'
import { avatarPaciente } from '@/Utils/avatares'
import { fecha } from '@/Utils/fechas'
import { confirmarEliminacion } from '@/Utils/confirmar'

const props = defineProps({
  asignaciones: { type: Array, default: () => [] },
  puedeRenovar: { type: Boolean, default: false },
})

// El paquete que se está por generar. No se renueva solo: lo dispara el
// coordinador desde el botón, que es lo que pidió Diana.
const renovando = ref(null)

const search = ref('')

const NOMBRE_DIA = { 1: 'Lun', 2: 'Mar', 3: 'Mié', 4: 'Jue', 5: 'Vie', 6: 'Sáb', 7: 'Dom' }

const ESTADOS = {
  activo: 'bg-green-100 text-green-700',
  finalizado: 'bg-gray-100 text-gray-600',
  cancelado: 'bg-red-100 text-red-700',
}

const quetzales = (n) => `Q${Number(n ?? 0).toFixed(2)}`

const dias = (lista) => (lista ?? []).map((d) => NOMBRE_DIA[d] ?? d).join(', ')

// Lo que cuesta cada cita: es el dato que termina en el cobro.
const porCita = (a) => (a.cantidad_citas > 0 ? a.precio / a.cantidad_citas : 0)

const asignacionesFiltradas = computed(() => {
  const texto = search.value.toLowerCase()

  return props.asignaciones.filter((a) =>
    `${a.paciente} ${a.programa} ${a.servicio} ${a.atiende}`.toLowerCase().includes(texto)
  )
})

function cancelar(a) {
  if (!confirmarEliminacion(
    `el programa de ${a.paciente} y se retirarán del calendario sus citas pendientes`
  )) return

  router.delete(`/programas/${a.id}`, { preserveScroll: true })
}
</script>

<template>

  <Head title="Programas" />
  <div class="bg-white rounded-lg shadow-md p-8 w-full">
    <!-- Encabezado -->
    <div class="flex flex-wrap justify-between items-center gap-3 mb-6">
      <div>
        <h2 class="text-2xl font-bold text-[#2D2B5B]">Programas</h2>
        <p class="text-sm text-gray-500">
          Se asignan desde la ficha de cada paciente. El paquete del mes siguiente
          se genera a mano, con el botón de cada fila.
        </p>
      </div>
      <div class="relative">
        <span class="material-icons absolute left-2 top-1/2 transform -translate-y-1/2 text-gray-400">search</span>
        <input v-model="search" type="text" placeholder="Buscar..."
          class="pl-8 pr-3 py-2 border rounded-md text-md focus:ring-2 focus:ring-[#53C6D3]" />
      </div>
    </div>

    <!-- Tabla -->
    <div class="overflow-x-auto">
      <table class="w-full border border-gray-200 text-md rounded-lg">
        <thead class="bg-gray-200 text-[#2D2B5B]">
          <tr>
            <th class="px-2 py-2 text-center w-12"></th>
            <th class="px-4 py-2 text-left">Paciente</th>
            <th class="px-4 py-2 text-left">Programa</th>
            <th class="px-4 py-2 text-left">Días y horario</th>
            <th class="px-4 py-2 text-left">Atiende</th>
            <th class="px-4 py-2 text-right">Costo</th>
            <th class="px-4 py-2 text-right">Por cita</th>
            <th class="px-4 py-2 text-center">Citas</th>
            <th class="px-4 py-2 text-left">Inicia</th>
            <th class="px-4 py-2 text-left">Estado</th>
            <th class="px-4 py-2 text-center">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="a in asignacionesFiltradas" :key="a.id" class="border-t hover:bg-[#FAF9F7] transition">
            <td class="px-2 py-2 text-center">
              <img :src="avatarPaciente(a.genero)" alt="avatar" class="w-8 h-8 rounded-full border inline-block" />
            </td>
            <td class="px-4 py-2 font-medium text-[#2D2B5B]">{{ a.paciente }}</td>
            <td class="px-4 py-2">
              {{ a.programa }}
              <span class="block text-sm text-gray-500">{{ a.servicio }}</span>
            </td>
            <td class="px-4 py-2 whitespace-nowrap">
              {{ dias(a.dias) }}
              <span class="block text-sm text-gray-500">{{ a.hora }}</span>
            </td>
            <td class="px-4 py-2">{{ a.atiende }}</td>
            <td class="px-4 py-2 text-right whitespace-nowrap">{{ quetzales(a.precio) }}</td>
            <td class="px-4 py-2 text-right whitespace-nowrap">{{ quetzales(porCita(a)) }}</td>
            <td class="px-4 py-2 text-center whitespace-nowrap">
              {{ a.citas_creadas }}
              <span v-if="a.citas_creadas !== a.cantidad_citas" class="text-gray-500">/ {{ a.cantidad_citas }}</span>
            </td>
            <td class="px-4 py-2 whitespace-nowrap">
              {{ fecha(a.fecha_inicio) }}
              <span v-if="a.ultima_cita" class="block text-sm text-gray-500">
                al {{ fecha(a.ultima_cita) }}
              </span>
            </td>
            <td class="px-4 py-2">
              <span class="px-2 py-1 rounded-full text-sm font-medium whitespace-nowrap"
                :class="ESTADOS[a.estado] ?? 'bg-gray-100 text-gray-600'">
                {{ a.estado }}
              </span>
            </td>
            <td class="px-4 py-2 text-center whitespace-nowrap">
              <button v-if="puedeRenovar && a.puede_renovar" @click="renovando = a"
                class="inline-flex items-center px-3 py-1 text-[#74BE69] hover:text-[#2D2B5B]">
                <span class="material-icons text-base">event_repeat</span>
                <span class="ml-1">Generar paquete</span>
              </button>
              <button @click="cancelar(a)" class="inline-flex items-center px-3 py-1 text-red-600 hover:text-red-800">
                <span class="material-icons text-base">delete</span>
                <span class="ml-1">Cancelar</span>
              </button>
            </td>
          </tr>

          <tr v-if="!asignacionesFiltradas.length">
            <td colspan="11" class="px-4 py-8 text-center text-gray-500">
              Todavía no hay programas asignados. Se asignan desde el botón Programa de cada paciente.
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <GenerarPaqueteModal v-if="renovando" :asignacion="renovando" @close="renovando = null" />
  </div>
</template>

<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

export default {
  layout: AuthenticatedLayout
}
</script>
