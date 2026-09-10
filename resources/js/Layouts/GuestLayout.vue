<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
  <!-- En columna hasta lg: la marca va arriba en lugar de desaparecer, que es
       lo que hacía antes con `hidden lg:flex`. -->
  <div class="min-h-screen flex flex-col lg:flex-row">
    <!-- Marca: arriba en teléfono y tablet, a la izquierda desde lg -->
    <div class="bg-[#2D2B5B] text-white flex items-center justify-center
                px-6 py-8 lg:w-3/5 lg:py-0">
      <img src="/images/00_referencia_marca.png" alt="Marca CAINE"
        class="w-48 sm:w-64 lg:w-auto lg:max-w-md" />
    </div>

    <!-- Formulario -->
    <div class="flex-1 flex items-center justify-center bg-[#FAF9F7] px-4 py-8">
      <div class="w-full max-w-md lg:p-8">
        <slot />
      </div>
    </div>
  </div>
</template>
