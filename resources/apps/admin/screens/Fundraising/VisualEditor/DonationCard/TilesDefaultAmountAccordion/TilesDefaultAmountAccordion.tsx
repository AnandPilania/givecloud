import type { ComponentPropsWithRef, FC, FocusEvent } from 'react'
import type { onChangeType } from '@/aerosol/Input/CurrencyInput/CurrencyInput'
import type { AccordionProps } from '@/aerosol/Accordion'
import type { DefaultAmountValue } from '@/screens/Fundraising/VisualEditor/DonationCard/donationState'
import { useRef, forwardRef, useEffect } from 'react'
import {
  Accordion,
  AccordionContent,
  AccordionHeader,
  Column,
  Columns,
  CurrencyInput,
  Input,
  RadioButton,
  RadioGroup,
  RadioTile,
  SlideTransition,
  Text,
} from '@/aerosol'
import getConfig from '@/utilities/config'
import { useFundraisingFormState } from '@/screens/Fundraising/useFundraisingFormState'
import { formatMoney } from '@/shared/utilities/formatMoney'
import { chunkArray } from '@/shared/utilities/chunkArray'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faExclamationCircle } from '@fortawesome/pro-regular-svg-icons'
import styles from './TilesDefaultAmountAccordion.styles.scss'

const getNamesWithActiveErrors = (values: DefaultAmountValue[]) =>
  values
    ?.map(({ errors, name }) => (errors?.length ? name : null))
    ?.filter((value) => !!value)
    ?.reduce((a, b) => ({ ...a, [b!]: b }), {})

type Props = Pick<AccordionProps, 'isOpen' | 'setIsOpen'> & Pick<ComponentPropsWithRef<'input'>, 'ref'>

const TilesDefaultAmountAccordion: FC<Props> = forwardRef(({ isOpen, setIsOpen }, ref) => {
  const { currency } = getConfig()
  const defaultMinAmount = formatMoney({ amount: 5, digits: 0, currency: currency.code })
  const isFocused = useRef(false)
  const { defaultAmountValue: state, setDefaultAmountState, isCustomAmountValuesError } = useFundraisingFormState()

  useEffect(() => {
    setDefaultAmountState({
      ...state,
      isCustomAmountValuesTouched: {
        ...getNamesWithActiveErrors(state.defaultAmountValues),
      },
    })
  }, [state.defaultAmountType])

  const handleFocus = ({ target }: FocusEvent<HTMLInputElement>) => {
    const { name } = target
    if (!!state.isCustomAmountValuesTouched?.[name]) {
      const { [name]: nameToBeRemoved, ...remainderOfNames } = state.isCustomAmountValuesTouched
      setDefaultAmountState({
        ...state,
        isCustomAmountValuesTouched: {
          ...remainderOfNames,
        },
      })
    }
  }

  const handleBlur = ({ target }: FocusEvent<HTMLInputElement>) => {
    const { name } = target
    setDefaultAmountState({
      ...state,
      isCustomAmountValuesTouched: {
        ...state.isCustomAmountValuesTouched,
        [name]: name,
      },
    })
  }

  const handleTypeChange = (defaultAmountType: string) => {
    defaultAmountType === 'custom' ? (isFocused.current = true) : (isFocused.current = false)
    setDefaultAmountState({
      ...state,
      defaultAmountType,
    })
  }

  const handleValueChange = ({ value, name }: onChangeType) =>
    setDefaultAmountState({
      ...state,
      defaultAmountValues: state.defaultAmountValues.map((defaultAmountValue) =>
        defaultAmountValue.name === name
          ? { name, value, errors: value < 5 ? [`Minimum is ${defaultMinAmount}.`] : [] }
          : defaultAmountValue
      ),
    })

  const getRef = (reference: string) => (reference === state.defaultAmountType ? ref : null)

  const getErrors = (name: string) =>
    state.isCustomAmountValuesTouched?.[name]
      ? state.defaultAmountValues.find((value) => value.name === name)?.errors
      : []

  const renderErrorIcon = () =>
    isCustomAmountValuesError ? <FontAwesomeIcon className={styles.icon} icon={faExclamationCircle} /> : null

  const renderCurrencyInput = ({ name, value }: DefaultAmountValue, index: number) => (
    <Column key={name} isPaddingless className='mb-2'>
      <CurrencyInput
        currency={currency.code}
        onFocus={handleFocus}
        onBlur={handleBlur}
        className={index === 0 ? 'pr-4' : ''}
        isMarginless
        isChecked
        name={name}
        label='Custom default amount'
        isLabelHidden
        onChange={handleValueChange}
        value={value}
        errors={getErrors(name)}
      />
    </Column>
  )

  const renderReadOnlyInput = (index: number) =>
    index === 2 ? (
      <Column className='mb-2' isPaddingless>
        <Input
          isMarginless
          isReadOnly
          name='customDefaultAmount'
          label='Other custom amount'
          isLabelHidden
          value='Other'
        />
      </Column>
    ) : null

  const renderCurrencyInputs = () => (
    <SlideTransition isOpen={state.defaultAmountType === 'custom'}>
      {chunkArray(state.defaultAmountValues).map((row, rowIndex) => (
        <Columns key={rowIndex} isMarginless isResponsive={false} isStackingOnMobile={false}>
          {row.map(renderCurrencyInput)}
          {renderReadOnlyInput(rowIndex)}
        </Columns>
      ))}
    </SlideTransition>
  )

  return (
    <Accordion isOpen={isOpen} setIsOpen={setIsOpen} className={styles.root} hasBorderTop>
      <AccordionHeader>
        <Text isError={isCustomAmountValuesError} isMarginless type='h5' isBold>
          Default Amount
          {renderErrorIcon()}
        </Text>
      </AccordionHeader>
      <AccordionContent className={isOpen ? 'mb-2' : ''}>
        <Text className='mb-4' isSecondaryColour>
          Choose a default donation amount that will initially be shown to your donors.
        </Text>
        <RadioGroup
          showInput={false}
          name='defaultAmountOptions'
          label='Default amount options'
          isLabelVisible={false}
          checkedValue={state.defaultAmountType}
          onChange={handleTypeChange}
        >
          <Columns isWrapping isMarginless>
            <Column isPaddingless>
              <RadioButton className='lg:pr-4' ref={getRef('automatic')} id='automatic' value='automatic'>
                <RadioTile>
                  <Text isBold>Automatic</Text>
                  <Text isMarginless isSecondaryColour>
                    Givecloud's technology will determine the right amount.
                  </Text>
                </RadioTile>
              </RadioButton>
            </Column>
            <Column isPaddingless>
              <RadioButton ref={getRef('custom')} id='custom' value='custom'>
                <RadioTile>
                  <Text isBold>Customize</Text>
                  <Text isSecondaryColour isMarginless>
                    Manually set the default amount tiles, minimum of {defaultMinAmount} is required
                  </Text>
                </RadioTile>
              </RadioButton>
            </Column>
          </Columns>
        </RadioGroup>
        {renderCurrencyInputs()}
      </AccordionContent>
    </Accordion>
  )
})

TilesDefaultAmountAccordion.displayName = 'TilesDefaultAmountAccordion'

export { TilesDefaultAmountAccordion }
