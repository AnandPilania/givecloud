import { forwardRef, useEffect, useMemo } from 'react'
import { createPortal } from 'react-dom'
import PropTypes from 'prop-types'

const Portal = forwardRef(({ children, className = '', fullscreen = false }, ref) => {
  const container = document.getElementById(fullscreen ? 'app-portal' : 'layout-portal')
  const portalElement = useMemo(() => document.createElement('div'), [])

  if (className) {
    portalElement.className = className
  }

  if (ref) {
    ref.current = portalElement
  }

  useEffect(() => {
    container && container.appendChild(portalElement)

    return () => container && container.removeChild(portalElement)
  }, [container, portalElement])

  return createPortal(children, portalElement)
})

Portal.displayName = 'Portal'

Portal.propTypes = {
  className: PropTypes.string,
  children: PropTypes.node,
  fullscreen: PropTypes.bool,
}

export default Portal
