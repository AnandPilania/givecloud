import { get as dataGet } from 'lodash'
import PropTypes from 'prop-types'
import useLocalizationValue from '@/hooks/useLocalizationValue'

const Translation = ({ id, substitutions }) => {
  const getLocalizationValue = useLocalizationValue()
  const value = getLocalizationValue(id, substitutions)

  const parts = String(value)
    .split(/({{[\s\S]+?}})/g)
    .map((part, index) => {
      const match = part.match(/{{([\s\S]+?)}}/)?.[1]

      if (typeof match === 'undefined') {
        return id.endsWith('_html') ? <span key={index} dangerouslySetInnerHTML={{ __html: part }} /> : part
      }

      const substitution = dataGet(substitutions, match.trim())
      return typeof substitution === 'undefined' ? match : substitution
    })

  return <>{parts}</>
}

Translation.propTypes = {
  id: PropTypes.string.isRequired,
  substitutions: PropTypes.object.isRequired,
}

export default Translation
