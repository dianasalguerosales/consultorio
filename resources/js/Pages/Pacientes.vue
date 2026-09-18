<script setup>
import { Head, Link, usePage, useForm, router } from "@inertiajs/vue3";
import TarjetaFicha from "@/Components/TarjetaFicha.vue";
import { ref } from "vue";
import PacienteForm from "@/Components/PacienteForm.vue";
import ExpedienteModal from "@/Components/ExpedienteModal.vue";
import HistorialModal from "@/Components/HistorialModal.vue";
import AsignarProgramaModal from "@/Components/AsignarProgramaModal.vue";
import { avatarPaciente } from "@/Utils/avatares";
import { confirmarEliminacion } from '@/Utils/confirmar'

const { props } = usePage();
const pacientes = props.pacientes;
const encargados = props.encargados;
const generos = props.generos;
const escolaridades = props.escolaridades;

const isOpen = ref(false);
const selectedPaciente = ref(null);

// El programa lo asigna coordinación, no cualquiera que gestione pacientes.
const puedeAsignarPrograma = props.auth.user.permissions.includes("gestionar programas");
const pacienteConPrograma = ref(null);

const form = useForm({
    nombres: "",
    apellidos: "",
    escolaridad_id: "",
    genero_id: "",
    encargado_id: "",
});

function openModal(paciente) {
    selectedPaciente.value = paciente;

    form.nombres = paciente.nombres;
    form.apellidos = paciente.apellidos;
    form.genero_id = paciente.genero_id ?? "";
    form.escolaridad_id = paciente.escolaridad_id ?? "";
    form.encargado_id = paciente.encargado_id ?? "";

    isOpen.value = true;
}
function closeModal() {
    isOpen.value = false;
}

function saveChanges() {
    if (selectedPaciente.value) {
        form.put(route("pacientes.update", selectedPaciente.value.id), {
            onSuccess: () => {
                isOpen.value = false;
                router.visit(route("pacientes.index"), { only: ["pacientes"] });
            },
        });
    } else {
        form.post(route("pacientes.store"), {
            onSuccess: () => {
                isOpen.value = false;
                router.visit(route("pacientes.index"), { only: ["pacientes"] });
            },
        });
    }
}

function deletePaciente(paciente) {
    if (confirmarEliminacion(`al paciente ${paciente.nombres} ${paciente.apellidos}`)) {
        form.delete(route("pacientes.destroy", paciente.id), {
            onSuccess: () => {
                router.visit(route("pacientes.index"), { only: ["pacientes"] });
            },
        });
    }
}

function newPaciente() {
    selectedPaciente.value = null;
    form.reset();
    isOpen.value = true;
}

const showExpediente = ref(false);
function openExpediente(paciente) {
    selectedPaciente.value = paciente;
    showExpediente.value = true;
}
function closeExpediente() {
    showExpediente.value = false;
    selectedPaciente.value = null;
}

const showHistorial = ref(false);
function openHistorial(paciente) {
    selectedPaciente.value = paciente;
    showHistorial.value = true;
}
function closeHistorial() {
    showHistorial.value = false;
}
</script>

<template>

    <Head title="Gestión de Pacientes" />
    <div class="p-8 max-w-7xl mx-auto">
        <h2 class="text-2xl font-bold text-caine-azul mb-6">
            Gestión de Pacientes
        </h2>

        <!-- Botón para crear nuevo paciente -->
        <div class="mb-6 flex justify-end" v-if="
            $page.props.auth.user.permissions.includes(
                'gestionar pacientes'
            )
        ">
            <button class="bg-caine-celeste text-white px-6 py-3 rounded-lg font-semibold shadow hover:scale-105"
                @click="newPaciente">
                + Registrar Paciente
            </button>
        </div>

        <!-- Grid estilo Contact Cards -->
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <TarjetaFicha v-for="paciente in pacientes" :key="paciente.id"
                :titulo="`${paciente.nombres} ${paciente.apellidos}`">
                <template #imagen>
                    <img :src="avatarPaciente(paciente.genero)" alt="Avatar"
                        class="h-20 w-20 rounded-full mx-auto" />
                </template>

                <template #datos>
                    <p class="text-sm text-gray-500">
                        Expediente:
                        {{ paciente.expediente?.codigo || "No asignado" }}
                    </p>
                    <p class="text-sm text-gray-500">
                        Género: {{ paciente.genero?.nombre || "No asignado" }}
                    </p>
                    <p class="text-sm text-gray-500">
                        Encargado:
                        {{ paciente.encargado
                            ? `${paciente.encargado.nombres} ${paciente.encargado.apellidos}`
                            : "No asignado"
                        }}
                    </p>
                </template>

                <template #acciones>
                    <button v-if="
                        $page.props.auth.user.permissions.includes(
                            'gestionar pacientes'
                        )
                    " class="py-3 text-sm font-medium text-caine-celeste hover:bg-gray-50"
                        @click="openModal(paciente)">
                        Editar
                    </button>
                    <button v-if="
                        $page.props.auth.user.permissions.includes(
                            'gestionar pacientes'
                        )
                    " class="py-3 text-sm font-medium text-caine-error hover:bg-gray-50"
                        @click="deletePaciente(paciente)">
                        Eliminar
                    </button>
                </template>

                <template #pie>
                <div class="grid grid-cols-2 gap-2 p-4 w-full border-t" v-if="
                    $page.props.auth.user.permissions.includes(
                        'gestionar pacientes'
                    )
                ">
                    <button class="bg-caine-azul text-white py-2 rounded-md text-sm hover:bg-caine-morado"
                        @click="openExpediente(paciente)">
                        Expediente
                    </button>
                    <button class="bg-caine-verde text-white py-2 rounded-md text-sm hover:bg-caine-azul"
                        @click="openHistorial(paciente)">
                        Historial
                    </button>
                    <!-- Página aparte y no un modal: lo que se escribe acá son
                         párrafos largos que en una celda no se pueden leer. -->
                    <Link :href="`/pacientes/${paciente.id}/observaciones`"
                        class="col-span-2 bg-caine-celeste text-white py-2 rounded-md text-sm text-center
                               hover:bg-caine-azul">
                        Observaciones
                    </Link>
                    <button v-if="puedeAsignarPrograma"
                        class="col-span-2 bg-caine-morado text-white py-2 rounded-md text-sm hover:bg-caine-azul"
                        @click="pacienteConPrograma = paciente">
                        Programa
                    </button>
                </div>
                </template>
            </TarjetaFicha>
        </div>

        <!-- Modales -->
        <PacienteForm v-if="isOpen" :paciente="selectedPaciente" :form="form" :generos="generos"
            :escolaridades="escolaridades" :encargados="encargados" @close="closeModal" @save="saveChanges" />
        <ExpedienteModal v-if="showExpediente" :expediente="selectedPaciente?.expediente" @close="closeExpediente" />
        <HistorialModal v-if="showHistorial" :paciente="selectedPaciente" @close="closeHistorial" />
        <AsignarProgramaModal v-if="pacienteConPrograma" :paciente="pacienteConPrograma"
            :catalogos="props.catalogosPrograma" @close="pacienteConPrograma = null" />
    </div>
</template>

<script>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

export default {
    layout: AuthenticatedLayout,
};
</script>
