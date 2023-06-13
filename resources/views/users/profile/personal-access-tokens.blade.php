<div class="mt-10 sm:mt-0">
    <div class="md:grid md:grid-cols-3 md:gap-6">
        <div class="md:col-span-1">
            <div class="px-4 sm:px-0">
                <h3 class="text-lg font-medium leading-6 text-gray-900">Personal Access Tokens</h3>
                <p class="mt-1 text-sm leading-5 text-gray-600">Generate a new scoped Personal Access Token.</p>
            </div>
        </div>
        <div class="mt-5 md:mt-0 md:col-span-2">
            {{-- Personal Access Token create form --}}
            <form class="form-horizontal" action="{{ route('backend.personal_access_tokens.store') }}" method="post">
                @csrf

                <div class="shadow overflow-hidden sm:rounded-md mb-5">
                    <div class="px-4 py-5 bg-white sm:p-6">
                        <h4 class="text-md font-medium leading-6 text-gray-600 m-0 mb-4">Create a new Personal Access Token</h4>

                        <div class="col-span-6 sm:col-span-4">
                            <label for="name" class="block text-sm font-medium leading-5 text-gray-700">Name</label>
                            <input
                                id="name"
                                class="form-control w-full max-w-md"
                                type="text"
                                name="name"
                                placeholder="Pick a memorable name for this Personal Access Token"
                                title="Pick a memorable name for this Personal Access Token"
                                maxlength="255"
                                required>
                        </div>
                    </div>

                    <div class="px-4 py-3 bg-gray-50 sm:px-6">
                        <input class="bg-gcb-300 btn font-bold rounded-md text-gcb-700" type="submit" value="Create">
                    </div>
                </div>
            </form>

            {{-- Personal access tokens list --}}
            <div class="shadow overflow-hidden sm:rounded-md">
                <div class="px-4 py-5 bg-white sm:p-6">
                    <h4 class="text-md font-medium leading-6 text-gray-600 m-0 mb-4">Your Personal Access Tokens</h4>
                    @if ($personalAccessTokens->isEmpty())
                        <div>You don't have any active Personal access token.</div>
                    @else
                        @foreach ($personalAccessTokens as $personalAccessToken)
                            <div class="flex hover:bg-gray-100 py-2">
                                <div>{{ $personalAccessToken->name }}</div>
                                <div class="ml-auto flex">
                                    <form action="{{ route('backend.personal_access_tokens.destroy', $personalAccessToken) }}" method="post">
                                        @method('DELETE')
                                        @csrf

                                        <a
                                            class="btn btn-xs bg-gcp-100 text-gcp-700 font-bold"
                                            title="Revoke access to &quot;{{ $personalAccessToken->name }}&quot;"
                                            onclick="$.confirm(
                                                'Are you sure you want to revoke access to &quot;{{ $personalAccessToken->name }}&quot; and all its tokens?',
                                                function () { $(this).parents('form').submit() }.bind(this),
                                                'danger',
                                                'fa-trash'
                                                )">
                                            revoke access
                                        </a>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
