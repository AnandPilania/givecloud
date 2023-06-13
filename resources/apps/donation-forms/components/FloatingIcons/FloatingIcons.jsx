import { memo, useEffect, useState } from 'react'
import PropTypes from 'prop-types'
import { uniqueId } from 'lodash'
import AnimatedIcons from './components/AnimatedIcons/AnimatedIcons'

const FloatingIcons = ({ iconKey = null, condition = true, src = null, text = '❤️', large = false, offset = null }) => {
  const [animatedIconKey, setAnimatedIconKey] = useState(null)

  useEffect(() => {
    if (iconKey === null && !animatedIconKey) {
      setAnimatedIconKey(uniqueId('FloatingIcons'))
    }

    if (iconKey && iconKey !== animatedIconKey) {
      setAnimatedIconKey(iconKey)
    }
  }, [animatedIconKey, iconKey])

  const showAnimatedIcons = condition && (animatedIconKey === iconKey || iconKey === null)

  return (
    showAnimatedIcons && <AnimatedIcons key={animatedIconKey} src={src} text={text} large={large} offset={offset} />
  )
}

FloatingIcons.propTypes = {
  iconKey: PropTypes.string,
  condition: PropTypes.bool,
  src: PropTypes.string,
  text: PropTypes.string,
  large: PropTypes.bool,
  offset: PropTypes.object,
}

export default memo(FloatingIcons)
