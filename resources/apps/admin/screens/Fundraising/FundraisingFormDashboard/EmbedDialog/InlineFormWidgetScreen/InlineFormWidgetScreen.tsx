import type { FC } from 'react'
import classNames from 'classnames'
import { CarouselButton, DialogProps } from '@/aerosol'
import {
  Button,
  CarouselLink,
  Column,
  Columns,
  DialogContent,
  DialogFooter,
  DialogHeader,
  Text,
  triggerToast,
} from '@/aerosol'
import { useTailwindBreakpoints } from '@/shared/hooks'
import InlineSVG from '@/screens/Fundraising/FundraisingFormDashboard/EmbedDialog/svgs/widget-inline-form.svg?react'
import styles from './InlineFormWidgetScreen.styles.scss'

interface Config {
  clientUrl: string
}

interface Props extends Pick<DialogProps, 'onClose'> {
  formId: string
}

const InlineFormWidgetScreen: FC<Props> = ({ formId, onClose }) => {
  const { extraSmall } = useTailwindBreakpoints()

  const pixelCode = `<div data-fundraising-form="${formId}" data-inline></div>`

  const handleCopyCode = () => {
    navigator.clipboard.writeText(pixelCode)

    triggerToast({
      type: 'success',
      header: 'Your code has been copied to your clipboard!',
      options: { containerId: 'embeddable-widgets' },
    })
  }

  const renderIcon = () =>
    extraSmall.greaterThan ? (
      <Column columnWidth='small'>
        <InlineSVG />
      </Column>
    ) : null

  return (
    <>
      <DialogHeader isPaddingless onClose={onClose} isMarginless>
        <Text type='h3' isBold isMarginless className='text-left pl-2'>
          Embeddable Components
        </Text>
      </DialogHeader>
      <DialogContent>
        <Columns isMarginless isResponsive={false} className={styles.root}>
          {renderIcon()}
          <Column>
            <Columns>
              <Column>
                <Text isBold type='h4'>
                  Inline Form
                </Text>
                <Text type='h5' isSecondaryColour>
                  Place your fundraising experience anywhere on your page.
                </Text>
              </Column>
            </Columns>
            <Columns>
              <Column>
                <Text isBold type='h5'>
                  Step 1
                </Text>
                <Text type='h5' isSecondaryColour>
                  Install{' '}
                  <CarouselLink indexToNavigate={1}>
                    <span className='hover:underline'>your Bridge Code</span>
                  </CarouselLink>
                  , if you haven't already.
                </Text>
              </Column>
            </Columns>
            <Columns>
              <Column>
                <Text isBold type='h5'>
                  Step 2
                </Text>
                <Text type='h5' isSecondaryColour>
                  Using the code tab in your website editor, place the following code where you want your form to
                  display:
                </Text>
                <span className={styles.embedCodeContainer}>{pixelCode}</span>
              </Column>
            </Columns>
          </Column>
        </Columns>
      </DialogContent>
      <DialogFooter className={styles.footer}>
        <Columns className={styles.buttons}>
          <Column columnWidth='small' className={classNames(styles.column, '!pr-0')}>
            <CarouselButton indexToNavigate={2} isClean>
              Go Back
            </CarouselButton>
          </Column>
          <Column className={styles.column} columnWidth='small'>
            <Button onClick={handleCopyCode}>Copy code</Button>
          </Column>
        </Columns>
      </DialogFooter>
    </>
  )
}

export { InlineFormWidgetScreen }
