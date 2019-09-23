<?php

namespace Northstar\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProfileAboutRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'birthdate' => 'nullable|date|before:now',
            'voter_registration_status' => 'nullable|in:uncertain,unregistered,confirmed',
            'causes.*' => 'nullable|in:animal_welfare,bullying,education,environment,gender_rights_equality,homelessness_poverty,immigration_refugees,lgbtq_rights_equality,mental_health,physical_health,racial_justice_equity,sexual_harassment_assault'
        ];
    }
}
