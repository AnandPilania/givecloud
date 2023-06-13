import { useState } from 'react'
import { useRecoilValue } from 'recoil'
import { setStateForCurrentVisit } from '@/utilities/config'
import formatCurrency from '@/utilities/currency'
import configState from '@/atoms/config'
import paymentStatus from '@/atoms/paymentStatus'
import pendingContribution from '@/atoms/pendingContribution'

const useCloseForm = () => {
  const config = useRecoilValue(configState)
  const { amount, currency_code } = useRecoilValue(pendingContribution)
  const [isConfirmModalOpen, setIsConfirmModalOpen] = useState(false)

  const closeFundraisingForm = () => {
    if (window.parentIFrame) {
      if (paymentStatus === 'approved') {
        window.parentIFrame.close()
      } else {
        setStateForCurrentVisit({
          amount: amount,
          friendlyAmount: formatCurrency(amount, currency_code),
        })

        window.parentIFrame.sendMessage({ name: 'fundraisingFormMinimize' })
        setIsConfirmModalOpen(false)
      }
    } else if (config?.global_settings?.org_website || config.campaign_url) {
      const redirectUrl = 'https://' + config?.global_settings?.org_website.split('//').pop()
      window.location.href = config?.global_settings?.org_website ? redirectUrl : config.campaign_url
    }
  }

  return { isConfirmModalOpen, setIsConfirmModalOpen, closeFundraisingForm }
}

export default useCloseForm
