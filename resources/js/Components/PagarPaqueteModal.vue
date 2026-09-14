<script setup>
import { computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import ModalCapa from '@/Components/ModalCapa.vue'
import { EscClose } from '@/Utils/EscClose'

const props = defineProps({
  paquete: { type: Object, required: true },
  metodos: { type: Array, default: () => [] },
})

const emit = defineEmits(['close'])

EscClose(() => emit('close'))

// Se propone el saldo completo, que es como se cobra casi siempre: el cliente
// paga el programa entero al empezar el mes.
const form = useForm({
  monto: props.paquete.saldo,
  metodo: 'Efectivo',
  numero_autorizacion: '',
  fecha: new Date().toLocaleDateString('sv-SE'),
})

const quetzales = (n) => `Q${Number(n ?? 0).toFixed(2)}`

const porCita = computed(() => {
  const n = props.paquete.por_cobrar
  return n > 0 ? Number(form.monto) / n : 0
})

const esAbono = computed(() => Number(form.monto) + 0.005 < props.paquete.saldo)

function guardar() {
  form.post(`/pagos/paquetes/${props.paquete.id}`, {
    preserveScroll: true,
    onSuccess: () => emit('close'),
  })
}
</script>

<template>
  <ModalCapa panel="max-w-lg max-h-[92vh] overflow-y-auto" @close="emit('close')">

    <div class="flex items-start justify-between gap-4 px-6 py-4 border-b border-gray-200">
      <div>
        <h3 class="text-lg font-bold text-[#2D2B5B]">Cobrar paquete</h3>
        <p class="text-sm text-gray-500">{{ paquete.paciente }} · {{ paquete.programa }}</p>
      </div>
      <button type="button" @click="emit('close')"
        class="text-gray-400 hover:text-gray-600" aria-label="Cerrar">
        <span class="material-icons">close</span>
      </button>
    </div>

    <div class="px-6 py-4 space-y-5">
      <!-- Qué se está cobrando -->
      <dl class="rounded-md bg-[#FAF9F7] p-4 text-sm grid grid-cols-2 gap-x-4 gap-y-2">
        <div>
          <dt class="text-xs text-gray-500">Costo del paquete</dt>
          <dd class="font-medium text-[#2D2B5B]">{{ quetzales(paquete.precio) }}</dd>
        </div>
        <div>
          <dt class="text-xs text-gray-500">Citas</dt>
          <dd class="text-gray-700">
            {{ paquete.citas }}<span v-if="paquete.por_cobrar !== paquete.citas" class="text-gray-500">
              · {{ paquete.por_cobrar }} por cobrar</span>
          </dd>
        </div>
        <div>
          <dt class="text-xs text-gray-500">Ya cobrado</dt>
          <dd class="text-green-700">{{ quetzales(paquete.cobrado) }}</dd>
        </div>
        <div>
          <dt class="text-xs text-gray-500">Saldo</dt>
          <dd class="font-medium text-orange-600">{{ quetzales(paquete.saldo) }}</dd>
        </div>
      </dl>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-[#2D2B5B] mb-1">Monto a cobrar</label>
          <input v-model="form.monto" type="number" step="0.01" min="0.01" :max="paquete.saldo"
            class="block w-full border rounded-md px-3 py-2 focus:ring-[#53C6D3] focus:border-[#53C6D3]" />
          <p v-if="form.errors.monto" class="mt-1 text-sm text-red-600">{{ form.errors.monto }}</p>
          <p v-else-if="esAbono" class="mt-1 text-sm text-orange-600">
            Es un abono: las citas quedarán como pago parcial.
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

      <!-- Cómo se reparte adentro -->
      <p class="flex items-start gap-2 text-xs text-gray-500">
        <span class="material-icons text-sm text-[#53C6D3]">call_split</span>
        El cobro se reparte entre las {{ paquete.por_cobrar }} citas pendientes,
        a {{ quetzales(porCita) }} cada una. En la tabla de abajo cada cita queda
        con su parte, con el mismo tipo de pago y documento.
      </p>
    </div>

    <div class="flex justify-end gap-2 px-6 py-4 border-t border-gray-200 bg-gray-50">
      <button type="button" @click="emit('close')"
        class="px-4 py-2 rounded-md border text-gray-600 hover:bg-gray-100">
        Cancelar
      </button>
      <button type="button" @click="guardar" :disabled="form.processing"
        class="inline-flex items-center gap-1 px-4 py-2 rounded-md bg-[#2D2B5B] text-white
               hover:opacity-90 disabled:opacity-50">
        <span class="material-icons text-base">payments</span>
        {{ form.processing ? 'Cobrando...' : 'Cobrar paquete' }}
      </button>
    </div>
  </ModalCapa>
</template>
