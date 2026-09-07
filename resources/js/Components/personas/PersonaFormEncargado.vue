<script setup>
const props = defineProps({
  form: Object,
  estadosCiviles: Array,
  relacionesPaciente: Array
})
</script>

<template>
  <form class="grid grid-cols-2 gap-6 text-gray-700 mt-6">
    <!-- Dirección -->
    <div>
      <label class="block text-sm font-medium">Dirección</label>
      <input v-model="form.direccion" type="text"
        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm 
               focus:ring-[#53C6D3] focus:border-[#53C6D3]" />
    </div>

    <!-- Ocupación -->
    <div>
      <label class="block text-sm font-medium">Ocupación</label>
      <input v-model="form.ocupacion" type="text"
        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm 
               focus:ring-[#53C6D3] focus:border-[#53C6D3]" />
    </div>

    <!-- Estado civil -->
    <div>
      <label class="block text-sm font-medium">Estado civil</label>
      <select v-model="form.estado_civil_id"
        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm 
               focus:ring-[#53C6D3] focus:border-[#53C6D3]">
        <option value="">Seleccione...</option>
        <option v-for="e in estadosCiviles" :key="e.id" :value="e.id">{{ e.nombre }}</option>
      </select>
    </div>

    <!-- Relación con paciente -->
    <div>
      <label class="block text-sm font-medium">Relación con paciente</label>
      <select v-model="form.relacion_paciente_id"
        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm 
               focus:ring-[#53C6D3] focus:border-[#53C6D3]">
        <option value="">Seleccione...</option>
        <option v-for="r in relacionesPaciente" :key="r.id" :value="r.id">{{ r.nombre }}</option>
      </select>
    </div>
  </form>
</template>