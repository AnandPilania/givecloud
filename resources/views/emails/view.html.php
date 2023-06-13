<script>
    function onDelete () {
        if (confirm('Are you sure you want to de-activate this email notification?')) {
            $('#email_form').attr('action','/jpanel/emails/destroy').submit();
        }
    }
</script>

<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">
            <span class="page-header-text block w-0 h-0 overflow-hidden"><?= e($pageTitle) ?></span>

            <?= e(\Illuminate\Support\Str::limit($pageTitle, 22)) ?></span>

            <div class="pull-right">
                <a onclick="$('#email_form').submit();" class="btn btn-success"><i class="fa fa-check fa-fw"></i><span class="hidden-sm hidden-xs"> Save</span></a>
                <?php if(!$emailModel->is_protected): ?><a onclick="onDelete();" class="btn btn-danger <?= e(($isNew == 1) ? 'hidden' : '') ?>"><i class="fa fa-trash"></i></a><?php endif; ?>
            </div>
        </h1>
    </div>
</div>

<?= dangerouslyUseHTML(app('flash')->output()) ?>

<?php if($emailModel->has_attachments): ?>
    <div class="alert alert-info"><i class="fa fa-paperclip"></i> This email includes an automated attachment.</div>
<?php endif; ?>

<form id="email_form" name="email" method="post" action="/jpanel/emails/save">
    <?= dangerouslyUseHTML(csrf_field()) ?>
    <input type="hidden" name="id" value="<?= e($email->id) ?>" />

    <div class="panel panel-default">
        <div class="panel-heading">
            General
        </div>
        <div class="panel-body">

            <div class="form-horizontal">

                <?php if ($email->is_protected): ?>
                    <div class="form-group">
                        <label for="code" class="col-sm-3 control-label">Name</label>
                        <div class="col-sm-9 col-lg-7">
                            <div class="form-control-static">
                                <strong><?= e($email->name) ?></strong>
                            </div>
                            <small class="text-info">Internal use only.</small>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="form-group">
                        <label for="code" class="col-sm-3 control-label">Name</label>
                        <div class="col-sm-9 col-lg-7">
                            <input type="text" class="form-control" name="name" id="name" value="<?= e($email->name) ?>" />
                            <small class="text-info">Internal use only.</small>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($email->is_protected): ?>
                    <div class="form-group">
                        <label for="code" class="col-sm-3 control-label">Description</label>
                        <div class="col-sm-9 col-lg-7">
                            <div class="form-control-static">
                                <?= e($email->hint) ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="is_active" class="col-sm-3 control-label">Status</label>
                    <div class="col-sm-9 col-lg-7">
                        <input type="checkbox" class="switch" value="1" name="is_active" <?= e(($email->is_active == 1)?'checked':'') ?> >
                    </div>
                </div>

            </div>

        </div>
    </div>

    <div class="panel panel-default <?= e(($email->is_protected)?'hide':'') ?>">
        <div class="panel-heading">
            Trigger
        </div>
        <div class="panel-body">

            <div class="form-horizontal">

                <div class="form-group email-type">
                    <label for="emailtype" class="col-sm-3 control-label">
                        Email Trigger<br>
                        <small class="text-muted">Under what circumstance do you want this email to be sent?</small>
                    </label>
                    <div class="col-sm-9 col-lg-7">
                        <select class="form-control email-type" name="emailtype" id="emailtype" required>
                            <option value="">[ Choose One ]</option>
                            <option value="product_purchase" <?= dangerouslyUseHTML(($email->type == "product_purchase") ? 'selected="selected"' : '') ?> >Product(s) Purchase</option>
                            <option value="variant_purchase" <?= dangerouslyUseHTML(($email->type == "variant_purchase") ? 'selected="selected"' : '') ?> >Product Variant(s) Purchase</option>
                            <option value="membership_expired" <?= dangerouslyUseHTML(($email->type == "membership_expired") ? 'selected="selected"' : '') ?> >Membership Expired</option>
                            <option value="sponsorship_birthday" <?= dangerouslyUseHTML(($email->type == "sponsorship_birthday") ? 'selected="selected"' : '') ?> >Sponsorship Birthday</option>
                            <option value="sponsorship_anniversary" <?= dangerouslyUseHTML(($email->type == "sponsorship_anniversary") ? 'selected="selected"' : '') ?> >Sponsorship Anniversary</option>
                            <option value="customer_manual_recurring_payment_reminder" <?= dangerouslyUseHTML(($email->type == "customer_manual_recurring_payment_reminder") ? 'selected="selected"' : '') ?> >Manual Recurring Payment Bill Date</option>
                        </select>
                    </div>
                </div>

                <div class="form-group memberships hide">
                    <label for="membership_id" class="col-sm-3 control-label">Membership</label>
                    <div class="col-sm-9 col-lg-7">
                        <select name="membership_id" id="membership_id" class="form-control">
                            <option value="">[ Choose One ]</option>
                            <?php foreach($memberships as $membership): ?>
                                <option value="<?= e($membership->id) ?>" <?= e(volt_selected($membership->id, $emailModel->memberships->pluck('id')->all())); ?> ><?= e($membership->name) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group products hide">
                    <label for="product_id" class="col-sm-3 control-label">Products</label>
                    <div class="col-sm-9 col-lg-7">
                        <select name="product_id[]" id="product_id"
                                class="ds-products auto-height form-control"
                                data-max-items="100"
                                multiple size="1">
                            <?php foreach($emailModel->products as $product): ?>
                                <option selected value="<?= e($product->id) ?>"><?= e($product->name) ?></option>
                            <?php endforeach; ?>
                        </select>

                        <?php if($emailModel->type === 'product_purchase' && $overlappingOnProducts->isNotEmpty()): ?>
                            Some selected products already have an email associated.
                            <ul>
                                <?php foreach($overlappingOnProducts as $one): ?>
                                    <li><a target="_blank" href="<?= e(route('backend.emails.add', ['i' => $one->id])) ?>"><?= e($one->name) ?></a></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-group variants hide">
                    <label for="variant_id" class="col-sm-3 control-label">Variants</label>
                    <div class="col-sm-9 col-lg-7">
                        <select name="variant_id[]"
                                data-max-items="100"
                                id="variant_id" class="ds-variants form-control auto-height"
                                multiple>
                            <?php foreach($emailModel->variants as $variant): ?>
                                <option selected value="<?= e($variant->id) ?>"><?= e($variant->name) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php if($emailModel->type === 'variant_purchase' && $overlappingOnVariants->isNotEmpty()): ?>
                                Some selected variants already have an email associated.
                                <ul>
                                    <?php foreach($overlappingOnVariants as $one): ?>
                                    <li><a target="_blank" href="<?= e(route('backend.emails.add', ['i' => $one->id])) ?>"><?= e($one->name) ?></a></li>
                                    <?php endforeach; ?>
                                </ul>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-group products variants hide">
                    <label for="is_active" class="col-sm-3 control-label">
                        Disable confirmation email<br>
                        <small class="text-muted">
                            Disables the "Contribution Received: To Supporter" email for Contributions that include any of these Products/Variants.
                        </small>
                    </label>

                    <div class="col-sm-9 col-lg-7">
                        <input type="checkbox" class="switch" value="1" name="disables_generic" <?= e(volt_checked($email->disables_generic, 1)); ?> >
                    </div>
                </div>

                <div class="form-group dayoffset hide">
                    <label for="product_id" class="col-sm-3 control-label">
                        Day Offset<br>
                        <small class="text-muted">Optionally allow send this email a couple of days before or after the trigger you selected above.</small>
                    </label>
                    <div class="col-sm-4 col-lg-3 offset-type">
                        <select name="day_offset_type" id="day_offset_type" class="form-control">
                            <option value="none"   <?= dangerouslyUseHTML(($email->day_offset == 0) ? 'selected="selected"' : '') ?>>No Offset</option>
                            <option value="before" <?= dangerouslyUseHTML(($email->day_offset < 0) ? 'selected="selected"' : '') ?>>(x) Days Before</option>
                            <option value="after"  <?= dangerouslyUseHTML(($email->day_offset > 0) ? 'selected="selected"' : '') ?>>(x) Days After</option>
                        </select>
                    </div>
                    <div class="col-sm-4 offset-days hide">
                        <div class="input-group">
                            <input type="tel" name="day_offset" id="day_offset" class="form-control" placeholder="0" value="<?= e(($email->day_offset) ? abs($email->day_offset) : '0') ?>">
                            <div class="input-group-addon">days</div>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading">
            Email
        </div>
        <div class="panel-body">

            <div class="form-horizontal">

                <div class="form-group">
                    <label for="to" class="col-sm-3 control-label">To</label>
                    <div class="col-sm-9 col-lg-7">
                        <!-- HACKKKKK -->
                        <?php if($email->type == 'merchant_recurring_payment_processing_summary'): ?>
                            <input type="text" class="form-control" value="<?= e(sys_get('email_from_name')) ?> <<?= e(sys_get('email_from_address')) ?>>" readonly="readonly" />
                            <input type="hidden" name="to" id="to" value="<?= e($to) ?>" />
                        <?php elseif($email->type == 'fundraising_page_donation_received'): ?>
                            <input type="text" class="form-control" value="Fundraiser Owner" readonly="readonly" />
                            <input type="hidden" name="to" id="to" value="<?= e($to) ?>" />
                        <?php elseif($is_to_customer): ?>
                            <input type="text" class="form-control" value="Customer" readonly="readonly" />
                            <input type="hidden" name="to" id="to" value="<?= e($to) ?>" />
                        <?php else: ?>
                            <input type="text" class="form-control" name="to" id="to" value="<?= e($to) ?>" />
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-group">
                    <label for="subject" class="col-sm-3 control-label">Subject</label>
                    <div class="col-sm-9 col-lg-7">
                        <input type="text" class="form-control" name="subject" id="subject" value="<?= e($email->subject) ?>" />
                    </div>
                </div>

                <div class="form-group">
                    <label for="cc" class="col-sm-3 control-label">Cc</label>
                    <div class="col-sm-9 col-lg-7">
                        <input type="text" class="form-control" name="cc" id="cc" value="<?= e($email->cc) ?>" />
                        <small>Must be a comma separated list.</small>
                    </div>
                </div>

                <div class="form-group">
                    <label for="bcc" class="col-sm-3 control-label">Bcc</label>
                    <div class="col-sm-9 col-lg-7">
                        <input type="text" class="form-control" name="bcc" id="bcc" value="<?= e($email->bcc) ?>" />
                        <small>Must be a comma separated list.</small>
                    </div>
                </div>

                <div class="form-group">
                    <label for="body_template" class="col-sm-3 control-label">
                        Email Body
                    </label>
                    <div class="col-sm-9 col-lg-7">
                        <textarea name="body_template" class="form-control html-doc" style="height:500px;" id="body_template"><?= e(stripslashes($email->body_template)) ?></textarea>

                        <br />
                        <div class="alert alert-info">
                            Click to access the <a onclick="$('#merge-tag-cheatsheet').toggle(); return false;">merge tag cheat-sheet</a>.
                            <div class="message_expand" style="display:none;" id="merge-tag-cheatsheet">
                                <h3>Contribution Received</h3>
                                <table class="simple">
                                    <tr>
                                        <td>[[bill_title]] <span class="label label-info label-xs">NEW</span></td>
                                        <td>[[ship_title]] <span class="label label-info label-xs">NEW</span></td>
                                        <td>[[special_notes]] <span class="label label-info label-xs">NEW</span></td>
                                    </tr>
                                    <tr>
                                        <td>[[bill_first_name]]</td>
                                        <td>[[ship_first_name]]</td>
                                        <td>[[order_number]]</td>
                                    </tr>
                                    <tr>
                                        <td>[[bill_last_name]]</td>
                                        <td>[[ship_last_name]]</td>
                                        <td>[[total_amount]]</td>
                                    </tr>
                                    <tr>
                                        <td>[[bill_email]]</td>
                                        <td>[[ship_email]]</td>
                                        <td>[[bill_card_type]]</td>
                                    </tr>
                                    <tr>
                                        <td>[[bill_address_01]]</td>
                                        <td>[[ship_address_01]]</td>
                                        <td>[[bill_card_last_4]]</td>
                                    </tr>
                                    <tr>
                                        <td>[[bill_address_02]]</td>
                                        <td>[[ship_address_02]]</td>
                                        <td>[[confirmation_number]]</td>
                                    </tr>
                                    <tr>
                                        <td>[[bill_city]]</td>
                                        <td>[[ship_city]]</td>
                                        <td>[[public_tracking_url]]</td>
                                    </tr>
                                    <tr>
                                        <td>[[bill_state]]</td>
                                        <td>[[ship_state]]</td>
                                        <td>[[admin_tracking_url]]</td>
                                    </tr>
                                    <tr>
                                        <td>[[bill_zip]]</td>
                                        <td>[[ship_zip]]</td>
                                        <td>[[invoice_table]]</td>
                                    </tr>
                                    <tr>
                                        <td>[[bill_country]]</td>
                                        <td>[[ship_country]]</td>
                                        <td>[[order_date]]</td>
                                    </tr>
                                    <tr>
                                        <td>[[bill_phone]]</td>
                                        <td>[[ship_phone]]</td>
                                        <td>[[shop_url]]</td>
                                    </tr>
                                    <tr>
                                        <td>[[bill_organization_name]]</td>
                                        <td>[[ship_organization_name]]</td>
                                        <td>[[shop_organization]]</td>
                                    </tr>
                                    <tr>
                                        <td>[[payment_type]]</td>
                                        <td>[[payment_type_description]]</td>
                                        <td>[[recurring_description]]</td>
                                    </tr>
                                </table><br />
                                <h3>Specific Product is Purchased</h3>
                                <table class="simple">
                                    <tr>
                                        <td>All Contribution Received merge tags</td>
                                        <td>[[product_name]]</td>
                                        <td>[[price]]</td>
                                    </tr>
                                    <tr>
                                        <td>[[quantity]]</td>
                                        <td>[[product_code]]</td>
                                        <td>[[original_price]]</td>
                                    </tr>
                                    <tr>
                                        <td>[[weight]]</td>
                                        <td>[[variant_name]]</td>
                                        <td>[[price_paid]]</td>
                                    </tr>
                                    <tr>
                                        <td>[[custom_field_01]]</td>
                                        <td>[[custom_field_02]]</td>
                                        <td>[[custom_field_<i>n</i>]]</td>
                                    </tr>
                                    <tr>
                                        <td>[[custom_field_value_01]]*</td>
                                        <td>[[custom_field_value_02]]*</td>
                                        <td>[[custom_field_value_<i>n</i>]]*</td>
                                    </tr>
                                    <tr>
                                        <td>[[tribute_name]] <span class="label label-info label-xs">NEW</span></td>
                                        <td>[[tribute_notification_type]] <span class="label label-info label-xs">NEW</span></td>
                                        <td>[[tribute_individual_name]] <span class="label label-info label-xs">NEW</span></td>
                                    </tr>
                                    <tr>
                                        <td>[[tribute_recipient_name]] <span class="label label-info label-xs">NEW</span></td>
                                        <td>[[tribute_recipient_email]] <span class="label label-info label-xs">NEW</span></td>
                                        <td>[[tribute_recipient_mailing_address]] <span class="label label-info label-xs">NEW</span></td>
                                    </tr>
                                    <tr>
                                        <td>[[tribute_tribute_message]] <span class="label label-info label-xs">NEW</span></td>
                                        <td>[[checkin_qr]]</td>
                                        <td>[[recurring_amount]]</td>
                                    </tr>
                                    <tr>
                                        <td>[[download_links]] <span class="label label-info label-xs">NEW</span></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                </table>
                                <small>Tags marked with an asterisk (*) will only be available for "select" or "multi-select" custom fields where you have enabled the option to choose both labels and values.</small>
                                <br />
                                <h3>eDownload is Purchased</h3>
                                <table class="simple">
                                    <tr>
                                        <td>All Contribution Received merge tags</td>
                                        <td>[[download_links]]</td>
                                    </tr>
                                </table><br />
                                <h3>Supporter Signup, Update or Password Reset</h3>
                                <table class="simple">
                                    <tr>
                                        <td>[[first_name]]</td>
                                        <td>[[bill_first_name]]</td>
                                        <td>[[ship_first_name]]</td>
                                    </tr>
                                    <tr>
                                        <td>[[last_name]]</td>
                                        <td>[[bill_last_name]]</td>
                                        <td>[[ship_last_name]]</td>
                                    </tr>
                                    <tr>
                                        <td>[[email]]</td>
                                        <td>[[bill_email]]</td>
                                        <td>[[ship_email]]</td>
                                    </tr>
                                    <tr>
                                        <td>[[created_at]]</td>
                                        <td>[[bill_address_01]]</td>
                                        <td>[[ship_address_01]]</td>
                                    </tr>
                                    <tr>
                                        <td>[[updated_at]]</td>
                                        <td>[[bill_address_02]]</td>
                                        <td>[[ship_address_02]]</td>
                                    </tr>
                                    <tr>
                                        <td>[[membership_expires_on]]</td>
                                        <td>[[bill_city]]</td>
                                        <td>[[ship_city]]</td>
                                    </tr>
                                    <tr>
                                        <td>[[profile_updates]] <i style="color:#999;">(Profile Updates Only)</i></td>
                                        <td>[[bill_state]]</td>
                                        <td>[[ship_state]]</td>
                                    </tr>
                                    <tr>
                                        <td>[[temporary_password]] <i style="color:#999;">(Password Reset Only)</i></td>
                                        <td>[[bill_zip]]</td>
                                        <td>[[ship_zip]]</td>
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td>[[bill_country]]</td>
                                        <td>[[ship_country]]</td>
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td>[[bill_phone]]</td>
                                        <td>[[ship_phone]]</td>
                                    </tr>
                                </table><br />
                                <h3>Tax Receipt</h3>
                                <table class="simple">
                                    <tr>
                                        <td>[[first_name]]</td>
                                        <td>[[name]] <i style="color:#999;">(Organization name or Person's Name Depending on Donor Type)</i></td>
                                        <td>[[full_address]] <i style="color:#999;">(Preformatted full address)</i></td>
                                    </tr>
                                    <tr>
                                        <td>[[last_name]]</td>
                                        <td>[[address_01]]</td>
                                        <td>[[city]]</td>
                                    </tr>
                                    <tr>
                                        <td>[[email]]</td>
                                        <td>[[address_02]]</td>
                                        <td>[[state]]</td>
                                    </tr>
                                    <tr>
                                        <td>[[number]] <i style="color:#999;">(Receipt number)</i></td>
                                        <td>[[amount]] <i style="color:#999;">(Receiptable amount)</i></td>
                                        <td>[[zip]]</td>
                                    </tr>
                                    <tr>
                                        <td>[[ordered_at]]</td>
                                        <td>[[issued_at]]</td>
                                        <td>[[summary_table]]</td>
                                    </tr>
                                </table><br />
                                <h3>Memberships</h3>
                                <table class="simple">
                                    <tr>
                                        <td>[[first_name]]</td>
                                        <td>[[bill_first_name]]</td>
                                        <td>[[ship_first_name]]</td>
                                    </tr>
                                    <tr>
                                        <td>[[last_name]]</td>
                                        <td>[[bill_last_name]]</td>
                                        <td>[[ship_last_name]]</td>
                                    </tr>
                                    <tr>
                                        <td>[[email]]</td>
                                        <td>[[bill_email]]</td>
                                        <td>[[ship_email]]</td>
                                    </tr>
                                    <tr>
                                        <td>[[membership_expiry_date]]</td>
                                        <td>[[bill_address_01]]</td>
                                        <td>[[ship_address_01]]</td>
                                    </tr>
                                    <tr>
                                        <td>[[membership_name]]</td>
                                        <td>[[bill_address_02]]</td>
                                        <td>[[ship_address_02]]</td>
                                    </tr>
                                    <tr>
                                        <td>[[membership_description]]</td>
                                        <td>[[bill_city]]</td>
                                        <td>[[ship_city]]</td>
                                    </tr>
                                    <tr>
                                        <td>[[shop_organization]]</i></td>
                                        <td>[[bill_state]]</td>
                                        <td>[[ship_state]]</td>
                                    </tr>
                                    <tr>
                                        <td>[[shop_url]]</td>
                                        <td>[[bill_zip]]</td>
                                        <td>[[ship_zip]]</td>
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td>[[bill_country]]</td>
                                        <td>[[ship_country]]</td>
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td>[[bill_phone]]</td>
                                        <td>[[ship_phone]]</td>
                                    </tr>
                                </table><br />
                                <h3>Sponsorship: Started</h3>
                                <table class="simple">
                                    <tr>
                                        <td>All Contribution Received merge tags</td>
                                        <td>[[first_name]]</td>
                                        <td>[[sponsorship_first_name]]</td>

                                    </tr>
                                    <tr>
                                        <td>[[sponsorship_image_raw]]</td>
                                        <td>[[last_name]]</td>
                                        <td>[[sponsorship_last_name]]</td>

                                    </tr>
                                    <tr>
                                        <td>[[sponsorship_image]]</td>
                                        <td>[[email]]</td>
                                        <td>[[sponsorship_bio]]</td>
                                    </tr>
                                    <tr>
                                        <td>[[sponsorship_source]]</td>
                                        <td>[[shop_organization]]</i></td>
                                        <td>[[sponsorship_start_date]]</td>

                                    </tr>
                                    <tr>
                                        <td>[[sponsorship_birth_date]]</td>
                                        <td>[[shop_url]]</i></td>
                                        <td>[[sponsorship_reference]]</td>
                                    </tr>
                                    <tr>
                                        <td>[[sponsorship_recurring_description]]</td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                </table><br />
                                <h3>Sponsorship: Ended</h3>
                                <table class="simple">
                                    <tr>
                                        <td>All Contribution Received merge tags</td>
                                        <td>[[first_name]]</td>
                                        <td>[[sponsorship_first_name]]</td>
                                    </tr>
                                    <tr>
                                        <td>[[last_name]]</td>
                                        <td>[[sponsorship_last_name]]</td>
                                        <td>[[shop_url]]</td>
                                    </tr>
                                    <tr>
                                        <td>[[sponsorship_end_date]]</td>
                                        <td>[[shop_organization]]</i></td>
                                        <td>[[sponsorship_ended_reason]]</td>
                                    </tr>
                                    <tr>
                                        <td>[[sponsorship_recurring_description]]</td>
                                        <td></td>
                                        <td></td>
                                    </tr>

                                </table><br />
                                <h3>Manual Recurring Payment: Reminder</h3>
                                <table class="simple">
                                    <tr>
                                        <td>[[first_name]]</td>
                                        <td>[[sponsorship_first_name]]</td>
                                    </tr>
                                    <tr>
                                        <td>[[last_name]]</td>
                                        <td>[[sponsorship_last_name]]</td>
                                    </tr>
                                    <tr>
                                        <td>[[email]]</td>
                                        <td>[[sponsorship_reference]]</td>
                                    </tr>
                                    <tr>
                                        <td>[[profile_next_bill_date]]</i></td>
                                        <td>[[product_name]]</td>
                                    </tr>
                                    <tr>
                                        <td>[[profile_amount]]</i></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td>[[profile_frequency]]</i></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td>[[profile_description]]</i></td>
                                        <td></td>
                                    </tr>
                                </table><br />
                                <h3>Fundraisers: Donation Notification</h3>
                                <table class="simple">
                                    <tr>
                                        <td>All Contribution Received merge tags</td>
                                        <td>[[page_name]]</td>
                                        <td>[[page_url]]</td>
                                    </tr>
                                    <tr>
                                        <td>[[page_deadline]]</td>
                                        <td>[[page_goal]]</td>
                                        <td>[[page_goal_amount_remaining]] <span class="label label-info label-xs">NEW</span></td>
                                    </tr>
                                    <tr>
                                        <td>[[page_author]]</td>
                                        <td>[[page_author_first_name]]</i></td>
                                        <td>[[page_author_url]]</td>
                                    </tr>
                                    <tr>
                                        <td>[[page_report_reason]]</i></td>
                                        <td>[[page_report_count]]</td>
                                        <td>[[page_amount_raised]] <span class="label label-info label-xs">NEW</span></td>
                                    </tr>
                                    <tr>
                                        <td>[[page_edit_url]] <span class="label label-info label-xs">NEW</span></td>
                                        <td>[[page_suspend_url]]</td>
                                        <td>&nbsp;</td>
                                    </tr>
                                </table>
                                IMPORTANT: Shortcodes do not work in emails.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>


</form>

<script>
spaContentReady(function() {
    onOffsetTypeChange = function (ev) {
        if (typeof ev !== 'undefined') {
            ev.preventDefault();
        }

        type = $('#day_offset_type option:selected').val();

        if (type == 'none') {
            $('.offset-days').addClass('hide');
        } else {
            $('.offset-days').removeClass('hide');
        }
    }

    onTypeChange = function (ev) {
        if (typeof ev !== 'undefined') {
            ev.preventDefault();
        }

        type = $('.email-type option:selected').val();

        // product_purchase, membership_expired, sponsorship_birthday, sponsorship_anniversary

        // day offset
        if (type === 'membership_expired' || type === 'sponsorship_birthday' || type === 'sponsorship_anniversary' || type === 'customer_manual_recurring_payment_reminder') {
            $('.dayoffset').removeClass('hide');
        } else {
            $('.dayoffset').addClass('hide');
        }

        // memberships / products
        $('.memberships, .products, .variants').addClass('hide');

        if (type === 'membership_expired') {
            $('.memberships').removeClass('hide');
        } else if (type === 'product_purchase') {
            $('.products').removeClass('hide');
        } else if (type === 'variant_purchase') {
            $('.variants').removeClass('hide');
        }

        // ui day offset
        onOffsetTypeChange();
    }

    $('.email-type').on('change', onTypeChange);
    $('#day_offset_type').on('change', onTypeChange);
    onTypeChange();
});
</script>
