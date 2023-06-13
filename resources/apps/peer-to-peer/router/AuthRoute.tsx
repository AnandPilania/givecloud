import type { RouteComponentProps, RouteProps } from 'react-router'
import type { FC, ReactNode } from 'react'
import { Redirect, Route } from 'react-router'
import { LOGIN_PATH } from '@/constants/paths'
import { useSupporterState } from '@/screens/PeerToPeer/useSupporterState'
import { triggerToast } from '@/aerosol'

interface Props extends RouteProps {
  children: ReactNode
  redirectPath?: string
}

const AuthRoute: FC<Props> = ({ children, path, redirectPath, ...rest }) => {
  const { isAuthenticated } = useSupporterState()

  const renderChildren = ({ location }: RouteComponentProps) =>
    isAuthenticated ? (
      children
    ) : (
      <>
        <Redirect
          to={{
            pathname: redirectPath ?? LOGIN_PATH,
            state: { from: location },
          }}
        />
        {renderErrorToast()}
      </>
    )

  const renderErrorToast = () =>
    triggerToast({
      type: 'error',
      header: 'You need to be logged in',
      description: 'Please login in order to start a challenge',
    })

  return <Route {...rest} path={path} render={renderChildren} />
}

export { AuthRoute }
