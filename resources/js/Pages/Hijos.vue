<script setup>
import { Head } from '@inertiajs/vue3'
import { ref } from 'vue'
import ExpedienteModal from '@/Components/ExpedienteModal.vue'
import HistorialModal from '@/Components/HistorialModal.vue'
import { avatarPaciente } from '@/Utils/avatares'

defineProps({
  hijos: { type: Array, default: () => [] },
})

// Del expediente al encargado le toca solo el terapeuta, las terapias y los
// objetivos. Los datos generales, la historia clínica y las evaluaciones son
// del equipo, no del papá.
const TABS_ENCARGADO = ['Terapias y objetivos']

const seleccionado = ref(null)
const verExpediente = ref(false)
const verHistorial = ref(false)

function abrirExpediente(hijo) {
  seleccionado.value = hijo
  verExpediente.value = true
}

function abrirHistorial(hijo) {
  seleccionado.value = hijo
  verHistorial.value = true
}

function cerrar() {
  verExpediente.value = false
  verHistorial.value = false
  seleccionado.value = null
}
</script>

<template>
  <Head title="Mis hijos" />

  <div class="p-8 max-w-7xl mx-auto">
    <h2 class="text-2xl font-bold text-caine-azul mb-6">Mis hijos</h2>

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
      <div v-for="hijo in hijos" :key="hijo.id"
        class="bg-white shadow rounded-lg overflow-hidden flex flex-col items-center text-center">

        <div class="mt-6">
          <img :src="avatarPaciente(hijo.genero)" alt="Avatar" class="h-20 w-20 rounded-full mx-auto" />
        </div>

        <div class="mt-4 px-4">
          <h3 class="text-lg font-semibold text-gray-900">
            {{ hijo.nombres }} {{ hijo.apellidos }}
          </h3>
          <p class="text-sm text-gray-500">
            Expediente: {{ hijo.expediente?.codigo || 'No asignado' }}
          </p>
          <p class="text-sm text-gray-500">
            Escolaridad: {{ hijo.escolaridad?.nombre || 'No asignada' }}
          </p>
          <p class="text-sm text-gray-500">
            Citas registradas: {{ hijo.citas?.length ?? 0 }}
          </p>
        </div>

        <div class="grid grid-cols-2 gap-2 p-4 mt-4 w-full border-t">
          <button class="bg-caine-azul text-white py-2 rounded-md text-sm hover:bg-caine-morado disabled:opacity-40"
            :disabled="!hijo.expediente" @click="abrirExpediente(hijo)">
            Expediente
          </button>
          <button class="bg-caine-verde text-white py-2 rounded-md text-sm hover:bg-caine-azul"
            @click="abrirHistorial(hijo)">
            Historial
          </button>
        </div>
      </div>
    </div>

    <p v-if="!hijos.length" class="text-center text-gray-400 py-12">
      Todavía no hay pacientes asociados a su cuenta.
    </p>

    <ExpedienteModal v-if="verExpediente" :expediente="seleccionado?.expediente"
      :tabs="TABS_ENCARGADO" @close="cerrar" />

    <HistorialModal v-if="verHistorial" :paciente="seleccionado"
      :mostrarClinicas="false" @close="cerrar" />
  </div>
</template>

<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

export default { layout: AuthenticatedLayout }
</script>
