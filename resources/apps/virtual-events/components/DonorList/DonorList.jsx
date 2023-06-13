import { memo } from 'react'
import PropTypes from 'prop-types'
import { isMobile } from 'react-device-detect'
import { CSSTransition, TransitionGroup } from 'react-transition-group'
import moment from 'moment'
import Donor from '@/components/Donor/Donor'
import styles from '@/components/DonorList/DonorList.scss'

const DonorList = ({ themeStyle, themePrimaryColor, donors, type, celebrationThreshold }) => {
  const donorsOrdered = donors
    .map((donor) => ({
      ...donor,
      date: moment(donor.date),
    }))
    .sort((firstEl, secondEl) => (firstEl.date > secondEl.date ? -1 : 1))

  return (
    <TransitionGroup className={styles.root}>
      {donorsOrdered.slice(0, isMobile ? 10 : 5).map((donor) => (
        <CSSTransition key={donor.id} timeout={500} classNames={styles}>
          <Donor
            themeStyle={themeStyle}
            themePrimaryColor={themePrimaryColor}
            type={type}
            {...donor}
            celebrationThreshold={celebrationThreshold}
          />
        </CSSTransition>
      ))}
    </TransitionGroup>
  )
}

DonorList.propTypes = {
  themeStyle: PropTypes.string,
  themePrimaryColor: PropTypes.string,
  donors: PropTypes.array.isRequired,
  type: PropTypes.string,
  celebrationThreshold: PropTypes.string,
}

export default memo(DonorList)
