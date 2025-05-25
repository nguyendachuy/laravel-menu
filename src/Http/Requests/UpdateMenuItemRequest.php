<?php

namespace NguyenHuy\Menu\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMenuItemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'dataItem' => 'nullable|array',
        ];

        if ($this->has('dataItem')) {
            $rules['dataItem.*.id'] = 'required|integer|exists:'.config('menu.table_prefix').config('menu.table_name_items').',id';
            $rules['dataItem.*.label'] = 'required|string|max:255';
            $rules['dataItem.*.link'] = 'required|string|max:255';
            $rules['dataItem.*.class'] = 'nullable|string|max:255';
            $rules['dataItem.*.icon'] = 'nullable|string|max:50';
            $rules['dataItem.*.target'] = 'required|string|in:_self,_blank';
            $rules['dataItem.*.depth'] = 'nullable|integer';
            $rules['dataItem.*.parent'] = 'nullable|integer';
            $rules['dataItem.*.role_id'] = 'nullable|integer';
        } else {
            $rules['id'] = 'required|integer|exists:'.config('menu.table_prefix').config('menu.table_name_items').',id';
            $rules['label'] = 'required|string|max:255';
            $rules['url'] = 'required|string|max:255';
            $rules['clases'] = 'nullable|string|max:255';
            $rules['icon'] = 'nullable|string|max:50';
            $rules['target'] = 'required|string|in:_self,_blank';
            $rules['depth'] = 'nullable|integer';
            $rules['role_id'] = 'nullable|integer';
        }

        return $rules;
    }
}
