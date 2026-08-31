<script setup>
import { Head, usePage, useForm, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import PacienteForm from '@/Components/PacienteForm.vue'
import ExpedienteModal from '@/Components/ExpedienteModal.vue'
import HistorialModal from '@/Components/HistorialModal.vue'

const { props } = usePage()
const pacientes = props.pacientes

const isOpen = ref(false)
const selectedPaciente = ref(null)

const form = useForm({
    nombre: '',
    fecha_nacimiento: '',
    telefono: '',
    direccion: '',
    genero: '',
    encargado_id: '',
})

function openModal(paciente) {
    selectedPaciente.value = paciente
    form.nombre = paciente.nombre
    form.fecha_nacimiento = paciente.fecha_nacimiento
    form.telefono = paciente.telefono
    form.direccion = paciente.direccion
    form.genero = paciente.genero
    form.encargado_id = paciente.encargados?.[0]?.id || ''
    isOpen.value = true
}

function closeModal() {
    isOpen.value = false
}

function saveChanges() {
    if (selectedPaciente.value) {
        form.put(route('pacientes.update', selectedPaciente.value.id), {
            onSuccess: () => {
                isOpen.value = false
                router.visit(route('pacientes.index'), { only: ['pacientes'] })
            }
        })
    } else {
        form.post(route('pacientes.store'), {
            onSuccess: () => {
                isOpen.value = false
                router.visit(route('pacientes.index'), { only: ['pacientes'] })
            }
        })
    }
}

function deletePaciente(paciente) {
    if (confirm(`¿Seguro que deseas eliminar a ${paciente.nombre}?`)) {
        form.delete(route('pacientes.destroy', paciente.id), {
            onSuccess: () => {
                router.visit(route('pacientes.index'), { only: ['pacientes'] })
            }
        })
    }
}

function newPaciente() {
    selectedPaciente.value = null
    form.reset()
    isOpen.value = true
}

const showExpediente = ref(false)
function openExpediente(paciente) {
    selectedPaciente.value = paciente
    showExpediente.value = true
}
function closeExpediente() {
    showExpediente.value = false
    selectedPaciente.value = null
}

const showHistorial = ref(false)
function openHistorial(paciente) {
    selectedPaciente.value = paciente
    showHistorial.value = true
}
function closeHistorial() {
    showHistorial.value = false
}
</script>

<template>

    <Head title="Gestión de Pacientes" />
    <div class="p-8 max-w-7xl mx-auto">
        <h2 class="text-2xl font-bold text-caine-azul mb-6">Gestión de Pacientes</h2>

        <!-- Botón para crear nuevo paciente -->
        <div class="mb-6 flex justify-end" v-if="$page.props.auth.user.permissions.includes('gestionar pacientes')">
            <button class="bg-caine-celeste text-white px-6 py-3 rounded-lg font-semibold shadow hover:scale-105"
                @click="newPaciente">
                + Registrar Paciente
            </button>
        </div>

        <!-- Grid estilo Contact Cards -->
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <div v-for="paciente in pacientes" :key="paciente.id"
                class="bg-white shadow rounded-lg overflow-hidden flex flex-col items-center text-center">

                <!-- Avatar -->
                <div class="mt-6">
                    <img :src="`https://ui-avatars.com/api/?name=${paciente.nombre}&background=random&size=128`"
                        alt="Avatar" class="h-20 w-20 rounded-full mx-auto" />
                </div>

                <!-- Nombre y expediente -->
                <div class="mt-4">
                    <h3 class="text-lg font-semibold text-gray-900">{{ paciente.nombre }}</h3>
                    <p class="text-sm text-gray-500">Expediente: {{ paciente.expediente?.id || 'No asignado' }}</p>
                    <p class="text-sm text-gray-500">Teléfono: {{ paciente.telefono }}</p>
                    <p class="text-sm text-gray-500">Dirección: {{ paciente.direccion }}</p>
                    <p class="text-sm text-gray-500">Género: {{ paciente.genero }}</p>
                    <p class="text-sm text-gray-500">Encargado: {{ paciente.encargados?.[0]?.nombre || 'No asignado' }}
                    </p>
                </div>

                <!-- Botones de acciones -->
                <div class="mt-6 grid grid-cols-2 divide-x divide-gray-200 border-t border-gray-200 w-full">
                    <button v-if="$page.props.auth.user.permissions.includes('gestionar pacientes')"
                        class="py-3 text-sm font-medium text-caine-celeste hover:bg-gray-50"
                        @click="openModal(paciente)">
                        Editar
                    </button>
                    <button v-if="$page.props.auth.user.permissions.includes('gestionar pacientes')"
                        class="py-3 text-sm font-medium text-caine-error hover:bg-gray-50"
                        @click="deletePaciente(paciente)">
                        Eliminar
                    </button>
                </div>

                <div class="grid grid-cols-2 gap-2 p-4 w-full border-t"
                    v-if="$page.props.auth.user.permissions.includes('gestionar pacientes')">
                    <button class="bg-caine-azul text-white py-2 rounded-md text-sm hover:bg-caine-morado"
                        @click="openExpediente(paciente)">
                        Expediente
                    </button>
                    <button class="bg-caine-verde text-white py-2 rounded-md text-sm hover:bg-caine-azul"
                        @click="openHistorial(paciente)">
                        Historial
                    </button>
                </div>
            </div>
        </div>

        <!-- Modales -->
        <PacienteForm v-if="isOpen" :paciente="selectedPaciente" :form="form" @close="closeModal" @save="saveChanges" />
        <ExpedienteModal v-if="showExpediente" :expediente="selectedPaciente?.expediente" @close="closeExpediente" />
        <HistorialModal v-if="showHistorial" :paciente="selectedPaciente" @close="closeHistorial" />
    </div>
</template>

<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

export default {
    layout: AuthenticatedLayout
}
</script>
