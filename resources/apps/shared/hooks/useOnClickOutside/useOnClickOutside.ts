import { useEffect } from 'react'

interface OutsideClickArgs<ReferenceElement> {
  ref: ReferenceElement
  onClickOutside: () => void
}

interface ContainsType {
  contains: (target: HTMLDivElement) => boolean
}

const useOnClickOutside = <ReferenceElement extends ContainsType>({
  ref,
  onClickOutside = () => null,
}: OutsideClickArgs<ReferenceElement>) => {
  useEffect(() => {
    const handleOnClickOutside = (e: MouseEvent) => {
      if (ref && !ref?.contains?.(e.target as HTMLDivElement)) {
        onClickOutside()
      }
    }

    document.addEventListener('click', handleOnClickOutside)

    return () => {
      document.removeEventListener('click', handleOnClickOutside)
    }
  })
}

export { useOnClickOutside }
