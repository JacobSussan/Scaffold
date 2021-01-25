<?php

namespace App\Http\Controllers\User;

use Exception;
use App\Http\Forms\UserForm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\UserUpdateRequest;
use App\Notifications\TwoFactorNotification;

class SettingsController extends Controller
{
    /**
     * View current user's settings.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $form = app(UserForm::class)->edit($user);

        $deleteAccountForm = form()
            ->confirm(trans('general.user.delete_account'), 'confirmation')
            ->action('delete', 'user.destroy', 'Delete My Account', [
                'class' => 'btn btn-block btn-danger mb-6',
            ]);

        return view('user.settings')->with(compact('form', 'deleteAccountForm'));
    }

    /**
     * Update the user.
     *
     * @param  UpdateAccountRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UserUpdateRequest $request)
    {
        try {
            $path = $request->user()->avatar;

            if (! is_null($request->avatar)) {
                if (($request->file('avatar')->getSize() / 1024) > 10000) {
                    return redirect()->back()->withErrors(['Avatar file is too big, must be below 10MB.']);
                }

                Storage::delete($request->user()->avatar);
                $path = Storage::putFile('public/avatars', $request->avatar, 'public');
            }

            $request->user()->update([
                'name' => $request->name,
                'email' => $request->email,
                'dark_mode' => $request->filled('dark_mode') ?? false,
                'avatar' => $path,
                'allow_email_based_notifications' => $request->filled('allow_email_based_notifications') ?? false,
                'billing_email' => $request->billing_email,
                'state' => $request->state,
                'country' => $request->country,
                'two_factor_platform' => $request->two_factor_platform,
            ]);

            activity('Settings updated.');

            if (! is_null($request->user()->two_factor_platform)) {
                activity('Enabled Two Factor Authenticator.');

                $request->user()->setTwoFactorCode();

                if ($request->user()->two_factor_platform === 'email') {
                    $request->user()->notify(new TwoFactorNotification);
                }

                if ($request->user()->two_factor_platform === 'authenticator') {
                    $google2fa = app('pragmarx.google2fa');
                    // log in the user automatically
                    $google2fa->login();
                    // Show them the QR or manual code
                    return view('user.authenticator', [
                        'manual' => $request->user()->two_factor_code,
                        'code' => $google2fa->getQRCodeInline(
                            config('app.name'),
                            $request->user()->email,
                            $request->user()->two_factor_code,
                        ),
                    ]);
                }
            }

            return redirect()->route('user.settings')->withMessage('Settings updated successfully');
        } catch (Exception $e) {
            Log::error($e);

            return redirect()->route('user.settings')->withErrors($e->getMessage());
        }
    }

    /**
     * Delete a user's avatar.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyAvatar(Request $request)
    {
        try {
            Storage::delete($request->user()->avatar);

            $request->user()->update([
                'avatar' => null,
            ]);

            return redirect()->back()->withMessage('Avatar deleted successfully');
        } catch (Exception $e) {
            Log::error($e);

            return redirect()->back()->withErrors($e->getMessage());
        }
    }
}
