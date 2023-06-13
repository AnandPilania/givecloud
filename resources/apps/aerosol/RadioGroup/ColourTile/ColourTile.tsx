import type { FC } from 'react'
import type { RadioButtonChild } from '@/aerosol/RadioGroup/RadioButton'
import type { ColoursType } from '@/shared/constants/theme'
import classNames from 'classnames'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faCheck } from '@fortawesome/pro-regular-svg-icons'
import styles from './ColourTile.styles.scss'

interface Props extends RadioButtonChild {
  colour: ColoursType
  isMarginless?: boolean
}

const ColourTile: FC<Props> = ({ colour, isChecked, isMarginless }) => {
  const css = classNames(styles.root, isChecked && styles.checked, !isMarginless && styles.margin)

  const style = {
    '--tw-ring-color': colour.code,
    backgroundColor: colour.code,
    borderColor: colour.code,
  }

  return (
    <div role='button' aria-label={`${colour.value} colour tile`} className={css} style={style}>
      {isChecked ? <FontAwesomeIcon icon={faCheck} /> : null}
    </div>
  )
}

export { ColourTile }
