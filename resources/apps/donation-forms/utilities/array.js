export const closestIndexOf = (array, needle) => {
  const values = array.map((x) => Math.abs(needle - x))
  const closest = Math.min(...values)

  return values.findIndex((x) => x === closest)
}
