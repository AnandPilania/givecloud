import { useRecoilValue, useSetRecoilState } from 'recoil'
import { uniqueId } from 'lodash'
import useCurrencyFormatter from '@/hooks/useCurrencyFormatter'
import useAnalytics from '@/hooks/useAnalytics'
import useLocalization from '@/hooks/useLocalization'
import coverCostsFeesState from '@/atoms/coverCostsFees'
import formInputState from '@/atoms/formInput'

const useCustomDccRate = (setFloatingIcons) => {
  const collectEvent = useAnalytics({ collectOnce: true })
  const coverCostsFees = useRecoilValue(coverCostsFeesState)

  const setFormInput = useSetRecoilState(formInputState)

  const t = useLocalization('components.cover_costs_selector')
  const formatCurrency = useCurrencyFormatter()

  const options = [
    {
      label: t('most_costs', { amount: formatCurrency(coverCostsFees) }),
      value: '1',
      selected: formatCurrency(coverCostsFees),
    },
    {
      label: t('no_thank_you'),
      value: '',
      selected: formatCurrency(0),
    },
  ]

  const handleOnChange = (e) => {
    const coverCostsEnabled = !!e.target.value

    setFormInput((formInput) => ({
      ...formInput,
      cover_costs_enabled: coverCostsEnabled,
    }))

    if (coverCostsEnabled) {
      setFloatingIcons(uniqueId('RateDropdownFloatingIcons'))
    }

    collectEvent({ event_name: 'dcc_toggle', event_value: coverCostsEnabled ? 1 : 0 })
  }

  return [options, handleOnChange]
}

export default useCustomDccRate
