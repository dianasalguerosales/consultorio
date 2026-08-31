<script setup>
defineProps({
  user: Object,
  form: Object,
  roles: Array,
})
defineEmits(['close','save'])
</script>

<template>
  <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-30 z-50">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full p-8">
      <h2 class="text-lg font-bold text-caine-azul mb-6">
        {{ user ? 'Editar Usuario' : 'Nuevo Usuario' }}
      </h2>

      <!-- Email y contraseña solo al crear -->
      <div v-if="!user" class="grid grid-cols-2 gap-4 mb-6">
        <div>
          <label class="block text-sm font-medium text-gray-700">Correo</label>
          <input v-model="form.email" type="email" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
          <div v-if="form.errors.email" class="text-red-500 text-sm">{{ form.errors.email }}</div>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Contraseña</label>
          <input v-model="form.password" type="password" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
          <div v-if="form.errors.password" class="text-red-500 text-sm">{{ form.errors.password }}</div>
        </div>
      </div>

      <!-- Tipo de usuario -->
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

      <!-- Campos dinámicos -->
      <div v-if="form.tipo_usuario === 'administrativo'" class="grid grid-cols-2 gap-4">
        <div>
          <label>Nombre</label>
          <input v-model="form.nombre" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
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
          <input v-model="form.fecha_nacimiento" type="date" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
        </div>
        <div>
          <label>Género</label>
          <input v-model="form.genero" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
        </div>
        <div>
          <label>Tipo</label>
          <select v-model="form.tipo" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
            <option value="">Seleccione...</option>
            <option value="administrador">Administrador</option>
            <option value="auxiliar">Auxiliar</option>
            <option value="coordinador">Coordinador</option>
          </select>
        </div>
      </div>

      <div v-if="form.tipo_usuario === 'terapeuta'" class="grid grid-cols-2 gap-4">
        <div>
          <label>Nombre</label>
          <input v-model="form.nombre" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
        </div>
        <div>
          <label>Especialidad</label>
          <input v-model="form.especialidad" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
        </div>
        <div>
          <label>Número colegiado</label>
          <input v-model="form.numero_colegiado" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
        </div>
        <div>
          <label>Experiencia</label>
          <input v-model="form.experiencia" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
        </div>
        <div>
          <label>Formación</label>
          <input v-model="form.formacion" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
        </div>
        <div>
          <label>Certificaciones</label>
          <input v-model="form.certificaciones" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
        </div>
      </div>

      <div v-if="form.tipo_usuario === 'encargado'" class="grid grid-cols-2 gap-4">
        <div>
          <label>Nombre</label>
          <input v-model="form.nombre" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
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

      <!-- Roles -->
      <div class="mt-6">
        <h3 class="text-sm font-medium text-gray-700 mb-2">Roles</h3>
        <div class="grid grid-cols-2 gap-2">
          <label v-for="role in roles" :key="role.id" class="flex items-center space-x-2">
            <input type="checkbox" :value="role.name" v-model="form.roles" class="rounded border-gray-300" />
            <span>{{ role.name.charAt(0).toUpperCase() + role.name.slice(1) }}</span>
          </label>
        </div>
      </div>

      <!-- Botones -->
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
