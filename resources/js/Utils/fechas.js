// Formato de fecha del front: dd/mm/yyyy. La base y los <input type="date">
// siguen en ISO (yyyy-mm-dd), que es lo que cada uno exige.

const MESES = [
  'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
  'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre',
]

// Se parte el texto a mano en vez de usar new Date(): '2026-09-09' se
// interpreta como UTC y en Guatemala (UTC-6) retrocede un día.
function partes(valor) {
  if (!valor) return null

  const [iso] = String(valor).split('T')
  const [anio, mes, dia] = iso.split('-')

  return anio && mes && dia ? { anio, mes, dia } : null
}

/** '2026-09-09' → '09/09/2026'. Devuelve `vacio` si no hay fecha. */
export function fecha(valor, vacio = 'N/D') {
  const p = partes(valor)
  return p ? `${p.dia}/${p.mes}/${p.anio}` : vacio
}

/** Sin año, para listados compactos donde el año se sobreentiende. */
export function fechaCorta(valor, vacio = '—') {
  const p = partes(valor)
  return p ? `${p.dia}/${p.mes}` : vacio
}

/** '09 de septiembre de 2026', para encabezados de documentos impresos. */
export function fechaLarga(valor, vacio = '—') {
  const p = partes(valor)
  return p ? `${Number(p.dia)} de ${MESES[Number(p.mes) - 1]} de ${p.anio}` : vacio
}

/** Fecha con hora local, para created_at y último acceso. */
export function fechaHora(valor, vacio = 'N/D') {
  if (!valor) return vacio

  const d = new Date(valor)
  if (Number.isNaN(d.getTime())) return fecha(valor, vacio)

  const dos = (n) => String(n).padStart(2, '0')

  return `${dos(d.getDate())}/${dos(d.getMonth() + 1)}/${d.getFullYear()} ${dos(d.getHours())}:${dos(d.getMinutes())}`
}

/** Hoy en dd/mm/yyyy, sin pasar por UTC. */
export const hoy = () => fecha(new Date().toLocaleDateString('sv-SE'))
