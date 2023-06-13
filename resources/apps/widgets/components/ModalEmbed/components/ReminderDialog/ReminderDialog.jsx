import PropTypes from 'prop-types'
import classNames from 'classnames'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faTimes } from '@fortawesome/pro-regular-svg-icons'
import { faHeart, faSparkles } from '@fortawesome/pro-solid-svg-icons'
import styles from './ReminderDialog.scss'

const ReminderDialog = ({ modal, dismissDialog, contributionAmount = null }) => {
  const config = modal.fundraisingFormConfig.config
  const position = config.embed_options.reminder.position

  const handleFinishMyDonationClick = () => {
    modal.open()
    dismissDialog()
  }

  const handleCloseClick = () => {
    localStorage.removeItem(`fundraisingForm_${config.id}`)
    dismissDialog()
  }

  return (
    <div className={classNames(styles.root, styles[position])}>
      <h3>{config.embed_options.reminder.description || 'We are counting on you!'}</h3>
      <div className={styles.btns}>
        <button type='button' onClick={handleFinishMyDonationClick}>
          <FontAwesomeIcon icon={faHeart} />
          {`Finish My ${contributionAmount || ''} Donation`}
        </button>
        <button type='button' onClick={handleCloseClick}>
          <FontAwesomeIcon icon={faSparkles} />
          No Thanks
        </button>
        {/*
          <button type='button' onClick={dismissDialog}>
            <FontAwesomeIcon icon={faSparkles} />
            Remind Me in November
          </button>
        */}
      </div>
      <button className={styles.closeBtn} type='button' onClick={handleCloseClick}>
        <FontAwesomeIcon icon={faTimes} />
      </button>
    </div>
  )
}

ReminderDialog.propTypes = {
  modal: PropTypes.object.isRequired,
  dismissDialog: PropTypes.func.isRequired,
  contributionAmount: PropTypes.string,
}

export default ReminderDialog
