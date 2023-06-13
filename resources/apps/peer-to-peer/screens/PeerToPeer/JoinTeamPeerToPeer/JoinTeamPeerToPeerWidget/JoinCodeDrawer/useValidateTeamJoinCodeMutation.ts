import type { UseMutationOptions } from 'react-query'
import { useMutation } from 'react-query'
import Givecloud from 'givecloud'

interface Error {
  message: string
}

interface Variables {
  id: string
  code: string
}

interface Response {
  valid: boolean
}

type Options = UseMutationOptions<Response, Error, Variables>

const validateJoinCode = async ({ id, code }: Variables) =>
  await Givecloud.PeerToPeerCampaigns.validateTeamJoinCode(id, code)

const useValidateTeamJoinCodeMutation = (options?: Options) => {
  return useMutation<Response, Error, Variables>((payload) => validateJoinCode(payload), {
    ...options,
  })
}

export { useValidateTeamJoinCodeMutation }
