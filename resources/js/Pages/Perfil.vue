<script setup>
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import FichaPersona from '@/Components/personas/FichaPersona.vue';
import { avatarUsuario, rolPrincipal, ETIQUETAS_ROL } from '@/Utils/avatares';
import { fechaHora } from '@/Utils/fechas';

const props = defineProps({
  user: Object,
  // 'administrativo', 'terapeuta' o 'encargado'. null si el usuario no tiene
  // ficha de persona, que es el caso del rol pruebas.
  tipo: { type: String, default: null },
  persona: { type: Object, default: null },
  pacientes: { type: Number, default: null },
});

const roles = computed(() => props.user.roles ?? []);

const nombreMostrado = computed(() =>
  props.persona?.nombre_completo || props.user.email
);

// Sin ficha de persona el avatar sale del rol del usuario.
const avatarSuelto = computed(() => avatarUsuario(roles.value));
const etiquetaRol = computed(() => ETIQUETAS_ROL[rolPrincipal(roles.value)] ?? 'Sin rol');

const roleColors = {
  administrador: 'bg-caine-azul text-white',
  coordinador: 'bg-caine-verde text-white',
  terapeuta: 'bg-caine-morado text-white',
  auxiliar: 'bg-caine-naranja text-white',
  encargado: 'bg-caine-celeste text-white',
  pruebas: 'bg-caine-rosa text-white',
};

// Tolera null: los campos opcionales del perfil pueden venir vacíos y antes
// eso rompía el render de toda la página.
const capitalize = (str) => (str ? str.charAt(0).toUpperCase() + str.slice(1) : '');
</script>

<template>
  <Head title="Perfil de usuario" />

  <div class="p-4 sm:p-8 max-w-5xl mx-auto">
    <h2 class="text-2xl font-bold text-caine-azul mb-6">Perfil de usuario</h2>

    <!-- La ficha es la misma que ve el coordinador en Personas. -->
    <div class="bg-white shadow rounded-lg p-4 sm:p-6">
      <FichaPersona v-if="persona" :persona="persona" :tipo="tipo" avatarClase="w-28 h-28 sm:w-40 sm:h-40 md:w-48 md:h-48" />

      <!-- Sin ficha de persona: solo el usuario. -->
      <div v-else class="flex flex-col sm:flex-row items-center gap-4 text-center sm:text-left">
        <img :src="avatarSuelto" :alt="etiquetaRol"
          class="w-24 h-24 sm:w-32 sm:h-32 rounded-full border object-cover bg-white shrink-0" />
        <div class="min-w-0">
          <p class="text-xl font-semibold text-caine-azul">{{ nombreMostrado }}</p>
          <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ etiquetaRol }}</p>
          <p class="text-sm text-gray-500">Este usuario no tiene ficha de persona asociada.</p>
        </div>
      </div>
    </div>

    <!-- Datos de la cuenta, que son del usuario y no de la persona -->
    <div class="bg-white shadow rounded-lg p-4 sm:p-6 mt-6">
      <h3 class="text-lg font-bold text-[#2D2B5B] mb-4">Cuenta</h3>

      <p class="text-sm text-gray-500 break-all">{{ user.email }}</p>

      <div class="mt-3 flex flex-wrap gap-2">
        <span v-for="role in roles" :key="role"
          :class="['px-3 py-1 rounded-md text-sm font-semibold', roleColors[role] || 'bg-gray-200 text-gray-700']">
          {{ capitalize(role) }}
        </span>
        <span v-if="!roles.length"
          class="bg-gray-200 text-gray-700 px-3 py-1 rounded-md text-sm font-semibold">
          Sin rol
        </span>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6 pt-6 border-t border-gray-100 text-gray-700">
        <p v-if="pacientes !== null">
          <strong>{{ tipo === 'encargado' ? 'Pacientes a cargo' : 'Pacientes asignados' }}:</strong>
          {{ pacientes }}
        </p>
        <p><strong>Fecha de creación:</strong> {{ fechaHora(user.created_at) }}</p>
        <p><strong>Última actualización:</strong> {{ fechaHora(user.updated_at) }}</p>
      </div>
    </div>
  </div>
</template>

<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

export default {
  layout: AuthenticatedLayout,
}
</script>
