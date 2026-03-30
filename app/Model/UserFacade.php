<?php

declare(strict_types=1);

namespace App\Model;

use Nette\Database\Explorer;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;
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
            ->where('login_name', $user)
            ->fetch();

        if (!$row) {
            throw new AuthenticationException('User not found.', self::IdentityNotFound);
        }

        $hash = $row->password;
        assert(is_string($hash));

        if (!$this->passwords->verify($password, $hash)) {
            throw new AuthenticationException('Invalid password.', self::InvalidCredential);
        }

        if ($this->passwords->needsRehash($hash)) {
            $row->update(['password' => $this->passwords->hash($password)]);
        }

        $role = $row->role;
        assert(is_string($role));

        return new SimpleIdentity($row->id, $role, [
            'login_name' => $row->login_name,
            'name' => $row->name,
            'surname' => $row->surname,
        ]);
    }

    /** @return Selection<ActiveRow> */
    public function getAll(): Selection
    {
        return $this->database->table('users');
    }

    public function getById(int $id): ?ActiveRow
    {
        $row = $this->database->table('users')->get($id);
        return $row instanceof ActiveRow ? $row : null;
    }

    public function register(
        string $name,
        string $surname,
        string $loginName,
        string $phoneNumber,
        string $emailAddress,
        string $password,
        string $role = 'user',
    ): void {
        $this->database->table('users')->insert([
            'name' => $name,
            'surname' => $surname,
            'login_name' => $loginName,
            'phone_number' => $phoneNumber,
            'email_address' => $emailAddress,
            'password' => $this->passwords->hash($password),
            'role' => $role,
        ]);
    }

    public function hashPassword(string $password): string
    {
        return $this->passwords->hash($password);
    }

    public function verifyPassword(int $id, string $password): bool
    {
        $row = $this->database->table('users')->get($id);
        if (!$row instanceof ActiveRow) {
            return false;
        }
        $hash = $row->password;
        assert(is_string($hash));
        return $this->passwords->verify($password, $hash);
    }

    /** @param array<string, mixed> $data */
    public function update(int $id, array $data): void
    {
        $this->database->table('users')->where('id', $id)->update($data);
    }
}
