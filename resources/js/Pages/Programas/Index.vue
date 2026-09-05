<script setup>
import { Head } from '@inertiajs/vue3'
import Servicios from './Servicios.vue'
import Especialidades from './Especialidades.vue'
</script>

<template>
    <Head title="Gestión de Programas" />
    <div class="bg-white rounded-lg shadow-md p-8 w-full">
        <h2 class="text-2xl font-bold text-[#2D2B5B] mb-6">Programas</h2>

        <!-- División de Servicios -->
        <section class="mb-12">
            <h3 class="text-xl font-semibold text-[#2D2B5B] mb-4">Servicios</h3>
            <Servicios :servicios="$page.props.servicios" />
        </section>

        <!-- División de Especialidades -->
        <section class="mb-12">
            <h3 class="text-xl font-semibold text-[#2D2B5B] mb-4">Especialidades</h3>
            <Especialidades :especialidades="$page.props.especialidades" />
        </section>
    </div>
</template>

<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
export default { layout: AuthenticatedLayout }
</script>
