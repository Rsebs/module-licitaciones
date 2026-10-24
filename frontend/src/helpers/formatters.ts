/**
 * Valida si la hora cumple estrictamente con el formato "HH:mm:ss" y la convierte a "HH:mm".
 * Si no cumple con el formato, retorna una cadena vacía.
 */
export const formatTime = (timeStr: string | null | undefined): string => {
  if (!timeStr) return ''

  const timeRegex = /^([01]\d|2[0-3]):([0-5]\d):([0-5]\d)$/
  if (timeRegex.test(timeStr)) return timeStr.substring(0, 5)

  return ''
}
