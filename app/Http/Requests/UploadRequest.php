<?php

namespace App\Http\Requests;

use Log;
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
        $rules = [
            'data.filename' => 'required',
            'data.content' => 'required'
        ];
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

        throw new HttpResponseException(response()->json(
            [
                'status' => 422,
                'error' => [
                    'message' => $validator->messages()->first(),
                    'error' => $validator->errors()
                ],
            ]
            , 400));
    }
}
