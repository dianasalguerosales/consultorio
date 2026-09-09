<script setup>
import { computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import ModalBaseEditar from '../ModalBaseEditar.vue'

const emit = defineEmits(['close'])

const props = defineProps({
  encargado: { type: Object, required: true },
  generos: { type: Array, default: () => [] },
  estadosCiviles: { type: Array, default: () => [] },
  relacionesPaciente: { type: Array, default: () => [] },
  usuariosDisponibles: { type: Array, default: () => [] }
})

// El usuario ya asignado no viene en usuariosDisponibles —esa lista trae solo
// los libres— así que se antepone para que el select pueda mostrarlo.
const opcionesUsuario = computed(() => {
  const libres = props.usuariosDisponibles ?? []
  const propio = props.encargado?.user

  return propio && !libres.some((u) => u.id === propio.id) ? [propio, ...libres] : libres
})

const form = useForm({
  nombres: props.encargado?.nombres || '',
  apellidos: props.encargado?.apellidos || '',
  user_id: props.encargado?.user_id ?? '',
  fecha_nacimiento: props.encargado?.fecha_nacimiento || '',
  dpi: props.encargado?.dpi || '',
  telefono: props.encargado?.telefono || '',
  correo: props.encargado?.correo || '',
  direccion: props.encargado?.direccion || '',
  ocupacion: props.encargado?.ocupacion || '',
  relacion_paciente_id: props.encargado?.relacion_paciente_id || '',
  genero_id: props.encargado?.genero_id || '',
  estado_civil_id: props.encargado?.estado_civil_id || ''
})

function submit() {
  form.put(`/personas/encargados/${props.encargado.id}`, {
    onSuccess: () => emit('close'),
    onError: (errors) => console.error(errors)
  })
}
</script>

<template>
  <ModalBaseEditar @close="$emit('close')">
    <template #header>
      <h2 class="text-2xl font-bold text-[#2D2B5B]">Editar Encargado</h2>
    </template>

    <!-- Formulario -->
    <form @submit.prevent="submit" class="grid grid-cols-2 gap-6 text-gray-700">
      <!-- Nombres -->
      <div>
        <label class="block text-sm font-medium">Nombres</label>
        <input v-model="form.nombres" type="text"
          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-[#53C6D3] focus:border-[#53C6D3]" />
      </div>

      <!-- Apellidos -->
      <div>
        <label class="block text-sm font-medium">Apellidos</label>
        <input v-model="form.apellidos" type="text"
          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-[#53C6D3] focus:border-[#53C6D3]" />
      </div>

      <div>
        <label class="block text-sm font-medium">Usuario del sistema</label>
        <select v-model="form.user_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm 
           focus:ring-[#53C6D3] focus:border-[#53C6D3]">
          <option :value="''">Seleccione...</option>
          <option v-for="u in opcionesUsuario" :key="u.id" :value="u.id">
            {{ u.email }}
          </option>
        </select>
      </div>

      <!-- Fecha nacimiento -->
      <div>
        <label class="block text-sm font-medium">Fecha nacimiento</label>
        <input v-model="form.fecha_nacimiento" type="date"
          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-[#53C6D3] focus:border-[#53C6D3]" />
      </div>

      <!-- DPI -->
      <div>
        <label class="block text-sm font-medium">DPI</label>
        <input v-model="form.dpi" type="text" inputmode="numeric"
          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-[#53C6D3] focus:border-[#53C6D3]" />
      </div>

      <!-- Teléfono -->
      <div>
        <label class="block text-sm font-medium">Teléfono</label>
        <input v-model="form.telefono" type="text"
          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-[#53C6D3] focus:border-[#53C6D3]" />
      </div>

      <!-- Correo -->
      <div>
        <label class="block text-sm font-medium">Correo</label>
        <input v-model="form.correo" type="email"
          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-[#53C6D3] focus:border-[#53C6D3]" />
      </div>

      <!-- Dirección -->
      <div>
        <label class="block text-sm font-medium">Dirección</label>
        <input v-model="form.direccion" type="text"
          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-[#53C6D3] focus:border-[#53C6D3]" />
      </div>

      <!-- Ocupación -->
      <div>
        <label class="block text-sm font-medium">Ocupación</label>
        <input v-model="form.ocupacion" type="text"
          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-[#53C6D3] focus:border-[#53C6D3]" />
      </div>

      <!-- Relación con paciente -->
      <div>
        <label class="block text-sm font-medium">Relación con paciente</label>
        <select v-model="form.relacion_paciente_id"
          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-[#53C6D3] focus:border-[#53C6D3]">
          <option value="">Seleccione...</option>
          <option v-for="r in props.relacionesPaciente" :key="r.id" :value="r.id">{{ r.nombre }}</option>
        </select>
      </div>

      <!-- Género -->
      <div>
        <label class="block text-sm font-medium">Género</label>
        <select v-model="form.genero_id"
          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-[#53C6D3] focus:border-[#53C6D3]">
          <option value="">Seleccione...</option>
          <option v-for="g in props.generos" :key="g.id" :value="g.id">{{ g.nombre }}</option>
        </select>
      </div>

      <!-- Estado civil -->
      <div>
        <label class="block text-sm font-medium">Estado civil</label>
        <select v-model="form.estado_civil_id"
          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-[#53C6D3] focus:border-[#53C6D3]">
          <option value="">Seleccione...</option>
          <option v-for="e in props.estadosCiviles" :key="e.id" :value="e.id">{{ e.nombre }}</option>
        </select>
      </div>
    </form>

    <!-- Footer fijo -->
    <template #footer>
      <button type="submit" class="px-6 py-2 bg-[#53C6D3] text-white rounded-md hover:bg-[#2D2B5B] transition"
        @click="submit">
        Guardar cambios
      </button>
    </template>
  </ModalBaseEditar>
</template>