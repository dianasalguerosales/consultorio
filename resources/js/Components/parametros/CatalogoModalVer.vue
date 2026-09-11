<script setup>
import ModalBaseVer from '../ModalBaseVer.vue'

const props = defineProps({
  item: { type: Object, required: true },
  titulo: { type: String, required: true },
  conDescripcion: { type: Boolean, default: false },
  campos: { type: Array, default: () => [] },
})

const valor = (campo) => {
  const v = props.item[campo.clave]
  if (v === null || v === undefined || v === '') return '—'
  return campo.tipo === 'moneda' ? `Q${Number(v).toFixed(2)}` : v
}

const emit = defineEmits(['close'])
</script>

<template>
  <ModalBaseVer @close="emit('close')">
    <template #header>
      <h2 class="text-xl font-bold text-[#2D2B5B]">Detalle de {{ titulo }}</h2>
    </template>

    <div class="space-y-4">
      <p><strong>Nombre:</strong> {{ item.nombre }}</p>
      <p v-if="conDescripcion"><strong>Descripción:</strong> {{ item.descripcion }}</p>
      <p v-for="c in campos" :key="c.clave"><strong>{{ c.etiqueta }}:</strong> {{ valor(c) }}</p>
      <p><strong>Activo:</strong> {{ item.activo ? 'Sí' : 'No' }}</p>
    </div>
  </ModalBaseVer>
</template>
