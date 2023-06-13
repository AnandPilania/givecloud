<template>
    <div v-show="showModal" class="fixed z-20 inset-0 overflow-y-auto">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

            <!-- This element is to trick the browser into centering the modal contents. -->
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>&#8203;

            <div
                class="relative inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6"
                role="dialog"
                aria-modal="true"
                aria-labelledby="modal-headline"
            >
                <div class="nonBootstrapHidden sm:block absolute top-0 right-0 pt-4 pr-4">
                    <button
                        @click="showModal = false"
                        type="button"
                        class="text-gray-400 hover:text-gray-500 focus:outline-none focus:text-gray-500 transition ease-in-out duration-150"
                        aria-label="Close"
                    >
                        <svg
                            class="h-6 w-6"
                            x-description="Heroicon name: x"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"
                            ></path>
                        </svg>
                    </button>
                </div>
                <label for="newComment" class="block text-lg font-bold text-gray-700"> Add a note </label>
                <div class="mt-3">
                    <textarea
                        v-model="comment"
                        ref="newComment"
                        id="newComment"
                        name="newComment"
                        rows="5"
                        class="simple-html form-control"
                        v-validate="'required'"
                        :class="{ 'is-danger': errors.has('newComment') }"
                    ></textarea>
                    <span v-if="errors.has('newComment')" class="text-sm text-bold text-red-600"
                        >This field is required.</span
                    >
                </div>
                <p class="mt-2 text-sm text-gray-500">Notes are visible by all users.</p>

                <div class="pt-5">
                    <div class="flex justify-end">
                        <button
                            @click="close"
                            type="button"
                            class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-blue"
                        >
                            Cancel
                        </button>
                        <button
                            @click.stop="save"
                            type="submit"
                            class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-brand-blue hover:opacity-75 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-blue"
                        >
                            Save
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
export default {
    props: {
        comment: {
            required: true,
            type: String,
        },
    },
    data() {
        return {
            showModal: false,
            error: false,
        };
    },
    methods: {
        save: function () {
            const newContent = tinymce.get('newComment').getContent();
            this.$validator.validate('newComment', newContent).then((result) => {
                if (!result) return;

                this.$emit('save', newContent);
                this.hide();
            });
        },
        close() {
            this.hide();
            this.$emit('close');
        },
        show() {
            this.showModal = true;
            this.$nextTick(() => {
                tinymce.get('newComment').destroy();
                j.ui.formatSpecialFields();
                tinymce.get('newComment').setContent(this.comment || '');
                tinymce.get('newComment').focus();
            });
        },
        hide() {
            this.showModal = false;
        },
    },
};
</script>
