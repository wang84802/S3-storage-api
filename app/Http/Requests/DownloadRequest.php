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
            'data.uni_id' => 'required',
        ];
        return $rules;
    }
    public function messages()
    {
        return [
            'uni_id.required' => 'uni_id is required!',
        ];
    }
    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json(
            [
                'status' => 422,
                'error' => [
                    'message' => $validator->errors()
                ],
            ]
            , 400));
    }
}
