import type { FC, PropsWithChildren } from 'react'
import { useEffect, useState } from 'react'
import { createPortal } from 'react-dom'

export interface Props extends PropsWithChildren {
  name: string
}

const Portal: FC<Props> = ({ children, name }) => {
  const [element, setElement] = useState<HTMLElement>()

  useEffect(() => {
    const container = document.getElementById(name)

    if (container) {
      setElement(container)
    }
  }, [])

  if (!element) {
    return null
  }

  return createPortal(children, element)
}

export { Portal }
