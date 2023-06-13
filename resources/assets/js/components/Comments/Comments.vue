<template>
    <div>
        <div v-if="hasNotes" class="bg-white pb-5">
            <div class="-ml-4 -mt-2 flex justify-between items-center flex-wrap sm:flex-nowrap">
                <div class="mt-3 sm:mt-0 sm:ml-4">
                    <div class="mt-1 flex rounded-md shadow-sm">
                        <div class="relative flex items-stretch flex-grow focus-within:z-10">
                            <input
                                v-on:keyup.enter="search()"
                                v-model="filter"
                                type="text"
                                name="filter"
                                id="filter"
                                class="focus:ring-indigo-500 focus:border-indigo-500 block w-full rounded-none rounded-l-md pl-3 sm:text-sm border border-gray-300"
                                placeholder="Search notes"
                            />
                            <button
                                v-if="filter"
                                @click="clearFilter"
                                class="absolute inset-y-0 right-0 pr-2 flex items-center text-gray-500 focus:outline-none"
                            >
                                <!-- Heroicon name: outline/x-circle -->
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-4 w-4"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"
                                    />
                                </svg>
                            </button>
                        </div>
                        <button
                            @click="search()"
                            class="-ml-px relative inline-flex items-center space-x-2 px-4 py-2 border border-gray-300 text-sm font-medium rounded-r-md text-gray-700 bg-gray-50 hover:bg-gray-100 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500"
                        >
                            <!-- Heroicon name: solid/search -->
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5 text-gray-400"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="ml-4 mt-4 shrink-0 ml-auto">
                    <button
                        @click.stop="create"
                        type="button"
                        class="relative inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-brand-blue hover:opacity-75 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-blue"
                    >
                        Create note
                    </button>
                </div>
            </div>
        </div>

        <CommentModal ref="commentModal" :comment="editedComment" @close="closeModal" @save="store"></CommentModal>

        <div v-if="hasNotes" class="divide-y divide-gray-200">
            <div class="flow-root">
                <ul class="-mb-8 list-none">
                    <li v-for="(group, month) in commentsGroupByMonth">
                        <div class="uppercase text-gray-400 pb-4" v-text="formatDateHeader(month)"></div>
                        <CommentCard
                            v-for="(comment, index) in group"
                            :key="comment.id"
                            :comment="comment"
                            :isLastCard="index === group.length - 1"
                            :isAccountAdmin="is_account_admin ? true : false"
                            :editable="comment.created_by.id == user_id"
                            @edit="edit"
                            @destroy="destroy"
                        >
                        </CommentCard>
                    </li>
                </ul>
            </div>
        </div>

        <div v-if="hasNotes && paginator.meta.total === 0" class="rounded-md bg-blue-50 p-4 mb-4">
            <div class="flex">
                <div class="shrink-0">
                    <!-- Heroicon name: solid/information-circle -->
                    <svg
                        class="h-5 w-5 text-blue-400"
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                        aria-hidden="true"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                            clip-rule="evenodd"
                        />
                    </svg>
                </div>
                <div class="ml-3 flex-1 md:flex md:justify-between">
                    <p class="text-sm text-blue-700">
                        No notes found for <strong>{{ filter }}</strong>
                    </p>
                    <p class="mt-3 text-sm md:mt-0 md:ml-6">
                        <button
                            @click="clearFilter"
                            class="whitespace-nowrap font-medium text-blue-700 hover:text-blue-600"
                        >
                            Reset filter
                        </button>
                    </p>
                </div>
            </div>
        </div>

        <Pagination v-if="hasNotes" :paginator="paginator" @pagination-change-page="index"></Pagination>

        <EmptyState v-if="!hasNotes" :create="create"></EmptyState>
    </div>
</template>

<script>
import CommentCard from '@app/components/Comments/CommentCard';
import CommentModal from '@app/components/Comments/CommentModal';
import EmptyState from '@app/components/Comments/EmptyState';
import Pagination from '@app/components/Pagination';

const defaultPath = '/jpanel/api/v1/comments/';

export default {
    name: 'Comments',
    components: { CommentModal, CommentCard, EmptyState, Pagination },
    props: {
        user_id: {
            required: true,
            type: Number,
        },
        commentable_id: {
            required: true,
            type: Number,
        },
        commentable_type: {
            required: true,
            type: String,
        },
        commentable_path: {
            required: true,
            type: String,
        },
        is_account_admin: {
            type: Boolean,
            required: true,
        },
    },
    data: function () {
        return {
            filter: null,
            paginator: {
                meta: {
                    current_page: 1,
                },
            },
            comment: null,
            editedComment: '',
            comments: [],
        };
    },
    methods: {
        create() {
            this.$refs.commentModal.show();
        },
        search() {
            // Reset to page 1 when searching
            this.index(1);
        },
        index(page) {
            const params = {
                page: page || this.paginator.meta.current_page,
            };

            let queryString = Object.keys(params)
                .map((key) => key + '=' + params[key])
                .join('&');
            if (this.filter) queryString += '&filter[body]=' + this.filter;

            axios.get(this.commentable_path + '?' + queryString).then(({ data }) => {
                this.comments = data.data;
                this.paginator = data;
            });
        },
        destroy(comment) {
            $.confirm('Are you sure you want to delete this note?', () => {
                axios
                    .delete(defaultPath + comment.id)
                    .then(() => {
                        toastr.success('Note has been deleted.');
                        this.index();
                    })
                    .catch(() => {
                        toastr.error('An error has occurred, please try again.');
                    });
            });
        },
        edit(comment) {
            this.editedComment = comment.body;
            this.comment = comment;
            this.$refs.commentModal.show();
        },
        store(newComment) {
            if (this.comment && this.comment.id) return this.update(newComment);

            axios
                .post(this.commentable_path, {
                    [this.commentable_type]: this.commentable_id,
                    body: newComment,
                })
                .then(({ data }) => {
                    this.reset();
                    this.index(1); //Forces page 1 when new comment stored.
                })
                .catch(() => {
                    toastr.error('An error has occurred, please try again.');
                });
        },
        update(comment) {
            axios
                .post(defaultPath + this.comment.id, {
                    body: comment,
                })
                .then(({ data }) => {
                    this.comment.body = data.data.body;
                    toastr.success('Note has been updated');
                    this.reset();
                })
                .catch(() => {
                    toastr.error('An error has occurred, please try again.');
                });
        },
        closeModal() {
            this.reset();
            this.$refs.commentModal.hide();
        },
        reset() {
            this.editedComment = '';
            this.comment = null;
        },
        clearFilter() {
            this.filter = null;
            this.search();
        },
        formatDateHeader(month) {
            return moment(month).format('MMMM YYYY');
        },
    },
    computed: {
        lastCard() {
            return this.comments.last();
        },
        hasNotes: function () {
            return this.paginator.meta.unfiltered > 0;
        },
        commentsGroupByMonth() {
            return this.comments.groupBy((comment) => {
                return moment(comment.created_at).format('YYYY-MM-01');
            });
        },
    },
    mounted() {
        this.index();
    },
};
</script>
