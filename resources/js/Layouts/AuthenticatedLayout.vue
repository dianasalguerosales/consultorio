<script setup>
import { ref, computed } from 'vue'
import ApplicationLogo from '@/Components/ApplicationLogo.vue'
import Dropdown from '@/Components/Dropdown.vue'
import DropdownLink from '@/Components/DropdownLink.vue'
import NavLink from '@/Components/NavLink.vue'
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue'
import { Link, router, usePage } from '@inertiajs/vue3'

const mostrandoDropdown = ref(false)
const { props } = usePage()
const roles = props.auth?.user?.roles ?? []

// Menús por rol
const menuPorRol = {
  administrador: [
    'usuarios', 'pacientes', 'terapeutas', 'agenda', 'expedientes',
    'programas', 'indicadores', 'parametros', 'pagos', 'informes'
  ],
  auxiliar: [
    'terapias-dia', 'pacientes', 'agenda', 'pagos'
  ],
  coordinador: [
    'pacientes', 'usuarios', 'agenda', 'terapeutas',
    'programas', 'terapias-dia', 'pagos', 'informes'
  ],
  encargado: [
    'hijos', 'agenda', 'estado-cuenta', 'informes'
  ],
  pruebas: [
    'usuarios', 'pacientes', 'indicadores', 'informes',
    'pagos', 'expedientes'
  ],
  terapeuta: [
    'terapias-dia', 'pacientes', 'agenda', 'objetivos',
    'evaluaciones', 'informes'
  ]
}

// Opciones comunes
const comunes = ['dashboard', 'configuracion']

const ordenMenu = [
  'dashboard',
  'usuarios',
  'pacientes',
  'terapeutas',
  'agenda',
  'expedientes',
  'programas',
  'indicadores',
  'parametros',
  'pagos',
  'hijos',
  'estado-cuenta',
  'terapias-dia',
  'objetivos',
  'evaluaciones',
  'informes',
  'configuracion'
]


// Calcular menú final
const menuFinal = computed(() => {
  let opciones = [...comunes]
  roles.forEach(r => {
    if (menuPorRol[r]) {
      opciones.push(...menuPorRol[r])
    }
  })
  opciones = [...new Set(opciones)]

  return ordenMenu.filter(item => opciones.includes(item))
})

// Diccionario de rutas
const menuConfig = {
  dashboard: { icon: 'home', label: 'Inicio', href: '/dashboard' },
  usuarios: { icon: 'people', label: 'Usuarios', href: '/usuarios' },
  pacientes: { icon: 'family_restroom', label: 'Pacientes', href: '/pacientes' },
  terapeutas: { icon: 'psychology', label: 'Terapeutas', href: '/terapeutas' },
  agenda: { icon: 'today', label: 'Agenda', href: '/agenda' },
  expedientes: { icon: 'folder_shared', label: 'Expedientes', href: '/expedientes' },
  programas: { icon: 'collections_bookmark', label: 'Programas', href: '/programas' },
  indicadores: { icon: 'insights', label: 'Indicadores', href: '/indicadores' },
  pagos: { icon: 'payments', label: 'Pagos', href: '/pagos' },
  parametros: { icon: 'tune', label: 'Parametros', href: '/parametros' },
  reportes: { icon: 'bar_chart', label: 'Reportes', href: '/reportes' },
  configuracion: { icon: 'settings', label: 'Configuración', href: '/configuracion' },
  hijos: { icon: 'family_restroom', label: 'Kids', href: '/hijos' },
  'estado-cuenta': { icon: 'account_balance_wallet', label: 'Estado de Cuenta', href: '/estado-cuenta' },
  informes: { icon: 'description', label: 'Informes', href: '/informes' },
  'terapias-dia': { icon: 'pending_actions', label: 'Terapias del día', href: '/terapias-dia' },
  objetivos: { icon: 'flag', label: 'Objetivos terapéuticos', href: '/objetivos' },
  evaluaciones: { icon: 'assignment', label: 'Evaluaciones', href: '/evaluaciones' }
}
</script>

