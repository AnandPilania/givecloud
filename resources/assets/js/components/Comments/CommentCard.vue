<template>
    <div class="relative pb-12 pt-2">
        <span
            v-if="!isLastCard"
            class="absolute top-8 left-4 -ml-px bottom-0 w-0.5 bg-gray-200"
            aria-hidden="true"
        ></span>
        <div class="relative flex items-start space-x-3">
            <div class="relative w-12">
                <span class="absolute -mt-2 -ml-2 bg-gray-100 rounded-full p-3">
                    <!-- Heroicon name: solid/chat-alt -->
                    <svg
                        class="h-5 w-5 text-gray-400"
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                        aria-hidden="true"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M18 5v8a2 2 0 01-2 2h-5l-5 4v-4H4a2 2 0 01-2-2V5a2 2 0 012-2h12a2 2 0 012 2zM7 8H5v2h2V8zm2 0h2v2H9V8zm6 0h-2v2h2V8z"
                            clip-rule="evenodd"
                        />
                    </svg>
                </span>
            </div>
            <div class="min-w-0 flex-1">
                <div>
                    <div class="text-sm">
                        <div class="font-medium text-gray-900">
                            {{ comment.created_by.firstname }} {{ comment.created_by.lastname }}
                        </div>
                    </div>
                    <p class="mt-0.5 text-sm text-gray-500">
                        Commented <time :datetime="comment.created_at" :title="humanizeDate">{{ fromNow }}</time>
                    </p>
                </div>
                <div
                    class="body mt-2 text-sm text-gray-700 pt-3 pb-4 px-4 bg-gray-100 rounded-lg leading-5"
                    v-html="comment.body"
                ></div>
            </div>
            <div v-click-outside="close" v-if="editable || isAccountAdmin" class="shrink-0 flex">
                <div class="relative z-30 inline-block text-left">
                    <div>
                        <button
                            type="button"
                            class="-m-2 p-2 rounded-full flex items-center text-gray-400 hover:text-gray-600"
                            id="menu-1"
                            aria-expanded="false"
                            aria-haspopup="true"
                            @click="open = !open"
                        >
                            <span class="sr-only">Open options</span>
                            <!-- Heroicon name: solid/dots-vertical -->
                            <svg
                                class="h-5 w-5"
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                                aria-hidden="true"
                            >
                                <path
                                    d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"
                                />
                            </svg>
                        </button>
                    </div>

                    <div
                        v-if="open"
                        class="origin-top-right absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none"
                        role="menu"
                        aria-orientation="vertical"
                        aria-labelledby="menu-1"
                    >
                        <div class="py-1" role="none">
                            <a
                                v-if="editable"
                                @click.stop="edit"
                                class="flex px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900"
                                role="menuitem"
                            >
                                <!-- Heroicon name: solid/code -->
                                <svg
                                    class="mr-3 h-5 w-5 text-gray-400"
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20"
                                    fill="currentColor"
                                    aria-hidden="true"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M12.316 3.051a1 1 0 01.633 1.265l-4 12a1 1 0 11-1.898-.632l4-12a1 1 0 011.265-.633zM5.707 6.293a1 1 0 010 1.414L3.414 10l2.293 2.293a1 1 0 11-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0zm8.586 0a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 11-1.414-1.414L16.586 10l-2.293-2.293a1 1 0 010-1.414z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                                <span>Edit</span>
                            </a>
                            <a
                                v-if="editable || isAccountAdmin"
                                @click.stop="destroy"
                                class="flex px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900"
                                role="menuitem"
                            >
                                <!-- Heroicon name: solid/trash -->
                                <svg
                                    class="mr-3 h-5 w-5 text-gray-400"
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20"
                                    fill="currentColor"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                                <span>Delete</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        editable: {
            required: true,
            type: Boolean,
        },
        comment: {
            required: true,
            type: Object,
            validator: (obj) => ['body', 'created_by', 'created_at'].every((property) => property in obj),
        },
        isLastCard: {
            required: true,
            type: Boolean,
        },
        isAccountAdmin: {
            required: true,
            type: Boolean,
        },
    },
    data() {
        return {
            open: false,
        };
    },
    methods: {
        close() {
            this.open = false;
        },
        edit() {
            this.close();
            this.$emit('edit', this.comment);
        },
        destroy() {
            this.close();
            this.$emit('destroy', this.comment);
        },
    },
    computed: {
        fromNow() {
            return window.moment(this.comment.created_at).fromNow();
        },
        humanizeDate() {
            //9:20 AM · Apr 26, 2018
            return window.moment(this.comment.created_at).format('LLLL');
        },
    },
};
</script>

<style scoped>
.body >>> p:not(:last-child) {
    @apply mb-4;
}
</style>
