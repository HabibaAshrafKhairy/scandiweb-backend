<?php

declare(strict_types=1);

namespace App\GraphQL;

use App\GraphQL\Resolvers\MutationResolver;
use App\GraphQL\Resolvers\QueryResolver;
use GraphQL\Type\Schema as BaseSchema;
use GraphQL\Type\SchemaConfig;

class Schema
{
    public static function build() : BaseSchema
    {
        $queryType = QueryResolver::getType();
        $mutationType = MutationResolver::getType();

        return new BaseSchema(
            (new SchemaConfig())
                ->setQuery($queryType)
                ->setMutation($mutationType)
        );
    }
}
