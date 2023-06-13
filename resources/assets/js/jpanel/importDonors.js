import $ from 'jquery';

export default function() {

    if (!confirm('Are you absolutely sure you want to import all your DonorPerfect donors into GC?'))
        return;

    if (!confirm('Last chance... 100% sure?'))
        return;

    var $modal = $('#dp-import-donor-modal');

    var params = {
        'create_login' : ($modal.find('input[name=create_login]').prop('checked')) ? 1 : 0
    };

    $modal.find('.modal-body').empty().html('<div class="text-muted text-center"><i class="fa fa-spin fa-2x fa-spinner"></i><br>Importing</div>');
    $modal.find('.modal-footer').remove();

    $.post('/jpanel/donors/import', params, function(){
        $modal.find('.modal-body').html('<div class="text-center"><span class="text-success"><i class="fa fa-check fa-2x"></i><br>Done!</span><br><br>We just sent you an email with all the details of the import.</div>');
    });

}
