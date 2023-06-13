<template>
    <div class="panel" :class="{ 'panel-info': dpEnabled, 'panel-default': !dpEnabled }">
        <div class="panel-heading">
            <img v-if="dpEnabled" src="/jpanel/assets/images/dp-blue.png" class="dp-logo inline" />
            Account Designation
        </div>
        <div class="p-4 pb-7">
            <input ref="meta1" type="hidden" name="meta1" />
            <input ref="designation_options" type="hidden" name="designation_options" />

            <p
                v-if="show_dp_warning_for_variant_level_coding"
                class="mb-6 px-4 py-2 rounded-md text-sm font-medium bg-red-100 text-red-800"
            >
                <template v-if="designation_style === 'single_account'">
                    <strong><i class="fas fa-exclamation-triangle"></i> Warning:</strong>
                    You have DP GL Codes on one or more of your pricing options. These pricing option codes will
                    override the account chosen here.
                </template>
                <template v-else>
                    <strong><i class="fas fa-exclamation-triangle"></i> Warning:</strong>
                    You have DP GL Codes on one or more of your pricing options. The supporter’s designation will be
                    used instead of the DP GL Codes.
                </template>
            </p>

            <div class="flex items-center">
                <input
                    id="inputDesignationStyleSingleAccount"
                    type="radio"
                    value="single_account"
                    v-model="designation_style"
                    class="focus:ring-gcb-500 h-4 w-4 text-gcb-600 border-gray-300"
                    style="margin: 0"
                />
                <label for="inputDesignationStyleSingleAccount" class="m-0 ml-3 text-sm block font-bold">
                    Single Account
                </label>
            </div>

            <div v-if="designation_style === 'single_account' && designations.length" class="mt-1 mb-4 ml-7">
                <div class="flex items-center">
                    <div class="w-2/4 mr-3">
                        <label class="form-label sr-only">ACCOUNT</label>
                        <vue-selectize
                            v-model="designations[0].account"
                            :settings="getSelectizeSettings(designations[0])"
                            class="form-control"
                            placeholder="Choose an Account..."
                        ></vue-selectize>
                    </div>
                </div>
            </div>

            <div class="flex items-center mt-3">
                <input
                    id="inputDesignationStyleSupportersChoice"
                    type="radio"
                    value="supporters_choice"
                    v-model="designation_style"
                    :disabled="disableSupportersChoice"
                    class="focus:ring-gcb-500 h-4 w-4 text-gcb-600 border-gray-300"
                    style="margin: 0"
                />
                <label for="inputDesignationStyleSupportersChoice" class="m-0 ml-3 text-sm block font-bold">
                    Give Supporter the Option
                </label>
            </div>

            <p
                v-if="disableSupportersChoice"
                class="mt-1 ml-6 px-4 py-2 rounded-md text-sm font-medium bg-yellow-100 text-yellow-800"
            >
                <strong><i class="fas fa-exclamation-circle"></i> Notice:</strong>
                Currently only supported when using the "Page with Payment" template (more coming soon) and is not
                supported when using product as part of a P2P fundraising page.
            </p>

            <div v-if="designation_style === 'supporters_choice' && !disableSupportersChoice" class="mt-3 ml-1">
                <div class="flex items-center">
                    <div class="mr-3">
                        <i class="far fa-bars text-lg text-gray-200 opacity-0"></i>
                    </div>
                    <div class="w-1/4 mr-3">
                        <div class="text-xs font-bold text-gray-500">DESIGNATION</div>
                    </div>
                    <div class="w-2/4 mr-3">
                        <div class="text-xs font-bold text-gray-500">ACCOUNT</div>
                    </div>
                    <div class="mr-3">
                        <div class="text-xs font-bold text-gray-500">DEFAULT</div>
                    </div>
                </div>
                <draggable v-model="designations" group="designations" handle=".fa-bars">
                    <div
                        class="flex items-center mb-1"
                        v-for="(designation, index) in designations"
                        :key="designation.id"
                    >
                        <div class="mr-3">
                            <i class="far fa-bars text-lg text-gray-200 cursor-move"></i>
                        </div>
                        <div class="w-1/4 mr-3">
                            <label class="form-label sr-only">DESIGNATION</label>
                            <input
                                type="text"
                                class="form-control"
                                v-model="designation.label"
                                placeholder="Designation Label"
                            />
                        </div>
                        <div class="w-2/4 mr-3">
                            <label class="form-label sr-only">ACCOUNT</label>
                            <vue-selectize
                                v-model="designation.account"
                                :settings="getSelectizeSettings(designation)"
                                class="form-control"
                                placeholder="Choose an Account..."
                            ></vue-selectize>
                        </div>
                        <div>
                            <toggle-button
                                v-model="designation.is_default"
                                @change="setDefaultDesignation(designation)"
                                :sync="true"
                            ></toggle-button>
                            <button type="button" class="ml-2 p-1" @click="removeDesignation(index)">
                                <i class="far fa-trash-alt text-lg text-gcp-500"></i>
                                <span class="sr-only">Remove designation</span>
                            </button>
                        </div>
                    </div>
                </draggable>
            </div>
        </div>
    </div>
</template>

<script>
import _ from 'lodash';
import $ from 'jquery';
import draggable from 'vuedraggable';

function cleanAccountOptionCode(value, trimTrailingDashesAndUnderscores = true) {
    value = value.toUpperCase(); // force uppercase
    value = value.replace(/\s/g, '_'); // replace spaces with underscores
    value = value.replace(/[^A-Z0-9_-]/g, ''); // only allow uppercase, numbers, dashes or underscores
    value = value.replace(/(?:_+-+|-+_+)/g, '_'); // replace adjacent dashses and underscores with an underscore
    value = value.replace(/--+/g, '-'); // remove consecutive dashes
    value = value.replace(/__+/g, '_'); // remove consecutive underscores
    value = value.replace(/^[_-]+/, ''); // remove prefixed dashes/underscores
    if (trimTrailingDashesAndUnderscores) {
        value = value.replace(/[_-]+$/, ''); // remove suffixed dashes/underscores
    }
    return value;
}

