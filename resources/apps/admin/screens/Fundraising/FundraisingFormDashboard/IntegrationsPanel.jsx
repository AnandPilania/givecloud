import PropTypes from 'prop-types'
import { ClickableBox, Column, Columns, Text } from '@/aerosol'
import { useTailwindBreakpoints } from '@/shared/hooks'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faRightLeft } from '@fortawesome/pro-regular-svg-icons'

const IntegrationsPanel = ({ to }) => {
  const { small, medium } = useTailwindBreakpoints()

  const renderIcon = () => {
    if (small.lessThan) {
      return null
    }

    return (
      <Column
        isPaddingless={small.greaterThan}
        columnWidth={medium.greaterThan ? 'one' : 'small'}
        className='justify-center'
      >
        <FontAwesomeIcon icon={faRightLeft} size='2x' />
      </Column>
    )
  }

  return (
    <ClickableBox isReducedPadding={small.lessThan} to={to} isMarginless>
      <Columns isMarginless isResponsive={false} isStackingOnMobile={false}>
        {renderIcon()}
        <Column columnWidth='five' className='text-left'>
          <Text type='h4' isBold isMarginless>
            Integrations
          </Text>
        </Column>
      </Columns>
    </ClickableBox>
  )
}

IntegrationsPanel.propTypes = {
  to: PropTypes.object,
}

export { IntegrationsPanel }
