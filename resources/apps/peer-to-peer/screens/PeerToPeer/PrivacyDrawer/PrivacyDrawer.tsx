import type { FC } from 'react'
import { SCREENS } from '@/constants/screens'
import { InfoBox, Link, Text, WidgetContent, WidgetFooter } from '@/components'
import { useParams } from '@/shared/hooks'
import { PRIVACY_PATH } from '@/constants/paths'
import { Button, Drawer } from '@/aerosol'
import { useFundraisingExperienceState } from '@/screens/PeerToPeer/useFundraisingExperience'
import { faLockKeyhole, faCircleCheck, faArrowRight } from '@fortawesome/pro-regular-svg-icons'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import styles from './PrivacyDrawer.styles.scss'

const friendlyUrl = (value: string) => String(value || '').replace(/^https?:\/\//, '')

const PrivacyDrawer: FC = () => {
  const { params, deleteAndReplaceParams } = useParams()
  const isPrivacyDrawerOpen = params.get(SCREENS.DRAWER) === PRIVACY_PATH

  const {
    fundraisingExperience: {
      accounts_login_url,
      payment_provider_website_url,
      global_settings: {
        org_legal_name,
        org_legal_number,
        org_legal_address,
        org_privacy_officer_email,
        org_privacy_policy_url,
      },
    },
  } = useFundraisingExperienceState()

  const handleClose = () => {
    deleteAndReplaceParams([SCREENS.DRAWER])
  }

  const renderOrgAddress = () => org_legal_address ?? null
  const renderOrgCharityNumber = () => (org_legal_number ? <>Charity {org_legal_number}</> : null)
  const renderOrgNameListItem = () =>
    org_legal_name ? (
      <li className={styles.bulletItem}>
        <FontAwesomeIcon icon={faCircleCheck} size='lg' className={styles.icon} />
        <Text type='footnote'>
          We are: <strong>{org_legal_name}</strong>
          <span className='block'>
            {renderOrgAddress()} {renderOrgCharityNumber()}
          </span>
        </Text>
      </li>
    ) : null

  const renderGDPRCompliantListItem = () =>
    org_privacy_officer_email ? (
      <li className={styles.bulletItem}>
        <FontAwesomeIcon icon={faCircleCheck} size='lg' className={styles.icon} />
        <Text type='footnote'>
          We are <strong>GDPR compliant.</strong>
          Manage your data by{' '}
          <Link href={accounts_login_url} rel='noreferrer' target='_blank'>
            Logging In <FontAwesomeIcon icon={faArrowRight} size='sm' />
          </Link>{' '}
          or by contacting our privacy officer at{' '}
          <Link href={`mailto:${org_privacy_officer_email}`}>
            {org_privacy_officer_email} <FontAwesomeIcon icon={faArrowRight} size='sm' />
          </Link>
        </Text>
      </li>
    ) : null

  const renderPrivacyPolicyUrlLinkItem = () =>
    org_privacy_policy_url ? (
      <Text type='footnote' className={styles.linkItem}>
        Our full privacy policy can be accessed at:
        <Link className='block' href={org_privacy_policy_url}>
          {friendlyUrl(org_privacy_policy_url)} <FontAwesomeIcon icon={faArrowRight} size='sm' />
        </Link>
      </Text>
    ) : null

  const renderPrivacyOfficerEmailLinkItem = () =>
    org_privacy_officer_email ? (
      <Text type='footnote' className={styles.linkItem}>
        Our privacy officer can be reached at:
        <Link className='block' href={`mailto:${org_privacy_officer_email}`}>
          {org_privacy_officer_email} <FontAwesomeIcon icon={faArrowRight} size='sm' />
        </Link>
      </Text>
    ) : null

  const renderPaymentProviderWebsiteUrlLinkItem = () =>
    payment_provider_website_url ? (
      <Text type='footnote' className={styles.linkItem}>
        Your payment is safely processed by:
        <Link className='block' href={payment_provider_website_url} rel='noreferrer' target='_blank'>
          {friendlyUrl(payment_provider_website_url)} <FontAwesomeIcon icon={faArrowRight} size='sm' />
        </Link>
      </Text>
    ) : null

  return (
    <Drawer name='Privacy' isOpen={isPrivacyDrawerOpen} onClose={handleClose} isFullHeight showCloseButton={false}>
      <Text type='h2' isBold className={styles.title}>
        Privacy & Legal
      </Text>
      <WidgetContent className={styles.content}>
        <InfoBox icon={faLockKeyhole}>
          <Text type='footnote' isMarginless>
            Your personal information and payment data are <strong>safe and secure.</strong>
          </Text>
        </InfoBox>
        <ul>
          {renderOrgNameListItem()}
          <li className={styles.bulletItem}>
            <FontAwesomeIcon icon={faCircleCheck} size='lg' className={styles.icon} />
            <Text type='footnote'>
              We use <strong>bank level encryption</strong> to process your payment.
            </Text>
          </li>
          <li className={styles.bulletItem}>
            <FontAwesomeIcon icon={faCircleCheck} size='lg' className={styles.icon} />
            <Text type='footnote'>
              Our vendors adhere to the high information security standards of <strong>SOC & PCI compliance.</strong>
            </Text>
          </li>
          <li className={styles.bulletItem}>
            <FontAwesomeIcon icon={faCircleCheck} size='lg' className={styles.icon} />
            <Text type='footnote'>
              We <strong>never share your information to any</strong> third-parties.
            </Text>
          </li>
          {renderGDPRCompliantListItem()}
        </ul>
        {renderPrivacyPolicyUrlLinkItem()}
        {renderPrivacyOfficerEmailLinkItem()}
        <Text type='footnote' className={styles.linkItem}>
          This form is powered and secured by:
          <Link className='block' href='https://givecloud.com' rel='noreferrer' target='_blank'>
            givecloud.com <FontAwesomeIcon icon={faArrowRight} size='sm' />
          </Link>
        </Text>
        {renderPaymentProviderWebsiteUrlLinkItem()}
      </WidgetContent>
      <WidgetFooter className={styles.footer}>
        <Button theme='custom' onClick={handleClose} isFullWidth>
          Close
        </Button>
      </WidgetFooter>
    </Drawer>
  )
}
export { PrivacyDrawer }
