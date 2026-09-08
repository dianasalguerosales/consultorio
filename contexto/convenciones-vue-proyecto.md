---
name: convenciones-vue-proyecto
description: "Patrones de Vue/Inertia establecidos en consultorio - cómo se aplica el layout, dónde va el título, permisos en frontend y EscClose en modales"
metadata: 
  node_type: memory
  type: reference
  originSessionId: a4bbc732-06d8-4517-9898-02e7027227e3
  modified: 2026-09-07T21:37:34.194Z
---

Convenciones que ya usa el proyecto `consultorio` y que hay que seguir para que todo se vea y se comporte igual:

**Layout.** Las páginas **no** se envuelven en `<AuthenticatedLayout>`. Se declara con un segundo bloque `<script>` (sin `setup`) al final del archivo:

```vue
<script>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
export default { layout: AuthenticatedLayout };
</script>
```

**No existe `<slot name="header" />`** en `AuthenticatedLayout.vue` — solo un `<slot />`. Un `<template #header>` se descarta en silencio, sin error. El título va **inline** al inicio del template: `<h2 class="text-2xl font-bold text-caine-azul mb-6">`, y los botones de acción en un `<div class="mb-6 flex justify-end">`.

**Permisos en frontend.** Se leen del array compartido por `HandleInertiaRequests`: `$page.props.auth.user.permissions.includes('gestionar pacientes')`. También hay `auth.user.roles`. No hace falta que el controlador mande flags de permiso aparte.

**Modales: salir con Escape.** Todos importan el composable `EscClose` y lo llaman en el `<script setup>`:

```js
import { EscClose } from '@/Utils/EscClose'
EscClose(() => emit('close'))
```

Vive en `resources/js/Utils/EscClose.js` (se movió ahí desde `Components/` el 2026-09-07, porque `Components/` es solo para `.vue`). Lo usan `CitaModal`, `ExpedienteModal`, `HistorialModal`, `InfoModal`, `ModalBaseEditar`, `ModalBaseVer`, `PacienteForm` y `UsuarioForm`. Cualquier modal nuevo debe hacer lo mismo.

Si el modal está **siempre montado** y solo oculta su contenido con `v-if` interno (como `CitaModal`), el callback necesita el guard `if (props.show)`; los que se montan y desmontan no lo necesitan.

**`resources/js/Utils/`** es donde va la lógica JS compartida sin plantilla: `avatares.js` y `EscClose.js`.

**Colores.** Paleta `caine` en `tailwind.config.js`: azul `#2D2B5B`, rosa `#EE518E`, celeste `#53C6D3`, verde `#74BE69`, morado `#8B70CD`, naranja `#F4A654`, error `#D64550`.

**Avatares.** Centralizados en `resources/js/Utils/avatares.js` (`avatarUsuario`, `avatarPaciente`). Ver [Agenda: decisiones de arquitectura](agenda-decisiones-arquitectura.md).
