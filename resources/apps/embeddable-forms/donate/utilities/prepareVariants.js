const prepareVariants = (variants) => {
  return variants
    .filter((variant) => {
      return variant.is_donation && variant.available
    })
    .map((variant) => ({
      id: variant.id,
      title: variant.title,
      billing_period: variant.billing_period,
      allow_first_payment_on_recurring: variant.recurring_first_payment,
      price_presets: variant.price_presets ? String(variant.price_presets).split(',') : null,
      minimum_price: variant.minimum_price,
    }))
}

export default prepareVariants
