<script setup>
import { ref, watch } from 'vue'
import { Head } from '@inertiajs/vue3'
import AdministrativosIndex from './Administrativos.vue'
import TerapeutasIndex from './Terapeutas.vue'
import EncargadosIndex from './Encargados.vue'
import PersonaModalNuevo from '@/Components/personas/PersonaModalNuevo.vue'

defineProps({
    administrativos: Array,
    terapeutas: Array,
    encargados: Array,
    cargos: Array,
    especialidades: Array,
    generos: Array,
    estadosCiviles: Array,
    relacionesPaciente: Array,
    usuariosDisponibles: Array,
    roles: Array
})


const tab = ref(localStorage.getItem('personas_tab') || 'administrativos')

watch(tab, (newVal) => {
  localStorage.setItem('personas_tab', newVal)
})


const showNuevoModal = ref(false)

function openNuevoModal() {
    showNuevoModal.value = true
}
function closeNuevoModal() {
    showNuevoModal.value = false
}
</script>

<template>

    <Head title="Gestión de Personas" />
    <div class="bg-white rounded-lg shadow-md p-8 w-full">
        <div class="flex justify-between items-center mb-10">
            <h1 class="text-2xl font-bold text-[#2D2B5B]">Gestión de Personas</h1>
            <button @click="openNuevoModal"
                class="inline-flex items-center px-4 py-2 bg-[#2D2B5B] text-white rounded-md hover:bg-green-700 transition">
                <span class="material-icons mr-1">add_circle</span>
                <span>Nuevo</span>
            </button>
        </div>

        <div class="flex space-x-6 border-b mb-6 text-[#2D2B5B]">
            <button @click="tab = 'administrativos'" class="pb-2 inline-flex items-center transition" :class="tab === 'administrativos'
                ? 'border-b-2 border-[#53C6D3] font-semibold'
                : 'text-gray-500 hover:text-[#2D2B5B]'">
                <span class="material-icons mr-1">groups</span>
                Administrativos
            </button>
            <button @click="tab = 'terapeutas'" class="pb-2 inline-flex items-center transition" :class="tab === 'terapeutas'
                ? 'border-b-2 border-[#53C6D3] font-semibold'
                : 'text-gray-500 hover:text-[#2D2B5B]'">
                <span class="material-icons mr-1">psychology</span>
                Terapeutas
            </button>
            <button @click="tab = 'encargados'" class="pb-2 inline-flex items-center transition" :class="tab === 'encargados'
                ? 'border-b-2 border-[#53C6D3] font-semibold'
                : 'text-gray-500 hover:text-[#2D2B5B]'">
                <span class="material-icons mr-1">supervisor_account</span>
                Encargados
            </button>
        </div>

        <AdministrativosIndex :roles="roles" v-if="tab === 'administrativos'" :administrativos="administrativos" :cargos="cargos"
            :especialidades="especialidades" :generos="generos" :usuariosDisponibles="usuariosDisponibles" />
        <TerapeutasIndex :roles="roles" v-if="tab === 'terapeutas'" :terapeutas="terapeutas" :especialidades="especialidades"
            :generos="generos" :usuariosDisponibles="usuariosDisponibles" />
        <EncargadosIndex :roles="roles" v-if="tab === 'encargados'" :encargados="encargados" :estados-civiles="estadosCiviles"
            :generos="generos" :relaciones-paciente="relacionesPaciente" :usuariosDisponibles="usuariosDisponibles" />
        <PersonaModalNuevo v-if="showNuevoModal" :cargos="cargos" :especialidades="especialidades" :generos="generos"
            :estadosCiviles="estadosCiviles" :relacionesPaciente="relacionesPaciente"
            :usuariosDisponibles="usuariosDisponibles" @close="closeNuevoModal" />
    </div>
</template>

<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
export default { layout: AuthenticatedLayout }
</script>