function getDpGlCodes() {
    if (typeof getDpGlCodes._promise === 'undefined') {
        getDpGlCodes._promise = $.getJSON('/jpanel/donor/codes/GL_CODE.json').then((items) => {
            return (items || [])
                .map((item) => ({
                    text: item.code,
                    value: item.code,
                    description: item.description,
                }))
                .filter((item) => item.value !== '');
        });
    }
    return getDpGlCodes._promise;
}

export default {
    name: 'AccountDesignations',
    components: {
        draggable,
    },
    props: {
        dpEnabled: Boolean,
        hasVariantLevelCoding: Boolean,
        disableSupportersChoice: Boolean,
        options: [String, Object],
    },
    data() {
        return {
            designations: [],
            designation_style: null,
        };
    },
    mounted() {
        if (this.options) {
            this.designation_style = this.options.type;
            this.addDesignations(this.options.designations);
        }
        this.ensureHasBlankDesignation();
        this.ensureDefaultDesignation();
    },
    watch: {
        designation_style(newValue, oldValue) {
            if (newValue !== oldValue && oldValue) {
                this.switchDesignationStyle();
            }
        },
        designations: {
            deep: true,
            handler() {
                this.ensureHasBlankDesignation();
                this.ensureDefaultDesignation();
                this.saveDesignationOptions();
            },
        },
        disableSupportersChoice(newValue, oldValue) {
            if (newValue && this.designation_style === 'supporters_choice') {
                this.designation_style = 'single_account';
            }
        },
    },
    computed: {
        doesnt_have_blank_designation() {
            const designation = this.designations.length ? this.designations[this.designations.length - 1] : null;
            return Boolean(!designation || designation.label || designation.account);
        },
        doesnt_have_default_designation() {
            return this.designations.filter((designation) => designation.is_default).length === 0;
        },
        show_dp_warning_for_variant_level_coding() {
            return this.dpEnabled && this.hasVariantLevelCoding;
        },
    },
    methods: {
        ensureHasBlankDesignation() {
            if (this.doesnt_have_blank_designation) {
                this.addDesignation({});
            }
        },
        ensureDefaultDesignation() {
            if (this.doesnt_have_default_designation && this.designations[0]) {
                this.designations[0].is_default = true;
            }
        },
        addDesignations(designations) {
            designations.forEach((designation) => this.addDesignation(designation));
        },
        addDesignation({ label = '', account = '', is_default = false }) {
            this.designations.push({ label, account, is_default, id: _.uniqueId('designation-') });
        },
        setDefaultDesignation(designation) {
            this.designations.forEach((designation) => (designation.is_default = false));
            designation.is_default = true;
        },
        removeDesignation(index) {
            this.designations.splice(index, 1);
        },
        switchDesignationStyle() {
            const designations = this.designations.filter((designation) => designation.is_default);
            this.designations = [];
            this.addDesignation(designations.length ? { account: designations[0].account } : {});
            this.ensureDefaultDesignation();
        },
        saveDesignationOptions() {
            var designation_options = {
                type: this.designation_style,
                default_account: null,
                designations: [],
            };
            designation_options.designations = _.cloneDeep(this.designations)
                .filter((designation) => designation.label || designation.account)
                .map((designation) => {
                    delete designation.id;
                    return designation;
                });
            designation_options.default_account = designation_options.designations
                .filter((designation) => designation.is_default)
                .reduce((_, designation) => designation.account, null);
            this.$refs.meta1.value = designation_options.default_account;
            this.$refs.designation_options.value = designation_options.default_account
                ? JSON.stringify(designation_options)
                : null;
        },
        getSelectizeSettings(designation) {
            return {
                create(input) {
                    var value = cleanAccountOptionCode(input);
                    return { text: value, value };
                },
                createOnBlur: true,
                maxItems: 1,
                onInitialize() {
                    this.load(function (callback) {
                        getDpGlCodes().then((items) => {
                            if (designation.account) {
                                items.push({ text: designation.account, value: designation.account });
                            }
                            callback(items);
                            this.setValue(designation.account, true);
                        });
                    });
                    // hook onMouseDown to prevent issue with remove button plugin where the dropdown
                    // menu flashes briefly and then disappears when clicking on the remove button
                    this.onMouseDown = (function (onMouseDown) {
                        return function (e) {
                            if (!e.target.matches('a.remove-single')) {
                                return onMouseDown.apply(this, arguments);
                            }
                        };
                    })(this.onMouseDown);
                },
                persist: false,
                plugins: ['remove_button'],
                render: {
                    _item: (item, escape) => {
                        var description = '';
                        if (this.dpEnabled && typeof item.description === 'undefined') {
                            description =
                                ' <small class="text-danger"><i class="fa fa-exclamation-triangle"></i> Missing in DPO</small>';
                        } else if (item.description) {
                            description = ` <small class="text-muted">${escape(item.description)}</small>`;
                        }
                        return `<div class="item">${escape(item.text)}${description}</div>`;
                    },
                    item(item, escape) {
                        return this.settings.render._item(item, escape);
                    },
                    option(item, escape) {
                        return this.settings.render._item(item, escape);
                    },
                    option_create(data, escape) {
                        return `<div class="create">Add <strong>${escape(
                            cleanAccountOptionCode(data.input)
                        )}</strong> account</div>`;
                    },
                },
                searchField: ['description', 'text'],
            };
        },
    },
};
</script>
