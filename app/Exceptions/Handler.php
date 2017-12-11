<?php

namespace Northstar\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Foundation\Validation\ValidationException as LegacyValidationException;
use Illuminate\Validation\ValidationException;
use League\OAuth2\Server\Exception\OAuthServerException;
use Psr\Http\Message\ResponseInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
    const PRODUCTION_ERROR_MESSAGE = 'Looks like something went wrong. We\'ve noted the problem and will try to get it fixed!';

    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthenticationException::class,
        AuthorizationException::class,
        HttpException::class,
        OAuthServerException::class,
        ModelNotFoundException::class,
        ValidationException::class,
        NorthstarValidationException::class,
        LegacyValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception $e
     * @return void
     */
    public function report(Exception $e)
    {
        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Exception $e
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function render($request, Exception $e)
    {
        // If we receive a OAuth exception, get the included PSR-7 response,
        // convert it to a standard Symfony HttpFoundation response and return.
        if ($e instanceof OAuthServerException) {
            $psrResponse = $e->generateHttpResponse(app(ResponseInterface::class));

            return (new HttpFoundationFactory())->createResponse($psrResponse);
        }

        // Re-cast specific exceptions or uniquely render them:
        if ($e instanceof HttpException && $e->getStatusCode() === 429) {
            return $this->rateLimited($e);
        } elseif ($e instanceof AuthenticationException) {
            return $this->unauthenticated($request, $e);
        } elseif ($e instanceof ValidationException || $e instanceof NorthstarValidationException) {
            return $this->invalidated($request, $e);
        } elseif ($e instanceof ModelNotFoundException) {
            $e = new NotFoundHttpException('That resource could not be found.');
        }

        // If request has 'Accepts: application/json' header or we're on a route that
        // is in the `api` middleware group, render the exception as JSON object.
        if ($request->ajax() || $request->wantsJson() || has_middleware('api')) {
            return $this->buildJsonResponse($e);
        }

        // Redirect to root if trying to access disabled methods on a controller or access denied to user.
        if ($e instanceof MethodNotAllowedHttpException || $e instanceof AccessDeniedHttpException) {
            return redirect('/');
        }

        return parent::render($request, $e);
    }

    /**
     * Create a 'too many attempts' response.
     *
     * @param  string  $key
     * @param  int  $maxAttempts
     * @return \Illuminate\Http\Response|JsonResponse
     */
    protected function rateLimited($exception)
    {
        // Report the rate-limited request to StatHat.
        event(new \Northstar\Events\Throttled());

        $retryAfter = $exception->getHeaders()['Retry-After'];
        $minutes = ceil($retryAfter / 60);
        $pluralizedNoun = $minutes === 1 ? 'minute' : 'minutes';
        $message = 'Too many attempts. Please try again in '.$minutes.' '.$pluralizedNoun.'.';

        if (request()->wantsJson() || request()->ajax()) {
            return new JsonResponse($message, 429, $exception->getHeaders());
        }

        return redirect()->back()->with('status', $message);
    }

    /**
     * Convert an validation exception into flash redirect or JSON response.
     *
     * @param \Illuminate\Http\Request $request
     * @param ValidationException|NorthstarValidationException $e
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function invalidated($request, $e)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return $e->getResponse();
        }

        if ($e instanceof ValidationException) {
            return $e->getResponse();
        }

        return redirect()->back()
            ->withInput($request->except('password', 'password_confirmation'))
            ->withErrors($e->getErrors());
    }

    /**
     * Convert an authentication exception into an redirect or JSON response.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Illuminate\Auth\AuthenticationException $e
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function unauthenticated($request, AuthenticationException $e)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return $this->buildJsonResponse(new HttpException(401, 'Unauthorized.'));
        }

        return redirect()->guest('register');
    }

    /**
     * Build a JSON error response for API clients.
     *
     * @param Exception $e
     * @return \Illuminate\Http\JsonResponse
     */
    protected function buildJsonResponse(Exception $e)
    {
        $code = $e instanceof HttpException ? $e->getStatusCode() : 500;
        $shouldHideErrorDetails = $code == 500 && ! config('app.debug');
        $response = [
            'error' => [
                'code' => $code,
                'message' => $shouldHideErrorDetails ? self::PRODUCTION_ERROR_MESSAGE : $e->getMessage(),
            ],
        ];

        // Show more information if we're in debug mode
        if (config('app.debug')) {
            $response['debug'] = [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ];
        }

        return response()->json($response, $code);
    }
}
