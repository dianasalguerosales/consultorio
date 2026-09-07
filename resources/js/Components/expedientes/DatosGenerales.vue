<script setup>
const props = defineProps({
  form: Object,
  escolaridadesList: Array,
  diagnosticosList: Array,
  modalidadesList: Array,
  estadoExpedientes: Array,
  serviciosList: Array
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
        <select v-model="form.estado_expediente_id"
          class="mt-1 block w-full border rounded-md px-3 py-2 focus:ring-[#53C6D3] focus:border-[#53C6D3]">
          <option value="">Seleccione...</option>
          <option v-for="estado in estadoExpedientes" :key="estado.id" :value="estado.id">
            {{ estado.nombre }}
          </option>
        </select>
      </div>

      <!-- Fecha inicio -->
      <div>
        <label class="block text-sm font-medium text-[#2D2B5B]">Fecha inicio</label>
        <input v-model="form.fecha_inicio" type="date"
          class="mt-1 block w-full border rounded-md px-3 py-2 focus:ring-[#53C6D3] focus:border-[#53C6D3]" />
      </div>

      <!-- Nombres -->
      <div>
        <label class="block text-sm font-medium text-[#2D2B5B]">Nombres</label>
        <input v-model="form.nombres" type="text"
          class="mt-1 block w-full border rounded-md px-3 py-2 focus:ring-[#53C6D3] focus:border-[#53C6D3]" />
      </div>

      <!-- Apellidos -->
      <div>
        <label class="block text-sm font-medium text-[#2D2B5B]">Apellidos</label>
        <input v-model="form.apellidos" type="text"
          class="mt-1 block w-full border rounded-md px-3 py-2 focus:ring-[#53C6D3] focus:border-[#53C6D3]" />
      </div>

      <!-- Fecha nacimiento -->
      <div>
        <label class="block text-sm font-medium text-[#2D2B5B]">Fecha nacimiento</label>
        <input v-model="form.fecha_nacimiento" type="date"
          class="mt-1 block w-full border rounded-md px-3 py-2 focus:ring-[#53C6D3] focus:border-[#53C6D3]" />
      </div>

      <!-- Modalidad -->
      <div>
        <label class="block text-sm font-medium text-[#2D2B5B]">Modalidad</label>
        <select v-model="form.modalidad_id"
          class="mt-1 block w-full border rounded-md px-3 py-2 focus:ring-[#53C6D3] focus:border-[#53C6D3]">
          <option value="">Seleccione una modalidad</option>
          <option v-for="mod in modalidadesList" :key="mod.id" :value="mod.id">
            {{ mod.nombre }}
          </option>
        </select>
      </div>

      <!-- Escolaridad -->
      <div>
        <label class="block text-sm font-medium text-[#2D2B5B]">Escolaridad</label>
        <select v-model="form.escolaridad_id"
          class="mt-1 block w-full border rounded-md px-3 py-2 focus:ring-[#53C6D3] focus:border-[#53C6D3]">
          <option :value="null">N/D</option>
          <option v-for="esc in escolaridadesList" :key="esc.id" :value="esc.id">
            {{ esc.nombre }}
          </option>
        </select>
      </div>
    </div>

    <!-- Motivo consulta -->
    <div class="mt-6">
      <label class="block text-sm font-medium text-[#2D2B5B]">Motivo de consulta</label>
      <textarea v-model="form.motivo_consulta" rows="2"
        class="mt-1 block w-full border rounded-md px-3 py-2 focus:ring-[#53C6D3] focus:border-[#53C6D3]"></textarea>
    </div>

    <!-- Observaciones -->
    <div class="mt-6">
      <label class="block text-sm font-medium text-[#2D2B5B]">Observaciones</label>
      <textarea v-model="form.observaciones" rows="2"
        class="mt-1 block w-full border rounded-md px-3 py-2 focus:ring-[#53C6D3] focus:border-[#53C6D3]"></textarea>
    </div>

    <!-- Consentimiento -->
    <div class="mt-6">
      <label class="inline-flex items-center">
        <input type="checkbox" v-model="form.consentimiento"
          class="rounded border-gray-300 text-[#53C6D3] focus:ring-[#53C6D3]" />
        <span class="ml-2 text-sm text-[#2D2B5B]">Consentimiento informado</span>
      </label>
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