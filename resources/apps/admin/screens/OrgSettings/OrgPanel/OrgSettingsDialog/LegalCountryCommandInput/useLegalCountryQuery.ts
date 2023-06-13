import type { UseQueryOptions } from 'react-query'
import type { Country } from './LegalCountryCommandInput'
import { useRecoilValue } from 'recoil'
import configState from '@/atoms/config'
import { useQuery } from 'react-query'
import { createAxios } from '@/utilities/createAxios'

interface ConfigState {
  clientUrl: string
}

interface Data {
  countries: Country
}

interface Error {
  message: string
}

type Options = UseQueryOptions<Country, Error>

const useLegalCountryQuery = (options: Options) => {
  const { clientUrl } = useRecoilValue<ConfigState>(configState)

  const { get } = createAxios({
    baseURL: clientUrl,
    disableCamelCase: true,
  })

  const fetchCountries = async () => {
    const { data } = await get<Data>('/gc-json/v1/services/locale/countries')

    return data.countries
  }

  return useQuery<Country, Error>('countries', fetchCountries, { ...options, staleTime: Infinity })
}
export { useLegalCountryQuery }
