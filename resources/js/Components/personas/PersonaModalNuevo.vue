<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import ModalBaseEditar from '../ModalBaseEditar.vue'
import PersonaFormShared from './PersonaFormShared.vue'
import PersonaFormAdministrativo from './PersonaFormAdministrativo.vue'
import PersonaFormTerapeuta from './PersonaFormTerapeuta.vue'
import PersonaFormEncargado from './PersonaFormEncargado.vue'

const emit = defineEmits(['close'])

const props = defineProps({
  cargos: Array,
  especialidades: Array,
  generos: Array,
  estadosCiviles: Array,
  relacionesPaciente: Array
})

const tipo = ref('administrativo')

const form = useForm({
  nombres: '', apellidos: '', fecha_nacimiento: '', dpi: '',
  telefono: '', correo: '', direccion: '', ocupacion: '',
  genero_id: '', cargo_id: '', especialidad_id: '',
  estado_civil_id: '', relacion_paciente_id: '',
  experiencia: '', certificaciones: '', cursos: ''
})

function submit() {
  let url = ''
  if (tipo.value === 'administrativo') url = '/personas/administrativos'
  if (tipo.value === 'terapeuta') url = '/personas/terapeutas'
  if (tipo.value === 'encargado') url = '/personas/encargados'

  form.post(url, { onSuccess: () => emit('close') })
}
</script>

<template>
  <ModalBaseEditar @close="$emit('close')">
    <template #header>
      <h2 class="text-2xl font-bold text-[#2D2B5B]">Nueva Persona</h2>
    </template>

    <!-- Selector de tipo -->
    <div class="mb-6">
      <label class="block text-sm font-medium">Tipo de persona</label>
      <select v-model="tipo"
        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-[#53C6D3] focus:border-[#53C6D3]">
        <option value="administrativo">Administrativo</option>
        <option value="terapeuta">Terapeuta</option>
        <option value="encargado">Encargado</option>
      </select>
    </div>

    <!-- Formulario compartido -->
    <PersonaFormShared :form="form" :generos="props.generos" />

    <!-- Formulario variable -->
    <PersonaFormAdministrativo v-if="tipo==='administrativo'" :form="form" :cargos="props.cargos" :especialidades="props.especialidades" />
    <PersonaFormTerapeuta v-if="tipo==='terapeuta'" :form="form" :especialidades="props.especialidades" />
    <PersonaFormEncargado v-if="tipo==='encargado'" :form="form" :estadosCiviles="props.estadosCiviles" :relacionesPaciente="props.relacionesPaciente" />

    <!-- Footer -->
    <template #footer>
      <button type="submit"
        class="px-6 py-2 bg-[#53C6D3] text-white rounded-md hover:bg-[#2D2B5B] transition"
        @click="submit">
        Crear
      </button>
    </template>
  </ModalBaseEditar>
</template>