<script setup>
import { useForm } from '@inertiajs/vue3'

const form = useForm({
  current_password: '',
  new_password: '',
  new_password_confirmation: '',
})

function submit() {
  form.put(route('configuracion.password.update'))
}
</script>

<template>
  <div class="p-8 max-w-7xl mx-auto">
    <h2 class="text-2xl font-bold text-caine-azul mb-6">Configuración</h2>

    <!-- Card principal -->
    <div class="bg-white shadow rounded-lg p-6">
      <h3 class="text-lg font-bold text-[#2D2B5B] mb-4">Cambio de contraseña</h3>

      <form @submit.prevent="submit" class="space-y-4">
        <!-- Contraseña actual -->
        <div>
          <label class="block text-sm font-medium">Contraseña actual</label>
          <input v-model="form.current_password" type="password"
                 class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
          <span v-if="form.errors.current_password" class="text-red-600 text-sm">
            {{ form.errors.current_password }}
          </span>
        </div>

        <!-- Nueva contraseña -->
        <div>
          <label class="block text-sm font-medium">Nueva contraseña</label>
          <input v-model="form.new_password" type="password"
                 class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
          <span v-if="form.errors.new_password" class="text-red-600 text-sm">
            {{ form.errors.new_password }}
          </span>
        </div>

        <!-- Confirmar nueva contraseña -->
        <div>
          <label class="block text-sm font-medium">Confirmar nueva contraseña</label>
          <input v-model="form.new_password_confirmation" type="password"
                 class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
        </div>

        <!-- Botón -->
        <button type="submit"
                class="bg-caine-azul text-white px-4 py-2 rounded-md hover:bg-caine-morado">
          Guardar cambios
        </button>
      </form>

      <!-- Mensaje de éxito -->
      <div v-if="$page.props.flash?.success" class="text-green-600 text-sm mt-4">
        {{ $page.props.flash.success }}
      </div>
    </div>
  </div>
</template>

<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
export default { layout: AuthenticatedLayout }
</script>
