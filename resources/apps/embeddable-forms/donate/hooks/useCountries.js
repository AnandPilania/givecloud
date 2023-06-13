import { useState, useEffect } from 'react'

const Givecloud = window.Givecloud

const useCountries = () => {
  const [countries, setCountries] = useState([])

  useEffect(() => {
    const getCountries = async () => {
      const data = await Givecloud.Services.Locale.countries()
      const allCountries = []

      if (Givecloud.config.force_country) {
        allCountries.push({
          value: Givecloud.config.force_country,
          label: data.countries[Givecloud.config.force_country] || Givecloud.config.force_country,
        })
      } else {
        Givecloud.config.pinned_countries.forEach((code) => {
          allCountries.push({
            value: code,
            label: data.countries[code] || code,
          })

          if (data.countries[code]) {
            delete data.countries[code]
          }
        })

        if (allCountries.length) {
          allCountries.push({ value: '', label: '--------' })
        }

        Object.keys(data.countries).map((code) => {
          allCountries.push({ value: code, label: data.countries[code] })
        })
      }

      setCountries(allCountries)
    }

    getCountries()
  }, [])

  return [countries]
}

export default useCountries
