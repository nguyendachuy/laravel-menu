<?php

namespace NguyenHuy\Menu\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMenuRequest extends FormRequest
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
            'idMenu' => 'required|integer|exists:'.config('menu.table_prefix').config('menu.table_name_menus').',id',
            'menuName' => 'required|string|max:255',
            'class' => 'nullable|string|max:255',
            'data' => 'nullable|array',
            'data.*.id' => 'required|integer|exists:'.config('menu.table_prefix').config('menu.table_name_items').',id',
            'data.*.parent_id' => 'nullable|integer',
            'data.*.depth' => 'nullable|integer',
        ];
    }
}
