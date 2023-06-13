import { memo } from 'react'
import PersonalInformationAccountTypeSelector from '@/components/PersonalInformation/PersonalInformationAccountTypeSelector'
import PersonalInformationTitleInput from '@/components/PersonalInformation/PersonalInformationTitleInput'
import PersonalInformationFirstNameInput from '@/components/PersonalInformation/PersonalInformationFirstNameInput'
import PersonalInformationLastNameInput from '@/components/PersonalInformation/PersonalInformationLastNameInput'
import PersonalInformationOrganizationNameInput from '@/components/PersonalInformation/PersonalInformationOrganizationNameInput'
import PersonalInformationEmailInput from '@/components/PersonalInformation/PersonalInformationEmailInput'
import PersonalInformationPhoneInput from '@/components/PersonalInformation/PersonalInformationPhoneInput'
import PersonalInformationAnonymousInput from '@/components/PersonalInformation/PersonalInformationAnonymousInput'

const PersonalInformation = () => (
  <>
    <PersonalInformationAccountTypeSelector />
    <PersonalInformationTitleInput />
    <PersonalInformationFirstNameInput />
    <PersonalInformationLastNameInput />
    <PersonalInformationOrganizationNameInput />
    <PersonalInformationPhoneInput />
    <PersonalInformationEmailInput />
    <PersonalInformationAnonymousInput />
  </>
)

export default memo(PersonalInformation)
