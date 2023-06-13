<template>
    <div class="flex justify-center">
        <div class="sm:my-8 sm:align-middle sm:max-w-md sm:w-full sm:p-6">
            <div>
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-gray-100">
                    <!-- Heroicon name: outline/check -->
                    <svg
                        class="w-6 h-6 text-gray-600"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                        ></path>
                    </svg>
                </div>
                <div class="mt-3 text-center sm:mt-5">
                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                        There aren't any notes yet for this account
                    </h3>
                    <div class="mt-2">
                        <p class="text-sm text-gray-500">
                            Why not be the first to add one? Click the button below to add a note.
                        </p>
                    </div>
                </div>
            </div>
            <div class="w-full mt-5 sm:mt-6 flex justify-center">
                <button
                    @click.stop="create"
                    type="button"
                    class="inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-brand-blue text-base font-medium text-white hover:opacity-75 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-blue sm:text-sm"
                >
                    Add the First Note
                </button>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        create: {
            required: true,
            type: Function,
        },
    },
};
</script>
