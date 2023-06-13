import { memo } from 'react'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faCheck } from '@fortawesome/pro-regular-svg-icons'
import PropTypes from 'prop-types'
import Button from '@/components/Button/Button'
import styles from './AmountTile.scss'

const AmountTile = ({ isSelected, value, id, label, handleAmountTile }) => {
  const renderIcon = (isSelected) => isSelected && <FontAwesomeIcon icon={faCheck} />

  return (
    <Button className={styles.root} outline={!isSelected} onClick={() => handleAmountTile(value, id)}>
      {renderIcon(isSelected)}
      {label}
    </Button>
  )
}

AmountTile.propTypes = {
  id: PropTypes.string.isRequired,
  isSelected: PropTypes.bool.isRequired,
  handleAmountTile: PropTypes.func.isRequired,
  label: PropTypes.string.isRequired,
  value: PropTypes.number.isRequired,
}

export default memo(AmountTile)
