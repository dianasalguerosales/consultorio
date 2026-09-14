<script setup>
import { computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import ModalCapa from '@/Components/ModalCapa.vue'

const props = defineProps({
  // La cita en el formato de evento que manda AgendaController.
  cita: { type: Object, default: null },
})

const emit = defineEmits(['close'])

const datos = computed(() => props.cita?.extendedProps ?? {})

const form = useForm({ motivo: '' })

function enviar() {
  if (!props.cita?.id) return

  form.post(`/agenda/${props.cita.id}/reprogramacion`, {
    preserveScroll: true,
    onSuccess: () => emit('close'),
  })
}
</script>

<template>
  <ModalCapa panel="max-w-lg" @close="emit('close')">
    <div class="flex items-start justify-between gap-4 px-6 py-4 border-b border-gray-200">
      <h3 class="text-lg font-bold text-caine-azul">Solicitar reprogramación</h3>
      <button type="button" @click="emit('close')"
        class="text-gray-400 hover:text-gray-600" aria-label="Cerrar">
        <span class="material-icons">close</span>
      </button>
    </div>

    <div class="px-6 py-4 space-y-4">
      <div class="rounded-md bg-[#FAF9F7] p-4 text-sm">
        <p class="font-medium text-caine-azul">{{ datos.paciente }}</p>
        <p class="text-gray-600">{{ datos.servicio }}</p>
        <p class="text-gray-600">
          {{ datos.horaInicio }}<template v-if="datos.horaFin"> - {{ datos.horaFin }}</template>
          · {{ datos.atiende }}
        </p>
      </div>

      <div>
        <label class="block text-sm font-medium text-caine-azul mb-1">
          Motivo <span class="font-normal text-gray-400">(opcional)</span>
        </label>
        <textarea v-model="form.motivo" rows="3"
          placeholder="Por qué necesita mover la cita"
          class="block w-full border rounded-md px-3 py-2 focus:ring-caine-celeste focus:border-caine-celeste"></textarea>
        <p v-if="form.errors.motivo" class="mt-1 text-sm text-caine-error">{{ form.errors.motivo }}</p>
      </div>

      <p class="text-sm text-gray-500">
        La coordinación revisará la solicitud y le asignará una fecha nueva.
        Le avisaremos por la campana de notificaciones.
      </p>
    </div>

    <div class="flex justify-end gap-2 px-6 py-4 border-t border-gray-200 bg-gray-50">
      <button type="button" @click="emit('close')"
        class="px-4 py-2 rounded-md border text-gray-600 hover:bg-gray-100">
        Cancelar
      </button>
      <button type="button" @click="enviar" :disabled="form.processing"
        class="px-4 py-2 rounded-md bg-caine-azul text-white hover:opacity-90 disabled:opacity-50">
        {{ form.processing ? 'Enviando...' : 'Enviar solicitud' }}
      </button>
    </div>
  </ModalCapa>
</template>
