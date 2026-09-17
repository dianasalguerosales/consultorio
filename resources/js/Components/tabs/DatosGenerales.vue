<script setup>
import { computed } from 'vue'
import { fecha } from '@/Utils/fechas'

const props = defineProps({
  expediente: {
    type: Object,
    required: true
  }
})
const nombrePaciente = computed(() => {
  const p = props.expediente?.paciente
  const nombres = p?.nombres ?? props.expediente?.nombres
  const apellidos = p?.apellidos ?? props.expediente?.apellidos
  return [nombres, apellidos].filter(Boolean).join(' ') || 'N/D'
})

function imprimirPDF(url) {
  // El visor de PDF no se inicializa con display:none: hay que renderizarlo fuera de pantalla.
  document.getElementById('iframe-impresion')?.remove()

  const iframe = document.createElement('iframe')
  iframe.id = 'iframe-impresion'
  iframe.style.cssText = 'position:fixed;right:0;bottom:0;width:1px;height:1px;border:0'

  // El onload va antes del src para no perder el evento si el PDF ya está en caché.
  iframe.onload = () => {
    try {
      iframe.contentWindow.focus()
      iframe.contentWindow.print()
    } catch {
      window.open(url, '_blank')
    }
  }
  iframe.onerror = () => window.open(url, '_blank')

  iframe.src = url
  document.body.appendChild(iframe)
}

</script>

<template>
  <table class="w-full border-collapse bg-white shadow-sm rounded-md">
    <tbody>
      <tr>
        <td class="p-2 border font-semibold bg-gray-100 w-1/5">Código Expediente</td>
        <td class="p-2 border w-4/5">{{ expediente?.codigo || 'N/D' }}</td>
      </tr>
      <tr>
        <td class="p-2 border font-semibold bg-gray-100">Nombre</td>
        <td class="p-2 border">{{ nombrePaciente }}</td>
      </tr>
      <tr>
        <td class="p-2 border font-semibold bg-gray-100">Fecha inicio</td>
        <td class="p-2 border">{{ fecha(expediente?.fecha_inicio) }}</td>
      </tr>
      <tr>
        <td class="p-2 border font-semibold bg-gray-100">Estado</td>
        <td class="p-2 border">{{ expediente?.estado?.nombre || 'N/D' }}</td>
      </tr>
      <tr>
        <td class="p-2 border font-semibold bg-gray-100">Modalidad</td>
        <td class="p-2 border">{{ expediente?.modalidad?.nombre || 'N/D' }}</td>
      </tr>
      <tr>
        <td class="p-2 border font-semibold bg-gray-100">Escolaridad</td>
        <td class="p-2 border">{{ expediente?.paciente?.escolaridad?.nombre || 'N/D' }}</td>
      </tr>
      <tr>
        <td class="p-2 border font-semibold bg-gray-100">Observaciones del paciente</td>
        <td class="p-2 border">{{ expediente?.observaciones || 'Ninguna' }}</td>
      </tr>
      <tr>
        <td class="p-2 border font-semibold bg-gray-100">Consentimiento Informado</td>
        <td class="p-2 border">
          <button @click="imprimirPDF('/documentos/Consentimiento.pdf')"
            class="bg-caine-azul text-white px-4 py-2 rounded-md hover:bg-caine-morado">
            Imprimir
          </button>
        </td>
      </tr>
    </tbody>
  </table>
</template>