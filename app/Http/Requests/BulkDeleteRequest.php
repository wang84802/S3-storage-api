<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class BulkDeleteRequest extends FormRequest
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
            'data' => 'required',
            'data.*.uni_id' => 'required|distinct',
        ];
        return $rules;
    }
    public function failedValidation(Validator $validator)
    {
        $errors = $validator->failed();
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
                    'message' => $validator->errors()->first()
                ]],
            ]
            , 400));
    }
}
