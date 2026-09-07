<script setup>
import { useForm } from '@inertiajs/vue3'
import ModalBaseEditar from '../ModalBaseEditar.vue'

const emit = defineEmits(['close'])

const props = defineProps({
  administrativo: { type: Object, required: true },
  cargos: { type: Array, default: () => [] },
  especialidades: { type: Array, default: () => [] },
  generos: { type: Array, default: () => [] },
  usuariosDisponibles: { type: Array, default: () => [] }
})

// 👇 aquí puedes inspeccionar lo que llega
console.log('Administrativo recibido:', props.administrativo)
console.log('Usuarios disponibles:', props.usuariosDisponibles)

const form = useForm({
  nombres: props.administrativo?.nombres || '',
  apellidos: props.administrativo?.apellidos || '',
  user_id: String(props.administrativo?.user_id || ''),
  fecha_nacimiento: props.administrativo?.fecha_nacimiento || '',
  dpi: props.administrativo?.dpi || '',
  telefono: props.administrativo?.telefono || '',
  correo: props.administrativo?.correo || '',
  genero_id: props.administrativo?.genero_id || '',
  cargo_id: props.administrativo?.cargo_id || '',
  especialidad_id: props.administrativo?.especialidad_id || '',
  experiencia: props.administrativo?.experiencia || '',
  certificaciones: props.administrativo?.certificaciones || '',
  cursos: props.administrativo?.cursos || ''
})

function submit() {
  form.put(`/personas/administrativos/${props.administrativo.id}`, {
    onSuccess: () => {
      emit('close')
    },
    onError: (errors) => {
      console.error(errors)
    }
  })
}

</script>

<template>
  <ModalBaseEditar @close="$emit('close')">
    <template #header>
      <h2 class="text-2xl font-bold text-[#2D2B5B]">Editar Administrativo</h2>
    </template>

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
          <option value="">Seleccione...</option>

          <!-- 👇 opción fija con el usuario actual -->
          <option v-if="props.usuarioActual" :value="String(props.usuarioActual.id)">
            {{ props.usuarioActual.email }}
          </option>

          <!-- 👇 resto de usuarios disponibles -->
          <option v-for="u in usuariosDisponibles" :key="u.id" :value="String(u.id)">
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

      <!-- Género -->
      <div>
        <label class="block text-sm font-medium">Género</label>
        <select v-model="form.genero_id"
          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-[#53C6D3] focus:border-[#53C6D3]">
          <option value="">Seleccione...</option>
          <option v-for="g in props.generos" :key="g.id" :value="g.id">{{ g.nombre }}</option>
        </select>
      </div>

      <!-- Cargo -->
      <div>
        <label class="block text-sm font-medium">Cargo</label>
        <select v-model="form.cargo_id"
          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-[#53C6D3] focus:border-[#53C6D3]">
          <option value="">Seleccione...</option>
          <option v-for="c in props.cargos" :key="c.id" :value="c.id">{{ c.nombre }}</option>
        </select>
      </div>

      <!-- Especialidad -->
      <div>
        <label class="block text-sm font-medium">Especialidad</label>
        <select v-model="form.especialidad_id"
          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-[#53C6D3] focus:border-[#53C6D3]">
          <option value="">Seleccione...</option>
          <option v-for="e in props.especialidades" :key="e.id" :value="e.id">{{ e.nombre }}</option>
        </select>
      </div>

      <!-- Experiencia -->
      <div class="col-span-2">
        <label class="block text-sm font-medium">Experiencia</label>
        <textarea v-model="form.experiencia"
          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-[#53C6D3] focus:border-[#53C6D3]"></textarea>
      </div>

      <!-- Certificaciones -->
      <div class="col-span-2">
        <label class="block text-sm font-medium">Certificaciones</label>
        <textarea v-model="form.certificaciones"
          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-[#53C6D3] focus:border-[#53C6D3]"></textarea>
      </div>

      <!-- Cursos -->
      <div class="col-span-2">
        <label class="block text-sm font-medium">Cursos</label>
        <textarea v-model="form.cursos"
          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-[#53C6D3] focus:border-[#53C6D3]"></textarea>
      </div>

    </form>
    <template #footer>
      <button type="submit" class="px-6 py-2 bg-[#53C6D3] text-white rounded-md hover:bg-[#2D2B5B] transition"
        @click="submit">
        Guardar cambios
      </button>
    </template>
  </ModalBaseEditar>
</template>