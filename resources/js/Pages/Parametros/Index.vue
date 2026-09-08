<script setup>
import { ref } from 'vue'
import { Head } from '@inertiajs/vue3'
import ServiciosIndex from './Servicios.vue'
import EspecialidadesIndex from './Especialidades.vue'
import EscolaridadesIndex from './Escolaridades.vue'
import EstadoExpedientesIndex from './EstadoExpedientes.vue'
import EstadoSesionesIndex from './EstadoSesiones.vue'
import ModalidadesIndex from './Modalidades.vue'

defineProps({
    servicios: Array,
    especialidades: Array,
    escolaridades: Array,
    estadoExpedientes: Array,
    estadoSesiones: Array,
    modalidades: Array
})

const tab = ref('servicios')
</script>


<template>

    <Head title="Gestión de Parámetros" />
    <div class="bg-white rounded-lg shadow-md p-8 w-full">
        <h1 class="text-2xl font-bold text-[#2D2B5B] mb-6">Gestión de Parámetros</h1>

        <div class="flex space-x-6 border-b mb-6 text-[#2D2B5B]">
            <button @click="tab = 'servicios'"
                :class="tab === 'servicios' ? 'border-b-2 border-[#53C6D3] font-semibold' : 'text-gray-500 hover:text-[#2D2B5B]'">Servicios</button>
            <button @click="tab = 'especialidades'"
                :class="tab === 'especialidades' ? 'border-b-2 border-[#53C6D3] font-semibold' : 'text-gray-500 hover:text-[#2D2B5B]'">Especialidades</button>
            <button @click="tab = 'escolaridades'"
                :class="tab === 'escolaridades' ? 'border-b-2 border-[#53C6D3] font-semibold' : 'text-gray-500 hover:text-[#2D2B5B]'">Escolaridades</button>
            <button @click="tab = 'estadoExpedientes'"
                :class="tab === 'estadoExpedientes' ? 'border-b-2 border-[#53C6D3] font-semibold' : 'text-gray-500 hover:text-[#2D2B5B]'">Estado
                Expedientes</button>
            <button @click="tab = 'estadoSesiones'"
                :class="tab === 'estadoSesiones' ? 'border-b-2 border-[#53C6D3] font-semibold' : 'text-gray-500 hover:text-[#2D2B5B]'">Estado
                Sesiones</button>
            <button @click="tab = 'modalidades'"
                :class="tab === 'modalidades' ? 'border-b-2 border-[#53C6D3] font-semibold' : 'text-gray-500 hover:text-[#2D2B5B]'">Modalidades</button>
        </div>

        <ServiciosIndex v-if="tab === 'servicios'" :servicios="servicios" />
        <EspecialidadesIndex v-if="tab === 'especialidades'" :especialidades="especialidades" />
        <EscolaridadesIndex v-if="tab === 'escolaridades'" :escolaridades="escolaridades" />
        <EstadoExpedientesIndex v-if="tab === 'estadoExpedientes'" :estadoExpedientes="estadoExpedientes" />
        <EstadoSesionesIndex v-if="tab === 'estadoSesiones'" :estadoSesiones="estadoSesiones" />
        <ModalidadesIndex v-if="tab === 'modalidades'" :modalidades="modalidades" />
    </div>
</template>

<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

export default {
  layout: AuthenticatedLayout
}
</script>