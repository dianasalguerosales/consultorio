<script setup>
import { useForm } from '@inertiajs/vue3'
import ModalBaseEditar from '../ModalBaseEditar.vue'

const emit = defineEmits(['close'])

const props = defineProps({
  escolaridad: { type: Object, required: true }
})

const form = useForm({
  nombre: props.escolaridad?.nombre || '',
  activo: props.escolaridad?.activo ? true : false
})

function submit() {
  form.put(route('escolaridades.update', props.escolaridad.id), {
    onSuccess: () => emit('close'),
    onError: (errors) => console.error(errors)
  })
}
</script>

<template>
  <ModalBaseEditar @close="$emit('close')">
    <template #header>
      <h2 class="text-2xl font-bold text-[#2D2B5B]">Editar Escolaridad</h2>
    </template>

    <form @submit.prevent="submit" class="grid grid-cols-2 gap-6 text-gray-700">
      <!-- Nombre -->
      <div class="col-span-2">
        <label class="block text-sm font-medium">Nombre</label>
        <input v-model="form.nombre" type="text"
          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-[#53C6D3] focus:border-[#53C6D3]" />
        <span v-if="form.errors.nombre" class="text-red-600 text-sm">{{ form.errors.nombre }}</span>
      </div>

      <!-- Activo -->
      <div>
        <label class="block text-sm font-medium">Activo</label>
        <select v-model="form.activo"
          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-[#53C6D3] focus:border-[#53C6D3]">
          <option :value="true">Sí</option>
          <option :value="false">No</option>
        </select>
      </div>
    </form>

    <template #footer>
      <button type="submit" :disabled="form.processing"
        class="px-6 py-2 bg-[#53C6D3] text-white rounded-md hover:bg-[#2D2B5B] transition"
        @click="submit">
        Guardar cambios
      </button>
    </template>
  </ModalBaseEditar>
</template>