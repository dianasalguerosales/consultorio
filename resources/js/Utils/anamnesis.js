// Escala de la anamnesis (anamnesis_items.respuesta, validada in:1,2,3).
// OJO: el número más bajo es el peor — 1 es el punto deficiente.
// Ver contexto/anamnesis-escala-y-grafo.md.

export const OBSERVACION = 1
export const EN_DESARROLLO = 2
export const ADECUADO = 3

// `clase` para el chip, `punto` para el círculo, `texto` para sobre blanco.
// El naranja es el de la paleta dataviz: el #F4A654 de marca queda bajo 3:1.
export const NIVELES = {
  [OBSERVACION]: {
    valor: OBSERVACION,
    etiqueta: 'Observación',
    clase: 'bg-caine-naranja/15 text-[#7a4e15]',
    texto: '#7a4e15',
    punto: '#c17924',
  },
  [EN_DESARROLLO]: {
    valor: EN_DESARROLLO,
    etiqueta: 'En desarrollo',
    clase: 'bg-gray-100 text-gray-600',
    texto: '#4b5563',
    punto: '#c9b79a',
  },
  [ADECUADO]: {
    valor: ADECUADO,
    etiqueta: 'Adecuado',
    clase: 'bg-caine-verde/15 text-[#2f5b28]',
    texto: '#2f5b28',
    punto: '#74be69',
  },
}

// De peor a mejor, el orden en que se leen en pantalla.
export const VALORES = [OBSERVACION, EN_DESARROLLO, ADECUADO]

// Number() porque un <input radio> entrega la respuesta como texto.
export const nivelDe = (respuesta) => NIVELES[Number(respuesta)] ?? NIVELES[ADECUADO]

export const esObservacion = (item) => Number(item?.respuesta) === OBSERVACION

// Observación o En desarrollo: el mismo corte que el filtro nivel=ambos.
export const esDeficiente = (item) => Number(item?.respuesta) <= EN_DESARROLLO

export const contarObservacion = (items = []) => items.filter(esObservacion).length

// Agrupa por área y descarta lo que no traiga su criterio cargado. El área es
// la unidad clínica: 86 criterios sueltos son ilegibles.
export function agruparPorArea(items = []) {
  const areas = new Map()

  for (const item of items) {
    if (!item.criterio) continue

    const { area } = item.criterio
    if (!areas.has(area)) areas.set(area, [])
    areas.get(area).push(item)
  }

  return [...areas.entries()].map(([nombre, lista]) => ({
    nombre,
    items: [...lista].sort((a, b) => (a.criterio?.numero ?? 0) - (b.criterio?.numero ?? 0)),
  }))
}
