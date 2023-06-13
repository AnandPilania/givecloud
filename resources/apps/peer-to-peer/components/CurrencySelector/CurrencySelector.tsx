import type { FC } from 'react'
import { useMemo } from 'react'
import Givecloud from 'givecloud'
import { Dropdown, DropdownButton, DropdownContent, DropdownItem, DropdownItems } from '@/aerosol'
import { TOP_CURRENCIES } from '@/shared/constants/topCurrencies'
import styles from './CurrencySelector.styles.scss'

interface Currency {
  active: boolean
  code: string
  countries: string[]
  default: boolean
  default_country: string
  has_unique_symbol: boolean
  iso_code: string
  local: boolean
  locale: string
  name: string
  rate: number
  symbol: string
  unique_symbol: string
}

interface Props {
  currencyCode: string
  onChange: (currencyCode: string) => void
}

const CurrencySelector: FC<Props> = ({ currencyCode, onChange }) => {
  const allCurrencies = Givecloud.config.currencies
  const currentCurrency = allCurrencies.find(({ code }: Currency) => code === currencyCode)

  const topCurrenciesAndLocalCurrency = useMemo(() => {
    const topCurrencies = allCurrencies.filter(({ code }) => TOP_CURRENCIES.includes(code))
    const isLocalCurrencyIncluded = topCurrencies.find(({ code }: Currency) => code === currentCurrency.code)
    return [...topCurrencies, ...(isLocalCurrencyIncluded ? [] : [{ ...currentCurrency }])]
  }, [currentCurrency])

  return (
    <Dropdown theme='custom' value={currentCurrency.code} aria-label='currency'>
      <DropdownContent>
        <DropdownButton isOutlined className={styles.button} aria-label={`Selected currency: ${currentCurrency.name}`}>
          {currentCurrency.code}
        </DropdownButton>
        <DropdownItems className={styles.items}>
          {topCurrenciesAndLocalCurrency.map(({ code, name }) => (
            <DropdownItem key={code} onClick={() => onChange(code)} value={code} aria-label={name}>
              {code} - {name}
            </DropdownItem>
          ))}
        </DropdownItems>
      </DropdownContent>
    </Dropdown>
  )
}

export { CurrencySelector }
