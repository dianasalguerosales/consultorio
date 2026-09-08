# Contexto del proyecto

Notas de contexto y reglas de trabajo del sistema **caine**. Cada archivo guarda
una decisión, una convención o una regla que **no se deduce leyendo el código**:
por qué algo se hizo de una forma, qué se descartó y qué hay que respetar al
seguir construyendo.

Están aquí para que las lea cualquiera que trabaje en el proyecto — persona o
asistente — sin tener que reconstruir el razonamiento desde cero.

## Historial

| Archivo | De qué trata |
|---|---|
| [Informe de cambios](informe-de-cambios.md) | Qué se construyó y qué se corrigió el 7 de septiembre de 2026, con los archivos tocados |

## Reglas de trabajo

| Archivo | De qué trata |
|---|---|
| [No aplicar cambios sin autorización](no-aplicar-cambios-sin-autorizacion.md) | Regla fundamental: proponer el cambio y esperar el OK antes de editar |
| [No modificar el esquema sin consultar](no-modificar-base-de-datos-sin-consultar.md) | Cambios de columnas o tablas requieren OK, porque la base real es MySQL; datos de prueba sí están bien |
| [No romper lo existente](no-romper-lo-existente.md) | Buscar todos los usos **antes** de renombrar o eliminar algo, y adaptarlos en el mismo cambio |
| [Comentarios breves y directos](comentarios-breves-y-directos.md) | Una o dos líneas que expliquen el por qué; el detalle largo va a una nota de `contexto/` |

## Convenciones técnicas

| Archivo | De qué trata |
|---|---|
| [Convenciones de Vue del proyecto](convenciones-vue-proyecto.md) | Cómo se aplica el layout, dónde va el título, permisos en el frontend, `EscClose` en modales |
| [Paleta dataviz caine](paleta-dataviz-caine.md) | Rampa secuencial validada del azul de marca, para heatmaps y gráficas de magnitud |

## Decisiones por módulo

| Archivo | De qué trata |
|---|---|
| [Agenda: decisiones de arquitectura](agenda-decisiones-arquitectura.md) | Polimorfismo `atendido_por`, permisos de agenda, y qué quedó pendiente de Google Calendar |
| [Anamnesis: escala y grafo](anamnesis-escala-y-grafo.md) | La escala va al revés de lo intuitivo (`1` = Observación es el punto deficiente); cómo se arma el grafo de `/indicadores` |

## Formato

Cada archivo abre con un frontmatter que lo describe:

```yaml
---
name: <identificador-en-kebab-case>
description: <resumen de una línea>
metadata:
  type: user | feedback | project | reference
---
```

- **feedback** — una regla de trabajo, con el *por qué* y el *cómo aplicarla*.
- **project** — una decisión de un módulo: qué se eligió y qué se descartó.
- **reference** — datos concretos y reutilizables (paletas, escalas, rutas).
- **user** — quién es la persona con la que se trabaja.

Los archivos se enlazan entre sí con markdown normal, así que se pueden navegar
directo desde aquí o desde GitHub.
