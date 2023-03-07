<?php

namespace App\Exceptions;

use Throwable;
use Illuminate\Support\ViewErrorBag;
use Whoops\Exception\ErrorException;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
        $this->renderable(function (ErrorException $e) {
        
            $app = app();
            $HtmlPenuh = $app->view->make('errors.lain', ['kesalahan' => $e->getMessage()]);
            $HtmlIsi = implode('',$HtmlPenuh->renderSections());
            $HtmlHeader = ['Vary' => 'Accept', 'X-Tujuan' => 'sematan_kesalahan'];
            $res = $app->make('Illuminate\Contracts\Routing\ResponseFactory');
            return $app->request->pjax() ? $res->make($HtmlIsi, 500, $HtmlHeader) : $res->make($HtmlPenuh, 500);
            
        });
        $this->renderable(function (ValidationException $exception)
        {
            // return response()->json([
            //     'message' => $exception->getMessage(),
            //     'errors' => $exception->errors(),
            // ], $exception->status);
            $app = app();
            $HtmlPenuh = $app->view->make('errors.422')->withErrors($exception->errors());
            $HtmlIsi = implode('',$HtmlPenuh->renderSections());
            $HtmlHeader = ['Vary' => 'Accept', 'X-Tujuan' => 'sematan_javascript'];
            $res = $app->make('Illuminate\Contracts\Routing\ResponseFactory');
            return $app->request->pjax() ? $res->make($HtmlIsi, $exception->status, $HtmlHeader) : $res->make($HtmlPenuh, $exception->status);
        });
    }

    protected function renderHttpException(HttpExceptionInterface $e)
    {
        parent::registerErrorViewPaths();

        if ($view = parent::getHttpExceptionView($e)) {
            $app = app();
            $HtmlPenuh = $app->view->make($view, ['errors' => new ViewErrorBag, 'exception' => $e]);
            $HtmlIsi = implode('',$HtmlPenuh->renderSections());
            $HtmlHeader = ['Vary' => 'Accept', 'X-Tujuan' => 'sematan_kesalahan'];
            $res = $app->make('Illuminate\Contracts\Routing\ResponseFactory');
            return $app->request->pjax() ? $res->make($HtmlIsi, $e->getStatusCode(), array_merge($e->getHeaders(), $HtmlHeader)) : $res->make($HtmlPenuh, $e->getStatusCode(), $e->getHeaders());
        }

        return parent::convertExceptionToResponse($e);
    }
}
