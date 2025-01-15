<?php

declare(strict_types=1);

namespace App\Controller;

use App\GraphQL\Schema;
use GraphQL\Error\DebugFlag;
use GraphQL\GraphQL as GraphQLBase;
use RuntimeException;
use Throwable;

use function file_get_contents;
use function header;
use function json_decode;
use function json_encode;

class GraphQL
{
    public static function handle()
    {
        try {
            $schema = Schema::build();
            $rawInput = file_get_contents('php://input');
            if ($rawInput === false) {
                throw new RuntimeException('Failed to get php://input');
            }

            $input = json_decode($rawInput, true);
            $query = $input['query'] ?? null;
            $variables = $input['variables'] ?? null;
            $result = GraphQLBase::executeQuery($schema, $query, null, null, $variables);
            $output = $result->toArray(DebugFlag::INCLUDE_DEBUG_MESSAGE);
        } catch (Throwable $e) {
            $output = [
                'error' => [
                    'message' => $e->getMessage(),
                ],
            ];
        }

        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($output);
    }
}
