<script setup>
import { useForm } from '@inertiajs/vue3'
import ModalBaseEditar from '../ModalBaseEditar.vue'

const emit = defineEmits(['close'])

const props = defineProps({
  terapeuta: { type: Object, required: true },
  especialidades: { type: Array, default: () => [] },
  generos: { type: Array, default: () => [] }
})

const form = useForm({
  nombres: props.terapeuta?.nombres || '',
  apellidos: props.terapeuta?.apellidos || '',
  fecha_nacimiento: props.terapeuta?.fecha_nacimiento || '',
  dpi: props.terapeuta?.dpi || '',
  telefono: props.terapeuta?.telefono || '',
  correo: props.terapeuta?.correo || '',
  genero_id: props.terapeuta?.genero_id || '',
  especialidad_id: props.terapeuta?.especialidad_id || '',
  experiencia: props.terapeuta?.experiencia || '',
  certificaciones: props.terapeuta?.certificaciones || '',
  cursos: props.terapeuta?.cursos || ''
})

function submit() {
  form.put(`/personas/terapeutas/${props.terapeuta.id}`, {
    onSuccess: () => emit('close'),
    onError: (errors) => console.error(errors)
  })
}
</script>

<template>
  <ModalBaseEditar @close="$emit('close')">
    <template #header>
      <h2 class="text-2xl font-bold text-[#2D2B5B]">Editar Terapeuta</h2>
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