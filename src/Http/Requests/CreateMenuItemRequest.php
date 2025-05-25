<?php

namespace NguyenHuy\Menu\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateMenuItemRequest extends FormRequest
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
        return [
            'data' => 'required|array',
            'data.*.label' => 'required|string|max:255',
            'data.*.url' => 'required|string|max:255',
            'data.*.icon' => 'nullable|string|max:50',
            'data.*.role' => 'nullable|integer',
            'data.*.id' => 'required|integer|exists:'.config('menu.table_prefix').config('menu.table_name_menus').',id',
        ];
    }
}
