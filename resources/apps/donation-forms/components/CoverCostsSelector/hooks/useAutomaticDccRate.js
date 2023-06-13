import { useRecoilState } from 'recoil'
import { uniqueId } from 'lodash'
import Givecloud from 'givecloud'
import useCurrencyFormatter from '@/hooks/useCurrencyFormatter'
import useAnalytics from '@/hooks/useAnalytics'
import useLocalization from '@/hooks/useLocalization'
import formInputState from '@/atoms/formInput'

const useAutomaticDccRate = (setFloatingIcons) => {
  const collectEvent = useAnalytics({ collectOnce: true })
  const [formInput, setFormInput] = useRecoilState(formInputState)

  const t = useLocalization('components.cover_costs_selector')
  const formatCurrency = useCurrencyFormatter()

  const values = ['most_costs', 'more_costs', 'minimum_costs', '']

  const options = values.map((type) => {
    const amount = formatCurrency(Givecloud.Dcc.getCost(formInput.item.amt, type))

    return {
      label: t(type || 'no_thank_you', { amount }),
      value: type,
      selected: amount,
    }
  })

  const handleOnChange = (e) => {
    const oldIndex = values.indexOf(formInput.cover_costs_type)
    const newIndex = values.indexOf(e.target.value)

    setFormInput((formInput) => ({
      ...formInput,
      cover_costs_enabled: !!e.target.value,
      cover_costs_type: e.target.value,
    }))

    if (oldIndex > newIndex) {
      setFloatingIcons(uniqueId('RateDropdownFloatingIcons'))

      collectEvent({ event_name: 'dcc_increase', event_value: e.target.value })
    } else {
      collectEvent({ event_name: 'dcc_decrease', event_value: e.target.value })
    }
  }

  return [options, handleOnChange]
}

export default useAutomaticDccRate
