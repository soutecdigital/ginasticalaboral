<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Database\QueryException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use PDOException;
use Throwable;


class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
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
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $exception)
    {
        // Se for erro 404, renderizar a view personalizada
        if ($exception instanceof HttpException && $exception->getStatusCode() == 404) {
            return response()->view('errors.404', [], 404);
        }

        // Se for erro de banco de dados (conexão, tabela não encontrada, campo não encontrado, etc)
        if ($exception instanceof QueryException || $exception instanceof PDOException) {
            return response()->view('errors.db_error', [
                'message' => $exception->getMessage(),
                'code' => $exception->getCode()
            ], 500);
        }

        return parent::render($request, $exception);
    }
}
