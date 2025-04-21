<?php

namespace App\Scramble\Extensions;

use App\Exceptions\Auth\OAuth\AuthOAuthException;
use Dedoc\Scramble\Extensions\ExceptionToResponseExtension;
use Dedoc\Scramble\Support\Generator\Reference;
use Dedoc\Scramble\Support\Generator\Response;
use Dedoc\Scramble\Support\Generator\Schema;
use Dedoc\Scramble\Support\Generator\Types as OpenApiTypes;
use Dedoc\Scramble\Support\Type\ObjectType;
use Dedoc\Scramble\Support\Type\Type;
use Illuminate\Support\Str;

class OAuthExceptionExtension extends ExceptionToResponseExtension {
    public function shouldHandle(Type $type): bool {
        return $type instanceof ObjectType
            && (
            $type->isInstanceOf(AuthOAuthException::class)
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

        return Response::make(500)
            ->description('OAuth Authentication exception')
            ->setContent(
                'application/json',
                Schema::fromType($validationResponseBodyType)
            );
    }

    public function reference(ObjectType $type): Reference {
        return new Reference('responses', Str::start($type->name, '\\'), $this->components);
    }
}
