export const chunkArray = <T>(array: T[], size = 2) => {
  const chunkedArray: T[][] = []
  let index = 0
  while (index < array.length) {
    chunkedArray.push(array.slice(index, size + index))
    index += size
  }
  return chunkedArray
}
