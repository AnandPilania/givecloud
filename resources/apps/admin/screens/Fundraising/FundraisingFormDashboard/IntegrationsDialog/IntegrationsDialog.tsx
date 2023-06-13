import type { FC, SyntheticEvent } from 'react'
import type { FundraisingForm } from '@/types'
import { useEffect } from 'react'
import { useRecoilValue } from 'recoil'
import { camelCase } from 'lodash'
import {
  Button,
  Column,
  Columns,
  Dialog,
  DialogContent,
  DialogFooter,
  DialogHeader,
  Input,
  Tabs,
  TabsNav,
  TabsNavItem,
  TabsPanel,
  TabsPanels,
  Text,
  triggerToast,
} from '@/aerosol'
import { DonorPerfectTabPanel } from './DonorPerfectTabPanel'
import { useFundraisingFormState } from '@/screens/Fundraising/useFundraisingFormState'
import { useIntegrationsMutation } from './useIntergrationsMutation'
import { useTailwindBreakpoints } from '@/shared/hooks'
import configState from '@/atoms/config'
import styles from './IntegrationsDialog.styles.scss'

interface CustomField {
  key: string
  label: string
  field: string
  default: string
  autocomplete: boolean
}

interface ConfigState {
  isDonorPerfectEnabled: boolean
  donorPerfectConfig: {
    udfs: CustomField[]
  }
}

type IntegrationsState = Pick<
  FundraisingForm,
  | 'dpEnabled'
  | 'dpGlCode'
  | 'dpSolicitCode'
  | 'dpSubSolicitCode'
  | 'dpCampaign'
  | 'gtmContainerId'
  | 'metaPixelId'
  | 'dpMeta9'
  | 'dpMeta10'
  | 'dpMeta11'
  | 'dpMeta12'
  | 'dpMeta13'
  | 'dpMeta14'
  | 'dpMeta15'
  | 'dpMeta16'
  | 'dpMeta17'
  | 'dpMeta18'
  | 'dpMeta19'
  | 'dpMeta20'
  | 'dpMeta21'
  | 'dpMeta22'
>

interface Props extends IntegrationsState {
  isOpen: boolean
  onClose: () => void
  id?: string
}

