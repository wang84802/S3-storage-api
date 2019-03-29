<?php

namespace App\Http\Requests;

use Log;
use App\Exceptions\ValidateException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class UploadRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = array(
            'data' => 'required',
            'data.filename' => 'required',
            'data.content' => 'required'
            //'data.content' => array('required','regex:/^([A-Za-z0-9\+=\/])*$/')
        );
        return $rules;
    }
    public function messages()
    {
        return [
            'filename.required' => 'name is required!',
            'content.required' => 'content is required!'
        ];
    }
    public function failedValidation(Validator $validator) {

        $errors = $validator->failed();
        //$validator->errors()->messages() -> $messages
        $keyname = key($errors);
        $serviceCode = config('error_code.service_code');
        $errorCode = array_merge(config('error_code.custom'),
            config('error_code.base'));

        throw new HttpResponseException(response()->json(
            [
                'status' => 400,
                'error' => [[
                    'key' => $keyname,
                    'code' => "400{$serviceCode}{$errorCode[snake_case(key(array_first($errors)))]}",
                    'message' => $validator->messages()->first()
                ]],
            ]
            , 400));
    }
}
