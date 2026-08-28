<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
  user: Object,
  terapeuta: Object,
  encargado: Object,
});

const roleColors = {
  administrador: 'bg-caine-azul text-white',
  coordinador: 'bg-caine-verde text-white',
  terapeuta: 'bg-caine-morado text-white',
  encargado: 'bg-caine-celeste text-white',
  pruebas: 'bg-caine-rosa text-white',
};
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
          <img :src="user.avatar || `https://ui-avatars.com/api/?name=${user.name}&background=2D2B5B&color=fff&size=128`" 
               alt="Avatar" class="mx-auto rounded-full mb-4" />
          <h3 class="text-lg font-bold text-[#2D2B5B]">{{ user.name }}</h3>
          <p class="text-sm text-gray-500">{{ user.email }}</p>

          <!-- Roles -->
          <div class="mt-4 flex flex-wrap justify-center gap-2 px-4">
            <span v-for="role in user.roles" :key="role"
              :class="['px-3 py-1 rounded-md text-sm font-semibold', roleColors[role] || 'bg-gray-200 text-gray-700']">
              {{ role }}
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
            <p><strong>Estado:</strong> {{ user.estado }}</p>
          </div>
        </div>

        <!-- Datos personales -->
        <div class="col-span-3 bg-white shadow rounded-lg p-6 space-y-4">
          <h3 class="text-lg font-bold text-[#2D2B5B] mb-4">Datos personales</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <p><strong>Teléfono:</strong> {{ user.telefono }}</p>
            <p><strong>Dirección:</strong> {{ user.direccion }}</p>
            <p><strong>Fecha de nacimiento:</strong> {{ user.fecha_nacimiento }}</p>
            <p><strong>Género:</strong> {{ user.genero }}</p>
            <p><strong>Nacionalidad:</strong> {{ user.nacionalidad }}</p>
            <p><strong>Estado civil:</strong> {{ user.estado_civil }}</p>
          </div>
        </div>

        <!-- Información profesional -->
        <div class="col-span-3 bg-white shadow rounded-lg p-6 space-y-4" v-if="terapeuta">
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
        <div class="col-span-3 bg-white shadow rounded-lg p-6 space-y-4" v-if="encargado">
          <h3 class="text-lg font-bold text-[#2D2B5B] mb-4">Encargado</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <p><strong>Nombre:</strong> {{ encargado.nombre }}</p>
            <p><strong>Teléfono:</strong> {{ encargado.telefono }}</p>
            <p><strong>Correo:</strong> {{ encargado.correo }}</p>
            <p><strong>Relación:</strong> {{ encargado.relacion }}</p>
          </div>
        </div>

        <!-- Información adicional -->
        <div class="col-span-3 bg-white shadow rounded-lg p-6 space-y-4">
          <h3 class="text-lg font-bold text-[#2D2B5B] mb-4">Información adicional</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <p><strong>Idioma preferido:</strong> {{ user.idioma }}</p>
            <p><strong>Notificaciones:</strong> {{ user.notificaciones }}</p>
            <p><strong>Historial de accesos:</strong> {{ user.historial_accesos }}</p>
            <p><strong>Última sesión:</strong> {{ user.ultima_sesion }}</p>
          </div>
        </div>

      </div>
    </div>
  </AuthenticatedLayout>
</template>
