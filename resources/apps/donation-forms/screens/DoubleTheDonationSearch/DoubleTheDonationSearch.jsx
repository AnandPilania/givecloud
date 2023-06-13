import { useState } from 'react'
import { useRecoilValue, useSetRecoilState } from 'recoil'
import { useHistory } from 'react-router-dom'
import classnames from 'classnames'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faArrowRight } from '@fortawesome/pro-regular-svg-icons'
import { faBuilding, faSparkles } from '@fortawesome/pro-light-svg-icons'
import { AnimatePresence, motion } from 'framer-motion'
import Givecloud from 'givecloud'
import Screen from '@/components/Screen/Screen'
import Input from '@/components/Input/Input'
import HerospaceIcon from '@/components/HerospaceIcon/HerospaceIcon'
import useCompanySearch from './hooks/useCompanySearch'
import useLocalization from '@/hooks/useLocalization'
import companyState from '@/atoms/company'
import configState from '@/atoms/config'
import contributionState from '@/atoms/contribution'
import { DOUBLE_THE_DONATION_MATCH, EMAIL_OPT_IN, THANK_YOU } from '@/constants/pathConstants'
import styles from './DoubleTheDonationSearch.scss'

const DoubleTheDonationSearch = () => {
  const t = useLocalization('screens.double_the_donation_search')

  const setCompany = useSetRecoilState(companyState)
  const config = useRecoilValue(configState)
  const [companies, searchQuery, setSearchQuery] = useCompanySearch()
  const [minHeight, setMinHeight] = useState(false)
  const contribution = useRecoilValue(contributionState)
  const history = useHistory()

  const handleOnBlur = () => setMinHeight(companies.length > 0)
  const handleOnFocus = () => setMinHeight(true)
  const handleOnChange = (e) => setSearchQuery(e.target.value)

  const handleOnClickCompany = (company) => {
    Givecloud.Cart(contribution.id).updateEmployerMatch({
      doublethedonation_status: 'found',
      doublethedonation_company_id: company.id,
      doublethedonation_entered_text: searchQuery,
      doublethedonation_company_name: company.doubleTheDonationCompanyName,
    })

    setCompany(company)
    history.push(DOUBLE_THE_DONATION_MATCH)
  }

  const handleOnClickNoThanks = () => {
    Givecloud.Cart(contribution.id).updateEmployerMatch({
      doublethedonation_status: 'no_interaction',
      doublethedonation_company_id: null,
      doublethedonation_entered_text: null,
      doublethedonation_company_name: null,
    })

    const emailOptInPath = config.email_optin_enabled && EMAIL_OPT_IN

    history.push(emailOptInPath || THANK_YOU)
  }

  const companyMotion = {
    initial: {
      opacity: 0,
      x: -100,
    },
    animate: (i) => ({
      opacity: 1,
      x: 0,
      transition: {
        delay: i * 0.1,
        ease: 'easeInOut',
        duration: 0.3,
      },
    }),
    exit: {
      display: 'none',
    },
  }

  return (
    <Screen className={styles.root} showBackButton={false}>
      <HerospaceIcon icon={faSparkles} />

      <h3>{t('heading')}</h3>
      <p>{t('description')}</p>

      <Input
        icon={faBuilding}
        onChange={handleOnChange}
        onFocus={handleOnFocus}
        onBlur={handleOnBlur}
        placeholder='Find my employer...'
      />

      <div className={classnames(styles.companies, minHeight && styles.minHeight)}>
        <AnimatePresence>
          {companies.map((company, i) => (
            <motion.button
              type='button'
              key={company.id}
              className={styles.company}
              onClick={() => handleOnClickCompany(company)}
              variants={companyMotion}
              custom={i}
              initial='initial'
              animate='animate'
              exit='exit'
            >
              <div className={styles.details}>
                <div>{company.companyName}</div>
                {company.parentCompanyName && <small>{company.parentCompanyName}</small>}
              </div>
              <div className={styles.link}>
                <FontAwesomeIcon icon={faArrowRight} />
              </div>
            </motion.button>
          ))}
        </AnimatePresence>
      </div>

      <button type='button' className={styles.noThanks} onClick={handleOnClickNoThanks}>
        {t('no_thanks')} <FontAwesomeIcon icon={faArrowRight} />
      </button>
    </Screen>
  )
}

export default DoubleTheDonationSearch
