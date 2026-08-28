<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
  <div class="min-h-screen flex">
    <!-- Panel izquierdo 60% -->
    <div class="hidden lg:flex w-3/5 bg-[#2D2B5B] items-center justify-center text-white">
      <img src="/images/00_referencia_marca.png" alt="Marca CAINE" class="max-w-md" />
    </div>

    <!-- Panel derecho 40% -->
    <div class="flex-1 w-2/5 flex items-center justify-center bg-[#FAF9F7]">
      <div class="w-full max-w-md p-8">
        <slot />
      </div>
    </div>
  </div>
</template>
