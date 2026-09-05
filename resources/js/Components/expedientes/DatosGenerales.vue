<script setup>
const props = defineProps({
  form: Object,
  escolaridadesList: Array
})
const emit = defineEmits(['next'])
</script>

<template>
  <div class="max-w-4xl mx-auto bg-white rounded-lg shadow p-8">
    <h3 class="text-2xl font-bold text-[#2D2B5B] mb-6">
      Datos Generales del Expediente
    </h3>

    <div class="grid grid-cols-2 gap-6">
      <!-- Estado -->
      <div>
        <label class="block text-sm font-medium text-[#2D2B5B]">Estado</label>
        <select v-model="form.estado" class="mt-1 block w-full border rounded-md px-3 py-2 focus:ring-[#53C6D3] focus:border-[#53C6D3]">
          <option value="activo">Activo</option>
          <option value="archivado">Archivado</option>
          <option value="pendiente">Pendiente</option>
          <option value="inactivo">Inactivo</option>
        </select>
      </div>

      <!-- Fecha apertura -->
      <div>
        <label class="block text-sm font-medium text-[#2D2B5B]">Fecha inicio</label>
        <input v-model="form.fecha_apertura" type="date" class="mt-1 block w-full border rounded-md px-3 py-2 focus:ring-[#53C6D3] focus:border-[#53C6D3]" />
      </div>

      <!-- Nombre -->
      <div class="col-span-2">
        <label class="block text-sm font-medium text-[#2D2B5B]">Nombre pila</label>
        <input v-model="form.nombre_pila" type="text" class="mt-1 block w-full border rounded-md px-3 py-2 focus:ring-[#53C6D3] focus:border-[#53C6D3]" />
      </div>

      <!-- Motivo consulta -->
      <div class="col-span-2">
        <label class="block text-sm font-medium text-[#2D2B5B]">Motivo de consulta</label>
        <textarea v-model="form.motivo_consulta" rows="2" class="mt-1 block w-full border rounded-md px-3 py-2 focus:ring-[#53C6D3] focus:border-[#53C6D3]"></textarea>
      </div>

      <!-- Escolaridad -->
      <div>
        <label class="block text-sm font-medium text-[#2D2B5B]">Escolaridad</label>
        <select v-model="form.escolaridad_id" class="mt-1 block w-full border rounded-md px-3 py-2 focus:ring-[#53C6D3] focus:border-[#53C6D3]">
          <option :value="null">N/D</option>
          <option v-for="esc in escolaridadesList" :key="esc.id" :value="esc.id">
            {{ esc.grado }}
          </option>
        </select>
      </div>

      <!-- Modalidad -->
      <div>
        <label class="block text-sm font-medium text-[#2D2B5B]">Modalidad</label>
        <select v-model="form.modalidad" class="mt-1 block w-full border rounded-md px-3 py-2 focus:ring-[#53C6D3] focus:border-[#53C6D3]">
          <option value="presencial">Presencial</option>
          <option value="virtual">Virtual</option>
        </select>
      </div>
    </div>

    <!-- Botón siguiente -->
    <div class="mt-8 flex justify-end">
      <button @click="emit('next')"
        class="inline-flex items-center px-6 py-2 bg-[#2D2B5B] text-white rounded-md hover:bg-[#53C6D3] transition-colors">
        Siguiente →
      </button>
    </div>
  </div>
</template>
