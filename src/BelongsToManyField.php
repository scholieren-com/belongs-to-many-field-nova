<?php

namespace Benjacho\BelongsToManyField;

use Benjacho\BelongsToManyField\Rules\ArrayRules;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\ResourceRelationshipGuesser;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * @method static static make(string $name, string|\Closure|callable|object|null $attribute = null, string|null $resource = null)
 */
class BelongsToManyField extends Field
{
    /**
     * The callback to be used for the field's options.
     *
     * @var array|callable
     */
    private $optionsCallback;

    /**
     * @var callable
     */
    protected $filledCallback;

    public $showOnIndex = true;
    public $showOnDetail = true;
    public $isAction = false;
    public $selectAll = false;
    public $messageSelectAll = 'Select All';
    public $height = '350px';
    public $viewable = true;
    public $creatable = false;
    public $showAsList = false;
    public $pivotData = [];
    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'BelongsToManyField';
    public $relationModel;
    public $label = null;
    public $trackBy = "id";

    /**
     * Create a new field.
     *
     * @param string $name
     * @param string|null $attribute
     * @param string|null $resource
     *
     * @return void
     */
    public function __construct($name, $attribute = null, $resource = null)
    {
        parent::__construct($name, $attribute);
        $resource = $resource ?? ResourceRelationshipGuesser::guessResource($name);

        $this->resource = $resource;

        if ($this->label === null) {
            $this->optionsLabel(($resource)::$title ?? 'name');
        }

        $this->resourceClass = $resource;
        $this->resourceName = $resource::uriKey();
        $this->manyToManyRelationship = $this->attribute;

        $this->fillUsing(function ($request, $model, $attribute, $requestAttribute) use ($resource) {
            if (is_subclass_of($model, 'Illuminate\Database\Eloquent\Model')) {
                $model::saved(function ($model) use ($attribute, $request) {
                    $requestData = $request->$attribute;

                    $values = self::getValuesFromRequestData($requestData, $model->getKeyName());

                    if (!empty($this->pivot())) {
                        $values = array_fill_keys($values, $this->pivot());
                    }

                    $model->$attribute()->sync(
                        $values,
                        detaching: !$model->wasRecentlyCreated
                    );

                    if ($this->filledCallback) {
                        call_user_func($this->filledCallback, $model, $attribute, $values, $request);
                    }
                });
                $request->except($attribute);
            }
        });
        $this->localize();
    }

    /**
     * @throws JsonException
     */
    public static function getValuesFromRequestData(mixed $requestData, string $primaryKey = 'id'): array
    {
        if ($requestData !== null && $requestData !== "null") {
            $valuesArray = json_decode($requestData, true, 512, JSON_THROW_ON_ERROR);

            if ($valuesArray === '') {
                $values = [];
            } else {
                $values = array_column($valuesArray, $primaryKey);
            }

        } else {
            $values = [];
        }

        return $values;
    }

    public function clearCacheAfterCreating(string $cacheKey): static
    {
        return $this->withMeta(['cache_key' => $cacheKey]);
    }

    public function optionsLabel(string $optionsLabel)
    {
        $this->label = $optionsLabel;

        return $this->withMeta(['optionsLabel' => $this->label]);
    }

    public function trackBy(string $trackBy)
    {
        $this->trackBy = $trackBy;
        return $this->withMeta(['trackBy' => $this->trackBy]);
    }

    public function options($options = [])
    {
        $this->optionsCallback = $options;

        return $this;
    }

    public function relationModel($model)
    {
        $this->relationModel = $model;

        return $this;
    }

    /**
     * @param array $fields
     *
     * @return self
     */
    public function fields(array $fields = ['name']): self {
        return $this->withMeta(['fields' => $fields]);
    }

    public function model($model = null): self {
        return $this->withMeta(['model' => $model]);
    }

    public function creatable($creatable = true): self {
        return $this->withMeta(['creatable' => $creatable]);
    }

    public function isAction($isAction = true)
    {
        $this->isAction = $isAction;

        return $this->withMeta(['height' => $this->height]);
    }

