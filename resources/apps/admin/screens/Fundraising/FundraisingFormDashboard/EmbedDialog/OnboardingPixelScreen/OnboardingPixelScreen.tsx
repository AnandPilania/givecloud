import type { FC } from 'react'
import type { DialogProps } from '@/aerosol'
import { useRecoilValue } from 'recoil'
import { useOnBoardingMutation } from './useOnBoardingPixelMutation'
import { useOnboardingState } from '@/screens/Fundraising/FundraisingFormDashboard/EmbedDialog/useOnBoardingState'
import classNames from 'classnames'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faCode } from '@fortawesome/free-solid-svg-icons'
import {
  Button,
  Column,
  Columns,
  CarouselNextButton,
  DialogContent,
  DialogFooter,
  DialogHeader,
  Text,
  triggerToast,
} from '@/aerosol'
import { Link } from '@/components/Link'
import { useTailwindBreakpoints } from '@/shared/hooks'
import configState from '@/atoms/config'
import styles from './OnboardingPixelScreen.styles.scss'

interface Config {
  clientUrl: string
}

type Props = Pick<DialogProps, 'onClose'>

const OnboardingPixelScreen: FC<Props> = ({ onClose }) => {
  const { extraSmall, medium } = useTailwindBreakpoints()
  const { clientUrl } = useRecoilValue<Config>(configState)
  const { setOnBoardingState, onBoardingState } = useOnboardingState()

  const { mutate } = useOnBoardingMutation({
    onSuccess: () => setOnBoardingState({ userShowFundraisingPixelInstructions: false }),
  })

  const pixelCode = `<script src="${clientUrl}/v1/widgets.js" async></script>  `

  const handleCopyCode = () => {
    navigator.clipboard.writeText(pixelCode)

    triggerToast({
      type: 'success',
      header: 'Your pixel code has been copied to your clipboard!',
      options: { containerId: 'embeddable-widgets' },
    })
  }

  const handleOnBoarding = () => {
    if (onBoardingState.userShowFundraisingPixelInstructions) {
      mutate({
        metadata: {
          show_fundraising_pixel_instructions: false,
        },
      })
    }
  }

  const renderIcon = () =>
    extraSmall.greaterThan ? (
      <Column columnWidth='small' className={styles.iconColumn}>
        <span className={styles.iconContainer}>
          <FontAwesomeIcon icon={faCode} size='2x' aria-hidden='true' />
        </span>
      </Column>
    ) : null

  return (
    <>
      <DialogHeader isPaddingless isMarginless onClose={onClose}>
        <Text type='h3' isBold isMarginless>
          Embeddable Components
        </Text>
      </DialogHeader>
      <DialogContent>
        <Columns isMarginless isResponsive={false} className={styles.root}>
          {renderIcon()}
          <Column>
            <Text type='h4' isBold>
              Install Your Bridge Code
            </Text>
            <Text
              type='h5'
              isSecondaryColour
              isMarginless
            >{`Place the following code in the <head> tag of your website`}</Text>
            <span className={styles.embedCodeContainer}>{pixelCode}</span>
            <Button isOutlined size='small' className={styles.copyButton} onClick={handleCopyCode}>
              Copy Code
            </Button>
            <Text isBold type='h5'>
              Need help?
            </Text>
            <Text type='h5' isSecondaryColour>
              View instructions specific to your website builder:
            </Text>
            <Columns isResponsive={false} isStackingOnMobile={false}>
              <Column>
                <Link
                  href='https://help.givecloud.com/en/articles/6988542-wordpress-givecloud'
                  target='_blank'
                  className={styles.link}
                >
                  <Text type='h5' isMarginless>
                    Wordpress
                  </Text>
                </Link>
                <Link
                  href='https://help.givecloud.com/en/articles/7313025-givecloud-donations-on-wix'
                  target='_blank'
                  className={styles.link}
                >
                  <Text type='h5' isMarginless>
                    Wix
                  </Text>
                </Link>
              </Column>
              <Column>
                <Link
                  href='https://help.givecloud.com/en/articles/7312681-givecloud-donations-on-squarespace'
                  target='_blank'
                  className={styles.link}
                >
                  <Text type='h5' isMarginless>
                    SquareSpace
                  </Text>
                </Link>
                <Link
                  href='https://help.givecloud.com/en/articles/7313280-givecloud-donations-on-your-website'
                  target='_blank'
                  className={styles.link}
                >
                  <Text type='h5' isMarginless>
                    Other / Custom
                  </Text>
                </Link>
              </Column>
            </Columns>
          </Column>
        </Columns>
      </DialogContent>
      <DialogFooter className={styles.footer}>
        <CarouselNextButton onClick={handleOnBoarding} className={classNames(medium.lessThan && 'w-full')}>
          Continue Embedding
        </CarouselNextButton>
      </DialogFooter>
    </>
  )
}

export { OnboardingPixelScreen }
