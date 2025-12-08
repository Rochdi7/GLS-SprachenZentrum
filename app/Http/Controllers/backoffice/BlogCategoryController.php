<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use Illuminate\Support\Str;
use App\Http\Requests\Backoffice\Blog\StoreBlogCategoryRequest;
use App\Http\Requests\Backoffice\Blog\UpdateBlogCategoryRequest;

class BlogCategoryController extends Controller
{
    public function index()
    {
        $categories = BlogCategory::orderBy('position')->paginate(10);
        return view('backoffice.blog.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('backoffice.blog.categories.create');
    }

    public function store(StoreBlogCategoryRequest $request)
    {
        BlogCategory::create([
            'name_fr'   => $request->name_fr,
            'name_en'   => $request->name_en,
            'slug'      => Str::slug($request->name_fr),
            'is_active' => $request->boolean('is_active'),
            'position'  => $request->position ?? 0,
        ]);

        return redirect()
            ->route('backoffice.blog.categories.index')
            ->with('success', 'Catégorie créée avec succès.');
    }

    public function edit(BlogCategory $category)
    {
        return view('backoffice.blog.categories.edit', compact('category'));
    }

    public function update(UpdateBlogCategoryRequest $request, BlogCategory $category)
    {
        $category->update([
            'name_fr'   => $request->name_fr,
            'name_en'   => $request->name_en,
            'slug'      => Str::slug($request->name_fr),
            'is_active' => $request->boolean('is_active'),
            'position'  => $request->position ?? 0,
        ]);

        return redirect()
            ->route('backoffice.blog.categories.index')
            ->with('success', 'Catégorie mise à jour avec succès.');
    }

    public function destroy(BlogCategory $category)
    {
        $category->delete();

        return redirect()
            ->route('backoffice.blog.categories.index')
            ->with('success', 'Catégorie supprimée avec succès.');
    }
}
