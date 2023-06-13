import PropTypes from 'prop-types'
import { useRecoilValue } from 'recoil'
import { Box, Columns, Column, Text } from '@/aerosol'
import { SparkLine } from '@/screens/Fundraising/SparkLine'
import { StatTrend } from './StatTrend'
import { formatMoney } from '@/shared/utilities/formatMoney'
import getConfig from '@/utilities/config'
import { useTailwindBreakpoints } from '@/shared/hooks'
import config from '@/atoms/config'

const TotalRevenuePanel = ({ revenue, trends }) => {
  const { currency } = getConfig()
  const { large, small } = useTailwindBreakpoints()

  const {
    currency: { code: currencyCode },
  } = useRecoilValue(config)

  const renderFigure = () =>
    revenue
      ? formatMoney({ amount: revenue, digits: 0, currency: currency.code })
      : formatMoney({ amount: 0, digits: 0, showZero: true, currency: currency.code })

  const renderChart = () => {
    if (large.lessThan) return null
    if (trends?.lastPeriod > 0) {
      return (
        <Column>
          <SparkLine data={trends?.data} />
        </Column>
      )
    }
    return (
      <Column className='self-center'>
        <Text isMarginless type='h5'>
          No data available
        </Text>
      </Column>
    )
  }

  return (
    <Box isMarginless isFullHeight isReducedPadding={small.lessThan}>
      <Columns className='h-full items-end' isResponsive={false}>
        <Column className='self-center'>
          <Text type='h5' className='whitespace-pre-wrap'>
            Revenue ({currencyCode})
          </Text>
          <Text type='h2' isBold isMarginless={trends?.trend === -1} isSecondaryColour={!revenue}>
            {renderFigure()}
          </Text>
          <StatTrend trend={trends?.trend} />
        </Column>
        {renderChart()}
      </Columns>
    </Box>
  )
}

TotalRevenuePanel.propTypes = {
  revenue: PropTypes.number,
  trends: PropTypes.object,
}

export { TotalRevenuePanel }
