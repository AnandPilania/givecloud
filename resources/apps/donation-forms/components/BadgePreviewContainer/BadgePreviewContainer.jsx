import PropTypes from 'prop-types'
import classnames from 'classnames'
import styles from './BadgePreviewContainer.scss'
import BadgePreview from '../BadgePreview/BadgePreview'

const BadgePreviewContainer = ({ children, className }) => {
  return (
    <div className={classnames(styles.root, className)}>
      <span>
        <BadgePreview />
      </span>
      <span>{children}</span>
      <span></span>
    </div>
  )
}

BadgePreviewContainer.propTypes = {
  children: PropTypes.node.isRequired,
  className: PropTypes.string,
}

export default BadgePreviewContainer
