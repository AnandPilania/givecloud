const getDefaultVariant = (variants) => {
  const defaultVariant = variants.find((variant) => {
    return variant.is_default
  })
  return defaultVariant ? defaultVariant : variants[0]
}

export default getDefaultVariant
