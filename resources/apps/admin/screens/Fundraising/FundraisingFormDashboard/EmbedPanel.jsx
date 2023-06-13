import PropTypes from 'prop-types'
import { ClickableBox, Column, Columns, Text } from '@/aerosol'
import { useTailwindBreakpoints } from '@/shared/hooks'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faBrowser } from '@fortawesome/pro-solid-svg-icons'

const EmbedPanel = ({ to }) => {
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
        <FontAwesomeIcon icon={faBrowser} size='2x' />
      </Column>
    )
  }

  return (
    <ClickableBox isReducedPadding={small.lessThan} to={to}>
      <Columns isMarginless isResponsive={false} isStackingOnMobile={false}>
        {renderIcon()}
        <Column columnWidth='five' className='text-left'>
          <Text type='h4' isBold isMarginless>
            Website Embed
          </Text>
        </Column>
      </Columns>
    </ClickableBox>
  )
}

EmbedPanel.propTypes = {
  to: PropTypes.object,
}

export { EmbedPanel }
