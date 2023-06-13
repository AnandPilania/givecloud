import type { FC, HTMLProps, PropsWithChildren } from 'react'
import type { LinkProps } from 'react-router-dom'
import { Link as RouterLink } from 'react-router-dom'
import classNames from 'classnames'
import styles from './Link.styles.scss'

type ReactLinkProps = Partial<Pick<LinkProps, 'to'>>

type Props = PropsWithChildren<Omit<HTMLProps<HTMLAnchorElement>, 'ref'>> & ReactLinkProps

const Link: FC<Props> = ({ to, href, children, className, ...rest }) => {
  const css = classNames(styles.root, className)

  if (to) {
    return (
      <RouterLink {...rest} to={to} className={css}>
        {children}
      </RouterLink>
    )
  }

  return (
    <a {...rest} href={href} className={css}>
      {children}
    </a>
  )
}

export { Link }
