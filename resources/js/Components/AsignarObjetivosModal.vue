<script setup>
import { computed, ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import ModalCapa from '@/Components/ModalCapa.vue'
import BuscadorSelect from '@/Components/BuscadorSelect.vue'
import { EscClose } from '@/Utils/EscClose'

const props = defineProps({
  // Con fila se corrige un grupo que ya existe; sin ella se asigna uno nuevo.
  fila: { type: Object, default: null },
  pacientes: { type: Array, default: () => [] },
  servicios: { type: Array, default: () => [] },
  minimo: { type: Number, default: 3 },
  maximo: { type: Number, default: 4 },
})

const emit = defineEmits(['close'])

EscClose(() => emit('close'))

const corrigiendo = computed(() => Boolean(props.fila))

// Primero el expediente y después el nombre, que es el patrón del sistema. El
// texto es también lo que se filtra: se llega por cualquiera de los dos.
const opcionesPacientes = computed(() =>
  props.pacientes.map((p) => ({
    id: p.id,
    texto: p.codigo ? `${p.codigo} · ${p.nombre}` : p.nombre,
  }))
)

// Se arranca con `minimo` renglones vacíos: son los que hay que llenar.
const inicial = () => {
  if (props.fila) return props.fila.objetivos.map((o) => o.descripcion)

  return Array.from({ length: props.minimo }, () => '')
}

const form = useForm({
  paciente_id: props.fila?.paciente_id ?? '',
  servicio_id: props.fila?.servicio_id ?? '',
  objetivos: inicial(),
})

const llenos = computed(() => form.objetivos.filter((o) => o.trim()).length)

const puedeAgregar = computed(() => form.objetivos.length < props.maximo)

function agregarRenglon() {
  if (puedeAgregar.value) form.objetivos.push('')
}

function quitarRenglon(i) {
  form.objetivos.splice(i, 1)
}

// Los renglones vacíos no se mandan: el formulario ofrece tres por comodidad,
// no porque los tres sean obligatorios.
function guardar() {
  form
    .transform((datos) => ({
      ...datos,
      objetivos: datos.objetivos.map((o) => o.trim()).filter(Boolean),
    }))
    .post('/objetivos', {
      preserveScroll: true,
      onSuccess: () => emit('close'),
    })
}

const nombrePaciente = computed(() =>
  props.pacientes.find((p) => p.id === Number(form.paciente_id))?.nombre
)
</script>

<template>
  <ModalCapa panel="max-w-2xl max-h-[92vh] flex flex-col" @close="emit('close')">

    <div class="flex items-start justify-between gap-4 px-6 py-4 border-b border-gray-200">
      <div>
        <h3 class="text-lg font-bold text-caine-azul">
          {{ corrigiendo ? 'Corregir objetivos' : 'Asignar objetivos' }}
        </h3>
        <p class="text-sm text-gray-500">
          De {{ minimo }} a {{ maximo }} objetivos por terapia
          <template v-if="corrigiendo"> · {{ fila.paciente }} · {{ fila.servicio }}</template>
        </p>
      </div>

      <button type="button" @click="emit('close')"
        class="text-gray-400 hover:text-gray-600" aria-label="Cerrar">
        <span class="material-icons">close</span>
      </button>
    </div>

    <div class="flex-1 overflow-y-auto px-6 py-4 space-y-5">

      <!-- A quién y en qué terapia. Al corregir no se mueven: cambiarlos sería
           asignarle el grupo a otro niño, no corregir este. -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <BuscadorSelect v-model="form.paciente_id" :opciones="opcionesPacientes"
          etiqueta="Paciente" marcador="Escriba el expediente o el nombre..."
          sin-coincidencias="Ningún paciente coincide." texto-quitar="Quitar paciente"
          :deshabilitado="corrigiendo" :error="form.errors.paciente_id" />

        <div>
          <label class="block text-sm font-medium text-caine-azul mb-1">Terapia</label>
          <select v-model="form.servicio_id" :disabled="corrigiendo"
            class="block w-full border rounded-md px-3 py-2 text-sm disabled:bg-gray-100
                   focus:ring-caine-celeste focus:border-caine-celeste">
            <option value="">Elija una terapia</option>
            <option v-for="s in servicios" :key="s.id" :value="s.id">{{ s.nombre }}</option>
          </select>
          <p v-if="form.errors.servicio_id" class="mt-1 text-sm text-caine-error">
            {{ form.errors.servicio_id }}
          </p>
        </div>
      </div>

      <!-- Los objetivos -->
      <div class="pt-4 border-t border-gray-100">
        <div class="flex items-baseline justify-between mb-3">
          <h4 class="text-sm font-bold text-caine-azul">
            Objetivos<template v-if="nombrePaciente && !corrigiendo"> de {{ nombrePaciente }}</template>
          </h4>
          <span class="text-xs" :class="llenos < minimo ? 'text-caine-naranja' : 'text-gray-400'">
            {{ llenos }} de {{ minimo }} a {{ maximo }}
          </span>
        </div>

        <div class="space-y-3">
          <div v-for="(_, i) in form.objetivos" :key="i" class="flex items-start gap-2">
            <span class="mt-2 w-5 shrink-0 text-sm text-gray-400 tabular-nums">{{ i + 1 }}.</span>

            <div class="flex-1">
              <textarea v-model="form.objetivos[i]" rows="2"
                placeholder="Qué se espera lograr en esta terapia"
                class="block w-full border rounded-md px-3 py-2 text-sm
                       focus:ring-caine-celeste focus:border-caine-celeste"></textarea>
              <p v-if="form.errors[`objetivos.${i}`]" class="mt-1 text-sm text-caine-error">
                {{ form.errors[`objetivos.${i}`] }}
              </p>
            </div>

            <button v-if="form.objetivos.length > 1" type="button" @click="quitarRenglon(i)"
              class="mt-2 text-gray-300 hover:text-caine-error" aria-label="Quitar">
              <span class="material-icons text-base">close</span>
            </button>
          </div>
        </div>

        <p v-if="form.errors.objetivos" class="mt-2 text-sm text-caine-error">
          {{ form.errors.objetivos }}
        </p>

        <button v-if="puedeAgregar" type="button" @click="agregarRenglon"
          class="mt-3 inline-flex items-center gap-1 text-sm font-medium text-caine-celeste hover:underline">
          <span class="material-icons text-base">add</span>
          Agregar otro objetivo
        </button>
      </div>
    </div>

    <div class="flex justify-end gap-2 px-6 py-4 border-t border-gray-200 bg-gray-50">
      <button type="button" @click="emit('close')"
        class="px-4 py-2 rounded-md border text-gray-600 hover:bg-gray-100">
        Cancelar
      </button>
      <!-- Guardar reemplaza el grupo entero: lo que queda es lo que se ve. -->
      <button type="button" @click="guardar" :disabled="form.processing || !llenos"
        class="px-4 py-2 rounded-md bg-caine-azul text-white hover:opacity-90 disabled:opacity-50">
        {{ form.processing ? 'Guardando...' : 'Guardar objetivos' }}
      </button>
    </div>
  </ModalCapa>
</template>
