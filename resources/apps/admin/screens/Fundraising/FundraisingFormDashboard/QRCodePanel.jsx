import PropTypes from 'prop-types'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faQrcode } from '@fortawesome/pro-regular-svg-icons'
import { ClickableBox, Column, Columns, Text } from '@/aerosol'
import { useTailwindBreakpoints } from '@/shared/hooks'

const QRCodePanel = ({ href, target }) => {
  const { small, medium } = useTailwindBreakpoints()

  const renderIcon = () => {
    if (small.lessThan) return null
    return (
      <Column
        isPaddingless={small.greaterThan}
        columnWidth={medium.greaterThan ? 'one' : 'small'}
        className='justify-center'
      >
        <FontAwesomeIcon icon={faQrcode} size='2x' />
      </Column>
    )
  }

  return href ? (
    <ClickableBox isReducedPadding={small.lessThan} href={href} target={target}>
      <Columns isMarginless isResponsive={false} isStackingOnMobile={false}>
        {renderIcon()}
        <Column columnWidth='five' className='text-left'>
          <Text type='h4' isBold isMarginless>
            Promote a QR Code
          </Text>
        </Column>
      </Columns>
    </ClickableBox>
  ) : null
}

QRCodePanel.propTypes = {
  href: PropTypes.string,
  target: PropTypes.string,
}

export { QRCodePanel }
