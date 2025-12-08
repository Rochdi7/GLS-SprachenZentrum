<?php

namespace App\Http\Requests\Backoffice\Blog;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBlogCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->category->id;

        return [
            'name_fr' => "required|string|min:2|max:150|unique:blog_categories,name_fr,$id",
            'name_en' => "required|string|min:2|max:150|unique:blog_categories,name_en,$id",

            'is_active' => 'nullable|boolean',
            'position'  => 'nullable|integer|min:0|max:999',
        ];
    }
}