    public function canSelectAll($messageSelectAll = 'Select All', $selectAll = true)
    {
        $this->selectAll = $selectAll;
        $this->messageSelectAll = $messageSelectAll;

        return $this->withMeta(['selectAll' => $this->selectAll, 'messageSelectAll' => $this->messageSelectAll]);
    }

    public function showAsListInDetail($showAsList = true)
    {
        $this->showAsList = $showAsList;

        return $this->withMeta(['showAsList' => $this->showAsList]);
    }

    public function viewable($viewable = true)
    {
        $this->viewable = $viewable;

        return $this;
    }

    public function setMultiselectProps($props)
    {
        return $this->withMeta(['multiselectOptions' => $props]);
    }

    public function setMultiselectSlots($slots)
    {
        return $this->withMeta(['multiselectSlots' => $slots]);
    }

    public function dependsOn($dependsOnField, $tableKey)
    {
        return $this->withMeta([
            'dependsOn' => $dependsOnField,
            'dependsOnKey' => $tableKey,
        ]);
    }

    public function rules($rules)
    {
        $rules = ($rules instanceof Rule || is_string($rules)) ? func_get_args() : $rules;
        $this->rules = [new ArrayRules($rules)];

        return $this;
    }

    public function resolve($resource, $attribute = null)
    {
        if ($this->isAction) {
            parent::resolve($resource, $attribute);
        } else {
            parent::resolve($resource, $attribute);
            $value = json_decode($resource->{$this->attribute});

            if ($value) {
                $this->value = $value;
            }
        }
    }

    public function jsonSerialize(): array
    {
        $this->resolveOptions();

        return array_merge([
            'attribute' => $this->attribute,
            'component' => $this->component(),
            'helpText' => $this->getHelpText(),
            'indexName' => $this->name,
            'name' => $this->name,
            'nullable' => $this->nullable,
            'optionsLabel' => $this->label,
            'trackBy' => $this->trackBy,
            'panel' => $this->panel,
            'prefixComponent' => true,
            'readonly' => $this->isReadonly(app(NovaRequest::class)),
            'required' => $this->isRequired(app(NovaRequest::class)),
            'resourceNameRelationship' => $this->resourceName,
            'sortable' => $this->sortable,
            'sortableUriKey' => $this->sortableUriKey(),
            'stacked' => $this->stacked,
            'textAlign' => $this->textAlign,
            'value' => $this->value,
            'viewable' => $this->viewable,
            'visible' => $this->visible,
            'validationKey' => $this->validationKey(),
        ], $this->meta());
    }

    public function pivot()
    {
        return $this->pivotData;
    }

    public function setPivot(array $attributes)
    {
        $this->pivotData = $attributes;

        return $this;
    }

    public function withFilledCallback(?callable $filledCallback): self
    {
        $this->filledCallback = $filledCallback;

        return $this;
    }

    protected function localize()
    {
        $this->setMultiselectProps([
            'selectLabel' => __('belongs-to-many-field-nova::vue-multiselect.select_label'),
            'selectGroupLabel' => __('belongs-to-many-field-nova::vue-multiselect.select_group_label'),
            'selectedLabel' => __('belongs-to-many-field-nova::vue-multiselect.selected_label'),
            'deselectLabel' => __('belongs-to-many-field-nova::vue-multiselect.deselect_label'),
            'deselectGroupLabel' => __('belongs-to-many-field-nova::vue-multiselect.deselect_group_label'),
        ]);

        $this->setMultiselectSlots([
            'noOptions' => $this->getNoOptionsSlot(),
            'noResult' => $this->getNoResultSlot()
        ]);
    }

    protected function getNoOptionsSlot()
    {
        return __('belongs-to-many-field-nova::vue-multiselect.no_options');
    }

    protected function getNoResultSlot()
    {
        return __('belongs-to-many-field-nova::vue-multiselect.no_result');
    }

    private function resolveOptions(): void
    {
        if (isset($this->optionsCallback)) {
            if (is_callable($this->optionsCallback)) {
                $this->withMeta(['options' => call_user_func($this->optionsCallback)]);
            } else {
                $this->withMeta(['options' => collect($this->optionsCallback)]);
            }
        }
    }
}
