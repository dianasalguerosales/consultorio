<script setup>
import { useForm } from '@inertiajs/vue3'
import ModalBaseEditar from '../ModalBaseEditar.vue'

const props = defineProps({
  // null = alta. Con item se edita.
  item: { type: Object, default: null },
  catalogo: { type: String, required: true },
  titulo: { type: String, required: true },
  conDescripcion: { type: Boolean, default: false },
  // Campos propios del catalogo, como la cantidad de citas y el costo de un
  // programa. Vienen de App\Catalogos\Catalogos.
  campos: { type: Array, default: () => [] },
})

const emit = defineEmits(['close'])

// Los campos extra se declaran aca a proposito: useForm solo manda las llaves
// que existen en el objeto inicial, y un v-model sobre una llave no declarada
// se descarta sin avisar.
const form = useForm({
  nombre: props.item?.nombre ?? '',
  descripcion: props.item?.descripcion ?? '',
  activo: props.item ? Boolean(props.item.activo) : true,
  ...Object.fromEntries(props.campos.map((c) => [c.clave, props.item?.[c.clave] ?? ''])),
})

function guardar() {
  const opciones = { preserveScroll: true, onSuccess: () => emit('close') }

  props.item
    ? form.put(route('catalogos.update', [props.catalogo, props.item.id]), opciones)
    : form.post(route('catalogos.store', props.catalogo), opciones)
}
</script>

<template>
  <ModalBaseEditar @close="emit('close')">
    <template #header>
      <h2 class="text-2xl font-bold text-[#2D2B5B]">
        {{ item ? 'Editar' : 'Nuevo' }} {{ titulo.toLowerCase() }}
      </h2>
    </template>

    <form @submit.prevent="guardar" class="grid grid-cols-2 gap-6 text-gray-700">
      <div class="col-span-2">
        <label class="block text-sm font-medium">Nombre</label>
        <input v-model="form.nombre" type="text"
          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-[#53C6D3] focus:border-[#53C6D3]" />
        <p v-if="form.errors.nombre" class="mt-1 text-sm text-caine-error">{{ form.errors.nombre }}</p>
      </div>

      <div v-if="conDescripcion" class="col-span-2">
        <label class="block text-sm font-medium">Descripción</label>
        <textarea v-model="form.descripcion"
          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-[#53C6D3] focus:border-[#53C6D3]"></textarea>
        <p v-if="form.errors.descripcion" class="mt-1 text-sm text-caine-error">{{ form.errors.descripcion }}</p>
      </div>

      <div v-for="c in campos" :key="c.clave">
        <label class="block text-sm font-medium">{{ c.etiqueta }}</label>
        <input v-model="form[c.clave]" type="number" :step="c.tipo === 'moneda' ? '0.01' : '1'" min="0"
          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-[#53C6D3] focus:border-[#53C6D3]" />
        <p v-if="form.errors[c.clave]" class="mt-1 text-sm text-caine-error">{{ form.errors[c.clave] }}</p>
      </div>

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
      <button type="button" @click="guardar" :disabled="form.processing"
        class="px-6 py-2 bg-[#53C6D3] text-white rounded-md hover:bg-[#2D2B5B] transition disabled:opacity-50">
        {{ form.processing ? 'Guardando...' : 'Guardar' }}
      </button>
    </template>
  </ModalBaseEditar>
</template>
