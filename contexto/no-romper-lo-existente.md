---
name: no-romper-lo-existente
description: "Antes de renombrar o eliminar una relación, columna o método, hacer grep de todos sus usos y adaptarlos en el mismo cambio"
metadata: 
  node_type: memory
  type: feedback
  originSessionId: a4bbc732-06d8-4517-9898-02e7027227e3
  modified: 2026-09-07T21:17:25.899Z
---

En `consultorio`, antes de renombrar o eliminar cualquier relación Eloquent, columna, método o prop, **hacer grep de todos sus usos en `app/`, `database/`, `routes/` y `resources/js/`** y adaptarlos dentro del mismo cambio. Si algo existente choca con el desarrollo nuevo, se **modifica para adaptarlo al nuevo uso** — no se deja roto ni se borra sin avisar.

**Why:** Al construir la Agenda reemplacé `Cita::terapeuta()` por la relación polimórfica `atendidoPor()` sin buscar quién más la usaba. Eso rompió el módulo Pacientes con `RelationNotFoundException` en dos lugares (`PacientesController::index` y `::historial`), más `HistorialModal.vue`. Diana lo descubrió al usar la app y me lo señaló: "en todos tus cambios me rompiste el modulo pacientes que ya estaba creado".

**How to apply:** El grep va **antes** de aplicar el cambio, no después de que falle. Cubrir también los nombres serializados que ve el frontend: una relación `atendidoPor` llega al JSON como `atendido_por`, y los accessors como `nombre_completo` **no** viajan salvo que el modelo los declare en `$appends`. Al terminar, verificar cada módulo que tocaba lo renombrado, no solo el nuevo.

Contexto del módulo: [Agenda: decisiones de arquitectura](agenda-decisiones-arquitectura.md). Flujo de trabajo: [No aplicar cambios sin autorización](no-aplicar-cambios-sin-autorizacion.md).

Dato útil del dominio: el botón **Historial** en Pacientes es una consulta de todas las citas del paciente en orden cronológico — es funcionalidad que Diana usa, no decorativa.
