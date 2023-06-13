import type { ComponentPropsWithRef, FC } from 'react'
import type { AccordionProps } from '@/aerosol/Accordion'
import { forwardRef } from 'react'
import {
  Accordion,
  AccordionContent,
  AccordionHeader,
  Alert,
  Column,
  Columns,
  RadioButton,
  RadioGroup,
  RadioTile,
  SlideTransition,
  Text,
} from '@/aerosol'
import { useFundraisingFormState } from '@/screens/Fundraising/useFundraisingFormState'
import { useTailwindBreakpoints } from '@/shared/hooks'
import styles from './TodayAndMonthlyAccordion.styles.scss'

type Props = Pick<AccordionProps, 'isOpen' | 'setIsOpen'> & Pick<ComponentPropsWithRef<'input'>, 'ref'>

const TodayAndMonthlyAccordion: FC<Props> = forwardRef(({ isOpen, setIsOpen }, ref) => {
  const { medium, small } = useTailwindBreakpoints()
  const { todayAndMonthlyValue, setTodayAndMonthlyState } = useFundraisingFormState()

  const handleTypeChange = (billingPeriods: string) => {
    setTodayAndMonthlyState({
      billingPeriods,
    })
  }

  return (
    <Accordion isOpen={isOpen} setIsOpen={setIsOpen} hasBorderTop className={styles.root}>
      <AccordionHeader>
        <Text isMarginless isBold type='h5'>
          Today And Monthly
        </Text>
      </AccordionHeader>
      <AccordionContent>
        <Text className='mb-4' isSecondaryColour>
          Configure the frequency of donations
        </Text>
        <SlideTransition isOpen={todayAndMonthlyValue.billingPeriods === 'today_only|monthly'}>
          <Alert type='info' iconPosition='center'>
            <Columns isMarginless>
              <Column columnWidth='six'>
                <Text isMarginless isBold>
                  Setting monthly as the default can increase monthly donations by 10%.
                </Text>
              </Column>
            </Columns>
          </Alert>
        </SlideTransition>
        <RadioGroup
          showInput={false}
          name='billingPeriods'
          label='today and monthly'
          isLabelVisible={false}
          checkedValue={todayAndMonthlyValue.billingPeriods}
          onChange={handleTypeChange}
          className='mb-2'
        >
          <Columns
            isWrapping={medium.greaterThan || small.lessThan}
            isResponsive={medium.lessThan}
            isStackingOnMobile={small.lessThan}
            isMarginless
          >
            <Column isPaddingless>
              <RadioButton
                isMarginless
                className={styles.buttonOne}
                ref={ref}
                id='monthly|today_only'
                value='monthly|today_only'
              >
                <RadioTile>
                  <Text isBold>Today & Monthly</Text>
                  <Text type='footnote' isMarginless isSecondaryColour>
                    (Monthly as default)
                  </Text>
                </RadioTile>
              </RadioButton>
            </Column>
            <Column isPaddingless>
              <RadioButton className={styles.buttonTwo} isMarginless id='today_only|monthly' value='today_only|monthly'>
                <RadioTile>
                  <Text isBold>Today & Monthly</Text>
                  <Text type='footnote' isMarginless isSecondaryColour>
                    (Today as default)
                  </Text>
                </RadioTile>
              </RadioButton>
            </Column>
            <Column isPaddingless>
              <RadioButton isMarginless className={styles.buttonThree} id='today_only' value='today_only'>
                <RadioTile>
                  <Text isBold>Today only</Text>
                </RadioTile>
              </RadioButton>
            </Column>
            <Column isPaddingless>
              <RadioButton className={styles.buttonFour} isMarginless id='monthly' value='monthly'>
                <RadioTile>
                  <Text isBold>Monthly only</Text>
                </RadioTile>
              </RadioButton>
            </Column>
          </Columns>
        </RadioGroup>
      </AccordionContent>
    </Accordion>
  )
})

TodayAndMonthlyAccordion.displayName = 'TodayAndMonthlyAccordion'

export { TodayAndMonthlyAccordion }
