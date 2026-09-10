<script setup>
import { computed, ref } from 'vue'
import { Head } from '@inertiajs/vue3'
import CatalogoTabla from '@/Components/parametros/CatalogoTabla.vue'

const props = defineProps({
  // Viene de App\Catalogos\Catalogos: pestañas y filas en una sola estructura.
  catalogos: { type: Array, default: () => [] },
})

const activa = ref(props.catalogos[0]?.clave ?? null)

const catalogoActivo = computed(() =>
  props.catalogos.find((c) => c.clave === activa.value) ?? null
)
</script>

<template>
  <Head title="Gestión de Parámetros" />

  <div class="bg-white rounded-lg shadow-md p-8 w-full">
    <h1 class="text-2xl font-bold text-[#2D2B5B] mb-6">Gestión de Parámetros</h1>

    <div class="flex flex-wrap gap-x-6 gap-y-2 border-b mb-6 text-[#2D2B5B]">
      <button v-for="c in catalogos" :key="c.clave" @click="activa = c.clave"
        :class="activa === c.clave
          ? 'border-b-2 border-[#53C6D3] font-semibold'
          : 'text-gray-500 hover:text-[#2D2B5B]'">
        {{ c.etiqueta }}
      </button>
    </div>

    <!-- El :key remonta la tabla al cambiar de pestaña, así no arrastra el
         modal ni la selección del catálogo anterior. -->
    <CatalogoTabla v-if="catalogoActivo" :key="catalogoActivo.clave" :catalogo="catalogoActivo" />
  </div>
</template>

<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

export default { layout: AuthenticatedLayout }
</script>
