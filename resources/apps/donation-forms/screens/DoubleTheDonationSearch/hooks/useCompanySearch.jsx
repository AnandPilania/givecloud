import { useEffect, useState } from 'react'
import { useDebounce } from 'react-use'
import { useRecoilValue } from 'recoil'
import { uniqueId } from 'lodash'
import axios from 'axios'
import useLocalization from '@/hooks/useLocalization'
import configState from '@/atoms/config'
import { stripTags } from '@/utilities/string'

const normalizeCompany = (company) => {
  if (company.highlight?.subsidiaries?.length && !company.highlight?.company_name) {
    return {
      id: company.id,
      companyName: stripTags(company.highlight.subsidiaries[0]),
      parentCompanyName: company.company_name,
      doubleTheDonationCompanyName: company.company_name,
      doubleTheDonationStatus: company.status || 'found',
    }
  }

  return {
    id: company.id,
    key: uniqueId('company'),
    companyName: company.company_name,
    parentCompanyName: null,
    doubleTheDonationCompanyName: company.company_name,
    doubleTheDonationStatus: company.status || 'found',
  }
}

const useCompanySearch = () => {
  const t = useLocalization('screens.double_the_donation_search')

  const config = useRecoilValue(configState)
  const [companies, setCompanies] = useState([])

  const [debouncedSearchQuery, setDebouncedSearchQuery] = useState('')
  const [searchQuery, setSearchQuery] = useState('')

  useDebounce(() => setDebouncedSearchQuery(searchQuery), 250, [searchQuery])

  useEffect(() => {
    const updateMatchingCompanies = async () => {
      let companies = []

      if (debouncedSearchQuery) {
        const res = await axios.get('https://doublethedonation.com/api/v1/prefix', {
          params: {
            query: debouncedSearchQuery,
            api_key: config.double_the_donation.publishable_key,
          },
        })

        companies = res.data.slice(0, 3)

        companies.push({
          id: null,
          company_name: t('new_employer'),
          highlight: { subsidiaries: [debouncedSearchQuery] },
          status: 'not_found',
        })
      }

      setCompanies(companies.map(normalizeCompany))
    }

    updateMatchingCompanies()
  }, [config.double_the_donation.publishable_key, debouncedSearchQuery, setCompanies, t])

  return [companies, searchQuery, setSearchQuery]
}

export default useCompanySearch