const IntegrationsDialog: FC<Props> = ({
  id,
  isOpen,
  onClose,
  dpEnabled,
  dpGlCode,
  dpSolicitCode,
  dpSubSolicitCode,
  dpCampaign,
  metaPixelId,
  gtmContainerId,
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
}) => {
  const { extraSmall } = useTailwindBreakpoints()
  const {
    isDonorPerfectEnabled,
    donorPerfectConfig: { udfs: dpMetaFields },
  } = useRecoilValue<ConfigState>(configState)

  const { integrationsValue, setIntegrationsState } = useFundraisingFormState()
  const { mutate, isLoading } = useIntegrationsMutation(id)

  useEffect(() => {
    setIntegrationsState({
      metaPixelId: metaPixelId ?? '',
      gtmContainerId: gtmContainerId ?? '',
      dpGlCode: dpGlCode ?? '',
      dpSolicitCode: dpSolicitCode ?? '',
      dpSubSolicitCode: dpSubSolicitCode ?? '',
      dpCampaign: dpCampaign ?? '',
      dpEnabled: dpEnabled ?? false,
      dpMeta9: dpMeta9 ? dpMeta9 : dpMetaFields[0].default ?? '',
      dpMeta10: dpMeta10 ? dpMeta10 : dpMetaFields[1].default ?? '',
      dpMeta11: dpMeta11 ? dpMeta11 : dpMetaFields[2].default ?? '',
      dpMeta12: dpMeta12 ? dpMeta12 : dpMetaFields[3].default ?? '',
      dpMeta13: dpMeta13 ? dpMeta13 : dpMetaFields[4].default ?? '',
      dpMeta14: dpMeta14 ? dpMeta14 : dpMetaFields[5].default ?? '',
      dpMeta15: dpMeta15 ? dpMeta15 : dpMetaFields[6].default ?? '',
      dpMeta16: dpMeta16 ? dpMeta16 : dpMetaFields[7].default ?? '',
      dpMeta17: dpMeta17 ? dpMeta17 : dpMetaFields[8].default ?? '',
      dpMeta18: dpMeta18 ? dpMeta18 : dpMetaFields[9].default ?? '',
      dpMeta19: dpMeta19 ? dpMeta19 : dpMetaFields[10].default ?? '',
      dpMeta20: dpMeta20 ? dpMeta20 : dpMetaFields[11].default ?? '',
      dpMeta21: dpMeta21 ? dpMeta21 : dpMetaFields[12].default ?? '',
      dpMeta22: dpMeta22 ? dpMeta22 : dpMetaFields[13].default ?? '',
    })
  }, [
    metaPixelId,
    gtmContainerId,
    dpEnabled,
    dpCampaign,
    dpGlCode,
    dpSolicitCode,
    dpSubSolicitCode,
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
  ])

  const handleChange = ({ target }: SyntheticEvent) => {
    const { name, value } = target as HTMLInputElement
    setIntegrationsState({
      ...integrationsValue,
      [name]: value,
    })
  }

  const renderDPTab = () => (isDonorPerfectEnabled ? <TabsNavItem>DonorPerfect</TabsNavItem> : null)

  const renderDPTabPanel = () => (isDonorPerfectEnabled ? <DonorPerfectTabPanel id={id} /> : null)

  const handleSubmit = (event: SyntheticEvent) => {
    event.preventDefault()

    const activeCustomFields = dpMetaFields
      .filter(({ label }) => label)
      .map(({ key }) => camelCase(key))
      .reduce((prev, currentKey) => {
        return { ...prev, [currentKey]: integrationsValue[currentKey] }
      }, {})

    mutate(
      {
        payload: {
          dpEnabled: integrationsValue.dpEnabled,
          dpCampaign: integrationsValue.dpCampaign,
          dpGlCode: integrationsValue.dpGlCode,
          dpSolicitCode: integrationsValue.dpSolicitCode,
          dpSubSolicitCode: integrationsValue.dpSubSolicitCode,
          gtmContainerId: integrationsValue.gtmContainerId,
          metaPixelId: integrationsValue.metaPixelId,
          ...activeCustomFields,
        },
      },
      {
        onSuccess: () => triggerToast({ type: 'success', header: 'Integrations saved' }),
        onError: () =>
          triggerToast({
            type: 'error',
            header: 'There was a problem updating your integrations. Please try again later.',
          }),
      }
    )
  }

  return (
    <Dialog isOpen={isOpen} onClose={onClose} isOverflowVisible={true}>
      <DialogHeader onClose={onClose}>
        <Text type='h3' isBold isMarginless>
          Integrations
        </Text>
      </DialogHeader>
      <DialogContent>
        <form className={styles.content} id='integrations-form' noValidate onSubmit={handleSubmit}>
          <Columns>
            <Column>
              <Tabs>
                <TabsNav className='mb-4' hasHorizontalScroll={extraSmall.lessThan}>
                  <TabsNavItem>Google</TabsNavItem>
                  <TabsNavItem>Facebook</TabsNavItem>
                  {renderDPTab()}
                </TabsNav>
                <TabsPanels>
                  <TabsPanel key={1}>
                    <Column>
                      <Input
                        onChange={handleChange}
                        name='gtmContainerId'
                        value={integrationsValue.gtmContainerId}
                        label='Google Tracking ID'
                        placeholder='GA3, GA4, Tag Manager, or Ads ID'
                      />
                    </Column>
                  </TabsPanel>
                  <TabsPanel key={2} className={styles.panel}>
                    <Column>
                      <Input
                        onChange={handleChange}
                        name='metaPixelId'
                        value={integrationsValue.metaPixelId}
                        label='Facebook Pixel ID'
                        placeholder='ID'
                      />
                    </Column>
                  </TabsPanel>
                  {renderDPTabPanel()}
                </TabsPanels>
              </Tabs>
            </Column>
          </Columns>
        </form>
      </DialogContent>
      <DialogFooter>
        <Columns className={styles.buttons}>
          <Column columnWidth='small' className={styles.column}>
            <Button isOutlined onClick={onClose}>
              Close
            </Button>
          </Column>
          <Column className={styles.column} columnWidth='small'>
            <Button type='submit' form='integrations-form' isLoading={isLoading}>
              Save
            </Button>
          </Column>
        </Columns>
      </DialogFooter>
    </Dialog>
  )
}

export { IntegrationsDialog }
