<script setup>
import { computed, ref, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { EscClose } from '@/Utils/EscClose'

const props = defineProps({
  show: { type: Boolean, default: false },
  catalogos: { type: Object, default: null },
  // Cuando viene una cita se edita; si es null se crea.
  cita: { type: Object, default: null },
  // Fecha y hora preseleccionadas al hacer clic en un hueco del calendario.
  // Se llama "hueco" y no "slot" porque slot es reservado en Vue.
  hueco: { type: Object, default: null },
  permisos: { type: Object, default: () => ({}) },
})

const emit = defineEmits(['close'])

// Misma salida con Escape que el resto de los modales del sistema.
EscClose(() => {
  if (props.show) emit('close')
})

const esEdicion = computed(() => Boolean(props.cita?.id))

const form = useForm({
  paciente_id: '',
  atiende_tipo: 'terapeuta',
  atiende_id: '',
  estado_cita_id: '',
  modalidad_id: '',
  tipo_cita_id: '',
  servicio_id: '',
  programa_id: '',
  fecha: '',
  hora_inicio: '',
  hora_fin: '',
  precio_aplicado: '',
})

// El <select> de quien atiende necesita un solo valor, pero el backend espera
// el par (tipo, id) para armar la relación polimórfica.
const atiendeSeleccionado = ref('')

watch(atiendeSeleccionado, (valor) => {
  if (!valor) {
    form.atiende_tipo = 'terapeuta'
    form.atiende_id = ''
    return
  }
  const [tipo, id] = valor.split(':')
  form.atiende_tipo = tipo
  form.atiende_id = id
})

// Cada vez que se abre, el formulario se llena con la cita a editar, con el
// hueco que se hizo clic, o queda limpio.
watch(() => props.show, (abierto) => {
  if (!abierto) return

  form.clearErrors()

  if (esEdicion.value) {
    const p = props.cita.extendedProps
    form.paciente_id = p.pacienteId ?? ''
    form.estado_cita_id = p.estadoId ?? ''
    form.modalidad_id = p.modalidadId ?? ''
    form.tipo_cita_id = p.tipoCitaId ?? ''
    form.servicio_id = p.servicioId ?? ''
    form.programa_id = p.programaId ?? ''
    form.fecha = props.cita.start?.slice(0, 10) ?? ''
    form.hora_inicio = p.horaInicio ?? ''
    form.hora_fin = p.horaFin ?? ''
    form.precio_aplicado = p.precio ?? ''
    atiendeSeleccionado.value = p.atiendeTipo && p.atiendeId
      ? `${p.atiendeTipo}:${p.atiendeId}`
      : ''
    return
  }

  form.reset()
  form.fecha = props.hueco?.fecha ?? ''
  form.hora_inicio = props.hueco?.horaInicio ?? ''
  form.hora_fin = props.hueco?.horaFin ?? ''

  // El auxiliar solo agenda para sí mismo, así que ya viene seleccionado.
  const propio = props.permisos?.yoAtiendo
  atiendeSeleccionado.value = props.permisos?.soloParaSiMismo && propio
    ? `${propio.tipo}:${propio.id}`
    : ''

  // El estado por omisión es el primero del catálogo (Pendiente).
  form.estado_cita_id = props.catalogos?.estados?.[0]?.id ?? ''
})

// Al auxiliar no se le deja cambiar quién atiende.
const atiendeFijo = computed(() =>
  Boolean(props.permisos?.soloParaSiMismo && props.permisos?.yoAtiendo)
)

// Al elegir un programa se sugiere su precio, pero se puede sobrescribir.
watch(() => form.programa_id, (id) => {
  if (!id || esEdicion.value) return
  const programa = props.catalogos?.programas?.find((p) => String(p.id) === String(id))
  if (programa?.precio_mensual) form.precio_aplicado = programa.precio_mensual
})

function guardar() {
  const opciones = {
    preserveScroll: true,
    onSuccess: () => emit('close'),
  }

  if (esEdicion.value) {
    form.put(`/agenda/${props.cita.id}`, opciones)
  } else {
    form.post('/agenda', opciones)
  }
}

function eliminar() {
  if (!esEdicion.value) return
  if (!confirm('¿Eliminar esta cita?')) return

  form.delete(`/agenda/${props.cita.id}`, {
    preserveScroll: true,
    onSuccess: () => emit('close'),
  })
}
</script>

<template>
  <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/40" @click="emit('close')"></div>

    <div class="relative bg-white rounded-lg shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
      <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
        <h3 class="text-lg font-bold text-[#2D2B5B]">
          {{ esEdicion ? 'Editar cita' : 'Nueva cita' }}
        </h3>
        <button type="button" class="text-gray-400 hover:text-gray-600" @click="emit('close')">
          <span class="material-icons">close</span>
        </button>
      </div>

      <form class="px-6 py-4 space-y-4" @submit.prevent="guardar">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Paciente</label>
            <select v-model="form.paciente_id" required
              class="w-full rounded-md border-gray-300 focus:border-caine-azul focus:ring-caine-azul">
              <option value="">Seleccione…</option>
              <option v-for="p in catalogos?.pacientes ?? []" :key="p.id" :value="p.id">
                {{ p.nombre_completo }}
              </option>
            </select>
            <p v-if="form.errors.paciente_id" class="mt-1 text-sm text-caine-error">
              {{ form.errors.paciente_id }}
            </p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Atiende</label>
            <select v-model="atiendeSeleccionado" required :disabled="atiendeFijo"
              class="w-full rounded-md border-gray-300 focus:border-caine-azul focus:ring-caine-azul disabled:bg-gray-100 disabled:text-gray-500">
              <option value="">Seleccione…</option>
              <option v-for="a in catalogos?.atienden ?? []" :key="`${a.tipo}:${a.id}`"
                :value="`${a.tipo}:${a.id}`">
                {{ a.nombre_completo }} ({{ a.tipo }})
              </option>
            </select>
            <p v-if="atiendeFijo" class="mt-1 text-xs text-gray-400">
              Solo puede agendar citas que usted mismo atiende.
            </p>
            <p v-if="form.errors.atiende_id" class="mt-1 text-sm text-caine-error">
              {{ form.errors.atiende_id }}
            </p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Fecha</label>
            <input v-model="form.fecha" type="date" required
              class="w-full rounded-md border-gray-300 focus:border-caine-azul focus:ring-caine-azul" />
            <p v-if="form.errors.fecha" class="mt-1 text-sm text-caine-error">{{ form.errors.fecha }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
            <select v-model="form.estado_cita_id" required
              class="w-full rounded-md border-gray-300 focus:border-caine-azul focus:ring-caine-azul">
              <option value="">Seleccione…</option>
              <!-- Las del sistema se muestran para que la cita que ya esté así
                   no aparezca en blanco, pero no se pueden asignar a mano. -->
              <option v-for="e in catalogos?.estados ?? []" :key="e.id" :value="e.id"
                :disabled="e.delSistema && e.id !== cita?.extendedProps?.estadoId">
                {{ e.nombre }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Hora inicio</label>
            <input v-model="form.hora_inicio" type="time" required
              class="w-full rounded-md border-gray-300 focus:border-caine-azul focus:ring-caine-azul" />
            <p v-if="form.errors.hora_inicio" class="mt-1 text-sm text-caine-error">
              {{ form.errors.hora_inicio }}
            </p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Hora fin</label>
            <input v-model="form.hora_fin" type="time" required
              class="w-full rounded-md border-gray-300 focus:border-caine-azul focus:ring-caine-azul" />
            <p v-if="form.errors.hora_fin" class="mt-1 text-sm text-caine-error">
              {{ form.errors.hora_fin }}
            </p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Servicio</label>
            <select v-model="form.servicio_id"
              class="w-full rounded-md border-gray-300 focus:border-caine-azul focus:ring-caine-azul">
              <option value="">—</option>
              <option v-for="s in catalogos?.servicios ?? []" :key="s.id" :value="s.id">
                {{ s.nombre }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de cita</label>
            <select v-model="form.tipo_cita_id"
              class="w-full rounded-md border-gray-300 focus:border-caine-azul focus:ring-caine-azul">
              <option value="">—</option>
              <option v-for="t in catalogos?.tiposCita ?? []" :key="t.id" :value="t.id">
                {{ t.nombre }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Modalidad</label>
            <select v-model="form.modalidad_id"
              class="w-full rounded-md border-gray-300 focus:border-caine-azul focus:ring-caine-azul">
              <option value="">—</option>
              <option v-for="m in catalogos?.modalidades ?? []" :key="m.id" :value="m.id">
                {{ m.nombre }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Programa</label>
            <select v-model="form.programa_id"
              class="w-full rounded-md border-gray-300 focus:border-caine-azul focus:ring-caine-azul">
              <option value="">—</option>
              <option v-for="pr in catalogos?.programas ?? []" :key="pr.id" :value="pr.id">
                {{ pr.nombre }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Precio aplicado</label>
            <input v-model="form.precio_aplicado" type="number" step="0.01" min="0"
              class="w-full rounded-md border-gray-300 focus:border-caine-azul focus:ring-caine-azul" />
          </div>
        </div>

        <div class="flex items-center justify-between pt-4 border-t border-gray-100">
          <button v-if="esEdicion" type="button" @click="eliminar" :disabled="form.processing"
            class="text-caine-error hover:underline text-sm font-medium disabled:opacity-50">
            Eliminar cita
          </button>
          <span v-else></span>

          <div class="flex gap-2">
            <button type="button" @click="emit('close')"
              class="px-4 py-2 rounded-md border border-gray-300 text-gray-700 hover:bg-gray-50">
              Cancelar
            </button>
            <button type="submit" :disabled="form.processing"
              class="px-4 py-2 rounded-md bg-caine-azul text-white hover:opacity-90 disabled:opacity-50">
              {{ form.processing ? 'Guardando…' : 'Guardar' }}
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>
