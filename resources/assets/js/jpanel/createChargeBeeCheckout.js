import $ from "jquery";

export default function(planId) {
    return window.Chargebee.getInstance().openCheckout({
        hostedPage: function () {
            return $.ajax({
                type: 'post',
                url: '/jpanel/settings/billing/chargebee/checkout',
                data: {
                    planId: planId
                }
            })
        },
        error: function(response) {
            window.toastr.error(response.responseJSON.error);
        },
    });
}
