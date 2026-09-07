<script setup>
const props = defineProps({
  form: Object,
  generos: Array
})
</script>

<template>
  <form class="grid grid-cols-2 gap-6 text-gray-700">
    <div>
      <label class="block text-sm font-medium">Nombres</label>
      <input v-model="form.nombres" type="text"
        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-[#53C6D3] focus:border-[#53C6D3]" />
    </div>
    <div>
      <label class="block text-sm font-medium">Apellidos</label>
      <input v-model="form.apellidos" type="text"
        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-[#53C6D3] focus:border-[#53C6D3]" />
    </div>
    <div>
      <label class="block text-sm font-medium">Fecha nacimiento</label>
      <input v-model="form.fecha_nacimiento" type="date"
        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-[#53C6D3] focus:border-[#53C6D3]" />
    </div>
    <div>
      <label class="block text-sm font-medium">DPI</label>
      <input v-model="form.dpi" type="text" inputmode="numeric"
        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-[#53C6D3] focus:border-[#53C6D3]" />
    </div>
    <div>
      <label class="block text-sm font-medium">Teléfono</label>
      <input v-model="form.telefono" type="text"
        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-[#53C6D3] focus:border-[#53C6D3]" />
    </div>
    <div>
      <label class="block text-sm font-medium">Correo</label>
      <input v-model="form.correo" type="email"
        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-[#53C6D3] focus:border-[#53C6D3]" />
    </div>
    <div>
      <label class="block text-sm font-medium">Género</label>
      <select v-model="form.genero_id"
        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-[#53C6D3] focus:border-[#53C6D3]">
        <option value="">Seleccione...</option>
        <option v-for="g in generos" :key="g.id" :value="g.id">{{ g.nombre }}</option>
      </select>
    </div>
    <!-- Usuario asignado -->
    <div>
      <label class="block text-sm font-medium">Usuario del sistema</label>
      <select v-model="form.user_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm 
           focus:ring-[#53C6D3] focus:border-[#53C6D3]">
        <option value="">Seleccione...</option>
        <option v-for="u in props.usuariosDisponibles" :key="u.id" :value="u.id">
          {{ u.name }} ({{ u.email }})
        </option>
      </select>
    </div>
  </form>
</template>
