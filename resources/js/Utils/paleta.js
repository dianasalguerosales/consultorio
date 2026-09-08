// Paleta para datos, aparte de los colores de marca: un color de botón puede
// quedar fuera de la banda que necesita una gráfica.
// Ver contexto/paleta-dataviz-caine.md.

// Rampa secuencial del azul de marca. Codifica magnitud, nunca identidad.
export const RAMPA = [
  { fondo: '#acaee6', texto: '#1f2937' }, // 2.06:1 contra la superficie
  { fondo: '#8788d2', texto: '#1f2937' }, // 3.16:1
  { fondo: '#6665b4', texto: '#ffffff' }, // 5.01:1
  { fondo: '#48468a', texto: '#ffffff' }, // 8.12:1
  { fondo: '#2d2b5a', texto: '#ffffff' }, // 12.75:1
]

// Sin datos. Es la superficie con filete y no el paso más claro, para que
// "libre" no se lea como "poca carga".
export const SUPERFICIE = { fondo: '#fbfbfa', borde: '#ececea', texto: '#b6b6b0' }

// Hues de identidad, validados por pares (ΔE 27.4 en protanopia). El naranja es
// el mismo de Observación en @/Utils/anamnesis.
export const CATEGORICO = {
  azul: RAMPA[3].fondo,
  naranja: '#c17924',
}

export const NEUTRO = {
  texto: '#374151',
  textoSuave: '#6b7280',
  linea: '#aab4c2',
}

// Bins repartidos por posición sobre [1, max]. Por ancho fijo quedaban bins
// vacíos y rangos invertidos.
export function cortesDe(max, pasosMaximos = RAMPA.length) {
  const tope = Math.max(1, max)
  const pasos = Math.min(pasosMaximos, tope)

  return Array.from({ length: pasos }, (_, i) => ({
    min: Math.floor((i * tope) / pasos) + 1,
    max: Math.floor(((i + 1) * tope) / pasos),
  }))
}

// Devuelve null cuando no hay dato: eso significa usar SUPERFICIE.
export function pasoEn(valor, cortes) {
  if (!valor) return null

  const nivel = cortes.findIndex((c) => valor >= c.min && valor <= c.max)
  if (nivel < 0) return null

  return RAMPA[Math.min(nivel, RAMPA.length - 1)]
}
