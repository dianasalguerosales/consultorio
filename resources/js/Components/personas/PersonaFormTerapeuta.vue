<script setup>
const props = defineProps({
  form: Object,
  especialidades: Array
})
</script>

<template>
  <form class="grid grid-cols-2 gap-6 text-gray-700 mt-6">
    <!-- Especialidad -->
    <div>
      <label class="block text-sm font-medium">Especialidad</label>
      <select v-model="form.especialidad_id"
        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm 
               focus:ring-[#53C6D3] focus:border-[#53C6D3]">
        <option value="">Seleccione...</option>
        <option v-for="e in especialidades" :key="e.id" :value="e.id">{{ e.nombre }}</option>
      </select>
    </div>

    <!-- Experiencia -->
    <div class="col-span-2">
      <label class="block text-sm font-medium">Experiencia</label>
      <textarea v-model="form.experiencia"
        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm 
               focus:ring-[#53C6D3] focus:border-[#53C6D3]"></textarea>
    </div>

    <!-- Certificaciones -->
    <div class="col-span-2">
      <label class="block text-sm font-medium">Certificaciones</label>
      <textarea v-model="form.certificaciones"
        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm 
               focus:ring-[#53C6D3] focus:border-[#53C6D3]"></textarea>
    </div>

    <!-- Cursos -->
    <div class="col-span-2">
      <label class="block text-sm font-medium">Cursos</label>
      <textarea v-model="form.cursos"
        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm 
               focus:ring-[#53C6D3] focus:border-[#53C6D3]"></textarea>
    </div>
  </form>
</template>