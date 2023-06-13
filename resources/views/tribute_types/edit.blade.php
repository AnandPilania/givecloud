
@extends('layouts.app')
@section('title', $pageTitle)

@section('content')
<form action="/jpanel/tribute_types/{{ ($tributeType->exists) ? $tributeType->id . '/update' : 'new' }}" method="post">
@csrf

<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">
            <span class="page-header-text">{{ $pageTitle }}</span>

            <div class="visible-xs-block"></div>

            <div class="pull-right">
                @if ($tributeType->userCan('add'))<button type="submit" class="btn btn-success"><i class="fa fa-check"></i><span class="hidden-xs hidden-sm"> Save</span></button>@endif
                @if ($tributeType->userCan('edit'))<button type="button" class="btn btn-danger" onclick="$.confirm('Are you sure you want to delete this tribute type?', function(){ window.location = '/jpanel/tribute_types/{{ $tributeType->id }}/delete'; }, 'danger', 'fa-trash');"><i class="fa fa-trash"></i></button>@endif
                <a href="/jpanel/tributes?type={{ $tributeType->id }}" class="btn btn-info"><i class="fa fa-bar-chart"></i><span class="hidden-xs hidden-sm"> Sales</span></a>
            </div>
        </h1>
    </div>
</div>

@inject('flash', 'flash')

{{ $flash->output() }}

