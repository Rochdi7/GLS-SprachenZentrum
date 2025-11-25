<?php

namespace App\Http\Requests\Backoffice\Blog;

use Illuminate\Foundation\Http\FormRequest;

class StoreBlogCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // autoriser tous les admins
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|min:2|max:100|unique:blog_categories,name',
        ];
    }
}
