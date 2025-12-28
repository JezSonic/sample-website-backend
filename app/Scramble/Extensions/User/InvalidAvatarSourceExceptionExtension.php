<?php

namespace App\Scramble\Extensions\User;

use App\Exceptions\User\AccountNotFoundException;
use App\Exceptions\User\InvalidAvatarSourceException;
use Dedoc\Scramble\Extensions\ExceptionToResponseExtension;
use Dedoc\Scramble\Support\Generator\Reference;
use Dedoc\Scramble\Support\Generator\Response;
use Dedoc\Scramble\Support\Generator\Schema;
use Dedoc\Scramble\Support\Generator\Types as OpenApiTypes;
use Dedoc\Scramble\Support\Type\ObjectType;
use Dedoc\Scramble\Support\Type\Type;
use Illuminate\Support\Str;

class InvalidAvatarSourceExceptionExtension extends ExceptionToResponseExtension {
    public function shouldHandle(Type $type): bool {
        return $type instanceof ObjectType
            && (
            $type->isInstanceOf(InvalidAvatarSourceException::class)
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

        return Response::make(400)
            ->description('Invalid avatar source exception')
            ->setContent(
                'application/json',
                Schema::fromType($validationResponseBodyType)
            );
    }

    public function reference(ObjectType $type): Reference {
        return new Reference('responses', Str::start($type->name, '\\'), $this->components);
    }
}
