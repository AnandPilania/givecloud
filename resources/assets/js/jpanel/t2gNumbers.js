/* globals j */

import $ from 'jquery';
import toastr from 'toastr';

export default {
    /** @var jQuery */
    $modal: null,

    /**
     * Choose a given phone_number to provision on the backend.
     *
     * @param {string} phone_number
     * @returns false
     */
    choose(phoneNumber) {
        const $resultsWrapper = j.t2gNumbers.loading(j.t2gNumbers.findResultsWrapper())

        $.post($resultsWrapper.data('choose-url'), { phone_number: phoneNumber })
            .done((phoneNumberCreated) => {
                $resultsWrapper.empty().append(
                    `<h3 class="text-center text-gcp-700">
                        Phone number ${phoneNumberCreated} successufully provisioned 🎉
                    </h3>`
                )
            })

        return false
    },

    /**
     * Delete a phone number by posting a ELETE to backend.
     *
     * @param {HTMLElement} link
     * @param {string} phoneNumber
     * @returns false
     */
    delete(link, phoneNumber) {
        if (! confirm(`Are you sure you want to delete ${phoneNumber}?`)) {
            return false
        }

        $.delete(link.href)
            .done(() => {
                toastr.success(`${phoneNumber} successfully released.`)
                $(link).parent().remove()
                if ($(link).parent().find('.provisionT2G--number').length === 0) {
                    window.location.reload()
                }
            })
            .fail(() => toastr.error(`An error occured when releasing ${phoneNumber}`))

        return false
    },

    /**
     * Hide the area_code input element.
     *
     * @returns void
     */
    hideAreaCode() {
        j.t2gNumbers.findResultsAreaCode()
            .attr('disabled', 'disabled')
    },

    /**
     * Make a GET search request to backend.
     *
     * @param {jQuery} $searchForm
     * @returns false
     */
    search($searchForm) {
        // Prevent making a search while another one is running.
        if ($searchForm.find('[type="submit"][disabled]').length > 0) {
            return false
        }

        const formData = {
            country: $searchForm.find('select[name="country"]').val(),
            type: $searchForm.find('input[name="type"]:checked').val(),
            area_code: $searchForm.find('input[name="area_code"]').val(),
        }

        const $resultsWrapper = j.t2gNumbers.loading(j.t2gNumbers.findResultsWrapper())

        $searchForm.find('[type="submit"]').attr('disabled', 'disabled')

        $.get($searchForm.attr('action'), formData)
            .done((phoneNumbers) => {
                let phoneNumbersHtml = '<div class="text-center">No phone numbers available for this search.</div>'

                if (phoneNumbers) {
                    const phoneNumbersElements = [...phoneNumbers.map((phoneNumber) => {
                        return `<div class="flex p-2 justify-between items-center hover:bg-gray-100">
                            <div class="text-bold">${phoneNumber.short}</div>
                            <div class="text-xs italic text-gray-400">international: ${phoneNumber.full}</div>
                            <div>
                                <a href="#" onclick="j.t2gNumbers.choose('${phoneNumber.full}'); return false" class="btn btn-sm btn-primary">
                                    Choose
                                </a>
                            </div>
                        </div>`
                    })]

                    phoneNumbersHtml = `<div class="max-h-96 overflow-y-auto">${phoneNumbersElements.join('')}</div>`
                }

                const refreshButtonHtml =
                    `<div class="text-center">
                        <button type="submit" class="mt-6 btn btn-success">Refresh results</button>
                    </div>`

                $resultsWrapper.empty().append(phoneNumbersHtml + refreshButtonHtml)
            }).fail((jqXHR, textStatus) => {
                const errorMessage = jqXHR.responseJSON['errors']
                    ? Object.keys(jqXHR.responseJSON.errors).map(key => jqXHR.responseJSON.errors[key]).join(', ')
                    : jqXHR.responseJSON

                $resultsWrapper.empty().append(
                    `<div class="text-center text-danger">
                        <span class="text-bold text-capitalize">${textStatus}:</span> ${errorMessage}
                    </div>`
                )
            }).always(() => $searchForm.find('[type="submit"]').removeAttr('disabled'))

        return false
    },

    /**
     * Show the text to give phone numbers choice modal.
     *
     * @returns jQuery
     */
    show() {
        const modal = j.templates.render('t2gNumbersModalTmpl')
        j.t2gNumbers.$modal = $(modal)

        const $modal = j.t2gNumbers.$modal.modal()
        $modal.css('z-index', 9999999999)
        $modal.on('hidden.bs.modal', () => $modal.remove())

        const $searchForm = j.t2gNumbers.$modal.find('.modal-body form')
        // Perform the search on submit.
        $searchForm.on('submit', (e) => {
            e.preventDefault()
            j.t2gNumbers.search($searchForm)
        })

        return $modal
    },

    /**
     * Show the area_code input element.
     *
     * @returns void
     */
    showAreaCode() {
        j.t2gNumbers.findResultsAreaCode()
            .removeAttr('disabled')
            .focus()
    },

    /**
     * Find the jQuery area_code element.
     *
     * @returns jQuery
     */
    findResultsAreaCode() {
        return j.t2gNumbers.$modal.find('#provisionT2G--search--area_code')
    },

    /**
     * Find the jQuery results element.
     *
     * @returns jQuery
     */
    findResultsWrapper() {
        return j.t2gNumbers.$modal.find('#provisionT2G--search--results')
    },

    /**
     * Switch a jQuery element state to loading.
     *
     * @param {jQuery} $wrapper jQuery wrapper element
     * @returns jQuery $wrapper
     */
    loading($wrapper) {
        const loadingTemplate =
            `<div class="panel-body text-center">
                <i class="fa fa-spinner fa-spin"></i> Loading...
            </div>`

        return $wrapper
            .empty()
            .append($(loadingTemplate))
    },
};
