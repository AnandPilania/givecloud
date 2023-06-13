import { memo, useState } from 'react'
import { useRecoilState, useRecoilValue } from 'recoil'
import PropTypes from 'prop-types'
import classnames from 'classnames'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faCheck } from '@fortawesome/pro-regular-svg-icons'
import { uniqueId } from 'lodash'
import { isPrimaryColourDark } from '@/utilities/theme'
import FloatingIcons from '@/components/FloatingIcons/FloatingIcons'
import useLocalization from '@/hooks/useLocalization'
import configState from '@/atoms/config'
import formInputState from '@/atoms/formInput'
import styles from './VariantButton.scss'

const VariantButton = ({ variant }) => {
  const config = useRecoilValue(configState)
  const [formInput, setFormInput] = useRecoilState(formInputState)

  const [floatingIcons, setFloatingIcons] = useState(null)

  const t = useLocalization('components.amount_selector.variant_button')

  const isOneTime = variant.billing_period === 'onetime'
  const isMonthly = variant.billing_period === 'monthly'
  const isVariantSelected = formInput.item.variant_id === variant.id

  const handleOnClick = () => {
    return () => {
      if (isVariantSelected) {
        return
      }

      setFormInput({
        ...formInput,
        item: {
          ...formInput.item,
          variant_id: variant.id,
          recurring_frequency: isOneTime ? null : variant.billing_period,
        },
      })

      if (config.floating_icons.variant_button[variant.billing_period || 'onetime']) {
        setFloatingIcons(uniqueId('VariantButton'))
      }
    }
  }

  const buttonClasses = classnames(
    styles.root,
    isPrimaryColourDark && styles.darkPrimaryColour,
    isVariantSelected && styles.selected,
    !isVariantSelected && isMonthly && styles.pulseAnimation
  )

  return (
    <button className={buttonClasses} onClick={handleOnClick()}>
      {isVariantSelected && <FontAwesomeIcon icon={faCheck} />} {t(variant.title)}
      {floatingIcons && <FloatingIcons iconKey={floatingIcons} condition={true} large />}
    </button>
  )
}

VariantButton.propTypes = {
  variant: PropTypes.object.isRequired,
}

export default memo(VariantButton)
