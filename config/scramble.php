<?php

use App\Scramble\Extensions\OAuth\UnsupportedDriverExceptionExtension;
use App\Scramble\Extensions\OAuthExceptionExtension;
use Dedoc\Scramble\Support\InferExtensions\JsonResourceExtension;
use Dedoc\Scramble\Support\InferExtensions\JsonResponseMethodReturnTypeExtension;
use Dedoc\Scramble\Support\InferExtensions\ModelExtension;
use Dedoc\Scramble\Support\InferExtensions\ResourceCollectionTypeInfer;
use Dedoc\Scramble\Support\InferExtensions\ResourceResponseMethodReturnTypeExtension;
use Dedoc\Scramble\Support\InferExtensions\ResponseFactoryTypeInfer;
use Dedoc\Scramble\Support\InferExtensions\TypeTraceInfer;
use Dedoc\Scramble\Support\OperationExtensions\DeprecationExtension;
use Dedoc\Scramble\Support\OperationExtensions\RequestBodyExtension;
use Dedoc\Scramble\Support\OperationExtensions\RequestEssentialsExtension;
use Dedoc\Scramble\Support\OperationExtensions\ResponseExtension;
use Dedoc\Scramble\Support\Type\VoidType;
use Dedoc\Scramble\Support\TypeToSchemaExtensions\CollectionToSchema;
use Dedoc\Scramble\Support\TypeToSchemaExtensions\EnumToSchema;
use Dedoc\Scramble\Support\TypeToSchemaExtensions\JsonResourceTypeToSchema;
use Dedoc\Scramble\Support\TypeToSchemaExtensions\MergesOpenApiObjects;
use Dedoc\Scramble\Support\TypeToSchemaExtensions\ModelToSchema;
use Dedoc\Scramble\Support\TypeToSchemaExtensions\ResourceResponseTypeToSchema;
use Dedoc\Scramble\Support\TypeToSchemaExtensions\ResponseTypeToSchema;
use Dedoc\Scramble\Support\TypeToSchemaExtensions\VoidTypeToSchema;

return [
    /*
     * Your API path. By default, all routes starting with this path will be added to the docs.
     * If you need to change this behavior, you can add your custom routes resolver using `Scramble::routes()`.
     */
    'api_path' => './',

    /*
     * Your API domain. By default, app domain is used. This is also a part of the default API routes
     * matcher, so when implementing your own, make sure you use this config if needed.
     */
    'api_domain' => null,

    /*
     * The path where your OpenAPI specification will be exported.
     */
    'export_path' => 'api.json',

    'info' => [
        /*
         * API version.
         */
        'version' => env('API_VERSION', '0.0.1'),

        /*
         * Description rendered on the home page of the API documentation (`/docs/api`).
         */
        'description' => '',
    ],

    /*
     * Customize Stoplight Elements UI
     */
    'ui' => [
        /*
         * Define the title of the documentation's website. App name is used when this config is `null`.
         */
        'title' => null,

        /*
         * Define the theme of the documentation. Available options are `light`, `dark`, and `system`.
         */
        'theme' => 'system',

        /*
         * Hide the `Try It` feature. Enabled by default.
         */
        'hide_try_it' => false,

        /*
         * Hide the schemas in the Table of Contents. Enabled by default.
         */
        'hide_schemas' => false,

        /*
         * URL to an image that displays as a small square logo next to the title, above the table of contents.
         */
        'logo' => '',

        /*
         * Use to fetch the credential policy for the Try It feature. Options are: omit, include (default), and same-origin
         */
        'try_it_credentials_policy' => 'include',

        /*
         * There are three layouts for Elements:
         * - sidebar - (Elements default) Three-column design with a sidebar that can be resized.
         * - responsive - Like sidebar, except at small screen sizes it collapses the sidebar into a drawer that can be toggled open.
         * - stacked - Everything in a single column, making integrations with existing websites that have their own sidebar or other columns already.
         */
        'layout' => 'responsive',
    ],

    /*
     * The list of servers of the API. By default, when `null`, server URL will be created from
     * `scramble.api_path` and `scramble.api_domain` config variables. When providing an array, you
     * will need to specify the local server URL manually (if needed).
     *
     * Example of non-default config (final URLs are generated using Laravel `url` helper):
     *
     * ```php
     * 'servers' => [
     *     'Live' => 'api',
     *     'Prod' => 'https://scramble.dedoc.co/api',
     * ],
     * ```
     */
    'servers' => [
        'local' => 'http://localhost:81',
    ],

    'middleware' => [
        'web',
    ],

    /**
     * Determines how Scramble stores the descriptions of enum cases.
     * Available options:
     * - 'description' – Case descriptions are stored as the enum schema's description using table formatting.
     * - 'extension' – Case descriptions are stored in the `x-enumDescriptions` enum schema extension.
     *
     *    @see https://redocly.com/docs-legacy/api-reference-docs/specification-extensions/x-enum-descriptions
     * - false - Case descriptions are ignored.
     */
    'enum_cases_description_strategy' => 'description',

    'extensions' => [
        OAuthExceptionExtension::class,
        UnsupportedDriverExceptionExtension::class,
        CollectionToSchema::class,
        DeprecationExtension::class,
        EnumToSchema::class,
        JsonResourceExtension::class,
        JsonResponseMethodReturnTypeExtension::class,
        JsonResourceTypeToSchema::class,
        MergesOpenApiObjects::class,
        ModelExtension::class,
        ModelToSchema::class,
        ResourceResponseTypeToSchema::class,
        ResourceResponseMethodReturnTypeExtension::class,
        ResourceCollectionTypeInfer::class,
        TypeTraceInfer::class,
        ResponseExtension::class,
        ResponseFactoryTypeInfer::class,
        ResponseTypeToSchema::class,
        RequestBodyExtension::class,
        RequestEssentialsExtension::class,
        VoidType::class,
        VoidTypeToSchema::class
    ],
];