<div class="row">

    <div class="col-sm-12">

        <div class="panel panel-default">
            <div class="panel-body">

                <div class="bottom-gutter">
                    <div class="panel-sub-title"><i class="fa fa-gift"></i> Tribute</div>
                    <div class="panel-sub-desc">
                        These are general settings specific to this tribute type.
                    </div>
                </div>

                <div class="form-horizontal">

                    <div class="form-group">
                        <label class="col-md-2 control-label">Sequence</label>
                        <div class="col-md-10">
                            <input type="text" class="form-control" style="width:80px;" name="sequence" value="{{ $tributeType->sequence }}">
                            <small class="text-muted">This determines the order your tribute types display on your website when a donor is selecting their tribute type.</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-2 control-label">Label</label>
                        <div class="col-lg-4 col-md-5 col-sm-9">
                            <input type="text" class="form-control" autofocus name="label" value="{{ $tributeType->label }}">
                            <small class="text-muted">The name or label of this tribute type.<br>(Ex: In-Memory or Birthday Wishes)</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-2 control-label">Status</label>
                        <div class="col-md-8">
                            <input type="checkbox" class="switch" value="1" name="is_enabled" @checked($tributeType->is_enabled)>
                            <small class="text-muted"><br>Determines whether or not this tribute is live on your account or not.</small>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        @if (user()->can('admin.dpo') and dpo_is_enabled())
            <div role="tabpanel" class="tab-pane" id="dpo-integration">
                <div class="panel panel-info">
                    <div class="panel-heading">
                        <img src="/jpanel/assets/images/dp-blue.png" class="dp-logo inline"> DonorPerfect Integration

                        <div class="btn-group pull-right">
                            <button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                <i class="fa fa-gear fa-fw"></i> <i class="fa fa-caret-down"></i>
                            </button>
                            <ul class="dropdown-menu slidedown">
                                <li><a href="#" class="dpo-codes-refresh"><i class="fa fa-refresh fa-fw"></i> Refresh DonorPerfect Codes</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="panel-body">

                        <div class="form-horizontal">

                            <div class="form-group">
                                <label for="default_url" class="col-sm-6 control-label col-md-3 col-md-offset-1">
                                    Tribute Type<br>
                                    <small class="text-muted">Choose the corresponding DonorPerfect Tribute type that these tributes will map to.</small>
                                </label>
                                <div class="col-sm-4">
                                    <input type="text" class="form-control dpo-codes" data-code="MEMORY_HONOR" name="dp_id" id="dp_id" value="{{ $tributeType->dp_id }}" maxlength="11" />
                                </div>
                            </div>

                        </div>

                    </div>
                </div>
            </div>
        @endif

        <div class="panel panel-default">
            <div class="panel-body">

                <div class="bottom-gutter">
                    <div class="panel-sub-title"><i class="fa fa-envelope"></i> Email Settings</div>
                    <div class="panel-sub-desc">
                        This is the email that will be triggered when your donor requests an email be sent to someone notifying them of the tribute donation they have made.
                    </div>
                </div>

                <div class="form-horizontal">

                    <div class="form-group">
                        <label class="col-md-2 control-label">Subject</label>
                        <div class="col-md-10">
                            <input type="text" class="form-control" name="email_subject" value="{{ $tributeType->email_subject }}">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-2 control-label">Cc</label>
                        <div class="col-md-10">
                            <input type="text" class="form-control" name="email_cc" value="{{ $tributeType->email_cc }}">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-2 control-label">Bcc</label>
                        <div class="col-md-10">
                            <input type="text" class="form-control" name="email_bcc" value="{{ $tributeType->email_bcc }}">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-2 control-label">Email</label>
                        <div class="col-md-10">
                            <textarea class="form-control html-tribute" style="height:400px;" name="email_template">{{ $tributeType->email_template }}</textarea>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-2">&nbsp;</div>
                        <div class="col-md-10">
                            <div class="alert alert-info">
                                Click to access the <a onclick="$('#merge-tag-cheatsheet').toggle(); return false;">merge tag
                                    cheat-sheet</a>.
                                <div class="message_expand" style="display:none;" id="merge-tag-cheatsheet">
                                    <h3>Tribute Notification</h3>
                                    <table class="simple">
                                        <tr>
                                            <td>[[donor_title]] <span class="label label-info label-xs">NEW</span></td>
                                            <td>[[donor_first_name]]</td>
                                            <td>[[donor_last_name]]</td>
                                        </tr>
                                        <tr>
                                            <td>[[donor_email]] <span class="label label-info label-xs">NEW</span></td>
                                            <td>[[tribute_type]]</td>
                                            <td>[[product_name]]</td>
                                        </tr>
                                        <tr>
                                            <td>[[notification_type]] <span class="label label-info label-xs">NEW</span></td>
                                            <td>[[name]]</td>
                                            <td>[[message]] <span class="label label-info label-xs">NEW</span></td>
                                        </tr>
                                        <tr>
                                            <td>[[recipient_name]] <span class="label label-info label-xs">NEW</span></td>
                                            <td>[[recipient_email]] <span class="label label-info label-xs">NEW</span></td>
                                            <td>[[recipient_mailing_address]] <span class="label label-info label-xs">NEW</span></td>
                                        </tr>
                                        <tr>
                                            <td>[[amount]]</td>
                                            <td>[[notify_at]]</td>
                                            <td>[[notified_at]]</td>
                                        </tr>
                                        <tr>
                                            <td>[[custom_field_01]]</td>
                                            <td>[[custom_field_02]]</td>
                                            <td>[[custom_field_<i>n</i>]]</td>
                                        </tr>
                                        <tr>
                                            <td>[[product_category_01]]</td>
                                            <td>[[product_category_02]]</td>
                                            <td>[[product_category_<i>n</i>]]</td>
                                        </tr>
                                        <tr>
                                            <td>[[created_at]]</td>
                                            <td>&nbsp;</td>
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

        <div class="panel panel-default">

            <div class="panel-body">

                <div class="bottom-gutter">
                    <div class="panel-sub-title"><i class="fa fa-pencil"></i> Letter Template</div>
                    <div class="panel-sub-desc">
                        This is the secure PDF letter that will be generated for every tribute of this type. If your donor requests a letter to be mailed, this is the letter that will be generated to be mailed. If a donor requests an email notification be sent to a receipient, this letter will be automatically attached to that email notification.

                        <!--<br><br>
                        To add merge codes, simply type [[ in the text editor and GC will list out all possible options for you to pick from.-->
                    </div>
                </div>

                <div class="form-horizontal">

                    <div class="form-group">
                        <div class="col-md-12">
                            <textarea class="form-control html-tribute" style="height:400px;" name="letter_template">{{ $tributeType->letter_template }}</textarea>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>

</div>


</form>
@endsection
