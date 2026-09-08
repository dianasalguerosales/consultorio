---
name: no-aplicar-cambios-sin-autorizacion
description: Regla fundamental del proyecto consultorio - proponer cambios y esperar el OK; nunca editar archivos sin autorización explícita
metadata: 
  node_type: memory
  type: feedback
  originSessionId: a4bbc732-06d8-4517-9898-02e7027227e3
  modified: 2026-09-07T20:08:01.626Z
---

En el proyecto `consultorio` (Laravel + Inertia + Vue), NUNCA aplicar ediciones a archivos sin que Diana lo autorice explícitamente. El flujo correcto es: explicar qué falla, por qué, dónde y **cómo** se corregiría (mostrando el código propuesto), y luego esperar el OK. A veces preferirá aplicarlo ella misma.

**Why:** Diana quiere revisar cada cambio e ir viendo cómo queda la estructura del proyecto mientras avanza. Lo declaró como "una regla fundamental en todo este proyecto". Los cambios aplicados por iniciativa propia le quitan ese control y visibilidad.

**How to apply:** Presentar diffs o bloques de código como propuesta, no como hecho consumado. Sí está permitido leer archivos, consultar la base de datos, correr linters/builds y diagnosticar libremente — la restricción es sobre escribir. Cuando dé el OK, aplicar solo lo aprobado, sin extras. Mantener el estilo de respuesta que pidió: breve y directo — qué falla, por qué, dónde y cómo, señalando siempre el archivo.
