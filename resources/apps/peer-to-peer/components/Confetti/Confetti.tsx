import type { FC } from 'react'
import type { IConfettiOptions } from 'react-confetti/dist/types/Confetti'
import ReactConfetti from 'react-confetti'

interface Props {
  options?: Partial<IConfettiOptions>
}

const Confetti: FC<Props> = ({ options }) => {
  const confettiOptions = {
    width: window.innerWidth,
    height: window.innerHeight,
    numberOfPieces: 500,
    recycle: false,
    colors: ['#2467CC'],
    ...options,
  }

  return <ReactConfetti {...confettiOptions} />
}

export { Confetti }
