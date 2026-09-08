<script setup>
import { computed, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { EscClose } from '@/Utils/EscClose'

const props = defineProps({
  // La cita a atender, en el formato de evento que manda AgendaController.
  cita: { type: Object, default: null },
})

const emit = defineEmits(['close'])

// Este modal se monta y desmonta con v-if, así que el callback no necesita
// guard: cuando existe, está visible.
EscClose(() => emit('close'))

const datos = computed(() => props.cita?.extendedProps ?? {})

// Atender una cita ya atendida corrige lo escrito, no agrega una segunda
// sesión: el backend hace updateOrCreate sobre cita_id.
const yaAtendida = computed(() => Boolean(datos.value.sesion))

const form = useForm({
  evolucion: '',
  observaciones_clinicas: '',
  observaciones_generales: '',
  duracion_minutos: '',
})

// La cita llega después del primer render cuando el modal se abre, así que el
// formulario se rellena por watch y no en el useForm inicial.
watch(
  () => props.cita,
  (cita) => {
    const sesion = cita?.extendedProps?.sesion

    form.defaults({
      evolucion: sesion?.evolucion ?? '',
      observaciones_clinicas: sesion?.observacionesClinicas ?? '',
      observaciones_generales: sesion?.observacionesGenerales ?? '',
      duracion_minutos: sesion?.duracionMinutos ?? '',
    })
    form.reset()
    form.clearErrors()
  },
  { immediate: true }
)

function guardar() {
  if (!props.cita?.id) return

  form.post(`/agenda/${props.cita.id}/sesion`, {
    preserveScroll: true,
    onSuccess: () => emit('close'),
  })
}
</script>

<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/40" @click="emit('close')"></div>

    <div class="relative bg-white rounded-lg shadow-xl w-full max-w-2xl max-h-[92vh] flex flex-col">

      <!-- Encabezado -->
      <div class="flex items-start justify-between gap-4 px-6 py-4 border-b border-gray-200">
        <div>
          <h3 class="text-lg font-bold text-caine-azul">
            {{ yaAtendida ? 'Editar observaciones' : 'Atender cita' }}
          </h3>
          <p class="text-sm text-gray-500">
            {{ datos.paciente }} · {{ cita?.title }}
            <template v-if="datos.horaInicio"> · {{ datos.horaInicio }}</template>
          </p>
        </div>

        <button type="button" @click="emit('close')"
          class="text-gray-400 hover:text-gray-600" aria-label="Cerrar">
          <span class="material-icons">close</span>
        </button>
      </div>

      <!-- Formulario -->
      <form @submit.prevent="guardar" class="flex-1 overflow-y-auto px-6 py-4 space-y-5">

        <div>
          <label class="block text-sm font-medium text-caine-azul mb-1">
            Observaciones psicológicas
          </label>
          <textarea v-model="form.observaciones_clinicas" rows="4"
            placeholder="Hallazgos clínicos de la sesión"
            class="block w-full border rounded-md px-3 py-2 focus:ring-caine-celeste focus:border-caine-celeste"></textarea>
          <p v-if="form.errors.observaciones_clinicas" class="mt-1 text-sm text-caine-error">
            {{ form.errors.observaciones_clinicas }}
          </p>
        </div>

        <div>
          <label class="block text-sm font-medium text-caine-azul mb-1">
            Observaciones generales
          </label>
          <textarea v-model="form.observaciones_generales" rows="3"
            placeholder="Conducta, asistencia, acompañamiento del encargado"
            class="block w-full border rounded-md px-3 py-2 focus:ring-caine-celeste focus:border-caine-celeste"></textarea>
          <p v-if="form.errors.observaciones_generales" class="mt-1 text-sm text-caine-error">
            {{ form.errors.observaciones_generales }}
          </p>
        </div>

        <div>
          <label class="block text-sm font-medium text-caine-azul mb-1">
            Evolución
          </label>
          <textarea v-model="form.evolucion" rows="3"
            placeholder="Avance del paciente respecto a la sesión anterior"
            class="block w-full border rounded-md px-3 py-2 focus:ring-caine-celeste focus:border-caine-celeste"></textarea>
          <p v-if="form.errors.evolucion" class="mt-1 text-sm text-caine-error">
            {{ form.errors.evolucion }}
          </p>
        </div>

        <div class="w-1/2">
          <label class="block text-sm font-medium text-caine-azul mb-1">
            Duración real (minutos)
          </label>
          <input v-model="form.duracion_minutos" type="number" min="1" max="600"
            class="block w-full border rounded-md px-3 py-2 focus:ring-caine-celeste focus:border-caine-celeste" />
          <p v-if="form.errors.duracion_minutos" class="mt-1 text-sm text-caine-error">
            {{ form.errors.duracion_minutos }}
          </p>
        </div>
      </form>

      <!-- Pie -->
      <div class="flex justify-end gap-2 px-6 py-4 border-t border-gray-200 bg-gray-50">
        <button type="button" @click="emit('close')"
          class="px-4 py-2 rounded-md border text-gray-600 hover:bg-gray-100">
          Cancelar
        </button>
        <!-- Guardar es dar la cita por atendida: no se le pide aparte el
             estado de la sesión, lo pone el backend. -->
        <button type="button" @click="guardar" :disabled="form.processing"
          class="px-4 py-2 rounded-md bg-caine-azul text-white hover:opacity-90 disabled:opacity-50">
          {{ form.processing ? 'Guardando...' : 'Guardar cita atendida' }}
        </button>
      </div>
    </div>
  </div>
</template>
