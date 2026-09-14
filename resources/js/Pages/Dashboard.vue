<script setup>
import { computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import { fechaCorta } from '@/Utils/fechas'
import { avatarPaciente } from '@/Utils/avatares'

const props = defineProps({
  cumpleanos: { type: Object, default: () => ({ ayer: [], hoy: [], manana: [] }) },
  tareasDelDia: { type: Array, default: () => [] },
  // Cómo atiende el usuario logueado, o null si no atiende citas.
  yoAtiendo: { type: Object, default: null },
})

/* ---------- Cumpleaños ---------- */

// Los tres días se muestran siempre, incluso vacíos: la tarjeta que desaparece
// no se puede revisar, y el punto es justamente acordarse de mirar.
const DIAS = [
  { clave: 'ayer', titulo: 'Cumplieron ayer', icono: 'history', acento: 'text-gray-400' },
  { clave: 'hoy', titulo: 'Cumplen hoy', icono: 'cake', acento: 'text-caine-naranja' },
  { clave: 'manana', titulo: 'Cumplen mañana', icono: 'event', acento: 'text-caine-celeste' },
]

const COLOR_TIPO = {
  Paciente: 'bg-caine-celeste text-white',
  Terapeuta: 'bg-caine-morado text-white',
  Personal: 'bg-caine-azul text-white',
  Encargado: 'bg-caine-verde text-white',
}

/* ---------- Tareas del día ---------- */

const pendientes = computed(() => props.tareasDelDia.filter((t) => !t.atendida).length)

const ESTADO_CITA = {
  Confirmada: 'text-caine-verde',
  Atendida: 'text-caine-verde',
  Programada: 'text-caine-celeste',
  Pendiente: 'text-caine-naranja',
  Reprogramada: 'text-caine-morado',
  Cancelada: 'text-caine-error',
  Vencida: 'text-gray-400',
}

/* ---------- Flujos, solo el diseño ---------- */

// Pantallas todavía sin construir. Se dejan dibujadas para no perder de vista
// el orden de los pasos; cada paso nombra el módulo donde se hace hoy a mano.
const FLUJOS = [
  {
    clave: 'colaborador',
    titulo: 'Nuevo colaborador',
    resumen: 'Dar de alta a quien entra a trabajar, de la ficha al primer turno.',
    icono: 'badge',
    color: 'bg-caine-morado',
    pasos: [
      'Crear el usuario y su contraseña',
      'Asignarle el rol que le toca',
      'Llenar su ficha de persona',
      'Elegir especialidad y cargo',
      'Dejarlo listo para que se le agende',
    ],
  },
  {
    clave: 'cliente',
    titulo: 'Nuevo cliente',
    resumen: 'Del primer contacto al niño con expediente, programa y citas.',
    icono: 'family_restroom',
    color: 'bg-caine-celeste',
    pasos: [
      'Registrar al encargado',
      'Registrar al niño y ligarlo al encargado',
      'Abrir el expediente y la anamnesis',
      'Asignar terapeuta y terapias',
      'Asignar el paquete y generar sus citas',
    ],
  },
]
</script>

<template>

  <Head title="Panel administrador" />

  <div class="p-4 sm:p-6 space-y-6">
    <div>
      <h2 class="text-2xl font-bold text-[#2D2B5B]">Bienvenido al Panel</h2>
      <p class="text-[#2B2B41]">Selecciona una opción del menú lateral para comenzar.</p>
    </div>

    <!-- Tareas del día: lo primero, porque es lo que hay que hacer hoy -->
    <section v-if="yoAtiendo" class="bg-white rounded-lg shadow-md p-5">
      <div class="flex flex-wrap items-baseline justify-between gap-2 mb-4">
        <h3 class="flex items-center gap-2 text-lg font-bold text-[#2D2B5B]">
          <span class="material-icons text-caine-azul">task_alt</span>
          Tareas de hoy
        </h3>

        <p class="text-sm text-gray-500">
          <template v-if="tareasDelDia.length">
            {{ pendientes }} de {{ tareasDelDia.length }}
            {{ tareasDelDia.length === 1 ? 'cita' : 'citas' }} por atender
          </template>
        </p>
      </div>

      <ul v-if="tareasDelDia.length" class="divide-y divide-gray-100">
        <li v-for="t in tareasDelDia" :key="t.id"
          class="flex flex-wrap items-center gap-3 py-3"
          :class="{ 'opacity-60': t.atendida }">

          <!-- La hora manda: la lista se lee de arriba abajo como el día -->
          <div class="w-16 shrink-0">
            <p class="font-semibold text-[#2D2B5B] tabular-nums">{{ t.hora }}</p>
            <p v-if="t.hora_fin" class="text-xs text-gray-400 tabular-nums">{{ t.hora_fin }}</p>
          </div>

          <img :src="avatarPaciente(t.genero)" alt="avatar" class="w-9 h-9 rounded-full border shrink-0" />

          <div class="min-w-0 flex-1">
            <p class="font-medium text-[#2D2B5B] truncate">
              {{ t.paciente }}
              <span v-if="t.cumpleanos" class="material-icons align-middle text-base text-caine-naranja"
                title="Hoy es su cumpleaños">cake</span>
            </p>
            <p class="text-sm text-gray-500 truncate">
              {{ t.servicio }}
              <span v-if="t.estado" :class="ESTADO_CITA[t.estado] ?? 'text-gray-400'">· {{ t.estado }}</span>
            </p>
          </div>

          <span v-if="t.atendida"
            class="inline-flex shrink-0 items-center gap-1 rounded-full bg-caine-verde/15 px-2 py-1
                   text-xs font-medium text-[#2F5B28]">
            <span class="material-icons text-xs">check</span>
            Atendida
          </span>
          <span v-else class="shrink-0 text-xs text-gray-400">Pendiente</span>
        </li>
      </ul>

      <p v-else class="py-6 text-center text-sm text-gray-400">
        No tiene citas asignadas para hoy.
      </p>

      <div class="mt-4 pt-4 border-t border-gray-100">
        <Link href="/agenda" class="text-sm font-medium text-caine-celeste hover:underline">
          Ver la agenda completa →
        </Link>
      </div>
    </section>

    <!-- Cumpleaños: ayer para el que se pasó, hoy para saludar, mañana para preparar -->
    <section class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div v-for="dia in DIAS" :key="dia.clave" class="bg-white rounded-lg shadow-md p-5">
        <h3 class="flex items-center gap-2 font-bold text-[#2D2B5B] mb-3">
          <span class="material-icons" :class="dia.acento">{{ dia.icono }}</span>
          {{ dia.titulo }}
        </h3>

        <ul v-if="(cumpleanos[dia.clave] ?? []).length" class="divide-y divide-gray-100">
          <li v-for="p in cumpleanos[dia.clave]" :key="`${p.tipo}-${p.nombre}`"
            class="flex flex-wrap items-center justify-between gap-2 py-2">
            <div class="min-w-0">
              <p class="font-medium text-[#2D2B5B] truncate">{{ p.nombre }}</p>
              <p class="text-sm text-gray-500">
                {{ p.edad }} {{ p.edad === 1 ? 'año' : 'años' }} · {{ fechaCorta(p.nacimiento) }}
              </p>
            </div>
            <span class="px-2 py-1 rounded-full text-xs font-semibold whitespace-nowrap"
              :class="COLOR_TIPO[p.tipo] ?? 'bg-gray-200 text-gray-700'">
              {{ p.tipo }}
            </span>
          </li>
        </ul>

        <p v-else class="text-sm text-gray-400 py-2">Nadie cumple años.</p>
      </div>
    </section>

    <!-- Flujos guiados: por ahora solo el dibujo, para no perder los pasos -->
    <section>
      <div class="flex items-baseline gap-3 mb-4">
        <h3 class="text-lg font-bold text-[#2D2B5B]">Flujos guiados</h3>
        <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-500">
          Diseño, todavía sin construir
        </span>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <article v-for="f in FLUJOS" :key="f.clave"
          class="bg-white rounded-lg shadow-md overflow-hidden border-2 border-dashed border-gray-200">

          <div class="flex items-center gap-3 px-5 py-4" :class="f.color">
            <span class="material-icons text-white">{{ f.icono }}</span>
            <div class="min-w-0">
              <h4 class="font-bold text-white">{{ f.titulo }}</h4>
              <p class="text-sm text-white/80">{{ f.resumen }}</p>
            </div>
          </div>

          <ol class="px-5 py-4 space-y-3">
            <li v-for="(paso, i) in f.pasos" :key="i" class="flex items-start gap-3">
              <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full
                           bg-gray-100 text-xs font-semibold text-gray-500 tabular-nums">
                {{ i + 1 }}
              </span>
              <span class="text-sm text-gray-600">{{ paso }}</span>
            </li>
          </ol>

          <div class="px-5 py-3 bg-gray-50 border-t border-gray-100">
            <button type="button" disabled
              class="w-full rounded-md border border-gray-300 px-4 py-2 text-sm font-medium
                     text-gray-400 cursor-not-allowed">
              Empezar el flujo
            </button>
          </div>
        </article>
      </div>
    </section>
  </div>
</template>

<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

export default {
  layout: AuthenticatedLayout
}
</script>
