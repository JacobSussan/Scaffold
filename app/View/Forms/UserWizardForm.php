<?php

namespace App\View\Forms;

use App\Models\Role;
use Grafite\Forms\Fields\Text;
use Grafite\Forms\Fields\Email;
use Grafite\Forms\Forms\WizardForm;
use Grafite\Forms\Fields\Bootstrap\HasOne;
use Grafite\Forms\Fields\Bootstrap\Country;
use Grafite\Forms\Fields\Bootstrap\Timezone;

class UserWizardForm extends WizardForm
{
    public $route = 'admin.users.store';

    public $method = 'post';

    public $isCardForm = true;

    public $progressBarColor = 'var(--bs-primary)';

    public $buttons = [
        'submit' => 'Submit',
    ];

    public function fields()
    {
        return [
            Text::make('name', [
                'required' => true,
            ]),
            Email::make('email', [
                'required' => true,
            ]),
            HasOne::make('role', [
                'required' => true,
                'visible' => false,
                'multiple' => false,
                'model' => Role::class,
                'model_options' => [
                    'label' => 'label',
                ],
            ]),
            Timezone::make('timezone'),
            Country::make('country'),
        ];
    }

    public function steps()
    {
        return [
            [['name','email'],['role']],
            [['timezone', 'country']],
        ];
    }
}