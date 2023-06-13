Date.isLeapYear = function (year) {
    return (((year % 4 === 0) && (year % 100 !== 0)) || (year % 400 === 0));
};

Date.getDaysInMonth = function (year, month) {
    return [31, (Date.isLeapYear(year) ? 29 : 28), 31, 30, 31, 30, 31, 31, 30, 31, 30, 31][month];
};

Date.prototype.isLeapYear = function () {
    return Date.isLeapYear(this.getFullYear());
};

Date.prototype.getDaysInMonth = function () {
    return Date.getDaysInMonth(this.getFullYear(), this.getMonth());
};

Date.prototype.addMonths = function (value) {
    var n = this.getDate();
    this.setDate(1);
    this.setMonth(this.getMonth() + value);
    this.setDate(Math.min(n, this.getDaysInMonth()));
    return this;
};

$(document).ready(function(){

    $form = $('#subscription-cancel');

    $('input[name=cancel_period]').change(function(){
        if ($(this).val() == 'forever') {
            $('#cancelReasonsContainer').collapse('show');
        } else {
            $('#cancelReasonsContainer').collapse('hide');
        }
    });

    $('input[name=cancel_reason]').change(function(){
        if ($(this).val() == 'other') {
            $('#cancelReasonOtherContainer').collapse('show');
            $('#inputCancelReasonOther').focus();
        } else {
            $('#cancelReasonOtherContainer').collapse('hide');
        }
    });

    $form.on('submit', function(ev){
        ev.preventDefault();

        var $this = $(this),
            data = {},
            subscription_id = $this.find('input[name=id]').val(),
            next_payment_date = new Date($this.find('input[name=next_payment_date]').val()),
            btn = Ladda.create($this.find('button[type=submit]')[0])
            cancel_period = $this.find('[name=cancel_period]:checked').val(),
            use_nps = ($this.find('[name=nps]').length > 0),
            nps = theme.toNumber($this.find('[name=nps]:checked').val());

        btn.start();

        if (cancel_period == '1' || cancel_period == '3') {

            next_payment_date.addMonths(parseInt(cancel_period));

            Givecloud.Account.Subscriptions.update(subscription_id, {
                'next_payment_date': next_payment_date.getFullYear() + '-' +
                    ("0" + (next_payment_date.getMonth()+1)).slice(-2) + "-" +
                    ("0" + (next_payment_date.getDate()+1)).slice(-2)
            })
                .then(function(account) {
                    theme.toast.success(theme.trans('scripts.templates.subscription_cancel.recurring_payment_updated'));
                    window.location = '/account/subscriptions/' + subscription_id;
                })
                .catch(function(err) {
                    theme.toast.error(err);
                }).finally(function() {
                    btn.stop();
                });
        } else {

            data.cancel_reason = $('input[name=cancel_reason]:checked').val();
            if (data.cancel_reason == 'other') {
                data.cancel_reason = $('input[name=cancel_reason_other]').val();
            }

            if (use_nps) {
                if (nps) {
                    data.nps = nps;
                } else {
                    btn.stop();
                    return theme.toast.error(theme.trans('scripts.templates.subscription_cancel.missed_recommend'));
                }
            }

            Givecloud.Account.Subscriptions.cancel(subscription_id, data)
                .then(function(account) {
                    theme.toast.success(theme.trans('scripts.templates.subscription_cancel.recurring_payment_cancelled'));
                    window.location = '/account/subscriptions/' + subscription_id;
                }).catch(function(err) {
                    theme.toast.error(err);
                }).finally(function(err) {
                    btn.stop();
                });
        }

        // action = cancel VS update (depending on the radio options selected)


    });

});
