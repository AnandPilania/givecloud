import { useMemo } from 'react'
import { useRouteMatch, NavLink, useLocation } from 'react-router-dom'
import classNames from 'classnames'

export default function Steps({ steps, disableAll }) {
  const { url } = useRouteMatch()
  const { pathname } = useLocation()

  const currentRoute = useMemo(() => {
    return steps.find((route) => `${url}/${route.link}` === pathname)
  }, [pathname])

  return (
    <nav className='mt-2 mb-8 flex space-x-4 justify-center' aria-label='Tabs'>
      {steps.map((step) => (
        <NavLink
          key={step.num}
          to={`${url}/${step.link}`}
          disabled={step >= currentRoute?.num || disableAll}
          activeClassName={'text-white pointer-events-none bg-brand-teal'}
          className={classNames(
            step.num < currentRoute?.num ? 'bg-white text-brand-blue' : 'text-white',
            { 'pointer-events-none bg-brand-blue text-white': step.num > currentRoute?.num },
            { 'pointer-events-none': disableAll },
            'uppercase px-3 py-2 font-bold text-xs rounded-full'
          )}
        >
          {step.num}. {step.name}
        </NavLink>
      ))}
    </nav>
  )
}
