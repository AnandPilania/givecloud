import type { FC } from 'react'
import type { FundraisingForm } from '@/types'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faBolt } from '@fortawesome/pro-regular-svg-icons'
import { Box, Column, Columns, Text, Button, triggerToast } from '@/aerosol'
import { useTailwindBreakpoints } from '@/shared/hooks'
import { useRestoreFundraisingFormMutation } from './useRestoreFundraisingFormMutation'
import { formatMoney } from '@/shared/utilities/formatMoney'
import styles from './DeletedFundraisingForm.styles.scss'

interface Props {
  form: FundraisingForm
}

const DeletedFundraisingForm: FC<Props> = ({ form }) => {
  const { medium } = useTailwindBreakpoints()
  const { mutate, isLoading } = useRestoreFundraisingFormMutation()

  const { id, name, stats, previewImageUrl } = form
  const { donorCount, revenueAmount, currency } = stats!

  const handleRestoreForm = () => {
    mutate(id, {
      onSuccess: () => {
        triggerToast({
          type: 'success',
          header: `${name} Restored!`,
          buttonProps: {
            children: 'View',
            to: `/fundraising/forms/${id}`,
            'aria-label': `View ${name}`,
          },
        })
      },
    })
  }

  const renderPreviewImg = () =>
    medium.greaterThan ? (
      <Column columnWidth='two'>
        <div className={styles.imageContainer}>
          <img src={previewImageUrl} alt='form preview image' className={styles.image} />
        </div>
      </Column>
    ) : null

  return (
    <Box isOverflowVisible isReducedPadding data-testid='deleted-form-panel'>
      <Columns isMarginless className='items-center'>
        {renderPreviewImg()}
        <Column>
          <Text className={styles.header} isTruncated type='h5' isBold>
            {name}
          </Text>
          <Text isSecondaryColour isMarginless isBold>
            <FontAwesomeIcon icon={faBolt} className='mr-2' />
            Standard Experience
          </Text>
        </Column>
        <Columns className='w-full' isMarginless isResponsive={false} isStackingOnMobile={false}>
          <Column columnWidth='four' className='justify-center'>
            <Text isSecondaryColour type='h5' isMarginless isBold>
              {donorCount}
            </Text>
            <Text isSecondaryColour isMarginless type='footnote' isBold className='uppercase'>
              Donor
            </Text>
          </Column>
          <Column columnWidth='six' className='justify-center'>
            <Text isSecondaryColour type='h5' isMarginless isBold>
              {formatMoney({ amount: revenueAmount!, currency, digits: 0, showZero: true })}
            </Text>
            <Text isSecondaryColour isMarginless type='footnote' isBold className='uppercase'>
              Revenue ({currency})
            </Text>
          </Column>
        </Columns>
        <Column columnWidth='small'>
          <Button aria-label={`Restore ${name}`} isOutlined onClick={handleRestoreForm} isLoading={isLoading}>
            Restore
          </Button>
        </Column>
      </Columns>
    </Box>
  )
}

export { DeletedFundraisingForm }
