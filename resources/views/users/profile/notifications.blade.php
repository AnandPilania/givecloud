<div class="non-bootstrap-hidden sm:block">
    <div class="py-5">
        <div class="border-t border-gray-200"></div>
    </div>
</div>

<div class="mt-10 sm:mt-0">
    <div class="md:grid md:grid-cols-3 md:gap-6">
        <div class="md:col-span-1">
            <div class="px-4 sm:px-0">
                <h3 class="text-lg font-medium leading-6 text-gray-900">Notifications</h3>
                <p class="mt-1 text-sm leading-5 text-gray-600">
                    Manage the email notifications youd like to receive.
                </p>
            </div>
        </div>
        <div class="mt-5 md:mt-0 md:col-span-2">
            <div class="shadow overflow-hidden sm:rounded-md">
                <form action="{{ route('backend.profile.notifications') }}" method="POST">
                    @csrf @method('PUT')

                    <div class="px-4 py-5 bg-white sm:p-6">
                        <h4 class="mb-6">Digests</h4>
                        <div class="sm:grid sm:grid-cols-2 sm:gap-4">
                            <div class="relative flex items-start">
                                <div class="flex items-center h-5">
                                    <input type="hidden" name="notify_digest_daily" value="0">
                                    <input id="inputNotifyDigestDaily" name="notify_digest_daily" type="checkbox" value="1" @checked($user->notify_digest_daily) class="form-checkbox h-4 w-4 text-indigo-600 transition duration-150 ease-in-out rounded focus:ring focus:ring-indigo-200">
                                </div>
                                <div class="ml-3 text-sm leading-5">
                                    <label for="inputNotifyDigestDaily" class="font-medium text-gray-700">Daily Digest</label>
                                    <p class="text-gray-500">Receive a summary of yesterday's fundraising activity by 7:30am, every day.</p>
                                </div>
                            </div>
                            <div class="relative flex items-start opacity-50 cursor-not-allowed">
                                <div class="flex items-center h-5">
                                    <input type="hidden" name="notify_digest_weekly" value="0">
                                    <input disabled id="inputNotifyDigestWeekly" name="notify_digest_weekly" type="checkbox" value="1" @checked($user->notify_digest_weekly) class="form-checkbox h-4 w-4 text-indigo-600 transition duration-150 ease-in-out rounded focus:ring focus:ring-indigo-200">
                                </div>
                                <div class="ml-3 text-sm leading-5">
                                    <label for="inputNotifyDigestWeekly" class="font-medium text-gray-700">Weekly Digest <small class="inline-flex items-center rounded-md bg-yellow-50 px-2 py-0.5 text-xs font-medium text-yellow-800 ring-1 ring-inset ring-yellow-600/20 align-top ml-1">coming soon</small></label>
                                    <p class="text-gray-500">Receive a summary of last week's fundraising activity by 7:30am, every Monday.</p>
                                </div>
                            </div>
                            <div class="relative flex items-start opacity-50 cursor-not-allowed">
                                <div class="flex items-center h-5">
                                    <input type="hidden" name="notify_digest_monthly" value="0">
                                    <input disabled id="inputNotifyDigestMonthly" name="notify_digest_monthly" type="checkbox" value="1" @checked($user->notify_digest_monthly) class="form-checkbox h-4 w-4 text-indigo-600 transition duration-150 ease-in-out rounded focus:ring focus:ring-indigo-200">
                                </div>
                                <div class="ml-3 text-sm leading-5">
                                    <label for="inputNotifyDigestDaily" class="font-medium text-gray-700">Monthly Digest <small class="inline-flex items-center rounded-md bg-yellow-50 px-2 py-0.5 text-xs font-medium text-yellow-800 ring-1 ring-inset ring-yellow-600/20 align-top ml-1">coming soon</small></label>
                                    <p class="text-gray-500">Receive a summary of last month's fundraising activity by 7:30am, the first of every month.</p>
                                </div>
                            </div>
                        </div>

                        <h4 class="my-6">Givecloud</h4>
                        <div class="sm:grid sm:grid-cols-2 sm:gap-4">
                            <div class="relative flex items-start">
                                <div class="flex items-center h-5">
                                    <input type="hidden" name="email_updates_optin" value="0">
                                    <input id="inputEmailUpdatesOptin" name="email_updates_optin" type="checkbox" value="1" @checked($user->ds_corporate_optin) class="form-checkbox h-4 w-4 text-indigo-600 transition duration-150 ease-in-out rounded focus:ring focus:ring-indigo-200">
                                </div>
                                <div class="ml-3 text-sm leading-5">
                                    <label for="inputEmailUpdatesOptin" class="font-medium text-gray-700">Product Updates</label>
                                    <p class="text-gray-500">Receive an email about product updates, twice a month (we're always improving).</p>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="px-4 py-3 bg-gray-50 sm:px-6">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 rounded-md text-gcb-700 bg-gcb-200 focus:outline-none focus:border-gcb-300 focus:ring focus:ring-gcb-200 disabled:opacity-50 transition ease-in-out duration-150 font-bold">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
