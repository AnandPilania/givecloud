import type { FundraisingForm } from '@/types'
import { atom, selector } from 'recoil'

interface RemindersErrors {
  exitConfirmationDescription: string[]
  embedOptionsReminderDescription: string[]
}

export interface RemindersState
  extends Pick<
    FundraisingForm,
    | 'embedOptionsReminderDescription'
    | 'exitConfirmationDescription'
    | 'embedOptionsReminderEnabled'
    | 'embedOptionsReminderPosition'
    | 'embedOptionsReminderBackgroundColour'
  > {
  errors?: RemindersErrors
  touchedInputs?: Record<string, string>
}

export const remindersState = atom<RemindersState>({
  key: 'remindersState',
  default: {
    embedOptionsReminderDescription: `We're counting on your support!`,
    exitConfirmationDescription: 'Are you sure you want to leave without making a difference?',
    embedOptionsReminderEnabled: true,
    embedOptionsReminderPosition: 'bottom_right',
    embedOptionsReminderBackgroundColour: '#2467CC',
    errors: {
      exitConfirmationDescription: [],
      embedOptionsReminderDescription: [],
    },
    touchedInputs: {},
  },
})

export const remindersErrorState = selector({
  key: 'remindersErrorState',
  get: ({ get }) => {
    const { embedOptionsReminderEnabled, errors, touchedInputs } = get(remindersState)
    const isExitConfirmationDescriptionDirty = !!touchedInputs?.['exitConfirmationDescription']
    const isOptionsReminderDescriptionDirty = !!touchedInputs?.['embedOptionsReminderDescription']
    const isExitConfirmationDescriptionError =
      !!errors?.exitConfirmationDescription?.length && isExitConfirmationDescriptionDirty
    const isOptionsReminderDescriptionError =
      !!errors?.embedOptionsReminderDescription?.length &&
      isOptionsReminderDescriptionDirty &&
      embedOptionsReminderEnabled

    const isReminderError = isExitConfirmationDescriptionError || isOptionsReminderDescriptionError
    return {
      isReminderError,
      isOptionsReminderDescriptionError,
      isExitConfirmationDescriptionError,
    }
  },
})
