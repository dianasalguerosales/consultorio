<script setup>
import { computed } from 'vue'
import { useForm, usePage } from '@inertiajs/vue3'
import ModalBaseVer from '../ModalBaseVer.vue'

const props = defineProps({
  persona: { type: Object, required: true },
  // 'administrativo', 'terapeuta' o 'encargado'.
  tipo: { type: String, required: true },
  // Los seis roles del sistema.
  roles: { type: Array, default: () => [] },
})

const emit = defineEmits(['close'])

const usuario = computed(() => props.persona.user ?? null)

// El rol de administrador solo lo otorga un administrador: al coordinador se
// le deshabilita la casilla en vez de dejarlo intentar y recibir el error.
const paginaRoles = computed(() => usePage().props.auth?.user?.roles ?? [])
const soyAdministrador = computed(() => paginaRoles.value.includes('administrador'))

const bloqueado = (rol) => rol === 'administrador' && !soyAdministrador.value

const nombre = computed(() =>
  props.persona.nombre_completo
  ?? [props.persona.nombres, props.persona.apellidos].filter(Boolean).join(' ')
)

// Los roles llegan como objetos de Spatie; el formulario trabaja con nombres.
const form = useForm({
  roles: (usuario.value?.roles ?? []).map((r) => r.name),
})

const descripciones = {
  administrador: 'Acceso completo al sistema.',
  coordinador: 'Gestiona pacientes, agenda, personas e informes.',
  terapeuta: 'Atiende sus citas y consulta a sus pacientes.',
  auxiliar: 'Agenda y atiende sus propias citas en sucursal.',
  encargado: 'Portal de padres: sus hijos y su agenda.',
  pruebas: 'Solo consulta, sin poder crear ni modificar.',
}

function guardar() {
  form.put(`/personas/${props.tipo}/${props.persona.id}/roles`, {
    preserveScroll: true,
    onSuccess: () => emit('close'),
  })
}
</script>

<template>
  <ModalBaseVer @close="emit('close')">
    <template #header>
      <div>
        <h2 class="text-2xl font-bold text-[#2D2B5B]">Roles</h2>
        <p class="text-sm text-gray-500">{{ nombre }}</p>
      </div>
    </template>

    <!-- Sin usuario ligado no hay a quién asignarle roles. -->
    <div v-if="!usuario" class="flex items-start gap-3 rounded-md bg-caine-naranja/10 p-4">
      <span class="material-icons text-[#7a4e15]">info</span>
      <div>
        <p class="font-medium text-[#7a4e15]">
          Debe asignar un usuario para tener acceso a asignación de roles.
        </p>
        <p class="text-sm text-gray-600 mt-1">
          Los roles se le dan al usuario del sistema, no a la persona. Editá
          {{ nombre }} y elegile un correo en «Usuario del sistema».
        </p>
      </div>
    </div>

    <template v-else>
      <p class="text-sm text-gray-500 mb-4">
        Asignados a <strong>{{ usuario.email }}</strong>. Puede tener más de uno.
      </p>

      <div class="space-y-2">
        <label v-for="rol in roles" :key="rol"
          class="flex items-start gap-3 rounded-md border p-3"
          :class="bloqueado(rol) ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer hover:bg-[#FAF9F7]'">
          <input type="checkbox" :value="rol" v-model="form.roles" :disabled="bloqueado(rol)"
            class="mt-1 rounded border-gray-300 text-caine-azul focus:ring-caine-azul" />
          <span>
            <span class="font-medium text-caine-azul capitalize">{{ rol }}</span>
            <span class="block text-sm text-gray-500">{{ descripciones[rol] }}</span>
            <span v-if="bloqueado(rol)" class="block text-xs text-[#7a4e15] mt-1">
              Solo un administrador puede otorgar este rol.
            </span>
          </span>
        </label>
      </div>

      <p v-if="form.errors.roles" class="mt-3 text-sm text-caine-error">{{ form.errors.roles }}</p>

      <p v-if="!form.roles.length" class="mt-3 text-sm text-[#7a4e15]">
        Sin ningún rol, este usuario puede entrar pero no verá ningún módulo.
      </p>

      <div class="mt-6 flex justify-end">
        <button type="button" @click="guardar" :disabled="form.processing"
          class="px-6 py-2 bg-caine-azul text-white rounded-md hover:opacity-90 disabled:opacity-50">
          {{ form.processing ? 'Guardando...' : 'Guardar roles' }}
        </button>
      </div>
    </template>
  </ModalBaseVer>
</template>
