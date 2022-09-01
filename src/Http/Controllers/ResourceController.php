<?php

namespace Benjacho\BelongsToManyField\Http\Controllers;

use App\Http\Controllers\Controller;
use Benjacho\BelongsToManyField\Http\Requests\QuickCreateRequest;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Laravel\Nova\Http\Requests\NovaRequest;
use Psr\SimpleCache\InvalidArgumentException;

class ResourceController extends Controller
{
    public function index(NovaRequest $request, $parent, $relationship, $optionsLabel, $dependsOnValue = null, $dependsOnKey = null)
    {
        $resourceClass = $request->newResource();
        $field = $resourceClass
            ->availableFields($request)
            ->where('component', 'BelongsToManyField')
            ->where('attribute', $relationship)
            ->first();
        $query = $field->resourceClass::newModel();

        $queryResult = $field->resourceClass::relatableQuery($request, $query);

        if ($dependsOnValue) {
            $queryResult = $queryResult->where($dependsOnKey, $dependsOnValue);
        }

        return $queryResult->get()
            ->mapInto($field->resourceClass)
            ->filter(function ($resource) use ($request, $field) {
                return $request->newResource()->authorizedToAttach($request, $resource->resource);
            })->map(function ($resource) use ($optionsLabel) {
                return [
                    'id' => $resource->id,
                    $optionsLabel => $resource->title(),
                    'value' => $resource->getKey(),
                ];
            })
            ->sortBy($optionsLabel)
            ->values();
    }

    /**
     * @throws InvalidArgumentException|AuthorizationException
     */
    public function create(QuickCreateRequest $request): array {
        /** @var Model $modelClass */
        $modelName  = $request->input('model');
        $modelClass = new $modelName();

        // Unique slug
        if ($request->has('values.slug') && $option = $modelClass::where('slug', $request->input('values.slug'))->first()) {
            return ['success' => true, 'option' => $option, 'existing' => true];
        }

        $this->authorize('create', $modelName);

        \Log::debug($request->input('values'));

        $modelClass->fill($request->input('values'));

        $cacheKey = $request->input('cache_key');

        if ($cacheKey !== null) {
            Cache::delete($cacheKey);
        }

        return ['success' => $modelClass->save(), 'option' => $modelClass];
    }
}
