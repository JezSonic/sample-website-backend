<?php

namespace App\Scramble\Extensions\Auth\OAuth;

use App\Exceptions\Auth\OAuth\UnsupportedDriver;
use Dedoc\Scramble\Extensions\ExceptionToResponseExtension;
use Dedoc\Scramble\Support\Generator\Reference;
use Dedoc\Scramble\Support\Generator\Response;
use Dedoc\Scramble\Support\Generator\Schema;
use Dedoc\Scramble\Support\Generator\Types as OpenApiTypes;
use Dedoc\Scramble\Support\Type\ObjectType;
use Dedoc\Scramble\Support\Type\Type;
use Illuminate\Support\Str;

class UnsupportedDriverExceptionExtension extends ExceptionToResponseExtension {
    public function shouldHandle(Type $type): bool {
        return $type instanceof ObjectType
            && (
            $type->isInstanceOf(UnsupportedDriver::class)
            );
    }

    public function toResponse(Type $type): ?Response {
        $validationResponseBodyType = (new OpenApiTypes\ObjectType)
            ->addProperty(
                'message',
                (new OpenApiTypes\StringType)
                    ->setDescription('Error overview.')
            )
            ->setRequired(['message']);

        return Response::make(401)
            ->description('Unsupported OAuth Driver exception')
            ->setContent(
                'application/json',
                Schema::fromType($validationResponseBodyType)
            );
    }

    public function reference(ObjectType $type): Reference {
        return new Reference('responses', Str::start($type->name, '\\'), $this->components);
    }
}
