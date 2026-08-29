<script setup>
import { Head, usePage, useForm, router } from '@inertiajs/vue3'
import { ref } from 'vue'

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
    form.expediente = paciente.expediente
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
                    <p class="text-sm text-gray-500">Encargado: {{ paciente.encargados?.[0]?.nombre || 'No asignado' }}</p>
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
                        @click="router.visit(route('pacientes.historial', paciente.id))">
                        Historial
                    </button>
                    <button class="bg-caine-rosa text-white py-2 rounded-md text-sm hover:bg-caine-celeste"
                        @click="router.visit(route('pacientes.observaciones', paciente.id))">
                        Observaciones
                    </button>
                    <button class="bg-caine-naranja text-white py-2 rounded-md text-sm hover:bg-caine-verde"
                        @click="router.visit(route('pacientes.seguimiento', paciente.id))">
                        Seguimiento
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal -->
    <div v-if="isOpen" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-30">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
            <h2 class="text-lg font-bold text-caine-azul mb-4">
                {{ selectedPaciente ? 'Editar Paciente' : 'Nuevo Paciente' }}
            </h2>

            <!-- Nombre -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Nombre</label>
                <input v-model="form.nombre" type="text"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
                <div v-if="form.errors.nombre" class="text-red-500 text-sm">{{ form.errors.nombre }}</div>
            </div>
            
            <!-- Género -->
            <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Género</label>
            <select v-model="form.genero" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                <option value="">Seleccione...</option>
                <option value="Masculino">Masculino</option>
                <option value="Femenino">Femenino</option>
                <option value="Otro">Otro</option>
            </select>
            <div v-if="form.errors.genero" class="text-red-500 text-sm">{{ form.errors.genero }}</div>
            </div>

            <!-- Encargado -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Encargado</label>
                <select v-model="form.encargado_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    <option value="">Seleccione...</option>
                    <option v-for="encargado in $page.props.encargados" :key="encargado.id" :value="encargado.id">
                    {{ encargado.nombre }}
                    </option>
                </select>
                <div v-if="form.errors.encargado_id" class="text-red-500 text-sm">{{ form.errors.encargado_id }}
                </div>
            </div>

            <!-- Fecha de nacimiento -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Fecha de nacimiento</label>
                <input v-model="form.fecha_nacimiento" type="date"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
                <div v-if="form.errors.fecha_nacimiento" class="text-red-500 text-sm">{{ form.errors.fecha_nacimiento }}
                </div>
            </div>

            <!-- Teléfono -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Teléfono</label>
                <input v-model="form.telefono" type="text"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
                <div v-if="form.errors.telefono" class="text-red-500 text-sm">{{ form.errors.telefono }}</div>
            </div>

            <!-- Dirección -->
            <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Dirección</label>
            <input v-model="form.direccion" type="text"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
            <div v-if="form.errors.direccion" class="text-red-500 text-sm">{{ form.errors.direccion }}</div>
            </div>

            <!-- Botones -->
            <div class="flex justify-end space-x-3 mt-6">
                <button class="px-4 py-2 bg-gray-200 rounded-md hover:bg-gray-300" @click="closeModal">
                    Cancelar
                </button>
                <button class="px-4 py-2 bg-caine-celeste text-white rounded-md hover:bg-caine-azul"
                    @click="saveChanges">
                    Guardar
                </button>
            </div>
        </div>
    </div>
    <!-- Modal Expediente -->
    <div v-if="showExpediente" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl w-2/3 max-w-3xl h-5/6 overflow-y-auto relative">
            <!-- Header -->
            <div class="flex justify-between items-center border-b p-4">
            <h2 class="text-xl font-bold text-caine-azul">
                Expediente de {{ selectedPaciente?.nombre }}
            </h2>
            <button @click="closeExpediente" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
            </div>

            <!-- Body -->
            <div class="p-6 space-y-4">
            <p><strong>Código:</strong> {{ selectedPaciente?.expediente?.id }}</p>
            <p><strong>Fecha de apertura:</strong> {{ selectedPaciente?.expediente?.fecha_apertura }}</p>
            <p><strong>Estado:</strong> {{ selectedPaciente?.expediente?.estado }}</p>
            <p><strong>Observaciones:</strong> {{ selectedPaciente?.expediente?.observaciones_administrativas || 'Ninguna' }}</p>
            </div>

            <!-- Footer -->
            <div class="flex justify-end border-t p-4">
            </div>
        </div>
    </div>

</template>

<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
export default { layout: AuthenticatedLayout }
</script>
