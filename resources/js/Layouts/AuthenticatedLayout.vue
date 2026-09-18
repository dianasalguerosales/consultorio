<script setup>
import { ref, computed } from 'vue'
import ApplicationLogo from '@/Components/ApplicationLogo.vue'
import Dropdown from '@/Components/Dropdown.vue'
import DropdownLink from '@/Components/DropdownLink.vue'
import NavLink from '@/Components/NavLink.vue'
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import { fecha } from '@/Utils/fechas'
import { EscClose } from '@/Utils/EscClose'

const mostrandoDropdown = ref(false)

/* ---------- Estado de la interfaz ---------- */

// Vive acá y no en un segundo bloque Options API para que EscClose lo alcance,
// como en el resto del proyecto.
const colapsado = ref(false)
const menuAbierto = ref(false)
const open = ref(false)
const mostrarDropdownNotificaciones = ref(false)
const mostrarModalNotificaciones = ref(false)

function abrirModalNotificaciones() {
  mostrarDropdownNotificaciones.value = false
  mostrarModalNotificaciones.value = true
  open.value = false
}

// Las dos ventanas de la barra superior se cierran juntas: nunca se muestran
// las dos a la vez, y salir de una debe salir de ambas.
function cerrarVentanas() {
  open.value = false
  mostrarDropdownNotificaciones.value = false
}

EscClose(cerrarVentanas)

const { props } = usePage()
const roles = props.auth?.user?.roles ?? []

// Menús por rol
const menuPorRol = {
  administrador: [
    'usuarios', 'pacientes', 'personas', 'agenda', 'expedientes',
    'programas', 'objetivos', 'evaluaciones', 'indicadores', 'parametros', 'pagos', 'informes'
  ],
  auxiliar: [
    'pacientes', 'agenda', 'evaluaciones', 'pagos'
  ],
  coordinador: [
    'pacientes', 'usuarios', 'agenda', 'personas',
    'programas', 'objetivos', 'evaluaciones', 'pagos', 'informes', 'indicadores'
  ],
  encargado: [
    'hijos', 'agenda', 'objetivos', 'evaluaciones', 'estado-cuenta'
  ],
  // 'indicadores' no va aquí: el rol pruebas no tiene el permiso
  // 'ver indicadores', así que el enlace le daría 403.
  pruebas: [
    'usuarios', 'pacientes', 'informes',
    'pagos', 'expedientes', 'evaluaciones'
  ],
  terapeuta: [
    'pacientes', 'agenda', 'objetivos', 'evaluaciones'
  ]
}

// Opciones comunes
const comunes = ['dashboard', 'configuracion']

const ordenMenu = [
  'dashboard',
  'pacientes',
  'personas',
  'agenda',
  'expedientes',
  'programas',
  'indicadores',
  'parametros',
  'pagos',
  'hijos',
  'estado-cuenta',
  'objetivos',
  'evaluaciones',
  'informes',
  'usuarios',
  'configuracion'
]

// Calcular menú final
// El circulito rojo de la campana.
const sinLeer = computed(() =>
  (usePage().props.notificaciones ?? []).filter((n) => !n.leida).length
)

const opcionesLectura = { preserveScroll: true, preserveState: true }

function marcarLeida(notif) {
  if (notif.leida) return

  router.put(`/notificaciones/${notif.id}/leer`, {}, opcionesLectura)
}

function marcarTodasLeidas() {
  if (!sinLeer.value) return

  router.put('/notificaciones/leer-todas', {}, opcionesLectura)
}

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
  pacientes: { icon: 'family_restroom', label: 'Pacientes', href: '/pacientes' },
  personas: { icon: 'groups', label: 'Personas', href: '/personas' },
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
  usuarios: { icon: 'people', label: 'Usuarios', href: '/usuarios' },
  informes: { icon: 'description', label: 'Informes', href: '/informes' },
  objetivos: { icon: 'flag', label: 'Objetivos terapéuticos', href: '/objetivos' },
  evaluaciones: { icon: 'assignment', label: 'Evaluaciones', href: '/evaluaciones' }
}
</script>

