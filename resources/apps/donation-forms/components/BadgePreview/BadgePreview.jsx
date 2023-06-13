import { memo } from 'react'
import { useRecoilValue } from 'recoil'
import classnames from 'classnames'
import PropTypes from 'prop-types'
import { AnimatePresence } from 'framer-motion'
import Badge from './components/Badge/Badge'
import configState from '@/atoms/config'
import amountSelectorState from '@/atoms/amountSelector'
import formInputState from '@/atoms/formInput'
import styles from './BadgePreview.scss'

const BadgePreview = ({ className }) => {
  const config = useRecoilValue(configState)
  const formInput = useRecoilValue(formInputState)
  const { amountChanged } = useRecoilValue(amountSelectorState)

  const percentageMap = {
    50: 40,
    150: 15,
    250: 10,
    500: 5,
    1000: 2,
    2500: 1,
    5000: 0.1,
  }

  const getPercentage = () => {
    const amount = formInput.item.amt

    return Object.keys(percentageMap).reduce((previousValue, value) => {
      return amount >= value ? percentageMap[value] : previousValue
    }, 0)
  }

  const percentage = getPercentage()

  const showBadgePreview = (value) => amountChanged && percentage === value

  return (
    config.badges.enabled && (
      <div className={classnames(styles.root, className)}>
        <AnimatePresence>
          {Object.values(percentageMap).map(
            (percentage) => showBadgePreview(percentage) && <Badge key={percentage} percentage={percentage} />
          )}
        </AnimatePresence>
      </div>
    )
  )
}

BadgePreview.propTypes = {
  className: PropTypes.string,
}

export default memo(BadgePreview)
