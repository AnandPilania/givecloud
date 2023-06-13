import PropTypes from 'prop-types'
import { Text } from '@/aerosol'
import { Column, Columns, Toggle } from '@/aerosol'
import styles from './WalletPayStatus.scss'

const WalletPayStatus = ({ isEnabled, setIsEnabled, isLoading }) => {
  return (
    <Columns isResponsive={false} isStackingOnMobile={false} className={styles.root}>
      <Column className={styles.column}>
        <Text isMarginless>Wallet Pay (Google Pay, Apple Pay)</Text>
      </Column>
      <Column columnWidth='small'>
        <Toggle
          isLoading={isLoading}
          name='wallet pay'
          isEnabled={isEnabled}
          setIsEnabled={setIsEnabled}
          className='self-end'
        />
      </Column>
    </Columns>
  )
}

WalletPayStatus.propTypes = {
  isLoading: PropTypes.bool,
  isEnabled: PropTypes.bool,
  setIsEnabled: PropTypes.func,
}

export { WalletPayStatus }
