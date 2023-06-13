import PropTypes from 'prop-types'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faArrowDown, faArrowUp, faInfoCircle } from '@fortawesome/pro-regular-svg-icons'
import { Text, Tooltip } from '@/aerosol'
import classNames from 'classnames'

const isPositiveFloat = (float) => (Math.sign(float) === 1 ? true : false)
const getWholeNumber = (float) => Math.round(Math.abs(float))

const StatTrend = ({ trend }) => {
  const isPositive = isPositiveFloat(trend)
  const css = classNames(isPositive ? 'text-green-600' : 'text-red-700')
  const icon = isPositive ? faArrowUp : faArrowDown

  const tooltipContent = (
    <Text isMarginless isBold>
      This shows the trend from the last 30 days.
    </Text>
  )

  if (trend === -1) return null

  return (
    <div className='flex items-center'>
      <Text className={css} isMarginless>
        <FontAwesomeIcon icon={icon} /> {getWholeNumber(trend)}%
      </Text>
      <Tooltip tooltipContent={tooltipContent}>
        <FontAwesomeIcon icon={faInfoCircle} className={classNames(css, 'ml-2')} />
      </Tooltip>
    </div>
  )
}

StatTrend.propTypes = {
  trend: PropTypes.number,
}

export { StatTrend }
