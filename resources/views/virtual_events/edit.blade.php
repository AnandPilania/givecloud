
@extends('layouts.app')
@section('title', 'Virtual Events')

@section('content')

    <div id="virtual-events-edit-screen"
        v-cloak
        data-id="{{ $event->id }}"
        data-name="{{ $event->name }}"
        data-slug="{{ $event->slug }}"
        data-logo="{{ $event->logo }}"
        data-base-url="{{ $base_url }}"
        data-background-image="{{ $event->background_image }}"
        data-theme-style="{{ $event->theme_style }}"
        data-theme-primary-color="{{ $event->theme_primary_color }}"
        data-start-date="{{ $event->start_date ? date("M j, Y", strtotime($event->start_date)) : '' }}"
        data-video-source="{{ $event->video_source }}"
        data-video-id="{{ $event->video_id }}"
        data-chat-id="{{ $event->chat_id }}"
        data-has-stream="{{ $hasStream }}"
        data-stream-url="{{ $streamUrl }}"
        data-stream-key="{{ $streamKey }}"
        data-is-amount-tally-enabled="{{ $event->is_amount_tally_enabled }}"
        data-is-honor-roll-enabled="{{ $event->is_honor_roll_enabled }}"
        data-is-emoji-reaction-enabled="{{ $event->is_emoji_reaction_enabled }}"
        data-is-celebration-enabled="{{ $event->is_celebration_enabled }}"
        data-is-chat-enabled="{{ $event->is_chat_enabled }}"
        data-celebration-threshold="{{ $event->celebration_threshold }}"
        data-prestream-message-line-one="{{ $event->prestream_message_line_1 }}"
        data-prestream-message-line-two="{{ $event->prestream_message_line_2 }}"
        data-tab-one-label="{{ $event->tab_one_label }}"
        data-tab-one-product-id="{{ $event->tab_one_product_id }}"
        data-tab-two-label="{{ $event->tab_two_label }}"
        data-tab-two-product-id="{{ $event->tab_two_product_id }}"
        data-tab-three-label="{{ $event->tab_three_label }}"
        data-tab-three-product-id="{{ $event->tab_three_product_id }}"
    >
        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header">
                    <span class="page-header-text">${ formattedPageTitle }</span>

                    <div class="pull-right">
                        <?php if (user()->can('virtualevents.edit')): ?>
                            <button ref="submit_button" type="button" v-on:click="onSubmit" class="btn btn-success" data-loading-text="Saving..."><i class="fa fa-check fa-fw"></i><span class="hidden-xs hidden-sm"> Save</span></button>
                            <button ref="delete_button" type="button" v-on:click="onDelete" class="btn btn-danger <?= e(($isNew == 1) ? 'hidden' : '') ?>" data-loading-text="Deleting..."><i class="fa fa-times fa-fw"></i><span class="hidden-xs hidden-sm"> Delete</span></button>
                        <?php endif; ?>
                    </div>

                    <div class="text-secondary">
                        @if ($event->exists)
                            <a :href="eventUrl" target="_blank">${ eventUrl }</a> <a href="#" data-toggle="modal" data-target="#modal-event-slug"><i class="fa fa-pencil-square"></i></a>
                        @endif
                    </div>
                </h1>
            </div>
        </div>

        <?= dangerouslyUseHTML(app('flash')->output()) ?>

        <div class="form-horizontal">

            <div class="panel panel-default">
                <div class="panel-heading">Event Information</div>
                <div class="panel-body">

                    <div class="form-group">
                        <label for="name" class="col-sm-4 control-label">Name</label>
                        <div class="col-sm-8">
                            <input id="name" required type="text" class="form-control" v-model="input.name" name="name">
                            <div v-if="formErrors.name" v-for="(error, index) in formErrors.name" class="alert alert-danger my-2">
                                ${ error }
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="start_date" class="col-sm-4 control-label">Date</label>
                        <div class="col-sm-8">
                            <div class="input-group input-group-transparent">
                                <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                                <input type="text" style="width:125px;" ref="start_date" class="form-control" name="start_date" v-model="input.start_date" maxlength="20">
                            </div>
                            <div v-if="formErrors.start_date" v-for="(error, index) in formErrors.start_date" class="alert alert-danger my-2">
                                ${ error }
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <div class="panel panel-default">
                <div class="panel-heading">Video Stream</div>
                <div class="panel-body">

                    <div class="form-group">
                        <label for="video_source" class="col-sm-4 control-label">Video Provider</label>
                        <div class="col-sm-8">

                            <select class="form-control" v-model="input.video_source" name="video_source" id="video_source" v-validate.initial="'required'">
                                <option value="" disabled>Please select One</option>
                                <option value="vimeo">Vimeo</option>
                                <option value="youtube">Youtube</option>
                                @if (feature('virtual_events_mux_streaming'))
                                    <option value="mux">Direct - Powered by Mux (Free During Beta)</option>
                                @endif
                            </select>

                            <div v-if="formErrors.video_source" v-for="(error, index) in formErrors.video_source" class="alert alert-danger my-2">
                                ${ error }
                            </div>
                        </div>
                    </div>

                    <div v-if="input.video_source === 'mux'"  class="col-sm-offset-4 col-sm-8 alert alert-warning">
                        <div v-if="!hasStream">
                            Stream will be setup on save
                        </div>
                        <div v-if="hasStream">
                            <h4 class="m-0 pb-2">Use the information below to connect to your encoding software</h4>
                            <p class="pb-2">Paste the URL and stream key into your software's settings and begin streaming</p>
                            <div class="pt-2 pb-2">
                                <p>RTMP URL:</p>
                                <div class="input-group" style="max-width:400px;">
                                    <input type="text" class="form-control" :value="streamUrl" readonly>
                                    <span class="input-group-btn">
                                        <button class="btn btn-default" v-on:click="copyStreamUrl" type="button">Copy</button>
                                    </span>
                                </div>
                            </div>
                            <div class="pt-2 pb-2">
                                <p>Stream Key:</p>
                                <div class="input-group" style="max-width:400px;">
                                    <input type="text" class="form-control" :value="streamKey" readonly>
                                    <span class="input-group-btn">
                                        <button class="btn btn-default" v-on:click="copyStreamKey" type="button">Copy</button>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="input.video_source === 'mux'">
                        <div class="form-group">
                            <label for="video_source" class="col-sm-4 control-label">
                                <div>Pre-Stream Message</div>
                                <small>These two lines of text will show on the screen before the stream starts.</small>
                            </label>
                            <div class="col-sm-8">
                                <div class="mb-4">
                                    <input id="prestream_message_line_1" required type="text" class="form-control" v-model="input.prestream_message_line_1" name="prestream_message_line_1" :placeholder="prestreamMessageLineOnePlaceHolder">
                                    <div v-if="formErrors.prestream_message_line_1" v-for="(error, index) in formErrors.prestream_message_line_1" class="alert alert-danger my-2">
                                        ${ error }
                                    </div>
                                </div>
                                <input id="prestream_message_line_2" required type="text" class="form-control" v-model="input.prestream_message_line_2" name="prestream_message_line_2" :placeholder="prestreamMessageLineTwoPlaceHolder">
                                <div v-if="formErrors.prestream_message_line_2" v-for="(error, index) in formErrors.prestream_message_line_2" class="alert alert-danger my-2">
                                    ${ error }
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="input.video_source === 'vimeo' || input.video_source === 'youtube'">

                        <div class="col-sm-8 col-sm-offset-4">
                            <div class="form-group" v-if="input.video_source === 'vimeo'">
                                <div class="alert alert-warning" role="alert"><strong>Please Note:</strong> You must have a Vimeo "Premium" account to be able to use this option.</div>
                            </div>
                            <div class="form-group" v-if="input.video_source === 'youtube'">
                                <div class="alert alert-warning" role="alert"><strong>Please Note:</strong> You must have a monetized Youtube account to be able to use this option.</div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="video_id" class="col-sm-4 control-label">Video ID</label>
                            <div class="col-sm-8">

                                <input id="video_id" required type="text" class="form-control" v-model="input.video_id" name="video_id">
                                <small v-if="input.video_source === 'vimeo'"><a href="#" data-toggle="modal" data-target="#modal-vimeo-help-video-id">Where do I find this?</a></small>
                                <small v-if="input.video_source === 'youtube'"><a href="#" data-toggle="modal" data-target="#modal-youtube-help-video-id">Where do I find this?</a></small>
                                <div v-if="formErrors.video_id" v-for="(error, index) in formErrors.video_id" class="alert alert-danger my-2">
                                    ${ error }
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group" v-if="input.video_source === 'vimeo'">
                        <label for="chat_id" class="col-sm-4 control-label">Chat ID</label>
                        <div class="col-sm-8">

                            <input id="chat_id" required type="text" class="form-control" v-model="input.chat_id" name="chat_id">
                            <small v-if="input.video_source === 'vimeo'"><a href="#" data-toggle="modal" data-target="#modal-vimeo-help-chat-id">Where do I find this?</a></small>
                            <div v-if="formErrors.chat_id" v-for="(error, index) in formErrors.chat_id" class="alert alert-danger my-2">
                                ${ error }
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <div class="panel panel-default">
                <div class="panel-heading">Page Design</div>
                <div class="panel-body">

                    <div class="form-group">
                        <label for="logo" class="col-sm-4 control-label">Logo</label>
                        <div class="col-sm-8">
                            <div class="row">
                                <div class="col-sm-9 col-xs-9">
                                    <div class="input-group">
                                        <div class="input-group-btn">
                                            <button ref="logo_image_browser_button" type="button" class="btn btn-default image-browser" data-image-browser-output="logo"><i class="fa fa-folder-open-o"></i></button>
                                        </div>
                                        <input id="logo" type="text" class="form-control" v-model="input.logo" name="logo">
                                    </div>
                                </div>
                                <div class="col-sm-3 col-xs-3">
                                    <div :style="'background-image:url(' + input.logo + '); width: 35px; height: 35px; border-radius: 35px; background-size: contain; background-repeat: no-repeat; background-position: center center; border: 1px solid #ccc;'"></div>
                                </div>
                            </div>
                            <div v-if="formErrors.logo" v-for="(error, index) in formErrors.logo" class="alert alert-danger my-2">
                                ${ error }
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="background_image" class="col-sm-4 control-label">Background Image</label>
                        <div class="col-sm-8">
                            <div class="row">
                                <div class="col-sm-9 col-xs-9">
                                    <div class="input-group">
                                        <div class="input-group-btn">
                                            <button ref="background_image_browser_button" type="button" class="btn btn-default image-browser" data-image-browser-output="background_image"><i class="fa fa-folder-open-o"></i></button>
                                        </div>
                                        <input id="background_image" type="text" class="form-control" v-model="input.background_image" name="background_image">
                                    </div>
                                </div>
                                <div class="col-sm-3 col-xs-3">
                                    <div :style="'background-image:url(' + input.background_image + '); width: 35px; height: 35px; border-radius: 35px; background-size: cover; background-repeat: no-repeat; background-position: center center; border: 1px solid #ccc;'"></div>
                                </div>
                            </div>
                            <div v-if="formErrors.background_image" v-for="(error, index) in formErrors.background_image" class="alert alert-danger my-2">
                                ${ error }
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="theme_style" class="col-sm-4 control-label">Theme Style</label>
                        <div class="col-sm-8">

                            <select class="form-control max-w-xs" v-model="input.theme_style" name="theme_style" id="theme_style" v-validate.initial="'required'">
                                @foreach($theme_styles as $key => $value)
                                    <option value="{{ $key }}">{{ $value }}</option>
                                @endforeach
                            </select>

                            <div v-if="formErrors.theme_style" v-for="(error, index) in formErrors.theme_style" class="alert alert-danger my-2">
                                ${ error }
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="theme_primary_color" class="col-sm-4 control-label">Theme Primary Color</label>
                        <div class="col-sm-8">

                            <select class="form-control max-w-xs" v-model="input.theme_primary_color" name="theme_primary_color" id="theme_primary_color" v-validate.initial="'required'">
                                @foreach($theme_primary_colors as $key => $value)
                                    <option value="{{ $key }}">{{ $value }}</option>
                                @endforeach
                            </select>

                            <div v-if="formErrors.theme_primary_color" v-for="(error, index) in formErrors.theme_primary_color" class="alert alert-danger my-2">
                                ${ error }
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="is_amount_tally_enabled" class="col-sm-4 control-label">Enable Amount Tally</label>
                        <div class="col-sm-8">
                            <input ref="is_amount_tally_enabled" id="is_amount_tally_enabled" type="checkbox" class="switch" name="is_amount_tally_enabled" v-model="input.is_amount_tally_enabled" >
                            <div v-if="formErrors.is_amount_tally_enabled" v-for="(error, index) in formErrors.is_amount_tally_enabled" class="alert alert-danger my-2">
                                ${ error }
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="is_chat_enabled" class="col-sm-4 control-label">Enable Chat</label>
                        <div class="col-sm-8">
                            <input ref="is_chat_enabled" id="is_chat_enabled" type="checkbox" class="switch" name="is_chat_enabled" v-model="input.is_chat_enabled" >
                            <div v-if="formErrors.is_chat_enabled" v-for="(error, index) in formErrors.is_chat_enabled" class="alert alert-danger my-2">
                                ${ error }
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="is_honor_roll_enabled" class="col-sm-4 control-label">Enable Honor Roll</label>
                        <div class="col-sm-8">
                            <input ref="is_honor_roll_enabled" id="is_honor_roll_enabled" type="checkbox" class="switch" name="is_honor_roll_enabled" v-model="input.is_honor_roll_enabled" >
                            <div><small class="text-muted">When enabled, your participants will be able to see who gave (unless the supporter chose to be anonymous).</small></div>
                            <div v-if="formErrors.is_honor_roll_enabled" v-for="(error, index) in formErrors.is_honor_roll_enabled" class="alert alert-danger my-2">
                                ${ error }
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="is_emoji_reaction_enabled" class="col-sm-4 control-label">Enable Emoji Reactions</label>
                        <div class="col-sm-8">
                            <input ref="is_emoji_reaction_enabled" id="is_emoji_reaction_enabled" type="checkbox" class="switch" name="is_emoji_reaction_enabled" v-model="input.is_emoji_reaction_enabled" >
                            <div><small class="text-muted">When enabled, your participants will be able to react with emojis that will float up the screen.</small></div>
                            <div v-if="formErrors.is_emoji_reaction_enabled" v-for="(error, index) in formErrors.is_emoji_reaction_enabled" class="alert alert-danger my-2">
                                ${ error }
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="is_celebration_enabled" class="col-sm-4 control-label">Enable Celebration</label>
                        <div class="col-sm-8">
                            <input ref="is_celebration_enabled" id="is_celebration_enabled" type="checkbox" class="switch" name="is_celebration_enabled" v-model="input.is_celebration_enabled" >
                            <div><small class="text-muted">When enabled, confetti will fall from the top of the screen when someone gives a donation greater or equal to the threshold amount below.</small></div>
                            <div v-if="formErrors.is_celebration_enabled" v-for="(error, index) in formErrors.is_celebration_enabled" class="alert alert-danger my-2">
                                ${ error }
                            </div>
                        </div>
                    </div>

                    <div class="form-group" v-if="input.is_celebration_enabled">
                        <label for="celebration_threshold" class="col-sm-4 control-label">Celebration Threshold</label>
                        <div class="col-sm-8">

                            <div class="input-group">
                                <div class="input-group-addon"><i class="fa fa-dollar fa-fw"></i></div>
                                <input id="celebration_threshold" type="text" class="form-control" name="celebration_threshold" v-model="input.celebration_threshold" />
                            </div>
                            <small class="text-muted">A celebration (confetti) will be triggered at this dollar amount and above.</small>

                            <div v-if="formErrors.celebration_threshold" v-for="(error, index) in formErrors.celebration_threshold" class="alert alert-danger my-2">
                                ${ error }
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <div class="panel panel-default">
                <div class="panel-heading">Donation Sidebar</div>
                <div class="panel-body">

                    <div class="row hidden-xs pb-3">
                        <div class="col-sm-offset-3 col-sm-4">
                            <strong>Label</strong>
                        </div>
                        <div class="col-sm-5">
                            <strong>Donation Item</strong>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="tab_one_label" class="col-sm-3 control-label">First Tab</label>
                        <div class="col-sm-4">
                            <input id="tab_one_label" type="text" class="form-control" v-model="input.tab_one_label" name="tab_one_label" placeholder="Label">
                            <div v-if="formErrors.tab_one_label" v-for="(error, index) in formErrors.tab_one_label" class="alert alert-danger my-2">
                                ${ error }
                            </div>
                        </div>
                        <div class="col-sm-5">
                            <input ref="tab_one_product_id" type="text" name="tab_one_product_id" class="form-control ds-products auto-height" v-model="input.tab_one_product_id" data-is-donation="1" value="{{ $event->tab_one_product_id }}" id="tab_one_product_id" placeholder="Donation Item" />
                            <div v-if="formErrors.tab_one_product_id" v-for="(error, index) in formErrors.tab_one_product_id" class="alert alert-danger my-2">
                                ${ error }
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="tab_two_label" class="col-sm-3 control-label">Second Tab</label>
                        <div class="col-sm-4">
                            <input id="tab_two_label" type="text" class="form-control" v-model="input.tab_two_label" name="tab_two_label" placeholder="Label">
                            <div v-if="formErrors.tab_two_label" v-for="(error, index) in formErrors.tab_two_label" class="alert alert-danger my-2">
                                ${ error }
                            </div>
                        </div>
                        <div class="col-sm-5">
                            <input ref="tab_two_product_id" type="text" name="tab_two_product_id" class="form-control ds-products auto-height" v-model="input.tab_two_product_id" data-is-donation="1" value="{{ $event->tab_two_product_id }}" id="tab_two_product_id" placeholder="Donation Item" />
                            <div v-if="formErrors.tab_two_product_id" v-for="(error, index) in formErrors.tab_two_product_id" class="alert alert-danger my-2">
                                ${ error }
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="tab_three_label" class="col-sm-3 control-label">Third Tab</label>
                        <div class="col-sm-4">
                            <input id="tab_three_label" type="text" class="form-control" v-model="input.tab_three_label" name="tab_three_label" placeholder="Label">
                            <div v-if="formErrors.tab_three_label" v-for="(error, index) in formErrors.tab_three_label" class="alert alert-danger my-2">
                                ${ error }
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <input ref="tab_three_product_id" type="text" name="tab_three_product_id" class="form-control ds-products auto-height" v-model="input.tab_three_product_id" data-is-donation="1" value="{{ $event->tab_three_product_id }}" id="tab_three_product_id" placeholder="Donation Item" />
                            <div v-if="formErrors.tab_three_product_id" v-for="(error, index) in formErrors.tab_three_product_id" class="alert alert-danger my-2">
                                ${ error }
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-8 col-sm-offset-3">
                            <div class='alert alert-danger'>
                                <ul class='pl-4'>
                                    <li>If you are using custom fields or tributes for the product, they will not show up on the embeddable form.</li>
                                    <?php if ($gocardlessInstalled): ?>
                                        <li>The GoCardless integration will not show up on the embeddable form.</li>
                                    <?php endif; ?>
                                    <?php if ($paysafeInstalled): ?>
                                        <li>The Paysafe integration will not show up on the embeddable form.</li>
                                    <?php endif; ?>
                                    <?php if ($paypalInstalled): ?>
                                        <li>The Paypal integration will not show up on the embeddable form on mobile devices.</li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div ref="slug_modal" class="modal modal-info fade" id="modal-event-slug">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title"><i class="fa fa-link"></i> Change Public URL</h4>
                    </div>
                    <div class="modal-body">

                        <div class="alert alert-warning">
                            <i class="fa fa-exclamation-triangle"></i> Changing this public URL will break all existing links to this virtual event.
                        </div>

                        <div class="form-group">
                            <label class="control-label">Public Url</label>
                            <div class="input-group">
                                <div class="input-group-addon">{{ $base_url }}/</div>
                                <input type="text" class="form-control" name="url_name" v-model="slug.inputValue" placeholder="event-name-goes-here">
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button ref="update_slug_button" type="button" v-on:click="onUpdateSlug" class="btn btn-primary" data-loading-text="Saving...">Save</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal modal-info fade" id="modal-youtube-help-video-id">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title"><i class="fa fa-question-circle-o"></i> Where is the Youtube Video ID?</h4>
                    </div>
                    <div class="modal-body">

                        <img src="/jpanel/assets/images/in-app-help/virtual_event_youtube_help_video_id.png" />

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal modal-info fade" id="modal-vimeo-help-video-id">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title"><i class="fa fa-question-circle-o"></i> Where is the Vimeo Video ID?</h4>
                    </div>
                    <div class="modal-body">

                        <img src="/jpanel/assets/images/in-app-help/virtual_event_vimeo_help_video_id.png" />

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal modal-info fade" id="modal-vimeo-help-chat-id">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title"><i class="fa fa-question-circle-o"></i> Where is the Vimeo Chat ID?</h4>
                    </div>
                    <div class="modal-body">

                        <img src="/jpanel/assets/images/in-app-help/virtual_event_vimeo_help_chat_id.png" />

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

    </div>



@endsection
