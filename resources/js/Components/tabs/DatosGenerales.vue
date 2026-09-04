<script setup>
const props = defineProps({
  expediente: {
    type: Object,
    required: true
  }
})

function imprimirPDF(url) {
  const iframe = document.createElement("iframe");
  iframe.style.display = "none";
  iframe.src = url;
  document.body.appendChild(iframe);
  iframe.onload = () => {
    iframe.contentWindow.print();
  };
}

</script>

<template>
  <table class="w-full border-collapse bg-white shadow-sm rounded-md">
    <tbody>
      <tr>
        <td class="p-2 border font-semibold bg-gray-100 w-1/5">Código Expediente</td>
        <td class="p-2 border w-4/5">{{ expediente?.id || 'N/D' }}</td>
      </tr>
      <tr>
        <td class="p-2 border font-semibold bg-gray-100">Nombre</td>
        <td class="p-2 border">{{ expediente?.paciente?.nombre || expediente?.nombre_pila }}</td>
      </tr>
      <tr>
        <td class="p-2 border font-semibold bg-gray-100">Fecha inicio</td>
        <td class="p-2 border">{{ expediente?.fecha_apertura || 'N/D' }}</td>
      </tr>
      <tr>
        <td class="p-2 border font-semibold bg-gray-100">Estado</td>
        <td class="p-2 border">{{ expediente?.estado || 'N/D' }}</td>
      </tr>
      <tr>
        <td class="p-2 border font-semibold bg-gray-100">Modalidad</td>
        <td class="p-2 border">{{ expediente?.modalidad || 'N/D' }}</td>
      </tr>
      <tr>
        <td class="p-2 border font-semibold bg-gray-100">Escolaridad</td>
        <td class="p-2 border">{{ expediente?.escolaridad?.grado || 'N/D' }}</td>
      </tr>
      <tr>
        <td class="p-2 border font-semibold bg-gray-100">Observaciones del paciente</td>
        <td class="p-2 border">{{ expediente?.observaciones_administrativas || 'Ninguna' }}</td>
      </tr>
      <tr>
        <td class="p-2 border font-semibold bg-gray-100">Consentimiento Informado</td>
        <td class="p-2 border">
          <button @click="imprimirPDF('storage/consentimiento/Consentimiento.pdf')"
            class="bg-caine-azul text-white px-4 py-2 rounded-md hover:bg-caine-morado">
            Imprimir
          </button>
        </td>
      </tr>
    </tbody>
  </table>
</template>