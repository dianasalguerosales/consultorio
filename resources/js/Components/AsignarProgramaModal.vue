<script setup>
import { computed, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
import ModalCapa from '@/Components/ModalCapa.vue'

const props = defineProps({
  paciente: { type: Object, required: true },
  catalogos: { type: Object, required: true },
})

const emit = defineEmits(['close'])

const DIAS = [
  { valor: 1, nombre: 'Lunes', corto: 'L' },
  { valor: 2, nombre: 'Martes', corto: 'M' },
  { valor: 3, nombre: 'Miércoles', corto: 'M' },
  { valor: 4, nombre: 'Jueves', corto: 'J' },
  { valor: 5, nombre: 'Viernes', corto: 'V' },
  { valor: 6, nombre: 'Sábado', corto: 'S' },
]

const form = useForm({
  programa_id: '',
  servicio_id: '',
  modalidad_id: '',
  tipo_cita_id: '',
  atiende: '',
  precio: '',
  cantidad_citas: '',
  dias: [],
  hora_inicio: '',
  hora_fin: '',
  fecha_inicio: new Date().toLocaleDateString('sv-SE'),
})

// Caine Kids es el único paquete con horario fijo: entra a las 9:15 y sale a
// las 12:15. Los demás se acomodan al terapeuta, así que no tienen horario que
// traer y por eso esto vive acá y no como columna de `programas`.
const HORARIO_FIJO = {
  'caine kids': { hora_inicio: '09:15', hora_fin: '12:15' },
}

const horarioDe = (nombre) => HORARIO_FIJO[String(nombre ?? '').trim().toLowerCase()] ?? null

// Al elegir el paquete se traen su cantidad de citas y su costo, que quedan
// editables: a un niño se le puede cobrar distinto.
watch(() => form.programa_id, (id) => {
  const programa = props.catalogos.programas.find((p) => p.id === Number(id))
  if (!programa) return

  form.cantidad_citas = programa.sesiones_por_mes ?? ''
  form.precio = programa.precio_mensual ?? ''

  // El horario también queda editable: es una propuesta, no un candado.
  const horario = horarioDe(programa.nombre)
  if (horario) {
    form.hora_inicio = horario.hora_inicio
    form.hora_fin = horario.hora_fin
  }
})

// Para avisar en el formulario de dónde salió el horario.
const programaConHorarioFijo = computed(() =>
  horarioDe(props.catalogos.programas?.find((p) => p.id === Number(form.programa_id))?.nombre)
    ? props.catalogos.programas.find((p) => p.id === Number(form.programa_id)).nombre
    : null
)

// La especialidad viene de quien atiende, no se selecciona aparte.
const especialidadDeQuienAtiende = computed(() =>
  props.catalogos.quienAtiende?.find((q) => q.clave === form.atiende)?.especialidad ?? null
)

function alternarDia(valor) {
  const i = form.dias.indexOf(valor)
  i === -1 ? form.dias.push(valor) : form.dias.splice(i, 1)
}

// Lo que va a costar cada cita. Se muestra antes de guardar porque es el dato
// que termina en el cobro.
const precioPorCita = computed(() => {
  const total = Number(form.precio)
  const n = Number(form.cantidad_citas)

  return total > 0 && n > 0 ? total / n : 0
})

function guardar() {
  form.post(`/pacientes/${props.paciente.id}/programa`, {
    preserveScroll: true,
    onSuccess: () => emit('close'),
  })
}
</script>

<template>
  <ModalCapa panel="max-w-3xl max-h-[92vh] overflow-y-auto" @close="emit('close')">
    <div class="flex items-start justify-between gap-4 px-6 py-4 border-b border-gray-200">
      <div>
        <h3 class="text-lg font-bold text-[#2D2B5B]">Asignar programa</h3>
        <p class="text-sm text-gray-500">{{ paciente.nombres }} {{ paciente.apellidos }}</p>
      </div>
      <button type="button" @click="emit('close')" class="text-gray-400 hover:text-gray-600" aria-label="Cerrar">
        <span class="material-icons">close</span>
      </button>
    </div>

    <div class="px-6 py-4 space-y-5">
      <!-- Paquete -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-[#2D2B5B] mb-1">Programa</label>
          <select v-model="form.programa_id"
            class="block w-full border rounded-md px-3 py-2 focus:ring-[#53C6D3] focus:border-[#53C6D3]">
            <option value="">Seleccione</option>
            <option v-for="p in catalogos.programas" :key="p.id" :value="p.id">{{ p.nombre }}</option>
          </select>
          <p v-if="form.errors.programa_id" class="mt-1 text-sm text-red-600">{{ form.errors.programa_id }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-[#2D2B5B] mb-1">Servicio</label>
          <select v-model="form.servicio_id"
            class="block w-full border rounded-md px-3 py-2 focus:ring-[#53C6D3] focus:border-[#53C6D3]">
            <option value="">Sin definir</option>
            <option v-for="s in catalogos.servicios" :key="s.id" :value="s.id">{{ s.nombre }}</option>
          </select>
        </div>
      </div>

      <!-- Precio y cantidad -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="block text-sm font-medium text-[#2D2B5B] mb-1">Costo del programa</label>
          <input v-model="form.precio" type="number" step="0.01" min="0"
            class="block w-full border rounded-md px-3 py-2 focus:ring-[#53C6D3] focus:border-[#53C6D3]" />
          <p v-if="form.errors.precio" class="mt-1 text-sm text-red-600">{{ form.errors.precio }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-[#2D2B5B] mb-1">Cantidad de citas</label>
          <input v-model="form.cantidad_citas" type="number" step="1" min="1"
            class="block w-full border rounded-md px-3 py-2 focus:ring-[#53C6D3] focus:border-[#53C6D3]" />
          <p v-if="form.errors.cantidad_citas" class="mt-1 text-sm text-red-600">{{ form.errors.cantidad_citas }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-[#2D2B5B] mb-1">Cada cita queda en</label>
          <p class="px-3 py-2 rounded-md bg-[#FAF9F7] border border-gray-200 font-semibold text-[#2D2B5B]">
            Q{{ precioPorCita.toFixed(2) }}
          </p>
        </div>
      </div>

      <!-- Días y horario -->
      <div>
        <label class="block text-sm font-medium text-[#2D2B5B] mb-2">Días de la semana</label>
        <div class="flex flex-wrap gap-2">
          <button v-for="d in DIAS" :key="d.valor" type="button" @click="alternarDia(d.valor)"
            class="px-4 py-2 rounded-md border text-sm transition" :class="form.dias.includes(d.valor)
              ? 'border-[#2D2B5B] bg-[#2D2B5B] text-white'
              : 'border-gray-300 text-gray-600 hover:bg-gray-50'">
            {{ d.nombre }}
          </button>
        </div>
        <p v-if="form.errors.dias" class="mt-1 text-sm text-red-600">{{ form.errors.dias }}</p>
      </div>

      <!-- Se avisa de dónde salió el horario, porque se llenó solo. -->
      <p v-if="programaConHorarioFijo" class="flex items-center gap-2 text-xs text-gray-500">
        <span class="material-icons text-sm text-[#53C6D3]">schedule</span>
        {{ programaConHorarioFijo }} entra de 9:15 a 12:15. Puede cambiarlo si este niño lleva otro horario.
      </p>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="block text-sm font-medium text-[#2D2B5B] mb-1">Hora inicio</label>
          <input v-model="form.hora_inicio" type="time"
            class="block w-full border rounded-md px-3 py-2 focus:ring-[#53C6D3] focus:border-[#53C6D3]" />
          <p v-if="form.errors.hora_inicio" class="mt-1 text-sm text-red-600">{{ form.errors.hora_inicio }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-[#2D2B5B] mb-1">Hora fin</label>
          <input v-model="form.hora_fin" type="time"
            class="block w-full border rounded-md px-3 py-2 focus:ring-[#53C6D3] focus:border-[#53C6D3]" />
          <p v-if="form.errors.hora_fin" class="mt-1 text-sm text-red-600">{{ form.errors.hora_fin }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-[#2D2B5B] mb-1">Inicia el</label>
          <input v-model="form.fecha_inicio" type="date"
            class="block w-full border rounded-md px-3 py-2 focus:ring-[#53C6D3] focus:border-[#53C6D3]" />
          <p v-if="form.errors.fecha_inicio" class="mt-1 text-sm text-red-600">{{ form.errors.fecha_inicio }}</p>
        </div>
      </div>

      <!-- Quién atiende y cómo -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="block text-sm font-medium text-[#2D2B5B] mb-1">Atiende</label>
          <select v-model="form.atiende"
            class="block w-full border rounded-md px-3 py-2 focus:ring-[#53C6D3] focus:border-[#53C6D3]">
            <option value="">Sin asignar</option>
            <option v-for="q in catalogos.quienAtiende" :key="q.clave" :value="q.clave">
              {{ q.nombre_completo }} · {{ q.rol }}
            </option>
          </select>
          <p v-if="especialidadDeQuienAtiende" class="mt-1 text-xs text-gray-500">
            Especialidad: {{ especialidadDeQuienAtiende }}
          </p>
        </div>

        <div>
          <label class="block text-sm font-medium text-[#2D2B5B] mb-1">Modalidad</label>
          <select v-model="form.modalidad_id"
            class="block w-full border rounded-md px-3 py-2 focus:ring-[#53C6D3] focus:border-[#53C6D3]">
            <option value="">Sin definir</option>
            <option v-for="m in catalogos.modalidades" :key="m.id" :value="m.id">{{ m.nombre }}</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-[#2D2B5B] mb-1">Tipo de cita</label>
          <select v-model="form.tipo_cita_id"
            class="block w-full border rounded-md px-3 py-2 focus:ring-[#53C6D3] focus:border-[#53C6D3]">
            <option value="">Sin definir</option>
            <option v-for="t in catalogos.tiposCita" :key="t.id" :value="t.id">{{ t.nombre }}</option>
          </select>
        </div>
      </div>

      <p class="text-sm text-gray-500">
        Al guardar, las citas quedan en el calendario desde la fecha de inicio.
        Si alguna choca con otra cita de la misma persona, se avisa y esa no se agenda.
      </p>
    </div>

    <div class="flex justify-end gap-2 px-6 py-4 border-t border-gray-200 bg-gray-50">
      <button type="button" @click="emit('close')" class="px-4 py-2 rounded-md border text-gray-600 hover:bg-gray-100">
        Cancelar
      </button>
      <button type="button" @click="guardar" :disabled="form.processing"
        class="px-4 py-2 rounded-md bg-[#2D2B5B] text-white hover:opacity-90 disabled:opacity-50">
        {{ form.processing ? 'Guardando...' : 'Guardar programa' }}
      </button>
    </div>
  </ModalCapa>
</template>
