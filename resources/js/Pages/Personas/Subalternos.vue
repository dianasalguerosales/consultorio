<script setup>
import { Head, Link } from '@inertiajs/vue3'
import NodoOrganigrama from '@/Components/personas/NodoOrganigrama.vue'

defineProps({
  organigrama: { type: Object, required: true },
})
</script>

<template>
  <Head title="Subalternos" />

  <div class="p-8 max-w-7xl mx-auto">
    <h2 class="text-2xl font-bold text-caine-azul mb-6">Organigrama</h2>

    <div class="mb-6 flex justify-end">
      <Link href="/personas"
        class="inline-flex items-center gap-1 px-4 py-3 rounded-lg border border-caine-azul
               text-caine-azul font-semibold hover:bg-caine-azul hover:text-white transition">
        <span class="material-icons text-base">arrow_back</span>
        Volver
      </Link>
    </div>

    <div class="bg-white rounded-lg shadow-md p-8">
      <div class="overflow-x-auto">
        <ul class="flex justify-center min-w-max">
          <NodoOrganigrama :nodo="organigrama" />
        </ul>
      </div>

      <p v-if="!organigrama.subalternos?.length" class="mt-6 text-center text-sm text-gray-400">
        {{ organigrama.nombre }} no tiene personal a su cargo.
      </p>
    </div>
  </div>
</template>

<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

export default { layout: AuthenticatedLayout }
</script>
