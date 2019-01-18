<?php

namespace App\Http\Requests;

use Log;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class DownloadRequest extends FormRequest
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
        $rules = [
            'data.uni_id' => 'required|regex:/^[\w\d]+$/',
        ];
        return $rules;
    }
    public function failedValidation(Validator $validator)
    {
        //$$validator->failed() -> $errors
        //$validator->errors()->messages() -> $messages
        $errors = $validator->failed();
        $keyname = key($errors);
        $serviceCode = config('error_code.service_code');
        $errorCode = array_merge(config('error_code.custom'),
            config('error_code.base'));
        Log::info($validator->messages()->first());
        throw new HttpResponseException(response()->json(
            [
                'status' => 400,
                'error' => [
                    'code' => "400{$serviceCode}{$errorCode[snake_case(key(array_first($errors)))]}",
                    'key' => $keyname,
                    'message' => $validator->messages()->first()
                ],
            ]
            , 400));
    }
}
