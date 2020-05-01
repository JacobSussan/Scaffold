<?php

namespace App\Http\Controllers\Auth\Concerns;

use Validator;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Services\InviteService;

trait RegisterViaInvites
{
    /**
     * Show the invite registration form
     *
     * @return Response
     */
    public function showRegistrationInviteForm()
    {
        return view('auth.register_invite');
    }

    /**
     * Register a user via an invite
     *
     * @param  Request $request
     * @return Response
     */
    public function registerViaInvite(Request $request)
    {
        $this->validateInvite($request->all())->validate();

        $inviteIsValid = app(InviteService::class)->validateInvitation(
            $request->activation_token,
            $request->email
        );

        if (! $inviteIsValid) {
            return back()->withErrors([
                'Could not validate your invite registration, please try again.',
            ]);
        }

        $payload = $request->all();

        $payload['email_verified_at'] = Carbon::now();

        $user = $this->create($payload);

        $this->guard()->login($user);

        return redirect($this->redirectPath());
    }

    /**
     * Validate the invite
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validateInvite(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|max:255',
            'email' => 'required|email|max:255',
            'password' => 'required|min:6|confirmed',
        ]);
    }
}
