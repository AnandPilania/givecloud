import { useEffect } from 'react'
import { useRecoilState } from 'recoil'
import Givecloud from 'givecloud'
import subdivisionsState from '@/atoms/subdivisions'

const useSubdivisions = (countryCode) => {
  const [subdivisions, setSubdivisions] = useRecoilState(subdivisionsState)

  useEffect(() => {
    if (!countryCode || subdivisions?.[countryCode]) {
      return
    }

    Givecloud.Services.Locale.subdivisions(countryCode).then((data) => {
      setSubdivisions({
        ...subdivisions,
        [countryCode]: data,
      })
    })
  })

  return [subdivisions?.[countryCode]?.subdivisions, subdivisions?.[countryCode]?.subdivision_type]
}

export default useSubdivisions
