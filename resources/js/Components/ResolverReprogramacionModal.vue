<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import ModalCapa from '@/Components/ModalCapa.vue'

const props = defineProps({
  solicitud: { type: Object, required: true },
})

const emit = defineEmits(['close'])

// Aceptar pide fecha y hora; rechazar solo el motivo.
const decision = ref('aceptada')

const form = useForm({
  decision: 'aceptada',
  fecha: '',
  hora_inicio: '',
  hora_fin: '',
  respuesta: '',
})

function elegir(valor) {
  decision.value = valor
  form.decision = valor
  form.clearErrors()
}

function resolver() {
  form.put(`/reprogramaciones/${props.solicitud.id}`, {
    preserveScroll: true,
    onSuccess: () => emit('close'),
  })
}
</script>

<template>
  <ModalCapa panel="max-w-lg max-h-[92vh] overflow-y-auto" @close="emit('close')">
    <div class="flex items-start justify-between gap-4 px-6 py-4 border-b border-gray-200">
      <h3 class="text-lg font-bold text-caine-azul">Resolver reprogramación</h3>
      <button type="button" @click="emit('close')"
        class="text-gray-400 hover:text-gray-600" aria-label="Cerrar">
        <span class="material-icons">close</span>
      </button>
    </div>

    <div class="px-6 py-4 space-y-5">
      <!-- La cita tal como está hoy -->
      <div class="rounded-md bg-[#FAF9F7] p-4 text-sm">
        <p class="font-medium text-caine-azul">{{ solicitud.paciente }}</p>
        <p class="text-gray-600">{{ solicitud.servicio }} · {{ solicitud.atiende }}</p>
        <p class="text-gray-600">Actualmente: {{ solicitud.fecha }} · {{ solicitud.hora }}</p>
        <p v-if="solicitud.motivo" class="mt-2 text-gray-600">
          <strong>Motivo:</strong> {{ solicitud.motivo }}
        </p>
      </div>

      <!-- Aceptar o rechazar -->
      <div class="grid grid-cols-2 gap-2">
        <button type="button" @click="elegir('aceptada')"
          class="rounded-md border px-4 py-2 text-sm font-medium transition"
          :class="decision === 'aceptada'
            ? 'border-caine-verde bg-caine-verde/10 text-[#2F5B28]'
            : 'border-gray-200 text-gray-500 hover:bg-gray-50'">
          Aceptar y reprogramar
        </button>
        <button type="button" @click="elegir('rechazada')"
          class="rounded-md border px-4 py-2 text-sm font-medium transition"
          :class="decision === 'rechazada'
            ? 'border-caine-error bg-caine-error/10 text-[#7A1F26]'
            : 'border-gray-200 text-gray-500 hover:bg-gray-50'">
          Rechazar
        </button>
      </div>

      <!-- El horario nuevo: la cita se mueve, no se crea otra -->
      <div v-if="decision === 'aceptada'" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-caine-azul mb-1">Fecha nueva</label>
          <input v-model="form.fecha" type="date"
            class="block w-full border rounded-md px-3 py-2 focus:ring-caine-celeste focus:border-caine-celeste" />
          <p v-if="form.errors.fecha" class="mt-1 text-sm text-caine-error">{{ form.errors.fecha }}</p>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-caine-azul mb-1">Hora inicio</label>
            <input v-model="form.hora_inicio" type="time"
              class="block w-full border rounded-md px-3 py-2 focus:ring-caine-celeste focus:border-caine-celeste" />
            <p v-if="form.errors.hora_inicio" class="mt-1 text-sm text-caine-error">{{ form.errors.hora_inicio }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-caine-azul mb-1">Hora fin</label>
            <input v-model="form.hora_fin" type="time"
              class="block w-full border rounded-md px-3 py-2 focus:ring-caine-celeste focus:border-caine-celeste" />
            <p v-if="form.errors.hora_fin" class="mt-1 text-sm text-caine-error">{{ form.errors.hora_fin }}</p>
          </div>
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium text-caine-azul mb-1">
          Mensaje para el encargado <span class="font-normal text-gray-400">(opcional)</span>
        </label>
        <textarea v-model="form.respuesta" rows="2"
          :placeholder="decision === 'aceptada' ? 'Nota sobre el cambio' : 'Por qué no se puede mover'"
          class="block w-full border rounded-md px-3 py-2 focus:ring-caine-celeste focus:border-caine-celeste"></textarea>
      </div>
    </div>

    <div class="flex justify-end gap-2 px-6 py-4 border-t border-gray-200 bg-gray-50">
      <button type="button" @click="emit('close')"
        class="px-4 py-2 rounded-md border text-gray-600 hover:bg-gray-100">
        Cancelar
      </button>
      <button type="button" @click="resolver" :disabled="form.processing"
        class="px-4 py-2 rounded-md text-white hover:opacity-90 disabled:opacity-50"
        :class="decision === 'aceptada' ? 'bg-caine-azul' : 'bg-caine-error'">
        {{ form.processing ? 'Guardando...' : (decision === 'aceptada' ? 'Reprogramar' : 'Rechazar') }}
      </button>
    </div>
  </ModalCapa>
</template>
