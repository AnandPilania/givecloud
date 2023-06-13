import { memo } from 'react'
import PropTypes from 'prop-types'
import classnames from 'classnames'
import styles from './FooterLinks.scss'

const FooterLinks = ({ links, gap = 'tight', className }) => {
  return (
    <div className={classnames(styles.root, className, styles[gap])}>
      {links.map((link) => (
        <button key={link.label} className={styles.footerLinkButton} onClick={link.onClick}>
          {link.label}
        </button>
      ))}
    </div>
  )
}

FooterLinks.propTypes = {
  links: PropTypes.arrayOf(
    PropTypes.exact({
      label: PropTypes.string.isRequired,
      onClick: PropTypes.func.isRequired,
    })
  ).isRequired,
  className: PropTypes.string,
  gap: PropTypes.oneOf(['tight', 'loose']),
}

export default memo(FooterLinks)
