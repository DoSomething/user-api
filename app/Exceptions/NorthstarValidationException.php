<?php

namespace Northstar\Exceptions;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\JsonResponse;
use Exception;
use Northstar\Http\Transformers\UserTransformer;
use Northstar\Models\User;

class NorthstarValidationException extends Exception
{
    /**
     * The errors from the validator.
     *
     * @var array
     */
    protected $errors;

    /**
     * The formatted response for this exception.
     *
     * @var array
     */
    protected $response;

    /**
     * Create a new exception instance.
     *
     * @param array $errors
     */
    public function __construct($errors, $context = null)
    {
        parent::__construct('The given data failed to pass validation.');

        $this->errors = $errors;
        $this->response = [
            'error' => [
                'code' => 422,
                'message' => 'Failed validation.',
                'fields' => $errors,
            ],
        ];

        // @TODO: We should have a central place to handle transformations like this...
        if ($context instanceof User) {
            $this->response['error']['context'] = (new UserTransformer())->transform($context);
        } elseif (is_array($context) || $context instanceof Arrayable) {
            $this->response['error']['context'] = $context;
        }
    }

    /**
     * Get the key-value array of errors.
     *
     * @return array
     */
    public function errors()
    {
        return $this->errors;
    }

    /**
     * Get the formatted JSON response.
     *
     * @return JsonResponse
     */
    public function getResponse()
    {
        return new JsonResponse($this->response, 422);
    }
}
