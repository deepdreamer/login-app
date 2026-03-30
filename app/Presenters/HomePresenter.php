<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette\Application\UI\Presenter;

final class HomePresenter extends Presenter
{
	public function actionDefault(): void
	{
		if (!$this->getUser()->isLoggedIn()) {
			$this->redirect('Sign:in');
		}
	}
}
