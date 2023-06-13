import PropTypes from 'prop-types'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faCheck } from '@fortawesome/pro-solid-svg-icons'
import { RadioTile } from '../../components'
import styles from './TilesDonationPreview.scss'

const DonationTiles = ({ isOverlayVisible, onClick, type, values }) => {
  const renderOverlay = () => (isOverlayVisible ? <div onClick={onClick} className={styles.overlay} /> : null)

  const tileValues = type === 'custom' && !!values.length ? values : [45, 95, 150, 250, 500]

  return (
    <div className='w-full relative'>
      <div className='flex'>
        <RadioTile isChecked className='mr-2'>
          <FontAwesomeIcon icon={faCheck} className='mr-1' />${tileValues[0]}
        </RadioTile>
        <RadioTile>${tileValues[1]}</RadioTile>
      </div>
      <div className='flex my-2'>
        <RadioTile className='mr-2'>${tileValues[2]}</RadioTile>
        <RadioTile>${tileValues[3]}</RadioTile>
      </div>
      <div className='flex'>
        <RadioTile className='mr-2'>${tileValues[4]}</RadioTile>
        <RadioTile>Other</RadioTile>
      </div>
      {renderOverlay()}
    </div>
  )
}

DonationTiles.propTypes = {
  isOverlayVisible: PropTypes.bool,
  onClick: PropTypes.func,
  type: PropTypes.oneOf(['automatic', 'custom']),
  values: PropTypes.arrayOf(PropTypes.number),
}

DonationTiles.defaultProps = {
  isOverlayVisible: false,
}

export { DonationTiles }
