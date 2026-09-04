<script setup>
import { Head } from '@inertiajs/vue3'
</script>

<template>
  <Head title="Panel administrador" />

  <div class="p-6">
    <h2 class="text-2xl font-bold text-[#2D2B5B] mb-4">Bienvenido al Panel</h2>
    <p class="text-[#2B2B41]">apacha una opción del menú lateral para comenzar.</p>

    
  </div>
</template>

<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

export default {
  layout: AuthenticatedLayout
}
</script>
