import { memo } from 'react'
import PropTypes from 'prop-types'
import ReactConfetti from 'react-confetti'
import useWindowSize from '@/hooks/useWindowSize'
import styles from '@/components/Confetti/Confetti.scss'

const Confetti = ({ onConfettiComplete = () => null }) => {
  const { width, height } = useWindowSize()

  return (
    <div className={styles.root}>
      <ReactConfetti
        width={width}
        height={height}
        recycle={false}
        numberOfPieces={500}
        onConfettiComplete={onConfettiComplete}
      />
    </div>
  )
}

Confetti.propTypes = {
  onConfettiComplete: PropTypes.func,
}

export default memo(Confetti)
