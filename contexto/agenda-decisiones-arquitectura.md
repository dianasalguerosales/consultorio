---
name: agenda-decisiones-arquitectura
description: "Decisiones de diseño del módulo Agenda del consultorio - polimorfismo atendido_por, permisos de agenda, y fases pendientes"
metadata: 
  node_type: memory
  type: project
  originSessionId: a4bbc732-06d8-4517-9898-02e7027227e3
  modified: 2026-09-07T21:07:24.470Z
---

Módulo Agenda (Fase 1 construida el 2026-09-07). Decisiones que no se deducen del código:

- **`citas.atendido_por_type/id` es polimórfico** (Terapeuta | Administrativo) porque los **auxiliares atienden sus propias citas cuando están solos en sucursal**. Reemplazó a `terapeuta_id`. La migración recrea la tabla en vez de usar `dropForeign`, porque SQLite no lo soporta.
- **Tres permisos nuevos** en lugar del viejo `gestionar citas`: `ver agenda` (los 5 roles), `agendar citas` (administrador, coordinador, auxiliar) y `ver ocupacion personal` (administrador, coordinador). A `terapeuta` se le **quitó** `gestionar citas` a propósito: solo consulta.
- **El scoping vive en `AgendaController::conAlcanceDe()`**: admin/coordinador/auxiliar ven todo, terapeuta solo lo que atiende, encargado solo las citas de sus pacientes. El caso por omisión es *no ver nada*, no ver todo.
- **El encargado se resuelve por `pacientes.encargado_id`**, no por el pivote `encargado_paciente` — ese pivote existe pero está vacío. Hay dos mecanismos para la misma relación; conviene unificarlos algún día.
- **Google Calendar quedó para Fase 3.** Diana creía que la API cobra por volumen; en realidad es gratis (~1M consultas/día) y lo que cuesta es Workspace. Una Service Account no puede invitar attendees sin domain-wide delegation, así que las variantes viables son OAuth2 con una cuenta Gmail, o .ics por correo. `citas.google_event_id` ya existe para no duplicar eventos al editar.
- **Fase 2 pendiente:** vista `/agenda/ocupacion`. El botón está comentado en `Agenda.vue` y `permisos.verOcupacion` ya llega listo desde el controlador.
- **Duda abierta:** si el auxiliar debe ver *todas* las citas o solo las suyas. Hoy ve todas, porque Diana solo restringió a terapeutas y encargados.

Ver [No aplicar cambios sin autorización](no-aplicar-cambios-sin-autorizacion.md) — todo esto se propuso antes de aplicarse.
