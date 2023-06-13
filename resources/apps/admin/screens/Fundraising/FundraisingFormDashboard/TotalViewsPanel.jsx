import PropTypes from 'prop-types'
import { Box, Column, Columns, Text } from '@/aerosol'
import { SparkLine } from '@/screens/Fundraising/SparkLine'
import { StatTrend } from './StatTrend'
import { useTailwindBreakpoints } from '@/shared/hooks'

const TotalViewsPanel = ({ views, trends }) => {
  const { large, small } = useTailwindBreakpoints()

  const renderFigure = () => views ?? 0

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
      <Columns className='h-full items-end'>
        <Column className='self-center'>
          <Text type='h5'>Views</Text>
          <Text type='h2' isBold isMarginless={trends?.trend === -1} isSecondaryColour={!views}>
            {renderFigure()}
          </Text>
          <StatTrend trend={trends?.trend} />
        </Column>
        {renderChart()}
      </Columns>
    </Box>
  )
}

TotalViewsPanel.propTypes = {
  views: PropTypes.number,
  trends: PropTypes.object,
}

export { TotalViewsPanel }
