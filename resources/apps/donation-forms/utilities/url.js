export const getURLParamValue = (paramName) => {
  const params = new URL(document.location).searchParams
  return params.get(paramName)
}
