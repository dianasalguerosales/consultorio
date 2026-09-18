<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  titulo: { type: String, required: true },
  // Qué se está viendo y cómo leerlo. Va debajo del título, siempre visible:
  // cerrada, la tarjeta tiene que poder explicarse sola.
  descripcion: { type: String, default: '' },
  // Con qué empieza si nadie la ha tocado.
  abiertaPorOmision: { type: Boolean, default: true },
  // Identifica la tarjeta para recordar si quedó abierta o cerrada. Sin clave
  // no se recuerda nada: cada carga arranca con `abiertaPorOmision`.
  clave: { type: String, default: '' },
})

const llave = props.clave ? `indicador:${props.clave}` : null

// El navegador puede negar el almacenamiento (ventana privada, permisos), y una
// tarjeta no se va a caer por no poder recordar si estaba abierta.
function recordado() {
  if (!llave) return null

  try {
    const valor = localStorage.getItem(llave)
    return valor === null ? null : valor === 'true'
  } catch {
    return null
  }
}

const abierta = ref(recordado() ?? props.abiertaPorOmision)

watch(abierta, (valor) => {
  if (!llave) return

  try {
    localStorage.setItem(llave, String(valor))
  } catch {
    // Sin persistencia la tarjeta sigue funcionando, solo no se acuerda.
  }
})
</script>

<template>
  <section class="bg-white shadow rounded-lg mb-6">
    <div class="flex flex-wrap items-start justify-between gap-4 p-6"
      :class="abierta ? 'pb-4' : ''">
      <!-- El título es el botón: toda la cabecera invita a plegar, y el icono
           gira para que se vea hacia dónde va. -->
      <button type="button" @click="abierta = !abierta"
        class="flex items-start gap-2 text-left group" :aria-expanded="abierta">
        <span class="material-icons text-caine-azul transition-transform mt-0.5"
          :class="abierta ? '' : '-rotate-90'">expand_more</span>

        <span>
          <span class="block font-bold text-caine-azul group-hover:underline">{{ titulo }}</span>

          <!-- Slot para las descripciones que llevan datos adentro; las de
               texto plano se pasan por la prop y no necesitan nada más. -->
          <span v-if="descripcion || $slots.descripcion" class="block text-sm text-gray-500">
            <slot name="descripcion">{{ descripcion }}</slot>
          </span>
        </span>
      </button>

      <!-- Los controles propios del indicador. Se ocultan al plegar: no tiene
           sentido cambiar de vista algo que no se está viendo. -->
      <div v-if="abierta && $slots.acciones" class="flex flex-wrap items-center gap-4">
        <slot name="acciones" />
      </div>
    </div>

    <div v-show="abierta" class="px-6 pb-6">
      <slot />
    </div>
  </section>
</template>
