<script setup>
import { useForm } from '@inertiajs/vue3'
import ModalCapa from '@/Components/ModalCapa.vue'
import { fecha } from '@/Utils/fechas'

const props = defineProps({
  fila: { type: Object, required: true },
  metodos: { type: Array, default: () => [] },
})

const emit = defineEmits(['close'])

// Si ya hay pago se abre para corregirlo; si no, se propone el precio de la
// cita y la fecha de hoy, que es lo que se llena el 99% de las veces.
const form = useForm({
  monto: props.fila.monto ?? props.fila.precio,
  metodo: props.fila.metodo ?? 'Efectivo',
  numero_autorizacion: props.fila.numero_autorizacion ?? '',
  fecha: props.fila.fecha_pago ?? new Date().toLocaleDateString('sv-SE'),
})

function guardar() {
  form.post(`/pagos/${props.fila.cita_id}`, {
    preserveScroll: true,
    onSuccess: () => emit('close'),
  })
}

function anular() {
  if (!confirm('¿Anular este pago? La cita volverá a pendiente de pago.')) return

  form.delete(`/pagos/${props.fila.pago_id}`, {
    preserveScroll: true,
    onSuccess: () => emit('close'),
  })
}
</script>

<template>
  <ModalCapa panel="max-w-lg max-h-[92vh] overflow-y-auto" @close="emit('close')">
    <div class="flex items-start justify-between gap-4 px-6 py-4 border-b border-gray-200">
      <h3 class="text-lg font-bold text-[#2D2B5B]">
        {{ fila.pago_id ? 'Editar pago' : 'Registrar pago' }}
      </h3>
      <button type="button" @click="emit('close')" class="text-gray-400 hover:text-gray-600" aria-label="Cerrar">
        <span class="material-icons">close</span>
      </button>
    </div>

    <div class="px-6 py-4 space-y-5">
      <!-- La sesión que se está cobrando -->
      <div class="rounded-md bg-[#FAF9F7] p-4 text-sm">
        <p class="font-medium text-[#2D2B5B]">{{ fila.paciente }}</p>
        <p class="text-gray-600">{{ fila.servicio }} · {{ fila.atiende }}</p>
        <p class="text-gray-600">Sesión del {{ fecha(fila.fecha) }} a las {{ fila.hora }}</p>
        <p class="mt-2 text-gray-600">
          <strong>Precio de la cita:</strong> Q{{ fila.precio.toFixed(2) }}
        </p>
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-[#2D2B5B] mb-1">Monto pagado</label>
          <input v-model="form.monto" type="number" step="0.01" min="0"
            class="block w-full border rounded-md px-3 py-2 focus:ring-[#53C6D3] focus:border-[#53C6D3]" />
          <p v-if="form.errors.monto" class="mt-1 text-sm text-red-600">{{ form.errors.monto }}</p>
          <p v-else-if="Number(form.monto) < fila.precio" class="mt-1 text-sm text-orange-600">
            Menor al precio: quedará como pago parcial.
          </p>
        </div>

        <div>
          <label class="block text-sm font-medium text-[#2D2B5B] mb-1">Fecha del pago</label>
          <input v-model="form.fecha" type="date"
            class="block w-full border rounded-md px-3 py-2 focus:ring-[#53C6D3] focus:border-[#53C6D3]" />
          <p v-if="form.errors.fecha" class="mt-1 text-sm text-red-600">{{ form.errors.fecha }}</p>
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium text-[#2D2B5B] mb-1">Tipo de pago</label>
        <select v-model="form.metodo"
          class="block w-full border rounded-md px-3 py-2 focus:ring-[#53C6D3] focus:border-[#53C6D3]">
          <option v-for="m in metodos" :key="m" :value="m">{{ m }}</option>
        </select>
        <p v-if="form.errors.metodo" class="mt-1 text-sm text-red-600">{{ form.errors.metodo }}</p>
      </div>

      <div>
        <label class="block text-sm font-medium text-[#2D2B5B] mb-1">
          N.° de documento
          <span class="font-normal text-gray-400">(voucher, boleta o recibo)</span>
        </label>
        <input v-model="form.numero_autorizacion" type="text" placeholder="Ej. 0045873"
          class="block w-full border rounded-md px-3 py-2 focus:ring-[#53C6D3] focus:border-[#53C6D3]" />
        <p v-if="form.errors.numero_autorizacion" class="mt-1 text-sm text-red-600">
          {{ form.errors.numero_autorizacion }}
        </p>
      </div>
    </div>

    <div class="flex justify-between gap-2 px-6 py-4 border-t border-gray-200 bg-gray-50">
      <button v-if="fila.pago_id" type="button" @click="anular"
        class="px-4 py-2 rounded-md text-red-600 hover:bg-red-50">
        Anular pago
      </button>
      <span v-else></span>

      <div class="flex gap-2">
        <button type="button" @click="emit('close')" class="px-4 py-2 rounded-md border text-gray-600 hover:bg-gray-100">
          Cancelar
        </button>
        <button type="button" @click="guardar" :disabled="form.processing"
          class="px-4 py-2 rounded-md bg-[#2D2B5B] text-white hover:opacity-90 disabled:opacity-50">
          {{ form.processing ? 'Guardando...' : 'Guardar pago' }}
        </button>
      </div>
    </div>
  </ModalCapa>
</template>
