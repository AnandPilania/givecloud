<div class="md:grid md:grid-cols-3 md:gap-6 pb-10">
    <div class="md:col-span-1">
        <div class="px-4 sm:px-0">
            <h3 class="text-lg font-medium leading-6 text-gray-900">Legal & Privacy</h3>
            <p class="mt-1 text-sm leading-5 text-gray-600">
                We'll reference these details on your fundraising forms to ensure you're compliant with all payment and privacy regulations.
            </p>
        </div>
    </div>
    <div class="mt-5 md:mt-0 md:col-span-2">
        <div class="shadow overflow-hidden rounded-lg">
            <div class="px-4 py-5 bg-white p-6">
                <div class="grid grid-cols-6 gap-6">
                    <div class="col-span-6">
                        <label for="legal-address" class="block text-sm font-medium leading-5 text-gray-700">Address</label>
                        <textarea name="org_legal_address" id="legal-address" rows="4" class="px-3 py-2 border mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">{{ sys_get('org_legal_address') }}</textarea>
                    </div>
                    <div class="col-span-6">
                        <label for="legal-country" class="block text-sm font-medium leading-5 text-gray-700">Country</label>
                        <select type="text" name="org_legal_country" id="legal-country"
                                class="selectize form-control w-full">
                            <option></option>
                            @foreach(cart_countries() as $code => $name)
                                <option @selected( sys_get('org_legal_country') === $code) value="{{ $code }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-span-6">
                        <label for="org_legal_charity_number" class="block text-sm font-medium leading-5 text-gray-700">Charity Number</label>
                        <input type="text" name="org_legal_number" id="org_legal_charity_number" value="{{ sys_get('org_legal_number') }}" class="px-3 py-2 border mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                    </div>
                    <div class="col-span-6">
                        <label for="legal-address-checks" class="block text-sm font-medium leading-5 text-gray-700">Mailing Address for Checks</label>
                        <textarea name="org_check_mailing_address" id="legal-address-checks" rows="4" class="px-3 py-2 border mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">{{ sys_get('org_check_mailing_address') }}</textarea>
                    </div>
                    <div class="col-span-6">
                        <label for="org_legal_privacy_officer_email" class="block text-sm font-medium leading-5 text-gray-700">Privacy Officer Email</label>
                        <input type="email" name="org_privacy_officer_email" id="org_legal_privacy_officer_email" value="{{ sys_get('org_privacy_officer_email') }}" class="px-3 py-2 border mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                    </div>
                    <div class="col-span-6">
                        <label for="org_legal_privacy_policy_url" class="block text-sm font-medium leading-5 text-gray-700">Privacy Policy URL</label>
                        <input type="url" name="org_privacy_policy_url" id="org_legal_privacy_policy_url" value="{{ sys_get('org_privacy_policy_url') }}" class="px-3 py-2 border mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
