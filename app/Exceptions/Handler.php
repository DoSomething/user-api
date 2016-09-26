<?php

namespace Northstar\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use League\OAuth2\Server\Exception\OAuthServerException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Foundation\Validation\ValidationException as LegacyValidationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Psr\Http\Message\ResponseInterface;
use Exception;
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
        if ($e instanceof AuthenticationException) {
            return $this->unauthenticated($request, $e);
        } elseif ($e instanceof ValidationException || $e instanceof NorthstarValidationException) {
            return $this->invalidated($request, $e);
        } elseif ($e instanceof ModelNotFoundException) {
            $e = new NotFoundHttpException('That resource could not be found.');
        }

        // If client requests it, render exception as JSON object
        if ($request->ajax() || $request->wantsJson()) {
            return $this->buildJsonResponse($e);
        }

        return parent::render($request, $e);
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

        $withChrome = $request->query('chrome', 'true');
        $loginUrl = 'login?chrome='.$withChrome;

        return redirect()->guest($loginUrl);
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
