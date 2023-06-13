import { useRecoilValue } from 'recoil'
import PropTypes from 'prop-types'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faBolt } from '@fortawesome/pro-regular-svg-icons'
import { faCheckCircle } from '@fortawesome/free-solid-svg-icons'
import { ClickableBox, Column, Columns, Text } from '@/aerosol'
import { formatMoney } from '@/shared/utilities/formatMoney'
import config from '@/atoms/config'
import { useTailwindBreakpoints } from '@/shared/hooks'
import { SparkLine } from '@/screens/Fundraising/SparkLine'
import styles from './FundraisingForm.scss'

const FundraisingForm = ({ form }) => {
  const { medium } = useTailwindBreakpoints()

  const {
    name,
    stats: { donorCount, revenueAmount, currency, trends },
    isDefaultForm,
    previewImageUrl,
  } = form

  const {
    currency: { code: currencyCode },
  } = useRecoilValue(config)

  const renderDefaultFormIcon = () =>
    isDefaultForm ? (
      <>
        <span className='sr-only'>Default experience</span>
        <FontAwesomeIcon icon={faCheckCircle} className={styles.defaultFormIcon} aria-hidden='true' />
      </>
    ) : null

  const renderPreviewImg = () =>
    medium.greaterThan ? (
      <Column columnWidth='two'>
        <div className={styles.imageContainer}>
          <img src={previewImageUrl} alt='form preview image' className={styles.image} />
        </div>
      </Column>
    ) : null

  const renderSparkLine = () => {
    return medium.greaterThan && trends?.revenues?.lastPeriod > 0 ? <SparkLine data={trends?.revenues?.data} /> : null
  }

  return (
    <ClickableBox dataTestId='fundraising-form-panel' isReducedPadding to={`/fundraising/forms/${form.id}`}>
      <Columns isMarginless isResponsive={false}>
        {renderPreviewImg()}
        <Column className='justify-center'>
          <div className={styles.textWrapper}>
            {renderDefaultFormIcon()}
            <Text isTruncated isMarginless type='h5' isBold className={styles.text}>
              {name}
            </Text>
          </div>
          <Text isSecondaryColour isMarginless isBold>
            <FontAwesomeIcon icon={faBolt} className='mr-2' />
            Standard Experience
          </Text>
        </Column>
        <Columns className='w-full' isMarginless isResponsive={false} isStackingOnMobile={false}>
          <Column columnWidth='one' className='justify-center'>
            <Text isSecondaryColour={!donorCount} type='h5' isMarginless isBold>
              {donorCount}
            </Text>
            <Text isSecondaryColour={!donorCount} isMarginless type='footnote' isBold className='uppercase'>
              Donors
            </Text>
          </Column>
          <Column columnWidth='two' className='justify-center'>
            <Text isSecondaryColour={!revenueAmount} type='h5' isMarginless isBold>
              {formatMoney({ amount: revenueAmount, currency, digits: 0, showZero: true })}
            </Text>
            <Text isSecondaryColour={!revenueAmount} isMarginless type='footnote' isBold className='uppercase'>
              Revenue ({currencyCode})
            </Text>
          </Column>
          <Column columnWidth='two' className={styles.sparklineContainer}>
            {renderSparkLine()}
          </Column>
        </Columns>
      </Columns>
    </ClickableBox>
  )
}

FundraisingForm.propTypes = {
  form: PropTypes.object.isRequired,
}

export { FundraisingForm }
