import axios from 'axios'

export default {
    init() {
        const countryDropdowns = document.querySelectorAll('select[data-country-state]')

        countryDropdowns
            .forEach(country => {
                country.addEventListener('change', async () => {
                    const stateDropdownName = country.getAttribute('data-country-state')
                    const data = await this.fetchStates(country.value)

                    this.updateStateOptions(data, stateDropdownName)
                } )
            })
    },

    updateStateOptions(data, stateElName) {
        let stateDropdown = document.getElementsByName(stateElName)[0]

        for (let i = stateDropdown.length; i > 0; i--) {
            stateDropdown.options.remove(i)
        }

        if (stateDropdown.labels.length > 0) { stateDropdown.labels[0].innerText = `${data.subdivision_type}`}
        stateDropdown.options[0].text = `Select ${data.subdivision_type}`

        for (let state in data.subdivisions) {
            let newOption = new Option;
            newOption.text = data.subdivisions[state]
            newOption.value = state

            stateDropdown.options.add(newOption)
        }
    },

    async fetchStates(countryCode) {
        const res = await axios.get(`/gc-json/v1/services/locale/${countryCode}/subdivisions`)
        return res.data
    }
}
