<script setup>
defineProps({ form: Object })
</script>

<template>
  <div class="grid grid-cols-3 gap-6">
    <div><label class="block text-sm font-medium text-[#2D2B5B]">Nombres</label><input v-model="form.nombres"
        type="text" class="mt-1 block w-full border rounded-md px-3 py-2 focus:ring-[#53C6D3] focus:border-[#53C6D3]" />
    </div>
    <div><label class="block text-sm font-medium text-[#2D2B5B]">Apellidos</label><input v-model="form.apellidos"
        type="text" class="mt-1 block w-full border rounded-md px-3 py-2 focus:ring-[#53C6D3] focus:border-[#53C6D3]" />
    </div>
    <div><label class="block text-sm font-medium text-[#2D2B5B]">DPI</label><input v-model="form.dpi" type="text"
        class="mt-1 block w-full border rounded-md px-3 py-2 focus:ring-[#53C6D3] focus:border-[#53C6D3]" /></div>
    <div><label class="block text-sm font-medium text-[#2D2B5B]">Teléfono</label><input v-model="form.telefono"
        type="text" class="mt-1 block w-full border rounded-md px-3 py-2 focus:ring-[#53C6D3] focus:border-[#53C6D3]" />
    </div>
    <div><label class="block text-sm font-medium text-[#2D2B5B]">Correo personal</label><input v-model="form.correo"
        type="email"
        class="mt-1 block w-full border rounded-md px-3 py-2 focus:ring-[#53C6D3] focus:border-[#53C6D3]" /></div>
    <div><label class="block text-sm font-medium text-[#2D2B5B]">Fecha nacimiento</label><input
        v-model="form.fecha_nacimiento" type="date"
        class="mt-1 block w-full border rounded-md px-3 py-2 focus:ring-[#53C6D3] focus:border-[#53C6D3]" /></div>
    <div>
      <label class="block text-sm font-medium text-[#2D2B5B]">Género</label>
      <select v-model="form.genero_id"
        class="mt-1 block w-full border rounded-md px-3 py-2 focus:ring-[#53C6D3] focus:border-[#53C6D3]">
        <option value="">Seleccione...</option>
        <option v-for="g in $page.props.generos" :key="g.id" :value="g.id">{{ g.nombre }}</option>
      </select>
    </div>
    <div>
      <label class="block text-sm font-medium text-[#2D2B5B]">Cargo</label>
      <select v-model="form.cargo_id"
        class="mt-1 block w-full border rounded-md px-3 py-2 focus:ring-[#53C6D3] focus:border-[#53C6D3]">
        <option value="">Seleccione...</option>
        <option v-for="c in $page.props.cargos" :key="c.id" :value="c.id">{{ c.nombre }}</option>
      </select>
    </div>
    <div>
      <label class="block text-sm font-medium text-[#2D2B5B]">Especialidad</label>
      <select v-model="form.especialidad_id"
        class="mt-1 block w-full border rounded-md px-3 py-2 focus:ring-[#53C6D3] focus:border-[#53C6D3]">
        <option value="">Seleccione...</option>
        <option v-for="e in $page.props.especialidades" :key="e.id" :value="e.id">{{ e.nombre }}</option>
      </select>
    </div>
    <div class="col-span-3"><label class="block text-sm font-medium text-[#2D2B5B]">Experiencia</label><textarea
        v-model="form.experiencia" rows="2"
        class="mt-1 block w-full border rounded-md px-3 py-2 focus:ring-[#53C6D3] focus:border-[#53C6D3]"></textarea>
    </div>
    <div class="col-span-3"><label class="block text-sm font-medium text-[#2D2B5B]">Certificaciones</label><textarea
        v-model="form.certificaciones" rows="2"
        class="mt-1 block w-full border rounded-md px-3 py-2 focus:ring-[#53C6D3] focus:border-[#53C6D3]"></textarea>
    </div>
    <div class="col-span-3"><label class="block text-sm font-medium text-[#2D2B5B]">Cursos</label><textarea
        v-model="form.cursos" rows="2"
        class="mt-1 block w-full border rounded-md px-3 py-2 focus:ring-[#53C6D3] focus:border-[#53C6D3]"></textarea>
    </div>
  </div>
</template>
