import { memo } from 'react'
import { useRecoilValue } from 'recoil'
import PropTypes from 'prop-types'
import useLocalization from '@/hooks/useLocalization'
import configState from '@/atoms/config'

const TestModeWarning = ({ className }) => {
  const t = useLocalization()
  const config = useRecoilValue(configState)

  if (config.livemode) {
    return null
  }

  return <div className={className}>{t('components.screen.test_mode_warning')}</div>
}

TestModeWarning.propTypes = {
  className: PropTypes.string,
}

export default memo(TestModeWarning)