<template>
  <div class="flex min-h-screen bg-[#FAF9F7]">
    <!-- Fondo del menú en teléfono: cierra el cajón al tocar fuera. -->
    <div v-if="menuAbierto" class="fixed inset-0 bg-black/40 z-30 md:hidden"
      @click="menuAbierto = false"></div>

    <!-- Menú lateral fijo. Debajo de md sale de pantalla y entra como cajón. -->
    <aside
      :class="[
        colapsado ? 'w-20' : 'w-64',
        menuAbierto ? 'translate-x-0' : '-translate-x-full md:translate-x-0',
      ]"
      class="bg-[#1F1D3F] text-white flex flex-col h-screen fixed left-0 top-0 z-40 transition-all duration-300">
      <!-- Logo -->
      <div class="flex justify-center items-center py-4 shrink-0">
        <Link href="/dashboard">
          <img v-if="!colapsado" src="/images/Logo_blanco.webp" alt="CAINE Logo" class="h-22 w-auto" />
          <img v-else src="/images/Isotipo_blanco.webp" alt="CAINE Logo reducido" class="h-20 w-auto" />
        </Link>
      </div>

      <!-- Menú ocupa todo el espacio disponible -->
      <!-- min-h-0 va junto con overflow-y-auto: sin él, el hijo de una
           columna flex no se encoge bajo su contenido y no aparece el scroll. -->
      <nav class="flex-1 min-h-0 overflow-y-auto mt-2" @click="menuAbierto = false">
        <NavLink v-for="item in menuFinal" :key="item" :href="menuConfig[item].href"
          :active="$page.url.startsWith(menuConfig[item].href)">
          <span class="material-icons">{{ menuConfig[item].icon }}</span>
          <span v-if="!colapsado" class="ml-2">{{ menuConfig[item].label }}</span>
        </NavLink>
      </nav>
      <!-- Botón colapsar abajo -->
      <button class="px-6 py-3 bg-[#1f1d3f] hover:bg-[#14132a] hidden md:flex items-center justify-center"
        @click="colapsado = !colapsado">
        <span class="material-icons">{{ colapsado ? 'chevron_right' : 'chevron_left' }}</span>
      </button>
    </aside>

    <!-- Contenido principal -->
    <div :class="colapsado ? 'md:ml-20' : 'md:ml-64'" class="flex-1 flex flex-col min-w-0">
      <!-- Barra superior -->
      <nav class="bg-white shadow px-6 py-4 flex justify-between items-center">
        <div class="flex items-center gap-3">
          <button class="md:hidden text-[#2D2B5B]" @click="menuAbierto = true" aria-label="Abrir menú">
            <span class="material-icons">menu</span>
          </button>
          <h1 class="text-xl font-bold text-[#2D2B5B]"></h1>
        </div>
        <div class="flex items-center space-x-4">

          <!-- Cierra las ventanas de la barra al tocar fuera. Transparente y
               por debajo de ellas (z-40 contra z-50): se ven normales, pero el
               clic no las atraviesa. -->
          <div v-if="open || mostrarDropdownNotificaciones" class="fixed inset-0 z-40"
            @click="cerrarVentanas"></div>

          <!-- Notificaciones -->
          <div class="relative">
            <button class="relative flex items-center focus:outline-none"
              @click="mostrarDropdownNotificaciones = !mostrarDropdownNotificaciones">
              <span class="material-icons">notifications</span>

              <span v-if="sinLeer"
                class="absolute -top-1 -right-1 min-w-[1.15rem] h-[1.15rem] px-1 rounded-full
                       bg-caine-error text-white text-[10px] font-bold leading-none
                       flex items-center justify-center">
                {{ sinLeer > 9 ? '9+' : sinLeer }}
              </span>
            </button>
            <div v-if="mostrarDropdownNotificaciones"
              class="absolute right-0 mt-2 w-96 bg-white border rounded shadow-lg z-50">
              <div class="px-4 py-2 border-b flex items-center justify-between gap-2">
                <span class="font-bold text-[#2D2B5B]">Notificaciones</span>
                <button v-if="sinLeer" type="button" @click="marcarTodasLeidas"
                  class="text-xs font-medium text-caine-celeste hover:underline">
                  Marcar todas como leídas
                </button>
              </div>
              <div class="max-h-300 overflow-y-auto">
                <div v-if="notificaciones && notificaciones.length"
                  v-for="(notif, index) in notificaciones.slice(0, 3)" :key="index"
                  class="flex items-start justify-between gap-2 px-4 py-3 border-b"
                  :class="{ 'bg-caine-celeste/5': !notif.leida }">
                  <div class="flex items-start space-x-3">
                    <span class="material-icons text-[#2D2B5B]">{{ notif.icono }}</span>
                    <div>
                      <p class="text-sm font-semibold text-gray-800">{{ notif.titulo }}</p>
                      <p class="text-xs text-gray-600">{{ notif.descripcion }}</p>
                      <span class="text-xs text-gray-400">{{ fecha(notif.fecha) }}</span>
                    </div>
                  </div>

                  <button v-if="!notif.leida" type="button" @click="marcarLeida(notif)"
                    title="Marcar como leída"
                    class="shrink-0 text-gray-300 hover:text-caine-verde">
                    <span class="material-icons text-base">check_circle</span>
                  </button>
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
      <main class="p-6">
        <slot />
      </main>
      <!-- Modal Notificaciones -->
      <div v-if="mostrarModalNotificaciones"
        class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
        <div class="bg-white rounded-lg shadow-lg w-[800px] max-h-[90vh] flex flex-col">
          <div class="px-6 py-4 border-b flex justify-between items-center">
            <h2 class="text-lg font-bold text-[#2D2B5B]">Historial de Notificaciones</h2>
            <div class="flex items-center gap-4">
              <button v-if="sinLeer" type="button" @click="marcarTodasLeidas"
                class="text-xs font-medium text-caine-celeste hover:underline">
                Marcar todas como leídas
              </button>
              <button @click="mostrarModalNotificaciones = false" class="text-gray-500 hover:text-gray-700">
                <span class="material-icons">close</span>
              </button>
            </div>
          </div>
          <div class="flex-1 overflow-y-auto px-6 py-4 space-y-4">
            <div v-for="(notif, index) in notificaciones" :key="index"
              class="flex items-start justify-between border-b pb-3"
              :class="{ 'bg-caine-celeste/5': !notif.leida }">
              <div class="flex items-start space-x-3">
                <span class="material-icons text-[#2D2B5B]">{{ notif.icono }}</span>
                <div>
                  <p class="text-sm font-semibold text-gray-800">{{ notif.titulo }}</p>
                  <p class="text-xs text-gray-600">{{ notif.descripcion }}</p>
                </div>
                <button v-if="!notif.leida" type="button" @click="marcarLeida(notif)"
                  title="Marcar como leída"
                  class="shrink-0 text-gray-300 hover:text-caine-verde">
                  <span class="material-icons text-base">check_circle</span>
                </button>
              </div>
              <span class="text-xs text-gray-400">{{ fecha(notif.fecha) }}</span>
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
  }
}
</script>
