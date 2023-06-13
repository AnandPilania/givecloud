import Givecloud from 'givecloud'
import useAutomaticDccRate from './useAutomaticDccRate'
import useCustomDccRate from './useCustomDccRate'

const usingDccAiPlus = Givecloud.config.processing_fees.using_ai
const useDccRate = usingDccAiPlus ? useAutomaticDccRate : useCustomDccRate

export default useDccRate
