<?php

namespace App\Http\Forms;

use Grafite\FormMaker\Forms\BaseForm;
use Grafite\FormMaker\Fields\Dropzone;

class ImageUploadForm extends BaseForm
{
    public $route = 'ajax.files-upload';

    public $method = 'post';

    public $buttons = [];

    public function fields()
    {
        return [
            Dropzone::make('pics', [
                'label' => 'Upload Images',
                'route' => $this->route,
                'theme' => (auth()->user()->dark_mode) ? 'dark' : 'light',
            ]),
        ];
    }
}
