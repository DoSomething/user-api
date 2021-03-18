<?php

namespace App\Http\Requests;

use App\Types\PostType;
use Illuminate\Validation\Rule;

class PostRequest extends Request
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
        if ($this->method() === 'PATCH') {
            return $this->patchRules();
        }

        $minImageSize = config('posts.image.min');
        $maxImageSize = config('posts.image.max');

        return [
            'campaign_id' => 'required_without:action_id|integer',
            'northstar_id' => 'nullable|objectid',
            'referrer_user_id' => 'nullable|objectid',
            'type' => ['required', 'string', Rule::in(PostType::all())],
            // @TODO: eventually, deprecate action in the payload and make action_id required when all systems have been updated.
            'action' => 'required_without:action_id|string',
            'action_id' =>
                'required_without:action,campaign_id|integer|exists:mysql.actions,id',
            'why_participated' => 'nullable|string',
            'text' => 'nullable|string|max:500',
            'location' => 'nullable|string',
            'postal_code' => 'nullable|max:10',
            'group_id' => 'nullable|integer|exists:mysql.groups,id',
            'school_id' => 'nullable|string|max:255',
            'quantity' => 'nullable|integer',
            // Maximum ensures we don't exceed the default precision limit for this Decimal db column.
            'hours_spent' => 'nullable|numeric|min:0.1|max:999999.99',
            'file' =>
                'image|dimensions:min_width=' .
                $minImageSize['width'] .
                ',min_height=' .
                $minImageSize['height'] .
                ',max_width=' .
                $maxImageSize['width'] .
                ',max_height=' .
                $maxImageSize['height'],
            'status' => $this->getStatusRules($this->type),
            'details' => 'json',
            'created_at' => 'date',
        ];
    }

    /**
     * Get the validation rules that apply to PATCH requests.
     *
     * @return array
     */
    private function patchRules()
    {
        return [
            'text' => 'nullable|string|max:500',
            'location' => 'nullable|string',
            'quantity' => 'nullable|integer',
            'status' => $this->getStatusRules($this->post->type),
            'school_id' => 'nullable|string|max:255',
        ];
    }

    /**
     * Get the allowed statuses for the type of post.
     *
     * @param string $type
     * @return string
     */
    private function getStatusRules($type)
    {
        switch ($type) {
            case 'photo':
                $rule = 'in:pending,accepted,rejected';
                break;
            case 'voter-reg':
                // Support new values and old values (until Chompy update is deployed)
                // @see https://github.com/DoSomething/chompy/pull/153
                $rule =
                    'in:pending,register-form,register-OVR,confirmed,ineligible,uncertain,rejected,under-18,step-1,step-2,step-3,step-4';
                break;
            case 'phone-call':
                $rule = 'in:accepted,incomplete';
                break;
            default:
                $rule = 'in:pending,accepted,rejected';
        }

        return $rule;
    }
}
