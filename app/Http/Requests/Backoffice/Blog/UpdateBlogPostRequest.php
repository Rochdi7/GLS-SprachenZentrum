<?php

namespace App\Http\Requests\Backoffice\Blog;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBlogPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Category
            'category_id' => 'required|exists:blog_categories,id',

            // Titles
            'title_fr' => 'required|string|max:255',
            'title_en' => 'required|string|max:255',

            // Contents
            'content_fr' => 'required|string',
            'content_en' => 'required|string',

            // Other fields
            'reading_time' => 'nullable|integer|min:1|max:60',
            'featured'     => 'nullable|boolean',
            'status'       => 'required|in:draft,published',

            // Image optional (only for update)
            'image'        => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ];
    }
}
