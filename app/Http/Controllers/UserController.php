<?php namespace Northstar\Http\Controllers;

use Illuminate\Http\Request;
use Northstar\Services\DrupalAPI;
use Northstar\Models\User;
use Input;
use Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;


class UserController extends Controller
{

    /**
     * Display a listing of the resource.
     * GET /users
     * Get /users?attr1=value1&attr2=value2&...
     *
     * @return Response
     */
    public function index()
    {
        $inputs = Input::except('page');
        $users = User::where($inputs);
        $response = $this->respondPaginated($users, $inputs);
        return $response;
    }

    /**
     * Store a newly created resource in storage.
     * POST /users
     *
     * @param Request $request
     * @return Response
     * @throws UnauthorizedHttpException
     */
    public function store(Request $request)
    {
        $check = $request->only('email', 'mobile');
        $input = $request->all();

        $user = false;
        $found_user = false;

        // Does this user exist already?
        if (Input::has('email')) {
            $found_user = User::where('email', '=', $check['email'])->first();
        } elseif (Input::has('mobile')) {
            $found_user = User::where('mobile', '=', $check['mobile'])->first();
        }

        if ($found_user && password_verify($input['password'], $found_user->password)) {
            $user = $found_user;
        } else {
            throw new HttpException(412, 'Incorrect password.');
        }

        // If there is no user found, create a new one.
        if (!$user) {
            $user = new User;

            // This validation might not be needed, the only validation happening right now
            // is for unique email or phone numbers, and that should return a user
            // from the query above.
            $this->validate($request, [
                'email' => 'email|unique:users|required_without:mobile',
                'mobile' => 'unique:users|required_without:email'
            ]);
        }
        // Update or create the user from all the input.
        try {
            $user->fill($input);

            // Do we need to forward this user to drupal?
            // If query string exists, make a drupal user.
            // @TODO: we can't create a Drupal user without an email. Do we just create an @mobile one like we had done previously?
            if (Input::has('create_drupal_user') && Input::has('password') && !$user->drupal_id) {
                try {
                    $drupal = new DrupalAPI;
                    $drupal_id = $drupal->register($user, Input::get('password'));
                    $user->drupal_id = $drupal_id;
                } catch (\Exception $e) {
                    // If user already exists, find the user to get the uid.
                    if ($e->getCode() == 403) {
                        try {
                            $drupal_id = $drupal->getUidByEmail($user->email);
                            $user->drupal_id = $drupal_id;
                        } catch (\Exception $e) {
                            // @TODO: still ok to just continue and allow the user to be saved?
                        }
                    }
                }
            }
            if (Input::has('created_at')) {
                $user->created_at = Input::get('created_at');
            }

            $user->save();

            // Log the user in & attach their session token to response.
            $token = $user->login();
            $user->session_token = $token->key;

            return $this->respond($user);
        } catch (\Exception $e) {
            return $this->respond($e, 401);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param $term - string
     *   term to search by (eg. mobile, drupal_id, id, email, etc)
     * @param $id - string
     *  the actual value to search for
     *
     * @return Response
     * @throws NotFoundHttpException
     */
    public function show($term, $id)
    {
        // Find the user.
        $user = User::where($term, $id)->get();
        if (!$user->isEmpty()) {
            return $this->respond($user);
        }

        throw new NotFoundHttpException('The resource does not exist.');
    }


    /**
     * Update the specified resource in storage.
     * PUT /users
     *
     * @param $id - User ID
     * @return Response
     * @throws NotFoundHttpException
     */
    public function update($id)
    {
        $input = Input::all();

        $user = User::where('_id', $id)->first();

        if ($user instanceof User) {
            foreach ($input as $key => $value) {
                if ($key == 'interests') {
                    $interests = array_map('trim', explode(',', $value));
                    $user->push('interests', $interests, true);
                } // Only update attribute if value is non-null.
                elseif (isset($key) && !is_null($value)) {
                    $user->$key = $value;
                }
            }

            $user->save();

            $response = array('updated_at' => $user->updated_at);

            return $this->respond($response, 202);
        }

        throw new NotFoundHttpException('The resource does not exist.');
    }

    /**
     * Delete a user resource.
     * DELETE /users/:id
     *
     * @param $id - User ID
     * @return Response
     * @throws NotFoundHttpException
     */
    public function destroy($id)
    {
        $user = User::where('_id', $id)->first();

        if ($user instanceof User) {
            $user->delete();

            return $this->respond('No Content.');
        } else {
            throw new NotFoundHttpException('The resource does not exist.');
        }
    }

    /**
     * Create the response for when a request fails validation. Overrides the ValidatesRequests trait.
     *
     * @param Request $request
     * @param array $errors
     * @return \Illuminate\Http\Response
     * @throws UnauthorizedHttpException
     */
    protected function buildFailedValidationResponse(Request $request, array $errors)
    {
        $error_message = '';
        if (count($errors) > 0) {
            foreach ($errors as $e) {
                foreach ($e as $message) {
                    $error_message .= $message . ' ';
                }
            }

            throw new UnauthorizedHttpException(null, trim($error_message));
        }
        else {
            return parent::buildFailedValidationResponse($request, $errors);
        }
    }

}
