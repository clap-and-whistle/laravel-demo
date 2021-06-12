<?php
declare(strict_types=1);

namespace App\Http\Controllers\UserOperation;

use App\Infrastructure\AggregateRepository\Exception\RegistrationProcessFailedException;
use Bizlogics\UseCase\UserOperation\CreateAccount\CreateAccountUseCase;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Routing\Redirector as LaraRedirector;
use Illuminate\Contracts\Foundation\Application as LaraApplication;
use Illuminate\Http\RedirectResponse as LaraRedirectResponse;
use Illuminate\View\View;
use Throwable;

final class CreateAccountController extends BaseController
{
    /** @var CreateAccountUseCase */
    private $useCase;

    public function __construct(CreateAccountUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function newAction(): View
    {
        $errors = session('errors');
        if ($errors) {
            logger(__METHOD__, $errors->all());
        }
        return view('user-operation.create-account.new');
    }

    public function storeAction(Request $request): LaraRedirector|LaraApplication|LaraRedirectResponse
    {
        $email = $request->get('email');
        $password = $request->get('password');
        try {
            $result = $this->useCase->execute($email, ($password ?? ''));
        } catch (RegistrationProcessFailedException $e) {
            logger(__METHOD__, [get_class($e), $e->getMessage()]);
            return redirect('/e');
        } catch (Throwable $e) {
            logger(__METHOD__, [
                '予期せぬエラー:'.$e->getMessage(),
                'トレース: ' . $e->getTraceAsString()
            ]);
            return redirect('/e');
        }

        return $result->isSuccess()
            ? redirect('/')
            : back()->withErrors($result->eMessage());
    }
}
