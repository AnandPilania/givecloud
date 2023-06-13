import type { FundraisingForm } from '@/types'
import type { Template } from '@/screens/Fundraising/VisualEditor/TemplatesCard/templates'
import { atom, selector } from 'recoil'
import { templates } from '@/screens/Fundraising/VisualEditor/TemplatesCard/templates'

interface TemplateErrors {
  [name: string]: string[]
}

export interface TemplateState extends Pick<FundraisingForm, 'name'> {
  template: Template
  errors?: TemplateErrors
  touchedInputs?: Record<string, string>
}

export const templateState = atom<TemplateState>({
  key: 'templateState',
  default: {
    template: templates[0],
    name: '',
    errors: {
      name: [],
    },
    touchedInputs: {},
  },
})

export const nameErrorState = selector({
  key: 'nameErrorState',
  get: ({ get }) => {
    const { errors, touchedInputs } = get(templateState)
    return !!touchedInputs?.['name'] && !!errors?.name?.length
  },
})

export const templateBrandingErrorState = selector({
  key: 'templateBrandingErrorState',
  get: ({ get }) => {
    const { name } = get(templateState)
    return !name?.length
  },
})
