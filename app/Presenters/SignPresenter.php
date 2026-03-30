<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Model\UserFacade;
use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;
use Nette\Security\AuthenticationException;

final class SignPresenter extends Presenter
{
    public function __construct(
        private readonly UserFacade $userFacade,
    ) {
        parent::__construct();
    }

    public function actionIn(): void
    {
        if ($this->getUser()->isLoggedIn()) {
            $this->redirect('Home:default');
        }
    }

    public function actionOut(): void
    {
        $this->getUser()->logout();
        $this->redirect('Sign:in');
    }

    protected function createComponentSignInForm(): Form
    {
        $form = new Form();

        $form->addText('username', 'Username:')
            ->setRequired('Please enter your username.');

        $form->addPassword('password', 'Password:')
            ->setRequired('Please enter your password.');

        $form->addSubmit('send', 'Sign in');

        $form->onSuccess[] = $this->signInFormSucceeded(...);

        return $form;
    }

    private function signInFormSucceeded(Form $form, mixed $data): void
    {
        assert($data instanceof \stdClass);
        assert(is_string($data->username));
        assert(is_string($data->password));
        try {
            $this->getUser()->login($data->username, $data->password);
            $this->redirect('Home:default');
        } catch (AuthenticationException $e) {
            $form->addError('Invalid username or password.');
        }
    }
}
