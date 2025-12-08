<?php

namespace App\Http\Requests\Backoffice\Blog;

use Illuminate\Foundation\Http\FormRequest;

class StoreBlogCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name_fr' => 'required|string|min:2|max:150|unique:blog_categories,name_fr',
            'name_en' => 'required|string|min:2|max:150|unique:blog_categories,name_en',

            'is_active' => 'nullable|boolean',
            'position'  => 'nullable|integer|min:0|max:999',
        ];
    }
}
