<script setup>
import { computed, ref } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import { fecha } from '@/Utils/fechas'

const props = defineProps({
  paciente: { type: Object, required: true },
  sesiones: { type: Array, default: () => [] },
})

// Lo que se escribe son párrafos, no etiquetas: el buscador va sobre el texto
// completo y no solo sobre la fecha o el servicio.
const busqueda = ref('')

const sesionesFiltradas = computed(() => {
  const texto = busqueda.value.trim().toLowerCase()
  if (!texto) return props.sesiones

  return props.sesiones.filter((s) =>
    `${s.servicio ?? ''} ${s.atiende ?? ''} ${s.evolucion ?? ''} ${s.observaciones ?? ''}`
      .toLowerCase()
      .includes(texto)
  )
})

const conTexto = computed(() =>
  props.sesiones.filter((s) => s.evolucion || s.observaciones).length
)
</script>

<template>
  <Head :title="`Observaciones de ${paciente.nombre_completo}`" />

  <div class="p-4 sm:p-8 max-w-5xl mx-auto">
    <h2 class="text-2xl font-bold text-caine-azul mb-1">
      Observaciones de {{ paciente.nombre_completo }}
    </h2>
    <p class="text-sm text-gray-500 mb-6">
      <template v-if="paciente.expediente">Expediente {{ paciente.expediente }} · </template>
      {{ sesiones.length }} {{ sesiones.length === 1 ? 'sesión atendida' : 'sesiones atendidas' }},
      {{ conTexto }} con algo escrito
    </p>

    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
      <div class="relative flex-1 min-w-[14rem]">
        <span class="material-icons absolute left-2 top-1/2 -translate-y-1/2 text-gray-400">search</span>
        <input v-model="busqueda" type="text" placeholder="Buscar dentro de las observaciones..."
          class="w-full pl-9 pr-3 py-2 border rounded-md focus:ring-caine-celeste focus:border-caine-celeste" />
      </div>

      <Link href="/pacientes"
        class="inline-flex items-center gap-1 px-4 py-2 rounded-lg border border-caine-azul
               text-caine-azul font-semibold hover:bg-caine-azul hover:text-white transition">
        <span class="material-icons text-base">arrow_back</span>
        Volver a pacientes
      </Link>
    </div>

    <!-- Una tarjeta por sesión, de la más reciente a la más vieja. El texto va
         suelto y con `whitespace-pre-line`: son párrafos, no celdas. -->
    <div class="space-y-4">
      <article v-for="s in sesionesFiltradas" :key="s.id"
        class="bg-white shadow rounded-lg p-5">

        <div class="flex flex-wrap items-baseline justify-between gap-2 pb-3 mb-4 border-b border-gray-100">
          <div>
            <p class="font-semibold text-caine-azul">
              {{ fecha(s.fecha) }} · {{ s.hora }}
            </p>
            <p class="text-sm text-gray-500">
              {{ s.servicio ?? 'Sin servicio' }} · {{ s.atiende ?? 'Sin asignar' }}
            </p>
          </div>

          <span v-if="s.duracion" class="text-xs text-gray-400">
            {{ s.duracion }} minutos
          </span>
        </div>

        <div class="space-y-4">
          <div>
            <h3 class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">
              Evolución
            </h3>
            <p v-if="s.evolucion" class="text-gray-700 whitespace-pre-line leading-relaxed">
              {{ s.evolucion }}
            </p>
            <p v-else class="text-sm text-gray-400 italic">Sin evolución escrita.</p>
          </div>

          <div>
            <h3 class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">
              Observaciones públicas
            </h3>
            <p v-if="s.observaciones" class="text-gray-700 whitespace-pre-line leading-relaxed">
              {{ s.observaciones }}
            </p>
            <p v-else class="text-sm text-gray-400 italic">Sin observaciones escritas.</p>
          </div>
        </div>
      </article>
    </div>

    <p v-if="!sesiones.length" class="bg-white shadow rounded-lg p-10 text-center text-gray-400">
      Este paciente todavía no tiene sesiones atendidas.
    </p>

    <p v-else-if="!sesionesFiltradas.length" class="bg-white shadow rounded-lg p-10 text-center text-gray-400">
      Ninguna sesión menciona «{{ busqueda }}».
    </p>
  </div>
</template>

<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

export default { layout: AuthenticatedLayout }
</script>
