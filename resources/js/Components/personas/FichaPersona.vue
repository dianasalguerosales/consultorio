<script setup>
import { computed } from 'vue'
import { avatarUsuario, ETIQUETAS_ROL } from '@/Utils/avatares'
import { fecha } from '@/Utils/fechas'

const props = defineProps({
  persona: { type: Object, required: true },
  // 'administrativo', 'terapeuta' o 'encargado': define el avatar y el bloque
  // de campos propios.
  tipo: { type: String, required: true },
  // El perfil lo quiere más grande que la ficha de los modales.
  avatarClase: { type: String, default: 'w-20 h-20' },
  // El perfil lo quiere más grande que la ficha de los modales.
  avatarClase: { type: String, default: 'w-20 h-20' }
})

/* ---------- Avatar ---------- */

// El administrativo se distingue por su cargo (Administrador, Coordinador,
// Auxiliar); el encargado por su género (Madre/Padre). Es la regla de
// @/Utils/avatares, no una copia.
const rolAvatar = computed(() =>
  props.tipo === 'administrativo'
    ? props.persona.cargo?.nombre?.toLowerCase()
    : props.tipo
)

const avatar = computed(() => avatarUsuario([rolAvatar.value], props.persona.genero))

const etiqueta = computed(() =>
  props.tipo === 'administrativo'
    ? props.persona.cargo?.nombre ?? 'Administrativo'
    : ETIQUETAS_ROL[props.tipo] ?? props.tipo
)

const nombre = computed(() =>
  props.persona.nombre_completo
  ?? [props.persona.nombres, props.persona.apellidos].filter(Boolean).join(' ')
)

/* ---------- Campos ---------- */

const vacio = (v) => (v === null || v === undefined || v === '' ? '—' : v)

// Los que tienen las tres tablas.
const comunes = computed(() => [
  ['Fecha de nacimiento', fecha(props.persona.fecha_nacimiento, '—')],
  ['DPI', vacio(props.persona.dpi)],
  ['Género', vacio(props.persona.genero?.nombre)],
  ['Usuario del sistema', vacio(props.persona.user?.email)],
])

// Los propios de cada tipo. Solo columnas que existen en su tabla.
const propios = computed(() => {
  if (props.tipo === 'administrativo') {
    return [
      ['Cargo', vacio(props.persona.cargo?.nombre)],
      ['Especialidad', vacio(props.persona.especialidad?.nombre)],
      ['Experiencia', vacio(props.persona.experiencia)],
      ['Certificaciones', vacio(props.persona.certificaciones)],
      ['Cursos', vacio(props.persona.cursos)],
    ]
  }

  if (props.tipo === 'terapeuta') {
    return [
      ['Especialidad', vacio(props.persona.especialidad?.nombre)],
      ['Experiencia', vacio(props.persona.experiencia)],
      ['Certificaciones', vacio(props.persona.certificaciones)],
      ['Cursos', vacio(props.persona.cursos)],
    ]
  }

  return [
    ['Parentesco', vacio(props.persona.relacion_paciente?.nombre)],
    ['Dirección', vacio(props.persona.direccion)],
    ['Ocupación', vacio(props.persona.ocupacion)],
    ['Estado civil', vacio(props.persona.estado_civil?.nombre)],
  ]
})

const tituloPropios = {
  administrativo: 'Datos administrativos',
  terapeuta: 'Información profesional',
  encargado: 'Datos del encargado',
}
</script>

<template>
  <div class="space-y-6">
    <!-- Cabecera -->
    <div class="flex items-center gap-4">
      <img :src="avatar" :alt="etiqueta" class="rounded-full border object-cover bg-white shrink-0"
        :class="avatarClase" />

      <div class="min-w-0">
        <p class="text-xl font-semibold text-caine-azul truncate">{{ nombre }}</p>
        <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ etiqueta }}</p>
        <p class="text-gray-600 truncate">{{ persona.correo || '—' }}</p>
        <p class="text-gray-600">{{ persona.telefono || '—' }}</p>
      </div>
    </div>

    <!-- Comunes a los tres tipos -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-2 text-gray-700 pt-4 border-t border-gray-100">
      <p v-for="[etiq, valor] in comunes" :key="etiq">
        <strong>{{ etiq }}:</strong> {{ valor }}
      </p>
    </div>

    <!-- Propios del tipo -->
    <div class="pt-4 border-t border-gray-100">
      <h3 class="text-sm font-bold text-caine-azul mb-3">{{ tituloPropios[tipo] }}</h3>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-2 text-gray-700">
        <p v-for="[etiq, valor] in propios" :key="etiq">
          <strong>{{ etiq }}:</strong> {{ valor }}
        </p>
      </div>
    </div>
  </div>
</template>
