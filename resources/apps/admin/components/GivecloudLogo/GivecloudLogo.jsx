import PropTypes from 'prop-types'

const GivecloudLogo = ({ className = '', withName = false }) => {
  const filename = withName ? 'givecloud-logo-full-color-rgb' : 'givecloud-logo-mark-full-color-rgb'
  const src = `https://cdn.givecloud.co/static/etc/${filename}.svg`

  return <img className={className} src={src} alt='Givecloud' />
}

GivecloudLogo.propTypes = {
  className: PropTypes.string,
  withName: PropTypes.bool,
}

export { GivecloudLogo }
