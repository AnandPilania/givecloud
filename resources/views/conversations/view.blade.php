
@extends('layouts.app')
@section('title', 'Messenger Conversations')

@section('content')
<form id="conversation-app" @submit.prevent="submitConversationForm" :class="{ 'was-validated': conversation_validated }" data-vv-scope="conversation" novalidate v-cloak>

    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">
                <span class="page-header-text">
                    @if ($conversation->exists)
                        Edit conversation
                    @else
                        Create conversation
                    @endif
                </span>

                <div class="pull-right">
                    <button type="submit" class="btn btn-success" data-toggle="tooltip" data-placement="top" title="Save your changes."><i class="fa fa-check"></i><span class="hidden-sm hidden-xs"> Save</span></button>
                </div>
            </h1>
        </div>
    </div>

    <div id="settings-payment-gateway" class="row">
        <div class="col-sm-12">
            {{ app('flash')->output() }}
        </div>
        <div class="col-sm-8">
            <div class="panel panel-default">
                <div class="panel-heading">
                    General
                </div>
                <div class="panel-body">
                    <div class="form-row">
                        <div class="col-sm-3">
                            <div class="form-group floating-label" :class="{ 'has-errors': errors.has('conversation.enabled') }">
                                <label>
                                    Status
                                </label>
                                <select class="form-control" v-model="conversation.enabled" name="enabled" v-validate.initial="'required'">
                                    <option :value="true">Enabled</option>
                                    <option :value="false">Disabled</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-9">
                            <div class="form-group floating-label" :class="{ 'has-errors': errors.has('conversation.conversation_type') }">
                                <label>
                                    Conversation Type
                                </label>
                                <select class="form-control" v-model="conversation.conversation_type" name="conversation_type" v-validate.initial="'required'">
                                    <option v-for="conversation_type in conversation_types" :value="conversation_type.id">${conversation_type.label}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="form-group floating-label conversation-command" :class="{ 'has-errors': errors.has('conversation.command') }">
                        <label>
                            <strong>Listening For</strong> (ie. how the conversation begins)
                        </label>
                        <input type="text" class="form-control" v-model="conversation.command" name="command" maxlength="128" v-validate.initial="'required'" :placeholder="command_placeholder">
                        <p class="help-block" v-if="parameters">
                            <i class="fa fa-exclamation-circle" aria-hidden="true"></i> Must include the
                            <template v-if="parameters.length > 1">
                                <template v-for="(parameter, index) in parameters">
                                    <template v-if="index < parameters.length - 1">
                                        <span class="badge parameter">{${parameter}}</span>,
                                    </template>
                                    <template v-else>
                                        and <span class="badge parameter">{${parameter}}</span>
                                    </template>
                                </template>
                                parameters
                            </template>
                            <template v-else>
                                <span class="badge parameter">{${parameters[0]}}</span> parameter
                            </template>
                            as required by the conversation type.
                        </p>
                    </div>
                    <div class="alert alert-warning help-block">
                        The following are opt-in/out keywords reserved for compliance with industry rules and regulations for opt-out handling.
                        <div style="margin-top:10px;">
                            <ul class="d-inline-block" style="vertical-align:top;font-size:12px;margin-bottom:0">
                                <li>INFO</li>
                                <li>HELP</li>
                                <li>START</li>
                                <li>UNSTOP</li>
                            </ul>
                            <ul class="d-inline-block" style="vertical-align:top;font-size:12px;margin-bottom:0">
                                <li>CANCEL</li>
                                <li>END</li>
                                <li>STOP</li>
                                <li>STOPALL</li>
                            </ul>
                            <ul class="d-inline-block" style="vertical-align:top;font-size:12px;margin-bottom:0">
                                <li>UNSUBSCRIBE</li>
                                <li>QUIT</li>
                            </ul>
                        </div>
                    </div>
                    <hr>
                    <div class="form-row">
                        <div class="col-sm-8">
                            <div class="form-group floating-label">
                                <label>
                                    Messaging Client(s)
                                </label>
                                <vue-multiselect class="form-control tag-select" v-model="conversation.recipients" :options="recipients" :multiple="true" :hide-selected="true" :taggable="true" track-by="id" label="identifier" placeholder="Add recipient(s) to this conversation..."></vue-multiselect>
                                <p class="help-block text-small">
                                    <i class="fa fa-exclamation-circle" aria-hidden="true"></i>
                                    If left blank this conversation will be available to <strong>ALL</strong> recipients.
                                </p>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group floating-label">
                                <label>
                                    Tracking Source
                                </label>
                                <input type="text" class="form-control" v-model="conversation.tracking_source" name="tracking_source" maxlength="50">
                                <p class="help-block text-small">
                                    Used to populate the Tracking Source on contributions created in the course of the conversation.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-4">

            <div ref="metadata" v-html="configuration.metadata_html"></div>

        </div>
    </div>

</form>

<style>
#conversation-app .panel.panel-default .panel-body {
    padding-bottom: 0;
}
#conversation-app .badge.parameter {
    padding: 3px 4px;
    background-color: #ffe6e6;
    color: #a20000;
    border-radius: 0;
}
#conversation-app hr {
    margin-left: -20px;
    margin-right: -20px;
}
</style>

<script>
spaContentReady(function($) {
    window.conversationApp = new Vue({
        el: '#conversation-app',
        delimiters: ['${', '}'],
        data: {
            conversation: {!! json_encode([
                'id'                => $conversation->id,
                'enabled'           => $conversation->enabled,
                'name'              => $conversation->name,
                'recipient'         => $conversation->recipient,
                'command'           => $conversation->command,
                'conversation_type' => $conversation->conversation_type,
                'tracking_source'   => $conversation->tracking_source,
                'recipients'        => $conversation->recipients,
                'metadata'          => null,
            ]) !!},
            error: null,
            conversation_validated: false,
            conversation_types: {!! json_encode($conversationTypes) !!},
            recipients: {!! json_encode($recipients) !!},
        },
        mounted: function() {
            jQuery(this.$refs.recipients).selectize();
        },
        computed: {
            configuration: function() {
                var type = this.conversation.conversation_type;
                if (type) {
                    return this.conversation_types[type];
                }
            },
            parameters: function() {
                if (this.configuration && _.size(this.configuration.parameters)) {
                    return Object.keys(this.configuration.parameters);
                }
            },
            command_placeholder: function() {
                if (this.configuration) {
                    return 'What to listen for... (Ex. ' + this.configuration.example + ')';
                }
            },
        },
        watch: {
            'conversation.conversation_type': function() {
                this.$nextTick(j.ui.formatSpecialFields);
            },
        },
        methods: {
            syncMetadataFields: function() {
                this.conversation.metadata = {};
                jQuery(this.$refs.metadata).find('[name^=metadata]').serializeArray()
                    .forEach(function(data) {
                        Sugar.Object.set(this.conversation, data.name, data.value);
                    }.bind(this));
            },
            submitConversationForm: function(event) {
                this.syncMetadataFields();
                this.$validator.validateAll('conversation')
                    .then(function(result) {
                        if (result) {
                            this.doConversationRequest();
                        } else {
                            this.conversation_validated = true;
                        }
                    }.bind(this));
            },
            doConversationRequest: function() {
                axios.post('/jpanel/messenger/conversations', this.conversation)
                    .then(function(res) {
                        top.location.href = '/jpanel/messenger/conversations/' + res.data.conversation.id;
                    }.bind(this))
                    .catch(function(err) {
                        if (err.response) {
                            toastr.error(err.response.data.error);
                        }
                    });
            },
        }
    });
});
</script>
@endsection
