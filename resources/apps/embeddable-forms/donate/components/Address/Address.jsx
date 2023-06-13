import { memo } from 'react'
import AddressCountrySelector from '@/components/Address/AddressCountrySelector'
import AddressLineOneInput from '@/components/Address/AddressLineOneInput'
import AddressLineTwoInput from '@/components/Address/AddressLineTwoInput'
import AddressCityInput from '@/components/Address/AddressCityInput'
import AddressSubdivisionSelector from '@/components/Address/AddressSubdivisionSelector'
import AddressPostalCodeInput from '@/components/Address/AddressPostalCodeInput'

const Address = () => (
  <>
    <AddressCountrySelector />
    <AddressLineOneInput />
    <AddressLineTwoInput />
    <AddressCityInput />
    <AddressSubdivisionSelector />
    <AddressPostalCodeInput />
  </>
)

export default memo(Address)
