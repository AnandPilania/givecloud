const convertToLowerCaseAndTrim = (value: string) => value.toLowerCase().trim()

const filteredBy = (value: string, query: string) =>
  convertToLowerCaseAndTrim(value).includes(convertToLowerCaseAndTrim(query))

interface Args {
  query: string
  data?: Record<string, string>
}

const filteredByObjKey = ({ query, data = {} }: Args) =>
  Object.keys(data)
    .filter((key) => filteredBy(key, query))
    .reduce((cur, key) => Object.assign(cur, { [key]: data[key] }), {})

const filteredByObjValue = ({ query, data = {} }: Args) =>
  Object.keys(data)
    .filter((key) => filteredBy(data[key], query))
    .reduce((cur, key) => Object.assign(cur, { [key]: data[key] }), {})

export { filteredByObjKey, filteredByObjValue, filteredBy }
