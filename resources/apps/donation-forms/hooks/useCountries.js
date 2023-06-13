import { useEffect } from 'react'
import { useRecoilState } from 'recoil'
import Givecloud from 'givecloud'
import countriesState from '@/atoms/countries'

const useCountries = () => {
  const [countries, setCountries] = useRecoilState(countriesState)

  useEffect(() => {
    if (countries) {
      return
    }

    Givecloud.Services.Locale.countries().then((data) => {
      setCountries(data.countries)
    })
  })

  return countries
}

export default useCountries
