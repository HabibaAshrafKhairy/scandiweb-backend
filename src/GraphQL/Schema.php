<?php

namespace App\GraphQL;

use GraphQL\Type\Schema as BaseSchema;
use GraphQL\Type\SchemaConfig;
use App\GraphQL\Resolvers\QueryResolver;
use App\GraphQL\Resolvers\MutationResolver;

class Schema
{
  public static function build(): BaseSchema
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
