<?php

namespace App\Http\Controllers\Api;

use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="Мой API магазина",
 *     description="Документация API для интернет-магазина на Laravel"
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer"
 * )
 */
class OpenApiSpec
{
    // Пустой класс, нужен только для аннотаций
}
