<template>
  <Link
    :href="href"
    class="flex items-center px-6 py-3"
    :class="classes"
  >
    <slot />
  </Link>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
  href: {
    type: String,
    required: true,
  },
  active: {
    type: Boolean,
    default: false,
  },
})

const classes = computed(() =>
  props.active
    ? 'bg-[#1f1d3f] text-[#53C6D3] font-bold flex items-center px-6 py-3'
    : 'hover:bg-[#1f1d3f] text-white flex items-center px-6 py-3'
)
</script>
