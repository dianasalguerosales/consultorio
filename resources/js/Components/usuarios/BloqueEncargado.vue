<script setup>
defineProps({ form: Object })
</script>

<template>
  <div class="grid grid-cols-3 gap-6">
    <div>
      <label class="block text-sm font-medium text-[#2D2B5B]">Nombres</label>
      <input v-model="form.nombres" type="text"
        class="mt-1 block w-full border rounded-md px-3 py-2 focus:ring-[#53C6D3] focus:border-[#53C6D3]" />
    </div>
    <div>
      <label class="block text-sm font-medium text-[#2D2B5B]">Apellidos</label>
      <input v-model="form.apellidos" type="text"
        class="mt-1 block w-full border rounded-md px-3 py-2 focus:ring-[#53C6D3] focus:border-[#53C6D3]" />
    </div>
    <div>
      <label class="block text-sm font-medium text-[#2D2B5B]">DPI</label>
      <input v-model="form.dpi" type="text"
        class="mt-1 block w-full border rounded-md px-3 py-2 focus:ring-[#53C6D3] focus:border-[#53C6D3]" />
    </div>
    <div>
      <label class="block text-sm font-medium text-[#2D2B5B]">Teléfono</label>
      <input v-model="form.telefono" type="text"
        class="mt-1 block w-full border rounded-md px-3 py-2 focus:ring-[#53C6D3] focus:border-[#53C6D3]" />
    </div>
    <div>
      <label class="block text-sm font-medium text-[#2D2B5B]">Fecha nacimiento</label>
      <input v-model="form.fecha_nacimiento" type="date"
        class="mt-1 block w-full border rounded-md px-3 py-2 focus:ring-[#53C6D3] focus:border-[#53C6D3]" />
    </div>
    <div class="col-span-3">
      <label class="block text-sm font-medium text-[#2D2B5B]">Dirección</label>
      <input v-model="form.direccion" type="text"
        class="mt-1 block w-full border rounded-md px-3 py-2 focus:ring-[#53C6D3] focus:border-[#53C6D3]" />
    </div>
    <div class="col-span-3">
      <label class="block text-sm font-medium text-[#2D2B5B]">Ocupación</label>
      <input v-model="form.ocupacion" type="text"
        class="mt-1 block w-full border rounded-md px-3 py-2 focus:ring-[#53C6D3] focus:border-[#53C6D3]" />
    </div>
    <div>
      <label class="block text-sm font-medium text-[#2D2B5B]">Relación con paciente</label>
      <select v-model="form.relacion_paciente_id"
        class="mt-1 block w-full border rounded-md px-3 py-2 focus:ring-[#53C6D3] focus:border-[#53C6D3]">
        <option value="">Seleccione...</option>
        <option v-for="r in $page.props.relaciones_paciente" :key="r.id" :value="r.id">{{ r.nombre }}</option>
      </select>
    </div>
    <div>
      <label class="block text-sm font-medium text-[#2D2B5B]">Género</label>
      <select v-model="form.genero_id"
        class="mt-1 block w-full border rounded-md px-3 py-2 focus:ring-[#53C6D3] focus:border-[#53C6D3]">
        <option value="">Seleccione...</option>
        <option v-for="g in $page.props.generos" :key="g.id" :value="g.id">{{ g.nombre }}</option>
      </select>
    </div>
    <div>
      <label class="block text-sm font-medium text-[#2D2B5B]">Estado civil</label>
      <select v-model="form.estado_civil_id"
        class="mt-1 block w-full border rounded-md px-3 py-2 focus:ring-[#53C6D3] focus:border-[#53C6D3]">
        <option value="">Seleccione...</option>
        <option v-for="ec in $page.props.estados_civiles" :key="ec.id" :value="ec.id">{{ ec.nombre }}</option>
      </select>
    </div>
  </div>
</template>
