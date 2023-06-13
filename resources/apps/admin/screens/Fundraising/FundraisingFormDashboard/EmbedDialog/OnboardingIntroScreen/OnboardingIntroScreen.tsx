import type { FC } from 'react'
import type { DialogProps } from '@/aerosol'
import classNames from 'classnames'
import { CarouselNextButton, DialogContent, DialogFooter, DialogHeader, Text } from '@/aerosol'
import { useTailwindBreakpoints } from '@/shared/hooks'
import { WidgetPreviewSVG } from '@/screens/Fundraising/FundraisingFormDashboard/EmbedDialog/svgs'

import styles from './OnboardingIntroScreen.styles.scss'

type Props = Pick<DialogProps, 'onClose'>

const OnboardingIntroScreen: FC<Props> = ({ onClose }) => {
  const { medium } = useTailwindBreakpoints()

  return (
    <>
      <DialogHeader isPaddingless isMarginless onClose={onClose} data-testid='embed-intro' />
      <DialogContent className={styles.root}>
        <WidgetPreviewSVG className={styles.previewImage} />
        <Text isBold type='h4'>
          The Power of Givecloud - Embedded Anywhere
        </Text>
        <Text type='h5'>
          Your Givecloud Bridge Code allows you to display engaging fundraising components throughout your website.
        </Text>
      </DialogContent>
      <DialogFooter className={styles.footer}>
        <CarouselNextButton className={classNames(medium.lessThan && 'w-full')}>Start</CarouselNextButton>
      </DialogFooter>
    </>
  )
}

export { OnboardingIntroScreen }
