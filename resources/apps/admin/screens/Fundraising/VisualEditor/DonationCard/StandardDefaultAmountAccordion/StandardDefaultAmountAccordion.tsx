import type { ComponentPropsWithRef, FC } from 'react'
import type { AccordionProps } from '@/aerosol/Accordion'
import { useRef, useEffect, forwardRef } from 'react'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faExclamationCircle } from '@fortawesome/pro-regular-svg-icons'
import {
  Column,
  Columns,
  Accordion,
  AccordionContent,
  AccordionHeader,
  CurrencyInput,
  RadioButton,
  RadioGroup,
  Text,
} from '@/aerosol'
import { useFundraisingFormState } from '@/screens/Fundraising/useFundraisingFormState'
import { useFocus } from '@/shared/hooks'
import { formatMoney } from '@/shared/utilities/formatMoney'
import getConfig from '@/utilities/config'
import styles from './StandardDefaultAmountAccordion.styles.scss'

type Props = Pick<AccordionProps, 'isOpen' | 'setIsOpen'> & Pick<ComponentPropsWithRef<'input'>, 'ref'>

const StandardDefaultAmountAccordion: FC<Props> = forwardRef(({ isOpen, setIsOpen }, ref) => {
  const { currency } = getConfig()
  const { defaultAmountValue: state, setDefaultAmountState, isDefaultAmountError } = useFundraisingFormState()
  const { isCustomAmountInputTouched, defaultAmountErrors } = state
  const [inputRef, setInputFocus] = useFocus<HTMLInputElement>()
  const isFocused = useRef(false)
  const defaultMinAmount = formatMoney({ amount: 5, digits: 0, currency: currency.code })

  useEffect(() => {
    if (isFocused.current) {
      setInputFocus()
    }
  }, [isFocused.current])

  useEffect(() => {
    if (state.defaultAmountType === 'custom' && state.defaultAmountValue < 5) {
      setDefaultAmountState({
        ...state,
        defaultAmountErrors: [`Sorry, custom amounts be must at least ${defaultMinAmount}.`],
        isCustomAmountInputTouched: true,
      })
    }
  }, [state.defaultAmountType])

  const handleTypeChange = (defaultAmountType: string) => {
    defaultAmountType === 'custom' ? (isFocused.current = true) : (isFocused.current = false)
    setDefaultAmountState({
      ...state,
      defaultAmountType,
      isCustomAmountInputTouched: false,
    })
  }

  const handleValueChange = ({ value: defaultAmountValue }) => {
    if (defaultAmountValue < 5) {
      setDefaultAmountState({
        ...state,
        defaultAmountValue,
        defaultAmountErrors: [`Sorry, custom amounts must be at least ${defaultMinAmount}.`],
        isCustomAmountInputTouched: false,
      })
    } else {
      setDefaultAmountState({
        ...state,
        defaultAmountValue,
        defaultAmountErrors: [],
      })
    }
  }

  const handleBlur = () => setDefaultAmountState({ ...state, isCustomAmountInputTouched: true })

  const getErrors = () => (isCustomAmountInputTouched ? defaultAmountErrors : [])

  const getRef = (reference: string) => (reference === state.defaultAmountType ? ref : null)

  const renderErrorIcon = () =>
    isDefaultAmountError ? <FontAwesomeIcon className={styles.icon} icon={faExclamationCircle} /> : null

  return (
    <Accordion isOpen={isOpen} setIsOpen={setIsOpen} className={styles.root} hasBorderTop>
      <AccordionHeader>
        <Text isError={isDefaultAmountError} isMarginless isBold type='h5'>
          Default Amount
          {renderErrorIcon()}
        </Text>
      </AccordionHeader>
      <AccordionContent>
        <Text isSecondaryColour className='mb-6'>
          Choose a default donation amount that will initially be shown to your donors.
        </Text>
        <RadioGroup
          name='defaultAmountOptions'
          label='Default amount options'
          isLabelVisible={false}
          checkedValue={state.defaultAmountType}
          onChange={handleTypeChange}
        >
          <Columns isMarginless isWrapping>
            <Column isPaddingless columnWidth='six'>
              <RadioButton
                ref={getRef('automatic')}
                id='default-amount-option-1'
                label='Automatic'
                description={`Givecloud's technology will determine the right amount to present to the donor based on their device, geo-location, and more.`}
                value='automatic'
              />
            </Column>
            <Column isPaddingless columnWidth='six'>
              <RadioButton
                ref={getRef('custom')}
                id='default-amount-option-2'
                label='Custom'
                description={`A minimum of ${defaultMinAmount} is required.`}
                value='custom'
              >
                <CurrencyInput
                  currency={currency.code}
                  ref={inputRef}
                  isMarginless
                  name='customDefaultAmount'
                  label='Custom default amount'
                  isLabelHidden
                  onChange={handleValueChange}
                  value={state.defaultAmountValue}
                  errors={getErrors()}
                  onBlur={handleBlur}
                />
              </RadioButton>
            </Column>
          </Columns>
        </RadioGroup>
      </AccordionContent>
    </Accordion>
  )
})

StandardDefaultAmountAccordion.displayName = 'StandardDefaultAmountAccordion'

export { StandardDefaultAmountAccordion }
