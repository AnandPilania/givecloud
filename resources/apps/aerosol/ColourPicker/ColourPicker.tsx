import type { ChangeEvent, FC, HTMLProps, FocusEvent } from 'react'
import type { ColoursType } from '@/shared/constants/theme'
import { useEffect, useRef, useState, useMemo } from 'react'
import { ColourTile, RadioButton, RadioGroup } from '@/aerosol/RadioGroup'
import { Tooltip, TooltipProps } from '@/aerosol/Tooltip'
import { Column } from '@/aerosol/Column'
import { Columns } from '@/aerosol/Columns'
import { Input } from '@/aerosol/Input'
import { Label } from '@/aerosol/Label'
import { COLOURS, CUSTOM } from '@/shared/constants/theme'
import { isValidHexColor } from '@/shared/utilities'
import { useFocus } from '@/shared/hooks'
import styles from './ColourPicker.styles.scss'

type AdditionalProps = Pick<HTMLProps<HTMLDivElement>, 'aria-label'> &
  Pick<TooltipProps, 'placement'> &
  Pick<HTMLProps<HTMLInputElement>, 'onBlur' | 'onFocus'>

interface Props extends AdditionalProps {
  colour: ColoursType
  onChange?: (colour: ColoursType) => void
  errors?: string[]
}

type ColourFilter = (colour: ColoursType) => void

const createCustomColour = (code: string) => ({
  value: 'custom',
  code,
})

const filterCustomColour = ({ value }: ColoursType) => value !== CUSTOM.value

const ColourPicker: FC<Props> = ({ colour, onChange, 'aria-label': ariaLabel, placement, errors, onBlur, onFocus }) => {
  const isError = !!errors?.length
  const isColourCustom = colour.value === 'custom'
  const [customHexCode, setCustomHexCode] = useState('')
  const [inputRef, setInputFocus] = useFocus<HTMLInputElement>()
  const radioButtonRef = useRef<HTMLInputElement>(null)

  useEffect(() => {
    setCustomHexCode(isColourCustom ? colour.code : CUSTOM.code)
  }, [isColourCustom])

  const handleFocus = (e: FocusEvent<HTMLInputElement>) => {
    onFocus?.(e)
    radioButtonRef?.current?.click()
    onChange?.(createCustomColour(e.target.value))
  }

  const handleBlur = (e: FocusEvent<HTMLInputElement>) => {
    onBlur?.(e)
    onChange?.(createCustomColour(e.target.value))
  }

  const handleInputChange = ({ target }: ChangeEvent<HTMLInputElement>) => {
    setCustomHexCode(target.value)
    onChange?.(createCustomColour(target.value))
  }

  const handleRadioChange = (colour: string) => {
    if (colour === CUSTOM.value) setInputFocus()
    const selectedColour = COLOURS.find(({ value }) => value === colour)
    onChange?.(selectedColour!)
  }

  const getCustomColour = () =>
    isValidHexColor(customHexCode) ? createCustomColour(customHexCode) : colour.value === 'custom' ? colour : CUSTOM

  const renderRecommendedColour = ({ value, code }: ColoursType) => (
    <Column key={code} columnWidth='small' className={styles.padding}>
      <RadioButton id={value} value={value} isMarginless>
        <ColourTile colour={{ value, code }} isMarginless />
      </RadioButton>
    </Column>
  )

  const renderRecommendedColours = (colourFilter: ColourFilter) =>
    useMemo(() => COLOURS.filter(colourFilter).map(renderRecommendedColour), [])

  const popoverContent = (
    <RadioGroup
      className={styles.radioGroup}
      checkedValue={colour.value}
      onChange={handleRadioChange}
      name='colours'
      label='Recommended'
      showInput={false}
    >
      <Columns isResponsive={false} isStackingOnMobile={false} className={styles.columns} isWrapping>
        {renderRecommendedColours(filterCustomColour)}
      </Columns>
      <Label>Custom</Label>
      <Columns isMarginless isStackingOnMobile={false} isResponsive={false}>
        <Column columnWidth='small' className={styles.radioButtonColumn}>
          <RadioButton
            ref={radioButtonRef}
            isMarginless
            id={CUSTOM.value}
            value={CUSTOM.value}
            className='items-center'
          >
            <ColourTile colour={getCustomColour()} isMarginless={!isError} />
          </RadioButton>
        </Column>
        <Column className={styles.inputColumn}>
          <Input
            aria-label='enter a valid colour code as your custom colour'
            placeholder={CUSTOM.code}
            onFocus={handleFocus}
            onBlur={handleBlur}
            value={customHexCode}
            onChange={handleInputChange}
            ref={inputRef}
            isMarginless
            name='custom'
            errors={errors}
          />
        </Column>
      </Columns>
    </RadioGroup>
  )

  return (
    <Columns isResponsive={false} isStackingOnMobile={false}>
      <Tooltip
        className={styles.root}
        tooltipContent={popoverContent}
        theme='light'
        placement={placement}
        isTriggeredOnClick
        aria-label={ariaLabel}
      >
        <Column columnWidth='small'>
          <ColourTile colour={colour} isMarginless />
        </Column>
      </Tooltip>
    </Columns>
  )
}

export { ColourPicker }
