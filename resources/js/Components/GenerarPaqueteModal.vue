<script setup>
import { computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import ModalCapa from '@/Components/ModalCapa.vue'
import { EscClose } from '@/Utils/EscClose'
import { fecha } from '@/Utils/fechas'

const props = defineProps({
  // La fila de /programas, con su `propuesta` ya calculada por el backend.
  asignacion: { type: Object, required: true },
})

const emit = defineEmits(['close'])

EscClose(() => emit('close'))

const DIAS = [
  { valor: 1, corto: 'L', nombre: 'Lunes' },
  { valor: 2, corto: 'M', nombre: 'Martes' },
  { valor: 3, corto: 'M', nombre: 'Miércoles' },
  { valor: 4, corto: 'J', nombre: 'Jueves' },
  { valor: 5, corto: 'V', nombre: 'Viernes' },
  { valor: 6, corto: 'S', nombre: 'Sábado' },
]

// Abre con lo mismo que el paquete que corre; queda todo editable porque el
// mes siguiente puede cambiar de horario o de cantidad.
const form = useForm({ ...props.asignacion.propuesta })

const porCita = computed(() =>
  form.cantidad_citas > 0 ? Number(form.precio) / Number(form.cantidad_citas) : 0
)

const quetzales = (n) => `Q${Number(n ?? 0).toFixed(2)}`

function alternarDia(valor) {
  const i = form.dias.indexOf(valor)
  if (i === -1) form.dias.push(valor)
  else form.dias.splice(i, 1)
}

function generar() {
  form.post(`/programas/${props.asignacion.id}/renovar`, {
    preserveScroll: true,
    onSuccess: () => emit('close'),
  })
}
</script>

<template>
  <ModalCapa panel="max-w-xl max-h-[92vh] flex flex-col" @close="emit('close')">

    <div class="flex items-start justify-between gap-4 px-6 py-4 border-b border-gray-200">
      <div>
        <h3 class="text-lg font-bold text-caine-azul">Generar paquete</h3>
        <p class="text-sm text-gray-500">
          {{ asignacion.paciente }} · {{ asignacion.programa }}
        </p>
      </div>

      <button type="button" @click="emit('close')"
        class="text-gray-400 hover:text-gray-600" aria-label="Cerrar">
        <span class="material-icons">close</span>
      </button>
    </div>

    <div class="flex-1 overflow-y-auto px-6 py-4 space-y-5">

      <!-- De dónde viene la propuesta -->
      <div class="rounded-md bg-[#FAF9F7] p-4 text-sm">
        <p class="text-gray-600">
          El paquete que corre cubre hasta el
          <strong>{{ asignacion.ultima_cita ? fecha(asignacion.ultima_cita) : '—' }}</strong>.
          El nuevo arranca el día siguiente, con las mismas condiciones.
        </p>
        <p class="mt-2 text-gray-500">
          {{ asignacion.servicio }} · {{ asignacion.atiende }}
        </p>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-caine-azul mb-1">Inicia el</label>
          <input v-model="form.fecha_inicio" type="date"
            class="block w-full border rounded-md px-3 py-2 focus:ring-caine-celeste focus:border-caine-celeste" />
          <p v-if="form.errors.fecha_inicio" class="mt-1 text-sm text-caine-error">
            {{ form.errors.fecha_inicio }}
          </p>
        </div>

        <div>
          <label class="block text-sm font-medium text-caine-azul mb-1">Citas del paquete</label>
          <input v-model="form.cantidad_citas" type="number" min="1" max="200"
            class="block w-full border rounded-md px-3 py-2 focus:ring-caine-celeste focus:border-caine-celeste" />
          <p v-if="form.errors.cantidad_citas" class="mt-1 text-sm text-caine-error">
            {{ form.errors.cantidad_citas }}
          </p>
        </div>

        <div>
          <label class="block text-sm font-medium text-caine-azul mb-1">Costo del paquete</label>
          <input v-model="form.precio" type="number" step="0.01" min="0"
            class="block w-full border rounded-md px-3 py-2 focus:ring-caine-celeste focus:border-caine-celeste" />
          <p class="mt-1 text-xs text-gray-400">{{ quetzales(porCita) }} por cita</p>
          <p v-if="form.errors.precio" class="mt-1 text-sm text-caine-error">{{ form.errors.precio }}</p>
        </div>

        <div class="grid grid-cols-2 gap-2">
          <div>
            <label class="block text-sm font-medium text-caine-azul mb-1">Desde</label>
            <input v-model="form.hora_inicio" type="time"
              class="block w-full border rounded-md px-3 py-2 focus:ring-caine-celeste focus:border-caine-celeste" />
          </div>
          <div>
            <label class="block text-sm font-medium text-caine-azul mb-1">Hasta</label>
            <input v-model="form.hora_fin" type="time"
              class="block w-full border rounded-md px-3 py-2 focus:ring-caine-celeste focus:border-caine-celeste" />
          </div>
          <p v-if="form.errors.hora_fin" class="col-span-2 text-sm text-caine-error">
            {{ form.errors.hora_fin }}
          </p>
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium text-caine-azul mb-2">Días</label>
        <div class="flex flex-wrap gap-2">
          <button v-for="d in DIAS" :key="d.valor" type="button" @click="alternarDia(d.valor)"
            :title="d.nombre"
            class="w-10 h-10 rounded-full border text-sm font-medium transition"
            :class="form.dias.includes(d.valor)
              ? 'border-caine-azul bg-caine-azul text-white'
              : 'border-gray-300 text-gray-600 hover:bg-gray-50'">
            {{ d.corto }}
          </button>
        </div>
        <p v-if="form.errors.dias" class="mt-2 text-sm text-caine-error">{{ form.errors.dias }}</p>
      </div>

      <p class="text-xs text-gray-500">
        Al generarlo, el paquete actual queda como <strong>finalizado</strong> — sus citas
        pendientes siguen en el calendario, solo deja de renovarse.
      </p>
    </div>

    <div class="flex justify-end gap-2 px-6 py-4 border-t border-gray-200 bg-gray-50">
      <button type="button" @click="emit('close')"
        class="px-4 py-2 rounded-md border text-gray-600 hover:bg-gray-100">
        Cancelar
      </button>
      <button type="button" @click="generar" :disabled="form.processing || !form.dias.length"
        class="inline-flex items-center gap-1 px-4 py-2 rounded-md bg-caine-verde text-white
               hover:opacity-90 disabled:opacity-50">
        <span class="material-icons text-base">event_repeat</span>
        {{ form.processing ? 'Generando...' : 'Generar paquete' }}
      </button>
    </div>
  </ModalCapa>
</template>
