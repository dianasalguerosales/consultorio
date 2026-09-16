// La advertencia de borrado, en un solo lugar.
//
// Todo lo que elimina pasa por acá: antes cada pantalla escribía su propio
// texto y cuatro botones borraban de un clic, sin preguntar nada.

export const MENSAJE_ELIMINAR = '¿Está seguro de realizar la eliminación permanente?'

/**
 * Devuelve true solo si el usuario aceptó.
 *
 * `detalle` dice qué se va a borrar ("el paciente Pedro Ramírez"); va en una
 * segunda línea para que la pregunta se lea siempre igual.
 */
export function confirmarEliminacion(detalle = null) {
  const texto = detalle
    ? `${MENSAJE_ELIMINAR}\n\nSe eliminará ${detalle}.`
    : MENSAJE_ELIMINAR

  return window.confirm(texto)
}