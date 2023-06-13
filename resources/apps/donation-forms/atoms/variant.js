import { selector } from 'recoil'
import configState from './config'
import contributionState from './contribution'
import formInputState from './formInput'
import paymentStatusState from './paymentStatus'

const variant = selector({
  key: 'variant',
  get: ({ get }) => {
    const config = get(configState)
    const contribution = get(contributionState)
    const formInput = get(formInputState)
    const paymentStatus = get(paymentStatusState)

    const variantId =
      paymentStatus === 'approved'
        ? contribution.line_items.find((item) => !!item.fundraising_form_upgrade)?.variant_id ||
          contribution.line_items[0].variant_id
        : formInput.item.variant_id

    return config.variants.find((variant) => variant.id === variantId)
  },
})

export default variant
