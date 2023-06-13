import PropTypes from 'prop-types'
import { Box, Text } from '@/aerosol'
import { StatTrend } from './StatTrend'
import { useTailwindBreakpoints } from '@/shared/hooks'

const TotalDonorsPanel = ({ donors, trend }) => {
  const { small } = useTailwindBreakpoints()

  const renderFigure = () => (donors ? donors : 0)

  return (
    <Box isMarginless isFullHeight isReducedPadding={small.lessThan}>
      <Text type='h5'>Donors</Text>
      <Text type='h2' isBold isSecondaryColour={!donors}>
        {renderFigure()}
      </Text>
      <StatTrend trend={trend} />
    </Box>
  )
}

TotalDonorsPanel.propTypes = {
  donors: PropTypes.number,
  trend: PropTypes.number,
}

export { TotalDonorsPanel }
