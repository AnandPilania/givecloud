import { useRecoilState, useRecoilValue } from 'recoil'
import Givecloud from 'givecloud'
import Checkbox from '@/components/Checkbox/Checkbox'
import useLocalization from '@/hooks/useLocalization'
import bankAccountState from '@/atoms/bankAccount'
import configState from '@/atoms/config'

const MandateInput = () => {
  const t = useLocalization('screens.pay_with_credit_card')

  const config = useRecoilValue(configState)
  const [bankAccount, setBankAccount] = useRecoilState(bankAccountState)

  const handleOnChange = () => {
    setBankAccount({ ...bankAccount, mandate_accepted: !bankAccount.mandate_accepted })
  }

  const achMandate = {
    gateway: Givecloud.PaymentTypeGateway('bank_account')?.$displayName,
    organization: config.global_settings.org_legal_name,
  }

  return (
    <Checkbox id='inputAchMandate' checked={bankAccount.mandate_accepted} onChange={handleOnChange}>
      <span dangerouslySetInnerHTML={t('ach_mandate_html', achMandate)} />
    </Checkbox>
  )
}

export default MandateInput
