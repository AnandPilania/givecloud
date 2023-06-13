import type { FC } from 'react'
import { Column, Columns, Label, Text, Toggle, Tooltip } from '@/aerosol'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faInfoCircle } from '@fortawesome/pro-regular-svg-icons'

export interface Props {
  isEnabled: boolean
  setIsEnabled: (checked: boolean) => void
  isLoading?: boolean
}

const multiCurrencyContent = <Text isMarginless>Allow supporters to donate in their local currency.</Text>

const MultiCurrencyStatus: FC<Props> = ({ isEnabled, setIsEnabled, isLoading }) => {
  return (
    <Columns isResponsive={false} isStackingOnMobile={false}>
      <Column>
        <Label htmlFor='multi-currency'>
          Allow Multi-Currency
          <Tooltip tooltipContent={multiCurrencyContent}>
            <FontAwesomeIcon icon={faInfoCircle} className='text-blue-600 ml-2' />
          </Tooltip>
        </Label>
      </Column>
      <Column columnWidth='small'>
        <Toggle
          isLoading={isLoading}
          name='multi-currency'
          isEnabled={isEnabled}
          setIsEnabled={setIsEnabled}
          className='self-end'
        />
      </Column>
    </Columns>
  )
}

export { MultiCurrencyStatus }
