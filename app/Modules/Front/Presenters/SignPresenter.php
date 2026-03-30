<?php

declare(strict_types=1);

namespace App\Modules\Front\Presenters;

use App\Model\UserFacade;
use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;
use Nette\Database\UniqueConstraintViolationException;
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
            $this->redirect(':Admin:User:default');
        }
    }

    public function actionOut(): void
    {
        $this->getUser()->logout();
        $this->redirect('in');
    }

    public function actionUp(): void
    {
        if ($this->getUser()->isLoggedIn()) {
            $this->redirect(':Admin:User:default');
        }
    }

    protected function createComponentSignInForm(): Form
    {
        $form = new Form();

        $form->addText('login_name', 'Přihlašovací jméno:')
            ->setRequired('Zadejte přihlašovací jméno.');

        $form->addPassword('password', 'Heslo:')
            ->setRequired('Zadejte heslo.');

        $form->addSubmit('send', 'Přihlásit se');

        $form->onSuccess[] = $this->signInFormSucceeded(...);

        return $form;
    }

    /** @param object{login_name: string, password: string} $data */
    private function signInFormSucceeded(Form $form, $data): void
    {
        try {
            $this->getUser()->login($data->login_name, $data->password);
            $this->redirect(':Admin:User:default');
        } catch (AuthenticationException) {
            $form->addError('Neplatné přihlašovací jméno nebo heslo.');
        }
    }

    protected function createComponentSignUpForm(): Form
    {
        $form = new Form();

        $form->addText('name', 'Jméno:')
            ->setRequired('Zadejte jméno.');

        $form->addText('surname', 'Příjmení:')
            ->setRequired('Zadejte příjmení.');

        $form->addText('login_name', 'Přihlašovací jméno:')
            ->setRequired('Zadejte přihlašovací jméno.');

        $form->addText('phone_number', 'Telefon:')
            ->setRequired('Zadejte telefonní číslo.');

        $form->addEmail('email_address', 'E-mail:')
            ->setRequired('Zadejte e-mailovou adresu.');

        $form->addPassword('password', 'Heslo:')
            ->setRequired('Zadejte heslo.')
            ->addRule(Form::MinLength, 'Heslo musí mít alespoň %d znaků.', 8)
            ->addRule(Form::Pattern, 'Heslo musí obsahovat alespoň jedno číslo.', '.*[0-9].*');

        $form->addPassword('password_confirm', 'Heslo znovu:')
            ->setRequired('Zadejte heslo znovu.')
            ->addRule(Form::Equal, 'Hesla se neshodují.', $form['password']);

        $form->addSubmit('send', 'Registrovat se');

        $form->onSuccess[] = $this->signUpFormSucceeded(...);

        return $form;
    }

    /** @param object{name: string, surname: string, login_name: string, phone_number: string, email_address: string, password: string} $data */
    private function signUpFormSucceeded(Form $form, $data): void
    {
        try {
            $this->userFacade->register(
                $data->name,
                $data->surname,
                $data->login_name,
                $data->phone_number,
                $data->email_address,
                $data->password,
            );
            $this->getUser()->login($data->login_name, $data->password);
            $this->redirect(':Admin:User:default');
        } catch (UniqueConstraintViolationException) {
            $form->addError('Přihlašovací jméno nebo e-mail již existuje.');
        }
    }
}
