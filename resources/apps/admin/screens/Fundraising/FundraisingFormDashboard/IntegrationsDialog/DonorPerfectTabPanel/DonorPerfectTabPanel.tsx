import type { FC, FocusEvent } from 'react'
import type { SelectedType } from '@/aerosol/CommandInput'
import type { DPCode } from './DonorPerfectCommandInput'
import type { ConfigState, CustomField } from './CustomDonorPerfectInputs'
import { useEffect, useState } from 'react'
import { useRecoilValue } from 'recoil'
import { Columns, Column, TabsPanel, Text, Toggle, ToggleLabel, Input } from '@/aerosol'
import { DonorPerfectCommandInput } from './DonorPerfectCommandInput'
import { CustomDonorPerfectInputs } from './CustomDonorPerfectInputs'
import { useFundraisingFormState } from '@/screens/Fundraising/useFundraisingFormState'
import { useDonorPerfectCodesQueries } from './useDonorPerfectCodesQueries'
import configState from '@/atoms/config'

interface Props {
  id?: string
}

const DonorPerfectTabPanel: FC<Props> = ({ id }) => {
  const {
    donorPerfectConfig: { udfs: customDPFields },
  } = useRecoilValue<ConfigState>(configState)

  const hasCustomFields = customDPFields.filter(({ label }: CustomField) => !!label)

  const [enabledQueries, setEnabledQueries] = useState({
    dpCampaign: false,
    dpGlCode: false,
    dpSolicitCode: false,
    dpSubSolicitCode: false,
  })

  const { integrationsValue, setIntegrationsState } = useFundraisingFormState()
  const { dpEnabled, dpCampaign, dpGlCode, dpSolicitCode, dpSubSolicitCode } = integrationsValue

  useEffect(() => {
    setEnabledQueries({
      dpCampaign: !!dpCampaign,
      dpGlCode: !!dpGlCode,
      dpSolicitCode: !!dpSolicitCode,
      dpSubSolicitCode: !!dpSubSolicitCode,
    })
  }, [dpCampaign, dpGlCode, dpSolicitCode, dpSubSolicitCode])

  const [
    { data: glCodesData, isLoading: isGlLoading },
    { data: campaignCodesData, isLoading: isCampaignLoading },
    { data: solicitationCodesData, isLoading: isSolicitationLoading },
    { data: subSolicitationCodesData, isLoading: isSubSolicitationLoading },
  ] = useDonorPerfectCodesQueries(enabledQueries, id)

  const handleFocus = ({ target }: FocusEvent<HTMLInputElement>) => {
    const { name } = target
    if (!enabledQueries[name]) setEnabledQueries((prevState) => ({ ...prevState, [name]: true }))
  }

  const handleSelected = (name: string, option: SelectedType<DPCode>) => {
    const value = typeof option === 'object' ? option?.code : option

    setIntegrationsState({
      ...integrationsValue,
      [name]: value,
    })
  }

  const toggleEnabledState = () => {
    setIntegrationsState({
      ...integrationsValue,
      dpEnabled: !dpEnabled,
    })
  }

  const renderCustomFieldInputs = () => (hasCustomFields ? <CustomDonorPerfectInputs /> : null)

  return (
    <TabsPanel key={3}>
      <Columns isMarginless>
        <Column columnWidth='six' className='items-start'>
          <Toggle
            labelPosition='right'
            isEnabled={dpEnabled ?? false}
            setIsEnabled={toggleEnabledState}
            name='donor perfect'
          >
            <ToggleLabel>
              <Text isMarginless isBold>
                Automatically create donors and gifts as donations are received
              </Text>
            </ToggleLabel>
          </Toggle>
        </Column>
      </Columns>
      <Columns isMarginless isResponsive={false}>
        <Column>
          <DonorPerfectCommandInput
            value={dpGlCode}
            isLoading={isGlLoading}
            data={glCodesData}
            onFocus={handleFocus}
            setSelected={handleSelected}
            label='General Ledger'
            name='dpGlCode'
          />
        </Column>
        <Column>
          <DonorPerfectCommandInput
            value={dpCampaign}
            isLoading={isCampaignLoading}
            data={campaignCodesData}
            onFocus={handleFocus}
            setSelected={handleSelected}
            label='Campaign'
            name='dpCampaign'
          />
        </Column>
      </Columns>
      <Columns isMarginless isResponsive={false}>
        <Column>
          <DonorPerfectCommandInput
            value={dpSolicitCode}
            isLoading={isSolicitationLoading}
            data={solicitationCodesData}
            onFocus={handleFocus}
            setSelected={handleSelected}
            label='Solicitation'
            name='dpSolicitCode'
          />
        </Column>
        <Column>
          <DonorPerfectCommandInput
            value={dpSubSolicitCode}
            isLoading={isSubSolicitationLoading}
            data={subSolicitationCodesData}
            onFocus={handleFocus}
            setSelected={handleSelected}
            label='Sub-Solicitation'
            name='dpSubSolicitCode'
          />
        </Column>
      </Columns>
      {renderCustomFieldInputs()}
    </TabsPanel>
  )
}

export { DonorPerfectTabPanel }
