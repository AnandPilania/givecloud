import { memo, useState } from 'react'
import { useRecoilValue } from 'recoil'
import PropTypes from 'prop-types'
import classnames from 'classnames'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faCircleInfo } from '@fortawesome/pro-regular-svg-icons'
import { isPrimaryColourDark } from '@/utilities/theme'
import Translation from '@/components/Translation/Translation'
import Checkbox from '@/components/Checkbox/Checkbox'
import FloatingIcons from '@/components/FloatingIcons/FloatingIcons'
import SelectDropdown from '@/components/SelectDropdown/SelectDropdown'
import DccInformationDialog from './components/DccInformationDialog/DccInformationDialog'
import useAnalytics from '@/hooks/useAnalytics'
import useDccRate from './hooks/useDccRate'
import formInputState from '@/atoms/formInput'
import configState from '@/atoms/config'
import styles from './CoverCostsSelector.scss'

const CoverCostsSelector = ({ className }) => {
  const config = useRecoilValue(configState)
  const collectEvent = useAnalytics({ collectOnce: true })

  const formInput = useRecoilValue(formInputState)
  const [floatingIcons, setFloatingIcons] = useState(null)
  const [options, handleOnChange] = useDccRate(setFloatingIcons)

  const [showDialog, setShowDialog] = useState(false)

  const openDialog = () => {
    setShowDialog(true)
    collectEvent({ event_name: 'dcc_info_click' })
  }

  const dismissDialog = () => {
    setShowDialog(false)
  }

  if (!config.cover_costs.enabled) {
    return null
  }

  // prettier-ignore
  const defaultValue = config.cover_costs.using_ai
    ? formInput.cover_costs_type
    : formInput.cover_costs_enabled ? 1 : null

  const RateDropdown = (
    <div key='amount' className={styles.rateDropdown}>
      <SelectDropdown
        compact
        name='cover_costs_type'
        className={styles.rateSelectDropdown}
        defaultValue={defaultValue}
        onChange={handleOnChange}
        options={options}
      />

      {floatingIcons && <FloatingIcons iconKey={floatingIcons} condition={true} large />}
    </div>
  )

  return (
    <div className={classnames(styles.root, className)}>
      <div className={styles.rateDropdownContainer}>
        <Checkbox checked={Boolean(defaultValue)}>
          <Translation id='components.cover_costs_selector.description_html' substitutions={{ amount: RateDropdown }} />
        </Checkbox>
      </div>
      <button
        type='button'
        className={classnames(styles.icon, isPrimaryColourDark && styles.darkPrimaryColour)}
        onClick={openDialog}
      >
        <FontAwesomeIcon icon={faCircleInfo} />
      </button>
      <DccInformationDialog showDialog={showDialog} dismissDialog={dismissDialog} />
    </div>
  )
}

CoverCostsSelector.propTypes = {
  className: PropTypes.string,
}

export default memo(CoverCostsSelector)
