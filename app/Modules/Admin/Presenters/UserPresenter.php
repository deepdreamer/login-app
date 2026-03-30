<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presenters;

use App\Model\UserFacade;
use Contributte\Datagrid\Datagrid;
use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;
use Nette\Database\Table\ActiveRow;
use Nette\Database\UniqueConstraintViolationException;

final class UserPresenter extends Presenter
{
    public function __construct(
        private readonly UserFacade $userFacade,
    ) {
        parent::__construct();
    }

    public function startup(): void
    {
        parent::startup();
        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect(':Front:Sign:in');
        }
    }

    public function actionDefault(): void
    {
    }

    public function actionEdit(int $id): void
    {
        $loggedInId = (int) $this->getUser()->getId();
        $isAdmin = $this->getUser()->isInRole('admin');

        if (!$isAdmin && $loggedInId !== $id) {
            $this->error('Přístup odepřen.', 403);
        }

        $user = $this->userFacade->getById($id);
        if (!$user) {
            $this->error('Uživatel nenalezen.');
        }

        $this->template->editedUser = $user;

        $defaults = [
            'name' => $user->name,
            'surname' => $user->surname,
            'login_name' => $user->login_name,
            'phone_number' => $user->phone_number,
            'email_address' => $user->email_address,
        ];

        if ($isAdmin) {
            $defaults['role'] = $user->role;
        }

        $this['editForm']->setDefaults($defaults);
    }

    public function actionAdd(): void
    {
        if (!$this->getUser()->isInRole('admin')) {
            $this->error('Přístup odepřen.', 403);
        }
    }

    protected function createComponentUsersGrid(string $name): Datagrid
    {
        $grid = new Datagrid($this, $name);
        $grid->setDataSource($this->userFacade->getAll());

        $grid->addColumnNumber('id', 'ID')
            ->setSortable();

        $grid->addColumnText('login_name', 'Přihlašovací jméno')
            ->setSortable()
            ->setFilterText();

        $grid->addColumnText('name', 'Jméno')
            ->setSortable()
            ->setFilterText();

        $grid->addColumnText('surname', 'Příjmení')
            ->setSortable()
            ->setFilterText();

        $grid->addColumnText('email_address', 'E-mail')
            ->setSortable()
            ->setFilterText();

        $grid->addColumnText('role', 'Role')
            ->setSortable();

        $loggedInId = (int) $this->getUser()->getId();
        $isAdmin = $this->getUser()->isInRole('admin');

        $grid->addAction('edit', 'Upravit', 'edit')
            ->setIcon('pencil')
            ->setRenderCondition(static function (ActiveRow $row) use ($loggedInId, $isAdmin): bool {
                $rowId = $row->id;
                assert(is_int($rowId) || is_string($rowId));
                return $isAdmin || (int) $rowId === $loggedInId;
            });

        if ($isAdmin) {
            $grid->addToolbarButton('add', 'Přidat uživatele')
                ->setIcon('plus')
                ->setClass('btn btn-success btn-sm');
        }

        $grid->setItemsPerPageList([10, 25, 50]);

        return $grid;
    }

    protected function createComponentEditForm(): Form
    {
        $isAdmin = $this->getUser()->isInRole('admin');
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

        $form->addPassword('password', 'Nové heslo:')
            ->addCondition(Form::Filled)
            ->addRule(Form::MinLength, 'Heslo musí mít alespoň %d znaků.', 8);

        if ($isAdmin) {
            $form->addSelect('role', 'Role:', ['user' => 'Uživatel', 'admin' => 'Administrátor'])
                ->setRequired('Vyberte roli.');
        }

        $form->addSubmit('send', 'Uložit');

        $form->onSuccess[] = $this->editFormSucceeded(...);

        return $form;
    }

    /** @param object{name: string, surname: string, login_name: string, phone_number: string, email_address: string, password: string, role?: string} $data */
    private function editFormSucceeded(Form $form, $data): void
    {
        $idParam = $this->getParameter('id');
        $id = is_numeric($idParam) ? (int) $idParam : 0;

        /** @var array<string, mixed> $values */
        $values = [
            'name' => $data->name,
            'surname' => $data->surname,
            'login_name' => $data->login_name,
            'phone_number' => $data->phone_number,
            'email_address' => $data->email_address,
        ];

        if ($data->password !== '') {
            $values['password'] = $this->userFacade->hashPassword($data->password);
        }

        if ($this->getUser()->isInRole('admin') && isset($data->role)) {
            $values['role'] = $data->role;
        }

        try {
            $this->userFacade->update($id, $values);
            $this->flashMessage('Uživatel byl úspěšně upraven.', 'success');
            $this->redirect('default');
        } catch (UniqueConstraintViolationException) {
            $form->addError('Přihlašovací jméno nebo e-mail již existuje.');
        }
    }

    protected function createComponentAddForm(): Form
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
            ->addRule(Form::MinLength, 'Heslo musí mít alespoň %d znaků.', 8);

        $form->addSelect('role', 'Role:', ['user' => 'Uživatel', 'admin' => 'Administrátor'])
            ->setRequired('Vyberte roli.');

        $form->addSubmit('send', 'Přidat uživatele');

        $form->onSuccess[] = $this->addFormSucceeded(...);

        return $form;
    }

    /** @param object{name: string, surname: string, login_name: string, phone_number: string, email_address: string, password: string, role: string} $data */
    private function addFormSucceeded(Form $form, $data): void
    {
        try {
            $this->userFacade->register(
                $data->name,
                $data->surname,
                $data->login_name,
                $data->phone_number,
                $data->email_address,
                $data->password,
                $data->role,
            );
            $this->flashMessage('Uživatel byl úspěšně přidán.', 'success');
            $this->redirect('default');
        } catch (UniqueConstraintViolationException) {
            $form->addError('Přihlašovací jméno nebo e-mail již existuje.');
        }
    }
}
