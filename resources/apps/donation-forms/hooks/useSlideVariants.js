const useSlideVariants = (inFromLeft) => {
  const initialX = inFromLeft ? '-100%' : '100%'
  const exitX = inFromLeft ? '100%' : '-100%'

  const variants = {
    hide: {
      x: initialX,
      opacity: 0,
      pointerEvents: 'none',
      transitionEnd: {
        display: 'none',
      },
    },
    show: {
      x: '0%',
      opacity: 1,
      pointerEvents: 'auto',
      display: 'block',
    },
    exit: {
      x: exitX,
      opacity: 0,
      pointerEvents: 'none',
      transitionEnd: {
        display: 'none',
      },
    },
  }

  return variants
}

export default useSlideVariants
