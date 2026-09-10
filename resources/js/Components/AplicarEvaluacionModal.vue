<script setup>
import { computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import ModalCapa from '@/Components/ModalCapa.vue'

const props = defineProps({
  evaluaciones: { type: Array, default: () => [] },
  expedientes: { type: Array, default: () => [] },
})

const emit = defineEmits(['close'])

const form = useForm({
  expediente_id: '',
  evaluacion_id: '',
})

const elegida = computed(() =>
  props.evaluaciones.find((e) => e.id === form.evaluacion_id)
)

function guardar() {
  form.post('/evaluaciones', {
    preserveScroll: true,
    onSuccess: () => emit('close'),
  })
}
</script>

<template>
  <ModalCapa panel="max-w-lg flex flex-col" @close="emit('close')">
      <div class="flex items-start justify-between gap-4 px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-bold text-caine-azul">Aplicar evaluación</h3>
        <button type="button" @click="emit('close')"
          class="text-gray-400 hover:text-gray-600" aria-label="Cerrar">
          <span class="material-icons">close</span>
        </button>
      </div>

      <form @submit.prevent="guardar" class="px-6 py-4 space-y-5">
        <div>
          <label class="block text-sm font-medium text-caine-azul mb-1">Paciente</label>
          <select v-model="form.expediente_id" required
            class="block w-full border rounded-md px-3 py-2 focus:ring-caine-celeste focus:border-caine-celeste">
            <option value="">Seleccione...</option>
            <option v-for="e in expedientes" :key="e.id" :value="e.id">
              {{ e.paciente }} · {{ e.codigo }}
            </option>
          </select>
          <p v-if="form.errors.expediente_id" class="mt-1 text-sm text-caine-error">
            {{ form.errors.expediente_id }}
          </p>
        </div>

        <div>
          <label class="block text-sm font-medium text-caine-azul mb-1">Evaluación</label>
          <select v-model="form.evaluacion_id" required
            class="block w-full border rounded-md px-3 py-2 focus:ring-caine-celeste focus:border-caine-celeste">
            <option value="">Seleccione...</option>
            <option v-for="ev in evaluaciones" :key="ev.id" :value="ev.id">
              {{ ev.nombre }}
            </option>
          </select>
          <p v-if="elegida?.descripcion" class="mt-2 text-sm text-gray-500">
            {{ elegida.descripcion }}
          </p>
          <p v-if="form.errors.evaluacion_id" class="mt-1 text-sm text-caine-error">
            {{ form.errors.evaluacion_id }}
          </p>
        </div>
      </form>

      <div class="flex justify-end gap-2 px-6 py-4 border-t border-gray-200 bg-gray-50">
        <button type="button" @click="emit('close')"
          class="px-4 py-2 rounded-md border text-gray-600 hover:bg-gray-100">
          Cancelar
        </button>
        <button type="button" @click="guardar" :disabled="form.processing"
          class="px-4 py-2 rounded-md bg-caine-azul text-white hover:opacity-90 disabled:opacity-50">
          {{ form.processing ? 'Aplicando...' : 'Aplicar' }}
        </button>
      </div>
  </ModalCapa>
</template>
