<script setup>
import { ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import CatalogoModalVer from './CatalogoModalVer.vue'
import CatalogoModalEditar from './CatalogoModalEditar.vue'
import { confirmarEliminacion } from '@/Utils/confirmar'

const props = defineProps({
  // Una entrada de App\Catalogos\Catalogos: clave, titulo, conDescripcion, items.
  catalogo: { type: Object, required: true },
})

// Los campos extra que declara el catalogo (Programas: cantidad y costo).
const campos = props.catalogo.campos ?? []

const valor = (item, campo) => {
  const v = item[campo.clave]
  if (v === null || v === undefined || v === '') return '—'
  return campo.tipo === 'moneda' ? `Q${Number(v).toFixed(2)}` : v
}

const seleccionado = ref(null)
const viendo = ref(false)
const editando = ref(false)

function ver(item) {
  seleccionado.value = item
  viendo.value = true
}

// Sin item es un alta; con item, edición. Mismo formulario.
function editar(item = null) {
  seleccionado.value = item
  editando.value = true
}

function cerrar() {
  viendo.value = false
  editando.value = false
  seleccionado.value = null
}
</script>

<template>
  <div>
    <div class="mb-4 flex justify-end">
      <button type="button" @click="editar()"
        class="bg-caine-celeste text-white px-5 py-2 rounded-lg font-semibold shadow hover:scale-105 transition">
        + Nuevo {{ catalogo.titulo.toLowerCase() }}
      </button>
    </div>

    <div class="overflow-x-auto bg-white rounded-lg shadow-md">
      <table class="min-w-full border border-gray-200 text-md rounded-lg">
        <thead class="bg-gray-200 text-[#2D2B5B]">
          <tr>
            <th class="px-4 py-2 text-left">Nombre</th>
            <th v-if="catalogo.conDescripcion" class="px-4 py-2 text-left">Descripción</th>
            <th v-for="c in campos" :key="c.clave" class="px-4 py-2 text-right">{{ c.etiqueta }}</th>
            <th class="px-4 py-2 text-center">Activo</th>
            <th class="px-4 py-2 text-center">Acciones</th>
          </tr>
        </thead>

        <tbody>
          <tr v-for="item in catalogo.items" :key="item.id" class="border-t hover:bg-[#FAF9F7] transition">
            <td class="px-4 py-2 font-medium text-[#2D2B5B]">{{ item.nombre }}</td>

            <td v-if="catalogo.conDescripcion" class="px-4 py-2 text-gray-700">{{ item.descripcion }}</td>

            <td v-for="c in campos" :key="c.clave" class="px-4 py-2 text-right whitespace-nowrap">
              <!-- Un color se entiende viéndolo; el hex va al lado por si lo
                   quieren copiar a otro lado. -->
              <span v-if="c.tipo === 'color' && item[c.clave]" class="inline-flex items-center gap-2 justify-end">
                <span class="w-5 h-5 rounded border border-gray-300 shrink-0"
                  :style="{ backgroundColor: item[c.clave] }"></span>
                <span class="uppercase text-gray-600">{{ item[c.clave] }}</span>
              </span>
              <template v-else>{{ valor(item, c) }}</template>
            </td>

            <td class="px-4 py-2 text-center">
              <span :class="item.activo ? 'text-green-600' : 'text-red-600'">
                {{ item.activo ? 'Sí' : 'No' }}
              </span>
            </td>

            <td class="px-4 py-2 text-center">
              <div class="flex justify-center space-x-2">
                <button @click="ver(item)"
                  class="inline-flex items-center px-3 py-1 text-[#74BE69] hover:text-[#1f1d3f]">
                  <span class="material-icons text-base">assignment</span>
                  <span class="ml-1">Ver</span>
                </button>

                <button @click="editar(item)"
                  class="inline-flex items-center px-3 py-1 text-[#53C6D3] hover:text-[#2D2B5B]">
                  <span class="material-icons text-base">edit</span>
                  <span class="ml-1">Editar</span>
                </button>

                <!-- Sin el @click borraba de un solo clic, sin preguntar. -->
                <Link as="button" method="delete" preserve-scroll
                  :href="route('catalogos.destroy', [catalogo.clave, item.id])"
                  @before="confirmarEliminacion(`«${item.nombre}» de ${catalogo.nombre}`)"
                  class="inline-flex items-center px-3 py-1 text-red-600 hover:text-red-800">
                  <span class="material-icons text-base">delete</span>
                  <span class="ml-1">Eliminar</span>
                </Link>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <p v-if="!catalogo.items?.length" class="py-8 text-center text-sm text-gray-400">
        No hay registros.
      </p>
    </div>

    <CatalogoModalVer v-if="viendo" :item="seleccionado" :titulo="catalogo.titulo"
      :conDescripcion="catalogo.conDescripcion" :campos="campos" @close="cerrar" />

    <CatalogoModalEditar v-if="editando" :item="seleccionado" :catalogo="catalogo.clave" :titulo="catalogo.titulo"
      :conDescripcion="catalogo.conDescripcion" :campos="campos" @close="cerrar" />
  </div>
</template>