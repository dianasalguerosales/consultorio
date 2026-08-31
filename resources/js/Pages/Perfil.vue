<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
  user: Object,
  terapeuta: Object,
  encargado: Object,
  administrativo: Object,
});

const roleColors = {
  administrador: 'bg-caine-azul text-white',
  coordinador: 'bg-caine-verde text-white',
  terapeuta: 'bg-caine-morado text-white',
  encargado: 'bg-caine-celeste text-white',
  pruebas: 'bg-caine-rosa text-white',
};

// Función para capitalizar roles
const capitalize = (str) => str.charAt(0).toUpperCase() + str.slice(1);
</script>

<template>
  <Head title="Perfil de usuario" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-[#2D2B5B] leading-tight">
        Perfil de usuario
      </h2>
    </template>

    <div class="py-12 bg-[#F4F6F9]">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-3 gap-6">

        <!-- Tarjeta principal con avatar -->
        <div class="col-span-1 bg-white shadow rounded-lg p-6 text-center">
          <img :src="user.avatar || `https://ui-avatars.com/api/?name=${administrativo?.nombre || terapeuta?.nombre || encargado?.nombre || user.email}&background=2D2B5B&color=fff&size=128`" 
               alt="Avatar" class="mx-auto rounded-full mb-4" />
          <h3 class="text-lg font-bold text-[#2D2B5B]">
            {{ administrativo?.nombre || terapeuta?.nombre || encargado?.nombre || user.email }}
          </h3>
          <p class="text-sm text-gray-500">{{ user.email }}</p>

          <!-- Roles -->
          <div class="mt-4 flex flex-wrap justify-center gap-2 px-4">
            <span v-for="role in user.roles" :key="role"
              :class="['px-3 py-1 rounded-md text-sm font-semibold', roleColors[role] || 'bg-gray-200 text-gray-700']">
              {{ capitalize(role) }}
            </span>
            <span v-if="!user.roles.length"
              class="bg-gray-200 text-gray-700 px-3 py-1 rounded-md text-sm font-semibold">
              Sin rol
            </span>
          </div>
        </div>

        <!-- Información básica -->
        <div class="col-span-2 bg-white shadow rounded-lg p-6 space-y-4">
          <h3 class="text-lg font-bold text-[#2D2B5B] mb-4">Información básica</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <p><strong>Fecha de creación:</strong> {{ user.created_at }}</p>
            <p><strong>Última actualización:</strong> {{ user.updated_at }}</p>
          </div>
        </div>

        <!-- Datos administrativos -->
        <div v-if="administrativo" class="col-span-3 bg-white shadow rounded-lg p-6 space-y-4">
          <h3 class="text-lg font-bold text-[#2D2B5B] mb-4">Datos administrativos</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <p><strong>Nombre:</strong> {{ administrativo.nombre }}</p>
            <p><strong>Teléfono:</strong> {{ administrativo.telefono }}</p>
            <p><strong>Correo:</strong> {{ administrativo.correo }}</p>
            <p><strong>Dirección:</strong> {{ administrativo.direccion }}</p>
            <p><strong>Fecha de nacimiento:</strong> {{ administrativo.fecha_nacimiento }}</p>
            <p><strong>Tipo:</strong> {{ capitalize(administrativo.tipo) }}</p>
          </div>
        </div>

        <!-- Información profesional -->
        <div v-if="terapeuta" class="col-span-3 bg-white shadow rounded-lg p-6 space-y-4">
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
        <div v-if="encargado" class="col-span-3 bg-white shadow rounded-lg p-6 space-y-4">
          <h3 class="text-lg font-bold text-[#2D2B5B] mb-4">Encargado</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <p><strong>Nombre:</strong> {{ encargado.nombre }}</p>
            <p><strong>Teléfono:</strong> {{ encargado.telefono }}</p>
            <p><strong>Correo:</strong> {{ encargado.correo }}</p>
            <p><strong>Relación:</strong> {{ encargado.relacion }}</p>
          </div>
        </div>

      </div>
    </div>
  </AuthenticatedLayout>
</template>