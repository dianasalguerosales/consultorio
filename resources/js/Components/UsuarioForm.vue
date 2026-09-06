<script setup>
defineProps({
  user: Object,
  form: Object,
  roles: Array,
})
defineEmits(['close', 'save'])
</script>

<template>
  <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-30 z-50">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full p-8">
      <h2 class="text-lg font-bold text-caine-azul mb-6">
        {{ user ? 'Editar Usuario' : 'Nuevo Usuario' }}
      </h2>

      <div v-if="!user" class="grid grid-cols-2 gap-4 mb-6">
        <div>
          <label class="block text-sm font-medium text-gray-700">Correo</label>
          <input v-model="form.email" type="email" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
          <div v-if="form.errors.email" class="text-red-500 text-sm">{{ form.errors.email }}</div>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Contraseña</label>
          <input v-model="form.password" type="password"
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
          <div v-if="form.errors.password" class="text-red-500 text-sm">{{ form.errors.password }}</div>
        </div>
      </div>

      <div v-if="!user" class="mb-6">
        <label class="block text-sm font-medium text-gray-700">Tipo de usuario</label>
        <select v-model="form.tipo_usuario" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
          <option value="">Seleccione...</option>
          <option value="administrativo">Administrativo</option>
          <option value="terapeuta">Terapeuta</option>
          <option value="encargado">Encargado</option>
        </select>
        <div v-if="form.errors.tipo_usuario" class="text-red-500 text-sm">{{ form.errors.tipo_usuario }}</div>
      </div>

      <div v-if="form.tipo_usuario === 'administrativo'" class="grid grid-cols-2 gap-4">
        <div>
          <label>Nombres</label>
          <input v-model="form.nombres" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
        </div>
        <div>
          <label>Apellidos</label>
          <input v-model="form.apellidos" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
        </div>
        <div>
          <label>Teléfono</label>
          <input v-model="form.telefono" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
        </div>
        <div>
          <label>Dirección</label>
          <input v-model="form.direccion" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
        </div>
        <div>
          <label>Fecha de nacimiento</label>
          <input v-model="form.fecha_nacimiento" type="date"
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
        </div>
        <div>
          <label>Género</label>
          <select v-model="form.genero_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
            <option value="">Seleccione...</option>
            <option v-for="g in $page.props.generos" :key="g.id" :value="g.id">
              {{ g.nombre }}
            </option>
          </select>
        </div>
        <div>
          <label>Cargo</label>
          <select v-model="form.cargo_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
            <option value="">Seleccione...</option>
            <option v-for="c in $page.props.cargos" :key="c.id" :value="c.id">
              {{ c.nombre }}
            </option>
          </select>
        </div>
      </div>

      <div v-if="form.tipo_usuario === 'terapeuta'" class="grid grid-cols-2 gap-4">
        <div>
          <label>Nombres</label>
          <input v-model="form.nombres" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
        </div>
        <div>
          <label>Apellidos</label>
          <input v-model="form.apellidos" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
        </div>
        <div>
          <label>Especialidad</label>
          <select v-model="form.especialidad_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
            <option value="">Seleccione...</option>
            <option v-for="e in $page.props.especialidades" :key="e.id" :value="e.id">
              {{ e.nombre }}
            </option>
          </select>
        </div>
        <div>
          <label>Número colegiado</label>
          <input v-model="form.numero_colegiado" type="text"
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
        </div>
        <div>
          <label>Experiencia</label>
          <input v-model="form.experiencia" type="text"
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
        </div>
        <div>
          <label>Formación</label>
          <input v-model="form.formacion" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
        </div>
        <div>
          <label>Certificaciones</label>
          <input v-model="form.certificaciones" type="text"
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
        </div>
      </div>

      <div v-if="form.tipo_usuario === 'encargado'" class="grid grid-cols-2 gap-4">
        <div>
          <label>Nombres</label>
          <input v-model="form.nombres" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
        </div>
        <div>
          <label>Apellidos</label>
          <input v-model="form.apellidos" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
        </div>
        <div>
          <label>Teléfono</label>
          <input v-model="form.telefono" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
        </div>
        <div class="col-span-2">
          <label>Relación</label>
          <input v-model="form.relacion" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
        </div>
      </div>

      <div class="mt-6">
        <h3 class="text-sm font-medium text-gray-700 mb-2">Roles</h3>
        <div class="grid grid-cols-2 gap-2">
          <label v-for="role in roles" :key="role.id" class="flex items-center space-x-2">
            <input type="checkbox" :value="role.name" v-model="form.roles" class="rounded border-gray-300" />
            <span>{{ role.name.charAt(0).toUpperCase() + role.name.slice(1) }}</span>
          </label>
        </div>
      </div>

      <div class="flex justify-end space-x-3 mt-6">
        <button class="px-4 py-2 bg-gray-200 rounded-md hover:bg-gray-300" @click="$emit('close')">
          Cancelar
        </button>
        <button class="px-4 py-2 bg-caine-celeste text-white rounded-md hover:bg-caine-azul" @click="$emit('save')">
          Guardar
        </button>
      </div>
    </div>
  </div>
</template>