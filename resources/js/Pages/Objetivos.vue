<script setup>
import { computed, ref } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import TablaBase from '@/Components/TablaBase.vue'
import AsignarObjetivosModal from '@/Components/AsignarObjetivosModal.vue'
import { confirmarEliminacion } from '@/Utils/confirmar'

const props = defineProps({
  filas: { type: Array, default: () => [] },
  puedeGestionar: { type: Boolean, default: false },
  minimo: { type: Number, default: 3 },
  maximo: { type: Number, default: 4 },
  pacientes: { type: Array, default: () => [] },
  servicios: { type: Array, default: () => [] },
})

/* ---------- Filtros ---------- */

const busqueda = ref('')
const filtroServicio = ref('')

const filtradas = computed(() =>
  props.filas.filter((f) => {
    if (filtroServicio.value && f.servicio_id !== Number(filtroServicio.value)) return false

    return `${f.paciente ?? ''} ${f.codigo ?? ''} ${f.servicio ?? ''} ${f.objetivos.map((o) => o.descripcion).join(' ')}`
      .toLowerCase()
      .includes(busqueda.value.toLowerCase())
  })
)

// Las terapias que ya tienen objetivos, para el selector de arriba.
const serviciosEnUso = computed(() => {
  const vistos = new Map()
  for (const f of props.filas) {
    if (f.servicio_id) vistos.set(f.servicio_id, f.servicio)
  }

  return [...vistos.entries()]
    .map(([id, nombre]) => ({ id, nombre }))
    .sort((a, b) => a.nombre.localeCompare(b.nombre))
})

/* ---------- Asignar y corregir ---------- */

const asignando = ref(false)
const enEdicion = ref(null)

function editar(fila) {
  enEdicion.value = fila
  asignando.value = true
}

function nuevo() {
  enEdicion.value = null
  asignando.value = true
}

function cerrar() {
  asignando.value = false
  enEdicion.value = null
}

function eliminar(fila) {
  if (!confirmarEliminacion(`los ${fila.objetivos.length} objetivos de ${fila.paciente} en ${fila.servicio}`)) return

  router.delete('/objetivos', {
    data: { paciente_id: fila.paciente_id, servicio_id: fila.servicio_id },
    preserveScroll: true,
  })
}
</script>

<template>
  <Head title="Objetivos terapéuticos" />

  <div class="p-4 sm:p-8 max-w-7xl mx-auto">
    <h2 class="text-2xl font-bold text-caine-azul mb-6">Objetivos terapéuticos</h2>

    <div v-if="puedeGestionar" class="mb-6 flex justify-end">
      <button type="button" @click="nuevo"
        class="bg-caine-celeste text-white px-6 py-3 rounded-lg font-semibold shadow hover:scale-105 transition">
        + Asignar objetivos
      </button>
    </div>

    <div class="bg-white rounded-lg shadow-md p-4 sm:p-6">
      <!-- Filtros -->
      <div class="flex flex-wrap items-center gap-3 mb-4">
        <input v-model="busqueda" type="text" placeholder="Buscar por paciente, expediente, terapia u objetivo"
          class="flex-1 min-w-[16rem] border rounded-md px-3 py-2 text-sm
                 focus:ring-caine-celeste focus:border-caine-celeste" />

        <select v-model="filtroServicio"
          class="border rounded-md px-3 py-2 text-sm focus:ring-caine-celeste focus:border-caine-celeste">
          <option value="">Todas las terapias</option>
          <option v-for="s in serviciosEnUso" :key="s.id" :value="s.id">{{ s.nombre }}</option>
        </select>

        <span class="text-sm text-gray-400">
          {{ filtradas.length }} de {{ filas.length }}
        </span>
      </div>

      <TablaBase>
          <thead class="bg-gray-100 text-caine-azul">
            <tr>
              <th class="px-4 py-2 text-left">Paciente</th>
              <th class="px-4 py-2 text-left">Expediente</th>
              <th class="px-4 py-2 text-left">Terapia</th>
              <th class="px-4 py-2 text-left">Objetivos</th>
              <th class="px-4 py-2 text-left">Terapeuta</th>
              <th v-if="puedeGestionar" class="px-4 py-2 text-center">Acciones</th>
            </tr>
          </thead>

          <tbody>
            <tr v-for="f in filtradas" :key="f.clave" class="border-t hover:bg-[#FAF9F7] transition align-top">
              <td class="px-4 py-2 font-medium text-caine-azul whitespace-nowrap">{{ f.paciente }}</td>
              <td class="px-4 py-2 text-gray-700 whitespace-nowrap">{{ f.codigo ?? '—' }}</td>
              <td class="px-4 py-2 text-gray-700">{{ f.servicio }}</td>
              <td class="px-4 py-2 text-gray-700">
                <ol class="list-decimal pl-4 space-y-1">
                  <li v-for="o in f.objetivos" :key="o.id" class="whitespace-pre-line">
                    {{ o.descripcion }}
                  </li>
                </ol>
                <!-- La regla del consultorio son de 3 a 4 por terapia. -->
                <p v-if="f.objetivos.length < minimo" class="mt-1 text-xs text-caine-naranja">
                  Solo {{ f.objetivos.length }} de {{ minimo }} a {{ maximo }}
                </p>
              </td>
              <td class="px-4 py-2 text-gray-500 whitespace-nowrap">{{ f.terapeuta ?? '—' }}</td>
              <td v-if="puedeGestionar" class="px-4 py-2 text-center whitespace-nowrap">
                <button type="button" @click="editar(f)"
                  class="inline-flex items-center gap-1 px-2 py-1 text-caine-celeste hover:text-caine-azul">
                  <span class="material-icons text-base">edit</span>
                  Editar
                </button>
                <button type="button" @click="eliminar(f)"
                  class="inline-flex items-center gap-1 px-2 py-1 text-gray-300 hover:text-caine-error">
                  <span class="material-icons text-base">delete</span>
                </button>
              </td>
            </tr>
          </tbody>
        </TablaBase>

      <p v-if="!filtradas.length" class="py-10 text-center text-sm text-gray-400">
        {{ filas.length
          ? 'Ningún objetivo coincide con el filtro.'
          : 'Todavía no se han asignado objetivos.' }}
      </p>
    </div>

    <AsignarObjetivosModal v-if="asignando && puedeGestionar" :fila="enEdicion"
      :pacientes="pacientes" :servicios="servicios" :minimo="minimo" :maximo="maximo"
      @close="cerrar" />
  </div>
</template>

<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

export default { layout: AuthenticatedLayout }
</script>
