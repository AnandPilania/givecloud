import type { FC } from 'react'
import type { DialogProps } from '@/aerosol'
import {
  Badge,
  Box,
  CarouselButton,
  Column,
  Columns,
  DialogContent,
  DialogFooter,
  DialogHeader,
  Text,
  CarouselLink,
} from '@/aerosol'
import InlineFormSVG from '@/screens/Fundraising/FundraisingFormDashboard/EmbedDialog/svgs/widget-inline-form.svg?react'
import HonorRollSVG from '@/screens/Fundraising/FundraisingFormDashboard/EmbedDialog/svgs/widget-honor-roll.svg?react'
import ThermometerSVG from '@/screens/Fundraising/FundraisingFormDashboard/EmbedDialog/svgs/widget-thermometer.svg?react'
import PopupSVG from '@/screens/Fundraising/FundraisingFormDashboard/EmbedDialog/svgs/widget-popup.svg?react'
import { useTailwindBreakpoints } from '@/shared/hooks'
import { widgets } from '@/screens/Fundraising/FundraisingFormDashboard/EmbedDialog/widgets'
import styles from './EmbedSelectionScreen.styles.scss'

type Props = Pick<DialogProps, 'onClose'>

type WidgetType = typeof widgets[number]['type']

const widgetsMap = {
  popup: <PopupSVG />,
  inline: <InlineFormSVG />,
  thermometer: <ThermometerSVG />,
  honorRoll: <HonorRollSVG />,
}

const EmbedSelectionScreen: FC<Props> = ({ onClose }) => {
  const { extraSmall, small } = useTailwindBreakpoints()

  const renderMobileBadge = (isAvailable: boolean) =>
    small.lessThan && !isAvailable ? (
      <Badge theme='secondary' className='ml-2'>
        Coming soon
      </Badge>
    ) : null

  const renderColumnContent = (isAvailable: boolean, index: number) =>
    isAvailable ? (
      <CarouselButton indexToNavigate={index}>Choose</CarouselButton>
    ) : extraSmall.greaterThan ? (
      <Badge theme='secondary'>Coming soon</Badge>
    ) : null

  const renderSvg = (type: WidgetType) =>
    extraSmall.greaterThan ? (
      <Column columnWidth='small'>
        <div className={styles.svgContainer}>{widgetsMap[type]}</div>
      </Column>
    ) : null

  const renderWidgets = () =>
    widgets.map(({ type, title, subtitle, isAvailable }, index) => {
      const indexToNavigate = index + 3

      return (
        <Box key={index} isReducedPadding>
          <Columns isResponsive={false} isMarginless className='items-center'>
            {renderSvg(type)}
            <Column>
              <div className='flex'>
                <Text isBold type='h5'>
                  {title}
                </Text>
                {renderMobileBadge(isAvailable)}
              </div>
              <Text isSecondaryColour type='h5'>
                {subtitle}
              </Text>
            </Column>
            <Column columnWidth='small'>{renderColumnContent(isAvailable, indexToNavigate)}</Column>
          </Columns>
        </Box>
      )
    })

  return (
    <>
      <DialogHeader isMarginless isPaddingless onClose={onClose}>
        <Text isBold isMarginless type='h3'>
          Embeddable Components
        </Text>
      </DialogHeader>
      <DialogContent className={styles.content}>{renderWidgets()}</DialogContent>
      <DialogFooter className={styles.footer}>
        <CarouselLink indexToNavigate={1}>
          <span className='hover:underline'>Install your Bridge Code before you start</span>
        </CarouselLink>
      </DialogFooter>
    </>
  )
}

export { EmbedSelectionScreen }
