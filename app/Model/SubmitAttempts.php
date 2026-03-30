<?php

declare(strict_types=1);

namespace App\Model;

use Nette\Database\Explorer;
use Nette\Database\Table\ActiveRow;
use Nette\Security\Passwords;

class SubmitAttempts
{
    public function __construct(
        private readonly Explorer $database,
    ) {
    }

    public function saveLoginAttempt(
        string $loginName,
        string $ipAddress,
        string $userAgent,
        ?ActiveRow $userRow,
    ): void {
        $this->database->table('login_attempts')->insert([
            'login_name' => $loginName,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'user_id' => $userRow['user_id'] ?? null,
        ]);
    }

}