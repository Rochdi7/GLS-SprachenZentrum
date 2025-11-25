<?php

namespace App\Http\Requests\Backoffice\Blog;

use Illuminate\Foundation\Http\FormRequest;

class StoreBlogPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'        => 'required|string|max:255',
            'category_id'  => 'required|exists:blog_categories,id',
            'content'      => 'required|string',
            'reading_time' => 'nullable|integer|min:1|max:60',
            'featured'     => 'nullable|boolean',
            'status'       => 'required|in:draft,published',
            'image'        => 'required|image|mimes:jpg,jpeg,png,webp|max:4096',
        ];
    }
}
