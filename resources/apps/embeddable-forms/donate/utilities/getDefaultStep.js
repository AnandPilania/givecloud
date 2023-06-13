export const processSteps = ['amount', 'personal', 'payment', 'address', 'confirm', 'thanks']

const getDefaultStep = (returnState) => {
  if (returnState === 'thankyou') {
    return 'thanks'
  } else {
    return processSteps[0]
  }
}

export default getDefaultStep
