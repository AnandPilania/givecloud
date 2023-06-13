import type { FC } from 'react'
import { SCREENS } from '@/constants/screens'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faArrowRight, faCheck, faLockKeyhole, faShieldCheck } from '@fortawesome/pro-regular-svg-icons'
import { InfoBox, Link, Text, WidgetContent, WidgetFooter } from '@/components'
import { useFundraisingExperienceState } from '@/screens/PeerToPeer/useFundraisingExperience'
import { useParams } from '@/shared/hooks'
import { FAQ_PATH } from '@/constants/paths'
import { Button, Drawer } from '@/aerosol'
import styles from './FAQDrawer.styles.scss'

const FAQDrawer: FC = () => {
  const { params, deleteAndReplaceParams } = useParams()
  const isFAQDrawerOpen = params.get(SCREENS.DRAWER) === FAQ_PATH

  const handleClose = () => {
    deleteAndReplaceParams([SCREENS.DRAWER])
  }

  const {
    fundraisingExperience: {
      global_settings: {
        org_legal_name,
        org_check_mailing_address,
        org_other_ways_to_donate,
        org_support_number,
        org_support_email,
        org_faq_alternative_question,
        org_faq_alternative_answer,
        org_legal_country,
        org_legal_number,
      },
      transparency_promise,
    },
  } = useFundraisingExperienceState()

  const renderTaxDeductibleInfoBox = () => {
    if (!org_legal_country && !org_legal_number) return null
    return (
      <InfoBox icon={faCheck}>
        <Text type='footnote' isMarginless>
          <strong>100% Tax Deductible</strong> in {org_legal_country} through our{' '}
          {org_legal_country === 'US' ? '501(c)(3)' : ''} charity number {org_legal_number}.
        </Text>
      </InfoBox>
    )
  }

  const renderTransparencyPromiseInfoBox = () => {
    if (!transparency_promise.enabled) return null
    return (
      <InfoBox icon={faShieldCheck}>
        <Text type='footnote' isMarginless>
          <strong>Impact Promise</strong> ensures 100% of your donation is used as promised.
        </Text>
      </InfoBox>
    )
  }

  const renderGiveByCheck = () => {
    if (!org_legal_name && !org_check_mailing_address) return null
    return (
      <div className={styles.list}>
        <Text isBold>Can I give by check?</Text>
        <Text type='footnote'>
          Using the online form allows us to streamline our financial operations so we can focus all our resources on
          our mission. If check is the best, please make it out to:
          <span className={styles.address}>
            {org_legal_name}
            <br />
            {org_check_mailing_address}
          </span>
        </Text>
      </div>
    )
  }

  const renderOtherWaysToDonate = () => {
    if (!org_other_ways_to_donate?.length) return null
    return (
      <div className={styles.list}>
        <Text isBold>Are there other ways I can donate?</Text>
        {org_other_ways_to_donate?.map(({ href, label }, index: number) => (
          <Link href={href} key={index}>
            <Text type='footnote' isMarginless>
              {label} <FontAwesomeIcon icon={faArrowRight} size='sm' />
            </Text>
          </Link>
        ))}
      </div>
    )
  }

  const renderWhoDoIContact = () => {
    if (!org_support_number && !org_support_email) return null
    return (
      <>
        <Text isMarginless isBold>
          Who can I contact about my donation?
        </Text>
        <Text type='footnote'>You can reach our team at: </Text>
      </>
    )
  }

  const renderNumberLink = () =>
    org_support_number ? (
      <Link href={`tel:${org_support_number}`}>
        <Text type='footnote' isMarginless>
          {org_support_number} <FontAwesomeIcon icon={faArrowRight} size='sm' />
        </Text>
      </Link>
    ) : null

  const renderEmailLink = () =>
    org_support_email ? (
      <Link href={`mailto:${org_support_email}`}>
        <Text type='footnote' isMarginless>
          {org_support_email} <FontAwesomeIcon icon={faArrowRight} size='sm' />
        </Text>
      </Link>
    ) : null

  const renderAlternativeQuestion = () => {
    if (!org_faq_alternative_question && !org_faq_alternative_answer) return null
    return (
      <div className={styles.list}>
        <Text isBold>{org_faq_alternative_question}</Text>
        <Text type='footnote'>{org_faq_alternative_answer}</Text>
      </div>
    )
  }
  return (
    <Drawer name='FAQ' isOpen={isFAQDrawerOpen} onClose={handleClose} isFullHeight showCloseButton={false}>
      <Text type='h2' isBold className={styles.title}>
        FAQ
      </Text>
      <WidgetContent className={styles.content}>
        <InfoBox icon={faLockKeyhole}>
          <Text type='footnote' isMarginless>
            <strong>Bank-Level Security</strong> keeps your payment safe and secure.
          </Text>
        </InfoBox>
        {renderTaxDeductibleInfoBox()}
        {renderTransparencyPromiseInfoBox()}
        {renderGiveByCheck()}
        {renderOtherWaysToDonate()}
        <div className={styles.list}>
          {renderWhoDoIContact()}
          {renderNumberLink()}
          {renderEmailLink()}
        </div>
        {renderAlternativeQuestion()}
      </WidgetContent>
      <WidgetFooter className={styles.footer}>
        <Button theme='custom' onClick={handleClose} isFullWidth>
          Close
        </Button>
      </WidgetFooter>
    </Drawer>
  )
}
export { FAQDrawer }
