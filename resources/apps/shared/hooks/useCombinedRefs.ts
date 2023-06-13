import type { Ref, MutableRefObject } from 'react'
import { useEffect, useRef } from 'react'

type RefsType<ReferenceElement> = (Ref<ReferenceElement> | undefined)[]

const useCombinedRefs = <ReferenceElement>(...refs: RefsType<ReferenceElement>) => {
  const targetRef = useRef<ReferenceElement>(null)

  useEffect(() => {
    refs.forEach((ref) => {
      if (!ref) return

      if (typeof ref === 'function') {
        ref(targetRef.current)
      } else {
        ;(ref as MutableRefObject<ReferenceElement | null>).current = targetRef.current
      }
    })
  }, [refs])

  return targetRef
}

export { useCombinedRefs }
