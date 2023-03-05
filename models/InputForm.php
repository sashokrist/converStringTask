<?php

namespace app\models;

use yii\base\Model;

class InputForm extends Model
{
    public $input;

    public function rules()
    {
        return [
            [['input'], 'required'],
        ];
    }

}