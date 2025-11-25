<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use Illuminate\Http\Request;
use App\Http\Requests\Backoffice\Blog\StoreBlogCategoryRequest;
use App\Http\Requests\Backoffice\Blog\UpdateBlogCategoryRequest;
use Illuminate\Support\Str;

class BlogCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = BlogCategory::latest()->paginate(10);

        return view('backoffice.blog.categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('backoffice.blog.categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBlogCategoryRequest $request)
    {
        BlogCategory::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
        ]);

        return redirect()
            ->route('backoffice.blog.categories.index')
            ->with('success', 'Catégorie créée avec succès.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BlogCategory $category)
    {
        return view('backoffice.blog.categories.edit', compact('category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBlogCategoryRequest $request, BlogCategory $category)
    {
        $category->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
        ]);

        return redirect()
            ->route('backoffice.blog.categories.index')
            ->with('success', 'Catégorie mise à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BlogCategory $category)
    {
        $category->delete();

        return redirect()
            ->route('backoffice.blog.categories.index')
            ->with('success', 'Catégorie supprimée avec succès.');
    }
}
