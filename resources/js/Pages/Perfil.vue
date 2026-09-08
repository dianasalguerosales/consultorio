<script setup>
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import { avatarUsuario, rolPrincipal, ETIQUETAS_ROL } from '@/Utils/avatares';

const props = defineProps({
  user: Object,
  terapeuta: Object,
  encargado: Object,
  administrativo: Object,
});

// El controlador expone el accessor `nombre_completo` de cada modelo.
const nombreMostrado = computed(() =>
  props.administrativo?.nombre_completo
  || props.terapeuta?.nombre_completo
  || props.encargado?.nombre_completo
  || props.user.email
);

const roles = computed(() => props.user.roles ?? []);

// El encargado se distingue por género, así que el helper necesita ese dato.
const avatarUrl = computed(() => avatarUsuario(roles.value, props.encargado?.genero));
const etiquetaRol = computed(() => ETIQUETAS_ROL[rolPrincipal(roles.value)] ?? 'Sin rol');

const roleColors = {
  administrador: 'bg-caine-azul text-white',
  coordinador: 'bg-caine-verde text-white',
  terapeuta: 'bg-caine-morado text-white',
  auxiliar: 'bg-caine-naranja text-white',
  encargado: 'bg-caine-celeste text-white',
  pruebas: 'bg-caine-rosa text-white',
};

// Función para capitalizar roles. Tolera null/undefined: los campos opcionales
// del perfil pueden venir vacíos y antes eso rompía el render de toda la página.
const capitalize = (str) => (str ? str.charAt(0).toUpperCase() + str.slice(1) : '');
</script>

<template>
  <Head title="Perfil de usuario" />

  <div class="p-8 max-w-7xl mx-auto">
    <h2 class="text-2xl font-bold text-caine-azul mb-6">
      Perfil de usuario
    </h2>

    <div>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <!-- Avatar según el rol -->
        <div class="col-span-1 bg-white shadow rounded-lg p-6 flex flex-col items-center justify-center">
          <img :src="user.avatar || avatarUrl"
               :alt="`Avatar de ${nombreMostrado}`"
               class="w-34 h-34 rounded-full object-cover" />
          <span class="mt-4 text-xs font-semibold uppercase tracking-wide text-gray-400">
            {{ etiquetaRol }}
          </span>
        </div>

        <!-- Información básica -->
        <div class="md:col-span-2 bg-white shadow rounded-lg p-6">
          <h3 class="text-lg font-bold text-[#2D2B5B] mb-4">Información básica</h3>

          <p class="text-xl font-bold text-[#2D2B5B]">{{ nombreMostrado }}</p>
          <p class="text-sm text-gray-500">{{ user.email }}</p>

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

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6 pt-6 border-t border-gray-100">
            <p><strong>Fecha de creación:</strong> {{ user.created_at }}</p>
            <p><strong>Última actualización:</strong> {{ user.updated_at }}</p>
          </div>
        </div>

        <!-- Datos administrativos -->
        <div v-if="administrativo" class="md:col-span-3 bg-white shadow rounded-lg p-6 space-y-4">
          <h3 class="text-lg font-bold text-[#2D2B5B] mb-4">Datos administrativos</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <p><strong>Nombre:</strong> {{ administrativo.nombre_completo }}</p>
            <p><strong>Teléfono:</strong> {{ administrativo.telefono }}</p>
            <p><strong>Correo:</strong> {{ administrativo.correo }}</p>
            <p><strong>Fecha de nacimiento:</strong> {{ administrativo.fecha_nacimiento }}</p>
          </div>
        </div>

        <!-- Información profesional -->
        <div v-if="terapeuta" class="md:col-span-3 bg-white shadow rounded-lg p-6 space-y-4">
          <h3 class="text-lg font-bold text-[#2D2B5B] mb-4">Información profesional</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <p><strong>Especialidad:</strong> {{ terapeuta.especialidad }}</p>
            <p><strong>Número de colegiado:</strong> {{ terapeuta.numero_colegiado }}</p>
            <p><strong>Pacientes asignados:</strong> {{ terapeuta.pacientes_count }}</p>
            <p><strong>Experiencia:</strong> {{ terapeuta.experiencia }}</p>
            <p><strong>Formación:</strong> {{ terapeuta.formacion }}</p>
            <p><strong>Certificaciones:</strong> {{ terapeuta.certificaciones }}</p>
          </div>
        </div>

        <!-- Información del encargado -->
        <div v-if="encargado" class="md:col-span-3 bg-white shadow rounded-lg p-6 space-y-4">
          <h3 class="text-lg font-bold text-[#2D2B5B] mb-4">Encargado</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <p><strong>Nombre:</strong> {{ encargado.nombre_completo }}</p>
            <p><strong>Teléfono:</strong> {{ encargado.telefono }}</p>
            <p><strong>Correo:</strong> {{ encargado.correo }}</p>
            <p><strong>Relación:</strong> {{ encargado.relacion }}</p>
          </div>
        </div>

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