const closestIndexOf = (array: number[], value: number): number => {
  const values = array.map((x) => Math.abs(value - x))
  const closest = Math.min(...values)

  return values.findIndex((x) => x === closest)
}

export { closestIndexOf }
