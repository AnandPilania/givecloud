import { createAxios } from '@/utilities/createAxios'
import { AxiosResponse } from 'axios'
import { UseMutationOptions, useMutation } from 'react-query'

interface MetaData {
  show_fundraising_pixel_instructions: false
}

interface Data {
  metadata: MetaData
}

type Response = AxiosResponse<Data>

interface Error {
  message: string
}

type Variables = Data

type Options = UseMutationOptions<Response, Error, Variables>

const useOnBoardingMutation = (options: Options = {}) => {
  const { patch } = createAxios()

  const updateOnboarding = async (payload: Variables) => await patch<Variables, Response>('settings/user', payload)

  return useMutation<Response, Error, Variables>((payload) => updateOnboarding(payload), {
    ...options,
  })
}

export { useOnBoardingMutation }
