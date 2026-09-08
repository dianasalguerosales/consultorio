---
name: no-modificar-base-de-datos-sin-consultar
description: "Cambios de ESQUEMA (columnas, tablas, eliminaciones) requieren consultar antes porque la base real es MySQL; inyectar datos de prueba en la sqlite local sí está bien"
metadata: 
  node_type: memory
  type: feedback
  originSessionId: a4bbc732-06d8-4517-9898-02e7027227e3
  modified: 2026-09-07T21:35:45.093Z
---

En `consultorio`, **los cambios de esquema requieren el OK de Diana antes de ejecutarse**: agregar/renombrar/eliminar columnas o tablas, cambiar tipos, quitar llaves foráneas. La `database/database.sqlite` del repo es una simulación de desarrollo; **la base real está en MySQL**, y cualquier cambio estructural hay que replicarlo allá.

**Lo que NO requiere consulta:** inyectar o modificar **datos de prueba** en la sqlite local (seeders con citas, pacientes, catálogos de ejemplo). Eso le sirve y lo agradece. La línea está en la *estructura*, no en las *filas*.

**Why:** Al construir la Agenda corrí una migración que recreó la tabla `citas` sin avisar. Su aclaración textual: "que inyectes datos para pruebas hasta me sirve... yo a lo que me refiero es a cambiar columnas, eliminar o cosas asi que afecten la integración con la base de datos existente en MYSQL."

**How to apply:** Escribir el archivo de migración está bien — es código y lo revisa como cualquier otro. Lo que requiere su OK es **ejecutarlo**. Al proponer una, decir qué tablas y columnas toca, si es reversible, y si es portable a MySQL. Ojo con los patrones que SQLite no soporta (`dropForeign`, `dropColumn` con FK): la salida es recrear la tabla, que funciona igual en ambos motores. Mantener `phpunit.xml` con `DB_DATABASE=:memory:` para que los tests jamás toquen una base real.

Ver [Agenda: decisiones de arquitectura](agenda-decisiones-arquitectura.md) para lo que ya se aplicó, y [No aplicar cambios sin autorización](no-aplicar-cambios-sin-autorizacion.md) para el flujo general.
