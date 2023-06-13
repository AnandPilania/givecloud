import type { FC } from 'react'
import { DialogProps, ToastContainer } from '@/aerosol'
import { Carousel, CarouselItem, CarouselItems, Dialog } from '@/aerosol'
import { OnboardingIntroScreen } from './OnboardingIntroScreen'
import { OnboardingPixelScreen } from './OnboardingPixelScreen'
import { EmbedSelectionScreen } from './EmbedSelectionScreen'
import { InlineFormWidgetScreen } from './InlineFormWidgetScreen'
import { InstantPopUpWidgetScreen } from './InstantPopUpWidgetScreen'
import { useOnboardingState } from './useOnBoardingState'
import styles from './EmbedDialog.styles.scss'
interface Props extends Pick<DialogProps, 'isOpen' | 'onClose'> {
  formId: string
}

const EmbedDialog: FC<Props> = ({ isOpen, onClose, formId }) => {
  const {
    onBoardingState: { userShowFundraisingPixelInstructions },
  } = useOnboardingState()

  const initIndex = userShowFundraisingPixelInstructions ? 0 : 2

  return (
    <Dialog isOpen={isOpen} onClose={onClose}>
      <Carousel initialIndex={initIndex} name='embeddable widgets'>
        <CarouselItems>
          <CarouselItem className={styles.root} isPaddingless>
            <OnboardingIntroScreen onClose={onClose} />
          </CarouselItem>
          <CarouselItem className={styles.root} isPaddingless>
            <OnboardingPixelScreen onClose={onClose} />
          </CarouselItem>
          <CarouselItem className={styles.root} isPaddingless>
            <EmbedSelectionScreen onClose={onClose} />
          </CarouselItem>
          <CarouselItem className={styles.root} isPaddingless>
            <InstantPopUpWidgetScreen formId={formId} onClose={onClose} />
          </CarouselItem>
          <CarouselItem className={styles.root} isPaddingless>
            <InlineFormWidgetScreen formId={formId} onClose={onClose} />
          </CarouselItem>
        </CarouselItems>
      </Carousel>
      <ToastContainer containerId='embeddable-widgets' />
    </Dialog>
  )
}

export { EmbedDialog }
