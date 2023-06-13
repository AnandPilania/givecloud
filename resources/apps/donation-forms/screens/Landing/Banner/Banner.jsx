import PropTypes from 'prop-types'
import classnames from 'classnames'
import { primaryColour500, primaryColour600 } from '@/utilities/theme'
import styles from './Banner.scss'
import CloseButton from '@/components/CloseButton/CloseButton'

const Banner = ({ image, className, onClose }) => {
  const backgroundImage = image ? `url(${image})` : `linear-gradient(45deg, ${primaryColour500}, ${primaryColour600})`

  return (
    <div className={classnames(styles.root, className)} style={{ backgroundImage }}>
      <CloseButton onClick={onClose} />
    </div>
  )
}

Banner.propTypes = {
  className: PropTypes.string,
  image: PropTypes.string,
  onClose: PropTypes.func.isRequired,
}

export default Banner
