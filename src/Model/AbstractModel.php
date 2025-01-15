<?php

declare(strict_types=1);

namespace App\Model;

use App\EnvLoader;
use PDO;

abstract class AbstractModel
{
    /**
     * PDO instance
     *
     * @var PDO
     */
    protected $pdo;

    public function __construct()
    {
        $env = new EnvLoader();
        $this->pdo = new PDO(
            'mysql:host=' . $env->dbHost . ';dbname=' . $env->dbName,
            $env->dbUser,
            $env->dbPass,
        );
    }
}
