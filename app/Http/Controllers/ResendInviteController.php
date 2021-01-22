<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Invite;
use App\Notifications\UserInviteEmail;
use Illuminate\Support\Facades\Notification;

class ResendInviteController extends Controller
{
    /**
     * Resend the invitation.
     *
     * @param  \App\Models\Invite  $appModelsInvite
     * @return \Illuminate\Http\RedirectResponse
     */
    public function __invoke(Invite $invite)
    {
        try {
            Notification::route('mail', $invite->email)
                ->notify(new UserInviteEmail(
                    $invite->email,
                    $invite->from,
                    $invite->message,
                    $invite->token
                ));

            return redirect()->back()->withMessage('Invitation was resent');
        } catch (Exception $e) {
            return redirect()->back()->withErrors(['Invitation was unable to be resent']);
        }
    }
}