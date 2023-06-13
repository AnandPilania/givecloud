import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faLink, faCopy } from '@fortawesome/pro-regular-svg-icons'
import PropTypes from 'prop-types'
import { ClickableBox, Column, Columns, Text } from '@/aerosol'
import { useTailwindBreakpoints } from '@/shared/hooks'

const ShareShortUrlPanel = ({ onClick }) => {
  const { small, medium } = useTailwindBreakpoints()

  const renderIcon = () => {
    if (small.lessThan) return null
    return (
      <Column
        isPaddingless={small.greaterThan}
        columnWidth={medium.greaterThan ? 'one' : 'small'}
        className='justify-center'
      >
        <FontAwesomeIcon icon={faLink} size='2x' />
      </Column>
    )
  }

  return (
    <ClickableBox isReducedPadding={small.lessThan} icon={faCopy} onClick={onClick}>
      <Columns isMarginless isResponsive={false} isStackingOnMobile={false}>
        {renderIcon()}
        <Column columnWidth='five' className='text-left'>
          <Text type='h4' isBold isMarginless>
            Share Your Link
          </Text>
        </Column>
      </Columns>
    </ClickableBox>
  )
}

ShareShortUrlPanel.propTypes = {
  onClick: PropTypes.func,
}

export { ShareShortUrlPanel }
