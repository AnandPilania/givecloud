import PropTypes from 'prop-types'
import { Box, Text } from '@/aerosol'
import { StatTrend } from './StatTrend'
import { useTailwindBreakpoints } from '@/shared/hooks'

const TotalConversionsPanel = ({ conversion, trend }) => {
  const { small } = useTailwindBreakpoints()

  const renderFigure = () => (conversion > -1 ? `${Math.round(conversion)}%` : '0%')

  return (
    <Box isMarginless isFullHeight isReducedPadding={small.lessThan}>
      <Text type='h5'>Conversions</Text>
      <Text type='h2' isBold isSecondaryColour={conversion === -1}>
        {renderFigure()}
      </Text>
      <StatTrend trend={trend} />
    </Box>
  )
}

TotalConversionsPanel.propTypes = {
  conversion: PropTypes.number,
  trend: PropTypes.number,
}

export { TotalConversionsPanel }
