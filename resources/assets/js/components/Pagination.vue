<template>
    <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
        <div class="flex-1 flex justify-between sm:hidden">
            <a
                :href="previousPageUrl"
                class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:text-gray-500"
            >
                Previous
            </a>
            <a
                :href="nextPageUrl"
                class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:text-gray-500"
            >
                Next
            </a>
        </div>
        <div class="nonBootstrapHidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-gray-700">
                    Showing
                    <span class="font-medium">{{ paginator.meta.from }}</span>
                    to
                    <span class="font-medium">{{ paginator.meta.to }}</span>
                    of
                    <span class="font-medium">{{ paginator.meta.total }}</span>
                    <template v-if="paginator.meta.total !== paginator.meta.unfiltered">
                        filtered results of
                        <span class="font-medium">{{ paginator.meta.unfiltered }}</span> total results
                    </template>
                    <template v-else>results</template>
                </p>
            </div>
            <div>
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                    <a
                        :href="previousPageUrl"
                        class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium"
                        :class="{
                            'text-gray-300 hover:text-gray-300': !previousPageUrl,
                            'text-gray-500 hover:bg-gray-50': previousPageUrl,
                        }"
                        @click.prevent="previousPage"
                    >
                        <span class="sr-only">Previous</span>
                        <!-- Heroicon name: solid/chevron-left -->
                        <svg
                            class="h-5 w-5"
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                            aria-hidden="true"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                                clip-rule="evenodd"
                            />
                        </svg>
                    </a>

                    <template v-for="link in linksWithoutPrevAndNext">
                        <a
                            v-if="link.url !== ''"
                            :href="link.url"
                            @click.prevent="changePage(link.label)"
                            class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50"
                            :class="{ 'bg-gray-100': link.active }"
                        >
                            {{ link.label }}
                        </a>

                        <span
                            v-if="link.url === ''"
                            class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700"
                        >
                            {{ link.label }}
                        </span>
                    </template>

                    <a
                        :href="nextPageUrl"
                        class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium"
                        :class="{
                            'text-gray-300 hover:text-gray-300': !nextPageUrl,
                            'text-gray-500 hover:bg-gray-50': nextPageUrl,
                        }"
                        @click.prevent="nextPage"
                    >
                        <span class="sr-only">Next</span>
                        <!-- Heroicon name: solid/chevron-right -->
                        <svg
                            class="h-5 w-5"
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                            aria-hidden="true"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                clip-rule="evenodd"
                            />
                        </svg>
                    </a>
                </nav>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        paginator: {
            required: true,
            type: Object,
        },
    },
    methods: {
        previousPage() {
            this.changePage(this.paginator.meta.current_page - 1);
        },
        nextPage() {
            this.changePage(Math.min(this.paginator.meta.current_page + 1, this.paginator.meta.last_page));
        },
        changePage(page) {
            if (page === '...') return false;

            if (page === this.paginator.meta.current_page) return false;

            this.$emit('pagination-change-page', page);
        },
    },
    computed: {
        hasPages() {
            return this.paginator.meta.last_page > 1;
        },
        onFirstPage() {
            return this.paginator.meta.current_page <= 1;
        },
        onLastPage() {
            return this.paginator.meta.current_page >= this.paginator.meta.last_page;
        },
        firstPageUrl() {
            return this.paginator.links.first;
        },
        lastPageUrl() {
            return this.paginator.links.last;
        },
        previousPageUrl() {
            return this.paginator.links.prev ?? false;
        },
        nextPageUrl() {
            return this.paginator.links.next ?? false;
        },
        linksWithoutPrevAndNext() {
            return this.paginator.meta.links.slice(1, -1);
        },
    },
};
</script>
