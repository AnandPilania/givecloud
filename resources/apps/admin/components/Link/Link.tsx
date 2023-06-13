import { ComponentPropsWithRef, FC } from 'react'
import { LinkProps } from 'react-router-dom'
import { forwardRef } from 'react'
import { useRecoilValue } from 'recoil'
import { Link as ReactRouterLink } from 'react-router-dom'
import appSourceState from '@/atoms/appSource'

type ReactLinkProps = Partial<Pick<LinkProps, 'to'>>

type Props = ComponentPropsWithRef<'a'> & ReactLinkProps

const Link: FC<Props> = forwardRef(({ children, href, to, ...rest }, ref) => {
  const appSource = useRecoilValue(appSourceState)

  if (to && (!href || appSource === 'SPA')) {
    return (
      <ReactRouterLink ref={ref} to={to} {...rest}>
        {children}
      </ReactRouterLink>
    )
  }

  return (
    <a ref={ref} {...(href && { href })} {...rest}>
      {children}
    </a>
  )
})

Link.displayName = 'Link'

export { Link }
