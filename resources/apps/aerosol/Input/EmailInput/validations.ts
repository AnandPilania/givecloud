export const isLengthValid = (email: string): boolean => !!email
export const isEmailAtValid = (email: string): boolean => email.includes('@')
export const isEmailDotValid = (email: string): boolean => email.includes('.')
export const basicEmailRegex = new RegExp(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)
export const isBasicEmailRegexValid = (email: string): boolean => basicEmailRegex.test(email)

export const isBasicEmailValid = (email: string): boolean => {
  return isLengthValid(email) && isEmailAtValid(email) && isEmailDotValid(email) && isBasicEmailRegexValid(email)
}
