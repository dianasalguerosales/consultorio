<script setup>
import { ref } from 'vue'
import ModalCapa from '@/Components/ModalCapa.vue'
import { fecha } from '@/Utils/fechas'
import TablaBase from '@/Components/TablaBase.vue'

defineProps({
  paciente: Object,
  // El encargado ve solo las observaciones públicas de cada sesión.
  mostrarClinicas: { type: Boolean, default: true },
})

const nombreCompleto = (p) => [p?.nombres, p?.apellidos].filter(Boolean).join(' ')

const emit = defineEmits(['close', 'save'])
const citaVista = ref(null)

function cerrar() {
  if (citaVista.value) citaVista.value = null
  else emit('close')
}

</script>

<template>
  <ModalCapa panel="max-w-6xl h-5/6 overflow-y-auto flex flex-col" @close="cerrar">
    <!-- Header -->
    <div class="flex justify-between items-center border-b p-4">
      <h2 class="text-2xl font-bold text-caine-verde">
        <template v-if="citaVista">Detalle de sesión de {{ nombreCompleto(paciente) }}</template>
        <template v-else>Historial de citas de {{ nombreCompleto(paciente) }}</template>
      </h2>
      <button @click="$emit('close')" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
    </div>

    <!-- Body -->
    <div class="flex-1 p-6 space-y-4 bg-gray-50">
      <TablaBase v-if="!citaVista" sin-marco>
        <thead>
          <tr>
            <th>Fecha</th>
            <th>Hora</th>
            <th>Atiende</th>
            <th>Servicio</th>
            <th>Estado</th>
            <th>Modalidad</th>
            <th>Sesión</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="cita in paciente?.citas || []" :key="cita.id" class="hover:bg-gray-50">
            <td>{{ fecha(cita.fecha) }}</td>
            <td>{{ cita.hora_inicio }} - {{ cita.hora_fin }}</td>
            <td>{{ cita.atendido_por?.nombre_completo }}</td>
            <td>{{ cita.servicio?.nombre }}</td>
            <td>{{ cita.estado_cita?.nombre }}</td>
            <td>{{ cita.modalidad?.nombre }}</td>
            <!-- Evolución y observaciones ya no caben en una celda: se leen en el detalle. -->
            <td class="p-2 border text-center">
              <button v-if="cita.sesion" type="button" @click="citaVista = cita"
                class="text-sm font-medium text-caine-celeste hover:text-caine-azul underline">
                Ver detalles de sesión
              </button>
              <span v-else class="text-sm text-gray-400">Pendiente</span>
            </td>
          </tr>
        </tbody>
      </TablaBase>
      <!-- Misma tarjeta que la página de Observaciones: son párrafos, no celdas. -->
      <article v-else class="bg-white shadow rounded-lg p-5">
        <div class="flex flex-wrap items-baseline justify-between gap-2 pb-3 mb-4 border-b border-gray-100">
          <div>
            <p class="font-semibold text-caine-azul">
              {{ fecha(citaVista.fecha) }} · {{ citaVista.hora_inicio }} - {{ citaVista.hora_fin }}
            </p>
            <p class="text-sm text-gray-500">
              {{ citaVista.servicio?.nombre ?? 'Sin servicio' }} ·
              {{ citaVista.atendido_por?.nombre_completo ?? 'Sin asignar' }}
            </p>
          </div>
          <span v-if="citaVista.sesion?.duracion_minutos" class="text-xs text-gray-400">
            {{ citaVista.sesion.duracion_minutos }} minutos
          </span>
        </div>

        <div class="space-y-4">
          <div v-if="mostrarClinicas">
            <h3 class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">
              Evolución
            </h3>
            <p v-if="citaVista.sesion?.observaciones_clinicas"
              class="text-gray-700 whitespace-pre-line leading-relaxed">
              {{ citaVista.sesion.observaciones_clinicas }}
            </p>
            <p v-else class="text-sm text-gray-400 italic">Sin evolución escrita.</p>
          </div>

          <div>
            <h3 class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">
              Observaciones públicas
            </h3>
            <p v-if="citaVista.sesion?.observaciones_generales"
              class="text-gray-700 whitespace-pre-line leading-relaxed">
              {{ citaVista.sesion.observaciones_generales }}
            </p>
            <p v-else class="text-sm text-gray-400 italic">Sin observaciones escritas.</p>
          </div>
        </div>
      </article>
    </div>

    <!-- Footer -->
    <div class="flex justify-end border-t p-4 bg-gray-50">
      <button v-if="citaVista" @click="citaVista = null"
        class="px-4 py-2 border border-caine-azul text-caine-azul rounded-md hover:bg-caine-azul hover:text-white">
        ← Volver al historial
      </button>
      <button v-else @click="emit('close')" class="px-4 py-2 bg-caine-verde text-white rounded-md hover:bg-caine-azul">
        Cerrar
      </button>
    </div>
  </ModalCapa>
</template>