<template>
  <div class="flex min-h-screen bg-[#FAF9F7]">
    <!-- Menú lateral fijo -->
    <aside :class="colapsado ? 'w-20' : 'w-64'"
      class="bg-[#1F1D3F] text-white flex flex-col h-screen fixed left-0 top-0 transition-all duration-300">
      <!-- Logo -->
      <div class="flex justify-center items-center py-4">
        <Link href="/dashboard">
          <img v-if="!colapsado" src="/images/Logo_blanco.webp" alt="CAINE Logo" class="h-22 w-auto" />
          <img v-else src="/images/Isotipo_blanco.webp" alt="CAINE Logo reducido" class="h-20 w-auto" />
        </Link>
      </div>

      <!-- Menú ocupa todo el espacio disponible -->
      <nav class="flex-1 mt-2">
        <NavLink v-for="item in menuFinal" :key="item" :href="menuConfig[item].href"
          :active="$page.url.startsWith(menuConfig[item].href)">
          <span class="material-icons">{{ menuConfig[item].icon }}</span>
          <span v-if="!colapsado" class="ml-2">{{ menuConfig[item].label }}</span>
        </NavLink>
      </nav>
      <!-- Botón colapsar abajo -->
      <button class="px-6 py-3 bg-[#1f1d3f] hover:bg-[#14132a] flex items-center justify-center"
        @click="colapsado = !colapsado">
        <span class="material-icons">{{ colapsado ? 'chevron_right' : 'chevron_left' }}</span>
      </button>
    </aside>

    <!-- Contenido principal -->
    <div :class="colapsado ? 'ml-20' : 'ml-64'" class="flex-1 flex flex-col">
      <!-- Barra superior -->
      <nav class="bg-white shadow px-6 py-4 flex justify-between items-center">
        <h1 class="text-xl font-bold text-[#2D2B5B]"></h1>
        <div class="flex items-center space-x-4">

          <!-- Notificaciones -->
          <div class="relative">
            <button class="flex items-center focus:outline-none"
              @click="mostrarDropdownNotificaciones = !mostrarDropdownNotificaciones">
              <span class="material-icons">notifications</span>
            </button>
            <div v-if="mostrarDropdownNotificaciones"
              class="absolute right-0 mt-2 w-96 bg-white border rounded shadow-lg z-50">
              <div class="px-4 py-2 border-b font-bold text-[#2D2B5B]">Notificaciones</div>
              <div class="max-h-300 overflow-y-auto">
                <div v-if="notificaciones && notificaciones.length" v-for="(notif, index) in notificaciones.slice(0, 3)"
                  :key="index" class="flex items-start px-4 py-3 border-b space-x-3">
                  <span class="material-icons text-[#2D2B5B]">{{ notif.icono }}</span>
                  <div>
                    <p class="text-sm font-semibold text-gray-800">{{ notif.titulo }}</p>
                    <p class="text-xs text-gray-600">{{ notif.descripcion }}</p>
                    <span class="text-xs text-gray-400">{{ notif.fecha }}</span>
                  </div>
                </div>
              </div>
              <div class="px-4 py-2">
                <button @click="abrirModalNotificaciones" class="w-full text-center text-[#2D2B5B] hover:underline">
                  Ver todas las notificaciones
                </button>
              </div>
            </div>
          </div>
          <!-- Dropdown perfil -->
          <div class="relative">
            <button class="flex items-center space-x-2 focus:outline-none"
              @click="open = !open; mostrarDropdownNotificaciones = false">
              <span>{{ $page.props.auth?.user?.nombre || $page.props.auth?.user?.email }}</span>
              <span class="material-icons">expand_more</span>
            </button>
            <div v-if="open" class="absolute right-0 mt-2 w-48 bg-white border rounded shadow-lg z-50">
              <a href="/perfil" class="block px-4 py-2 hover:bg-gray-100">Perfil</a>
              <button @click="router.post(route('logout'))"
                class="w-full text-left px-4 py-2 hover:bg-gray-100 text-[#D64550]">
                Cerrar sesión
              </button>
            </div>
          </div>
        </div>
      </nav>
      <!-- Área de trabajo -->
      <main class="p-6 overflow-y-auto">
        <slot />
      </main>
      <!-- Modal Notificaciones -->
      <div v-if="mostrarModalNotificaciones"
        class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
        <div class="bg-white rounded-lg shadow-lg w-[800px] max-h-[90vh] flex flex-col">
          <div class="px-6 py-4 border-b flex justify-between items-center">
            <h2 class="text-lg font-bold text-[#2D2B5B]">Historial de Notificaciones</h2>
            <button @click="mostrarModalNotificaciones = false" class="text-gray-500 hover:text-gray-700">
              <span class="material-icons">close</span>
            </button>
          </div>
          <div class="flex-1 overflow-y-auto px-6 py-4 space-y-4">
            <div v-for="(notif, index) in notificaciones" :key="index"
              class="flex items-start justify-between border-b pb-3">
              <div class="flex items-start space-x-3">
                <span class="material-icons text-[#2D2B5B]">{{ notif.icono }}</span>
                <div>
                  <p class="text-sm font-semibold text-gray-800">{{ notif.titulo }}</p>
                  <p class="text-xs text-gray-600">{{ notif.descripcion }}</p>
                </div>
              </div>
              <span class="text-xs text-gray-400">{{ notif.fecha }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    notificaciones: {
      type: Array,
      default: () => []
    }
  },
  data() {
    return {
      colapsado: false,
      open: false,
      mostrarDropdownNotificaciones: false,
      mostrarModalNotificaciones: false
    }
  },
  methods: {
    abrirModalNotificaciones() {
      this.mostrarDropdownNotificaciones = false
      this.mostrarModalNotificaciones = true
      this.open = false
    }
  }
}
</script>
