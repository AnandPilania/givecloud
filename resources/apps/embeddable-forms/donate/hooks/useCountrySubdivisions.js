import { useState, useEffect } from 'react'

const useCountrySubdivisions = (Givecloud, country) => {
  const [billingCountrySubdivisionLabel, setBillingCountrySubdivisionLabel] = useState(null)
  const [billingCountrySubdivisions, setBillingCountrySubdivisions] = useState(null)

  useEffect(() => {
    const getSubdivisions = async () => {
      const data = await Givecloud.Services.Locale.subdivisions(country)

      if (Object.entries(data.subdivisions).length) {
        setBillingCountrySubdivisionLabel(data.subdivision_type)
        setBillingCountrySubdivisions(data.subdivisions)
      } else {
        setBillingCountrySubdivisionLabel('Province')
        setBillingCountrySubdivisions(null)
      }
    }

    getSubdivisions()
  }, [country, Givecloud])

  return [billingCountrySubdivisionLabel, billingCountrySubdivisions]
}

export default useCountrySubdivisions
