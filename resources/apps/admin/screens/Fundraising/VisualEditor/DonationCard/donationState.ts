import type { FundraisingForm } from '@/types'
import { atom, selector } from 'recoil'

export type TodayAndMonthlyState = Pick<FundraisingForm, 'billingPeriods'>

interface TransparencyErrors {
  transparencyPromise1Description?: string[]
  transparencyPromise2Description?: string[]
  transparencyPromiseStatement?: string[]
}

export interface TransparancyPromiseState
  extends Pick<
    FundraisingForm,
    | 'transparencyPromiseEnabled'
    | 'transparencyPromiseType'
    | 'transparencyPromise1Percentage'
    | 'transparencyPromise1Description'
    | 'transparencyPromise2Percentage'
    | 'transparencyPromise2Description'
    | 'transparencyPromiseStatement'
  > {
  errors?: TransparencyErrors
  touchedInputs?: Record<string, string>
}

export type SocialProofState = Pick<FundraisingForm, 'socialProofEnabled' | 'socialProofPrivacy'>

export interface DefaultAmountValue {
  name: string
  value: number
  errors: string[]
}

export interface DefaultAmountState extends Pick<FundraisingForm, 'defaultAmountType' | 'defaultAmountValue'> {
  defaultAmountValues: DefaultAmountValue[]
  defaultAmountErrors?: string[]
  isCustomAmountInputTouched?: boolean
  isCustomAmountValuesTouched?: Record<string, string>
  customAmountErrors?: string[]
}

export const defaultAmountState = atom<DefaultAmountState>({
  key: 'defaultAmountState',
  default: {
    defaultAmountType: 'automatic',
    defaultAmountValue: 45,
    defaultAmountValues: [
      {
        name: 'inputOne',
        value: 45,
        errors: [],
      },
      {
        name: 'inputTwo',
        value: 95,
        errors: [],
      },
      {
        name: 'inputThree',
        value: 150,
        errors: [],
      },
      {
        name: 'inputFour',
        value: 250,
        errors: [],
      },
      {
        name: 'inputFive',
        value: 500,
        errors: [],
      },
    ],
    defaultAmountErrors: [],
    isCustomAmountInputTouched: false,
    isCustomAmountValuesTouched: {},
    customAmountErrors: [],
  },
})

export const defaultAmountErrorState = selector({
  key: 'defaultAmountErrorState',
  get: ({ get }) => {
    const {
      isCustomAmountInputTouched,
      defaultAmountErrors,
      defaultAmountType,
      defaultAmountValues,
      isCustomAmountValuesTouched,
    } = get(defaultAmountState)
    const isDefaultAmountError = isCustomAmountInputTouched && !!defaultAmountErrors?.length
    const isCustomAmountsContainError = defaultAmountValues.some(({ errors }) => errors?.length)
    const isCustomAmountValuesInputsTouched = Object.values(isCustomAmountValuesTouched ?? {}).some((value) => !!value)
    const isDefaultAmountCustom = defaultAmountType === 'custom'
    const isCustomAmountValuesError =
      isDefaultAmountCustom && isCustomAmountValuesInputsTouched && isCustomAmountsContainError
    return {
      isCustomAmountValuesError,
      isDefaultAmountError,
    }
  },
})

export const todayAndMonthlyState = atom<TodayAndMonthlyState>({
  key: 'todayAndMonthlyState',
  default: {
    billingPeriods: 'monthly|today_only',
  },
})

export const transparencyPromiseState = atom<TransparancyPromiseState>({
  key: 'transparencyPromiseState',
  default: {
    transparencyPromiseEnabled: true,
    transparencyPromiseType: 'statement',
    transparencyPromiseStatement: '100% of your donation funds our critical mission',
    transparencyPromise1Percentage: 80,
    transparencyPromise1Description: 'goes directly to our mission',
    transparencyPromise2Percentage: 20,
    transparencyPromise2Description: 'supports our lean expert staff and admin operations',
    errors: {
      transparencyPromise1Description: [],
      transparencyPromise2Description: [],
      transparencyPromiseStatement: [],
    },
    touchedInputs: {},
  },
})

export const transparencyPromiseErrorState = selector({
  key: 'transparencyPromiseErrorState',
  get: ({ get }) => {
    const { errors, transparencyPromiseType, touchedInputs } = get(transparencyPromiseState)
    const isTransparencyPromiseStatement = transparencyPromiseType === 'statement'
    const isTransparencyStatementDirty = !!touchedInputs?.['transparencyPromiseStatement']
    const istTransparencyPromise1DescriptionDirty = !!touchedInputs?.['transparencyPromise1Description']
    const istTransparencyPromise2DescriptionDirty = !!touchedInputs?.['transparencyPromise2Description']
    const isTransparencyPromise1DescriptionError =
      !!errors?.transparencyPromise1Description?.length && istTransparencyPromise1DescriptionDirty
    const isTransparencyPromise2DescriptionError =
      !!errors?.transparencyPromise2Description?.length && istTransparencyPromise2DescriptionDirty

    const isTransparencyStatementError = !!errors?.transparencyPromiseStatement?.length && isTransparencyStatementDirty
    const isTransparencyPromiseError = isTransparencyPromise1DescriptionError || isTransparencyPromise2DescriptionError
    return isTransparencyPromiseStatement ? isTransparencyStatementError : isTransparencyPromiseError
  },
})

export const socialProofState = atom<SocialProofState>({
  key: 'socialProofState',
  default: {
    socialProofEnabled: true,
    socialProofPrivacy: 'initials-and-geography',
  },
})

export const donationFieldsError = selector({
  key: 'donationFieldsError',
  get: ({ get }) => {
    const isTransparencyError = get(transparencyPromiseErrorState)
    const { isDefaultAmountError, isCustomAmountValuesError } = get(defaultAmountErrorState)
    return isDefaultAmountError || isTransparencyError || isCustomAmountValuesError
  },
})
