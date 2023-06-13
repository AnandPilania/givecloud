import { memo } from 'react'
import PropTypes from 'prop-types'
import styles from '@/components/Logo/Logo.scss'

const Logo = ({ logo }) => <img alt='Logo' className={styles.root} src={logo} />

Logo.propTypes = {
  logo: PropTypes.string.isRequired,
}

export default memo(Logo)
