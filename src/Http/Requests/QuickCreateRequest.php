<?php

    namespace Benjacho\BelongsToManyField\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;
    use JetBrains\PhpStorm\ArrayShape;

    class QuickCreateRequest extends FormRequest {
        /**
         * Determine if the user is authorized to make this request.
         *
         * @return bool
         */
        public function authorize(): bool {
            return true;
        }

        /**
         * Get the validation rules that apply to the request.
         *
         * @return array
         */
        #[ArrayShape(['model' => "string", 'values' => "string", 'cache_key' => "string"])]
        public function rules(): array {
            return [
                'model'     => 'string|required',
                'values'    => 'array|required',
                'cache_key' => 'string|nullable'
            ];
        }
    }
