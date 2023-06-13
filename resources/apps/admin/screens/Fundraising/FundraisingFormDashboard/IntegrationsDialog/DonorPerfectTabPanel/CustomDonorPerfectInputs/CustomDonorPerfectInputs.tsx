import type { ChangeEvent, FC, FocusEvent } from 'react'
import type { SelectedType } from '@/aerosol/CommandInput'
import type { DPCode } from '@/screens/Fundraising/FundraisingFormDashboard/IntegrationsDialog/DonorPerfectTabPanel/DonorPerfectCommandInput'
import { useEffect, useState } from 'react'
import { useRecoilValue } from 'recoil'
import { camelCase } from 'lodash'
import { Columns, Column, Input } from '@/aerosol'
import { DonorPerfectCommandInput } from '@/screens/Fundraising/FundraisingFormDashboard/IntegrationsDialog/DonorPerfectTabPanel/DonorPerfectCommandInput'
import { useFundraisingFormState } from '@/screens/Fundraising/useFundraisingFormState'
import { useCustomDPCodesQueries } from './useCustomDPCodeQueries'
import { chunkArray } from '@/shared/utilities/chunkArray'
import configState from '@/atoms/config'

export interface CustomField {
  key: string
  label: string
  field: string
  default: string
  autocomplete: boolean
}

export interface ConfigState {
  donorPerfectConfig: {
    udfs: CustomField[]
  }
}

const CustomDonorPerfectInputs: FC = () => {
  const {
    donorPerfectConfig: { udfs: customDPFields },
  } = useRecoilValue<ConfigState>(configState)

  const filteredCustomFields = customDPFields.filter(({ label }) => label)

  const customFieldQueries = filteredCustomFields.map(({ key, field }) => ({
    key: camelCase(key),
    field,
    isEnabled: false,
  }))

  const [enabledQueries, setEnabledQueries] = useState({
    customFieldQueries,
  })

  const { integrationsValue, setIntegrationsState } = useFundraisingFormState()
  const {
    dpMeta9,
    dpMeta10,
    dpMeta11,
    dpMeta12,
    dpMeta13,
    dpMeta14,
    dpMeta15,
    dpMeta16,
    dpMeta17,
    dpMeta18,
    dpMeta19,
    dpMeta20,
    dpMeta21,
    dpMeta22,
  } = integrationsValue

  useEffect(() => {
    const updatedCustomFieldQueries = customFieldQueries.map((field) => ({
      ...field,
      isEnabled: !!integrationsValue[field.key],
    }))

    setEnabledQueries({
      customFieldQueries: updatedCustomFieldQueries,
    })
  }, [
    dpMeta9,
    dpMeta10,
    dpMeta11,
    dpMeta12,
    dpMeta13,
    dpMeta14,
    dpMeta15,
    dpMeta16,
    dpMeta17,
    dpMeta18,
    dpMeta19,
    dpMeta20,
    dpMeta21,
    dpMeta22,
  ])

  const [...customQueries] = useCustomDPCodesQueries(enabledQueries)

  const handleCustomInputFocus = ({ target }: FocusEvent<HTMLInputElement>) => {
    const { name } = target

    const updatedFieldQueries = customFieldQueries.map((field) =>
      field.key === name ? { ...field, isEnabled: true } : { ...field }
    )

    if (!enabledQueries[name])
      setEnabledQueries((prevState) => ({ ...prevState, customFieldQueries: updatedFieldQueries }))
  }

  const handleSelected = (name: string, option: SelectedType<DPCode>) => {
    const value = typeof option === 'object' ? option?.code : option

    setIntegrationsState({
      ...integrationsValue,
      [name]: value,
    })
  }

  const handleInputChange = ({ target }: ChangeEvent<HTMLInputElement>) => {
    const { name, value } = target

    setIntegrationsState({
      ...integrationsValue,
      [name]: value,
    })
  }

  const renderCustomFieldInputs = () => {
    const renderCustomInput = ({ key, autocomplete, label }: CustomField, index: number) => {
      const { isLoading, data } = customQueries[index]

      const renderInput = () =>
        autocomplete ? (
          <DonorPerfectCommandInput
            value={integrationsValue[camelCase(key)]}
            isLoading={isLoading}
            data={data}
            onFocus={handleCustomInputFocus}
            setSelected={handleSelected}
            label={label}
            name={camelCase(key)}
          />
        ) : (
          <Input
            name={camelCase(key)}
            isMarginless
            label={label}
            value={integrationsValue[camelCase(key)]}
            onChange={handleInputChange}
          />
        )

      return <Column key={camelCase(key)}>{renderInput()}</Column>
    }

    const renderRow = (row: CustomField[], index: number) => {
      const remainder = 2 - row.length

      const renderCustomInputs = () => row.map(renderCustomInput)
      const renderEmptyColumn = () => (remainder ? <Column /> : null)

      return (
        <Columns isMarginless isResponsive={false} key={index}>
          {renderCustomInputs()}
          {renderEmptyColumn()}
        </Columns>
      )
    }

    return chunkArray(filteredCustomFields).map(renderRow)
  }

  return <>{renderCustomFieldInputs()}</>
}

export { CustomDonorPerfectInputs }
