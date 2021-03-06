<?php

namespace App\Http\Requests\Settings\API;

use Laravel\Spark\Spark;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TokenSecretRequest extends FormRequest
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
     * Get the validator for the request.
     *
     * @return \Illuminate\Validation\Validator
     */
    public function validator()
    {
        return $this->validateAbilities(Validator::make($this->all(), [
            'name' => 'required|max:255',
            'site' => [
                'bail',
                'required',
                'url',
                'max:191',
                Rule::unique('api_tokens')->where(function ($query) {
                    $query->where('user_id', $this->user()->id);
                })
            ] // added this
        ], $this->messages()));
    }

    /**
     * Configure the valdiator to validate the token abilities.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return \Illuminate\Validation\Validator
     */
    protected function validateAbilities($validator)
    {
        $abilities = implode(',', array_keys(Spark::tokensCan()));

        $validator->sometimes('abilities', 'required|array|in:'.$abilities, function () {
            return count(Spark::tokensCan()) > 0;
        });

        return $validator;
    }

    /**
     * Set custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'abilities.required' => 'Please select at least one ability.',
        ];
    }
}
