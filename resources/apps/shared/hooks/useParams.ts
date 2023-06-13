import { useHistory, useLocation, useParams as useRouterParams } from 'react-router-dom'

interface Id {
  id?: string
}

const useParams = () => {
  const { search, pathname } = useLocation()
  const { id } = useRouterParams<Id>()
  const history = useHistory()
  const params = new URLSearchParams(search)

  const setAndReplaceParams = (key: string, value: string, newPath?: string) => {
    params.set(key, value)
    const search = params.toString()
    history.replace({ pathname: newPath ?? pathname, search })
  }

  const deleteAndReplaceParams = (keys: string[], newPath?: string) => {
    keys.forEach((key) => params.delete(key))
    const search = params.toString()
    history.replace({ pathname: newPath ?? pathname, search })
  }

  return {
    id,
    params,
    pathname,
    setAndReplaceParams,
    deleteAndReplaceParams,
    ...params,
    ...history,
  }
}

export { useParams }
