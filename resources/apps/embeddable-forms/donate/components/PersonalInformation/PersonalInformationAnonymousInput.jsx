import { memo, useContext } from 'react'
import { StoreContext } from '@/root/store'
import Label from '@/fields/Label/Label'
import Checkbox from '@/fields/Checkbox/Checkbox'

const PersonalInformationAnonymousInput = () => {
  const { anonymity } = useContext(StoreContext)
  const value = anonymity.value

  const onChange = (e) => {
    const value = e.target.checked

    anonymity.set(value)
  }

  return (
    <Label>
      <Checkbox value='1' checked={value} onChange={onChange}>
        Keep me anonymous
      </Checkbox>
    </Label>
  )
}

export default memo(PersonalInformationAnonymousInput)
