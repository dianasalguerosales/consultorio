<script setup>
import { computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import ModalCapa from '@/Components/ModalCapa.vue'
import BuscadorSelect from '@/Components/BuscadorSelect.vue'

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

// El texto es lo que se ve y lo que se filtra: con el nombre y el código juntos
// se llega al niño escribiendo cualquiera de los dos.
const opcionesPacientes = computed(() =>
  props.expedientes.map((e) => ({ id: e.id, texto: `${e.codigo} · ${e.paciente}` }))
)

const opcionesEvaluaciones = computed(() =>
  props.evaluaciones.map((ev) => ({ id: ev.id, texto: ev.nombre }))
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
        <BuscadorSelect v-model="form.expediente_id" :opciones="opcionesPacientes"
          etiqueta="Paciente" marcador="Escriba el nombre o el expediente..."
          sin-coincidencias="Ningún paciente coincide." texto-quitar="Quitar paciente"
          :error="form.errors.expediente_id" />

        <div>
          <BuscadorSelect v-model="form.evaluacion_id" :opciones="opcionesEvaluaciones"
            etiqueta="Evaluación" marcador="Escriba el nombre de la evaluación..."
            sin-coincidencias="Ninguna evaluación coincide." texto-quitar="Quitar evaluación"
            :error="form.errors.evaluacion_id" />

          <p v-if="elegida?.descripcion" class="mt-2 text-sm text-gray-500">
            {{ elegida.descripcion }}
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
