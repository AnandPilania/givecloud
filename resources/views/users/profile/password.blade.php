<div class="mt-10 sm:mt-0">
    <div class="md:grid md:grid-cols-3 md:gap-6">
        <div class="md:col-span-1">
            <div class="px-4 sm:px-0">
                <h3 class="text-lg font-medium leading-6 text-gray-900">Update Password</h3>
                <p class="mt-1 text-sm leading-5 text-gray-600">
                    Ensure your account is using a long, random password to stay secure.
                </p>
            </div>
        </div>
        <div class="mt-5 md:mt-0 md:col-span-2">
            <form action="{{ route('user-password.update') }}" method="POST">
                @csrf @method('PUT')
                <div class="shadow overflow-hidden sm:rounded-md">
                    <div class="px-4 py-5 bg-white sm:p-6">
                        <div class="grid grid-cols-6 gap-6">
                            <div class="col-span-6 sm:col-span-4">
                                <label for="inputCurrentPassword" class="block text-sm font-medium leading-5 text-gray-700">Current password</label>
                                <input id="inputCurrentPassword" type="password" name="current_password" required class="mt-1 form-input block w-full transition duration-150 ease-in-out sm:text-sm sm:leading-5 @error('current_password', 'updatePassword') border-red-500 @enderror">
                                @error('current_password', 'updatePassword') <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div class="col-span-6 sm:col-span-4">
                                <label for="inputPassword" class="block text-sm font-medium leading-5 text-gray-700">New password</label>
                                <input id="inputPassword" type="password" name="password" required class="mt-1 form-input block w-full transition duration-150 ease-in-out sm:text-sm sm:leading-5 @error('password', 'updatePassword') border-red-500 @enderror">
                                @error('password', 'updatePassword') <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div class="col-span-6 sm:col-span-4">
                                <label for="inputPasswordConfirmation" class="block text-sm font-medium leading-5 text-gray-700">Confirm password</label>
                                <input id="inputPasswordConfirmation" type="password" name="password_confirmation" required class="mt-1 form-input block w-full transition duration-150 ease-in-out sm:text-sm sm:leading-5 @error('password_confirmation', 'updatePassword') border-red-500 @enderror">
                                @error('password_confirmation', 'updatePassword') <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="px-4 py-3 bg-gray-50 sm:px-6">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gcb-700 bg-gcb-200 focus:outline-none focus:border-gcb-300 focus:ring focus:ring-gcb-200 disabled:opacity-50 transition ease-in-out duration-150 font-bold">
                            Change password
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
