import type { FundraisingForm } from '@/types'
import { atom } from 'recoil'

export type IntegrationsState = Pick<
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

export const integrationsState = atom<IntegrationsState>({
  key: 'integrationsState',
  default: {
    dpEnabled: false,
    dpGlCode: '',
    dpSolicitCode: '',
    dpSubSolicitCode: '',
    dpCampaign: '',
    gtmContainerId: '',
    metaPixelId: '',
    dpMeta9: '',
    dpMeta10: '',
    dpMeta11: '',
    dpMeta12: '',
    dpMeta13: '',
    dpMeta14: '',
    dpMeta15: '',
    dpMeta16: '',
    dpMeta17: '',
    dpMeta18: '',
    dpMeta19: '',
    dpMeta20: '',
    dpMeta21: '',
    dpMeta22: '',
  },
})
