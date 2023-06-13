<?php

namespace Ds\Domain\Fortify\Actions;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;

class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    /**
     * Validate and update the given user's profile information.
     *
     * @param mixed $user
     * @param array $input
     * @return void
     */
    public function update($user, array $input)
    {
        Validator::make($input, [
            'first_name' => ['string', 'max:45'],
            'last_name' => ['string', 'max:45'],
            'phone' => ['nullable', 'string', 'max:45'],
            'email' => ['required', 'string', 'email', 'max:45', Rule::unique('user')->ignore($user->id)],
            'notify_recurring_batch_summary' => ['boolean'],
        ], [
            'email.unique' => 'The email is already in use by another user.',
        ])->validateWithBag('updateProfileInformation');

        $user->forceFill([
            'firstname' => $input['first_name'],
            'lastname' => $input['last_name'],
            'primaryphonenumber' => $input['phone'],
            'email' => $input['email'],
            'notify_recurring_batch_summary' => $input['notify_recurring_batch_summary'] ?? 0,
        ])->save();

        app('flash')->success('Your profile has been updated successfully.');
    }
}
