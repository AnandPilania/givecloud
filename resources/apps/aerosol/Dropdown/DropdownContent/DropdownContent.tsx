import type { FC } from 'react'
import type { FloatProps } from '@headlessui-float/react'
import { Float } from '@headlessui-float/react'
import { useDropdownContext } from '@/aerosol/Dropdown/DropdownContext'
import { Fragment } from 'react'

const DropdownContent: FC<FloatProps> = ({ children, ...rest }) => {
  const { isOpen, placement, isResponsive, isAutoPlacement } = useDropdownContext()

  return (
    <Float
      enter='transition duration-100 ease-out'
      enterFrom='scale-50 opacity-0'
      enterTo='scale-100 opacity-100'
      leave='transition duration-75 ease-in'
      leaveFrom='scale-100 opacity-100'
      leaveTo='scale-50 opacity-0'
      className='w-full relative'
      offset={4}
      {...rest}
      transform={false}
      placement={placement}
      flip={isResponsive}
      autoPlacement={isResponsive ? false : isAutoPlacement}
      floatingAs={Fragment}
      show={isOpen}
      tailwindcssOriginClass
    >
      {children}
    </Float>
  )
}

export { DropdownContent }
