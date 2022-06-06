<?php

namespace App\Exceptions;

use Throwable;
use Illuminate\Support\ViewErrorBag;
use Illuminate\Database\QueryException;
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
        $this->renderable(function (QueryException $e) {
            $HtmlPenuh = view('errors.lain', ['kesalahan' => $e->getMessage()]);
            $HtmlIsi = implode('',$HtmlPenuh->renderSections());
            $HtmlHeader = ['Vary' => 'Accept'];
            return request()->pjax() ? response($HtmlIsi, 500, array_merge($HtmlHeader)) : response($HtmlPenuh, 500);
        });
    }

    protected function renderHttpException(HttpExceptionInterface $e)
    {
        parent::registerErrorViewPaths();

        if ($view = parent::getHttpExceptionView($e)) {
            $HtmlPenuh = view($view, ['errors' => new ViewErrorBag, 'exception' => $e]);
            $HtmlIsi = implode('',$HtmlPenuh->renderSections());
            $HtmlHeader = ['Vary' => 'Accept'];
            return request()->pjax() ? response($HtmlIsi, $e->getStatusCode(), array_merge($e->getHeaders(),$HtmlHeader)) : response($HtmlPenuh, $e->getStatusCode(), $e->getHeaders());
        }

        return parent::convertExceptionToResponse($e);
    }
}
