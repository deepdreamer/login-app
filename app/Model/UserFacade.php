<?php

declare(strict_types=1);

namespace App\Model;

use Nette\Database\Explorer;
use Nette\Security\AuthenticationException;
use Nette\Security\Authenticator;
use Nette\Security\IIdentity;
use Nette\Security\Passwords;
use Nette\Security\SimpleIdentity;

final class UserFacade implements Authenticator
{
	public function __construct(
		private readonly Explorer $database,
		private readonly Passwords $passwords,
	) {
	}

	public function authenticate(string $user, string $password): IIdentity
	{
		$row = $this->database->table('users')
			->where('username', $user)
			->fetch();

		if (!$row) {
			throw new AuthenticationException('User not found.', self::IdentityNotFound);
		}

		if (!$this->passwords->verify($password, $row->password)) {
			throw new AuthenticationException('Invalid password.', self::InvalidCredential);
		}

		if ($this->passwords->needsRehash($row->password)) {
			$row->update(['password' => $this->passwords->hash($password)]);
		}

		return new SimpleIdentity($row->id, $row->role, ['username' => $row->username]);
	}
}
