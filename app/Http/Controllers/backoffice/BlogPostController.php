<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\BlogCategory;
use Illuminate\Support\Str;
use App\Http\Requests\Backoffice\Blog\StoreBlogPostRequest;
use App\Http\Requests\Backoffice\Blog\UpdateBlogPostRequest;

class BlogPostController extends Controller
{
    public function index()
    {
        $posts = BlogPost::with('category')->latest()->paginate(15);
        return view('backoffice.blog.posts.index', compact('posts'));
    }

    public function create()
    {
        $categories = BlogCategory::orderBy('position')->get();

        return view('backoffice.blog.posts.create', compact('categories'));
    }

    public function store(StoreBlogPostRequest $request)
    {
        $post = BlogPost::create([
            'category_id' => $request->category_id,

            // Titles
            'title_fr'    => $request->title_fr,
            'title_en'    => $request->title_en,

            // Slug
            'slug'        => Str::slug($request->title_fr) . '-' . uniqid(),

            // Content
            'content_fr'  => $request->content_fr,
            'content_en'  => $request->content_en,

            // Meta
            'reading_time' => $request->reading_time ?? 3,
            'featured'     => $request->boolean('featured'),
            'status'       => $request->status,
        ]);

        // Upload main image
        if ($request->hasFile('image')) {
            $post->addMediaFromRequest('image')
                 ->toMediaCollection('blog_images');
        }

        return redirect()
            ->route('backoffice.blog.posts.index')
            ->with('success', 'Article créé avec succès.');
    }

    public function edit(BlogPost $post)
    {
        $categories = BlogCategory::orderBy('position')->get();

        return view('backoffice.blog.posts.edit', compact('post', 'categories'));
    }

    public function update(UpdateBlogPostRequest $request, BlogPost $post)
    {
        $post->update([
            'category_id' => $request->category_id,

            // Titles
            'title_fr'    => $request->title_fr,
            'title_en'    => $request->title_en,

            // Slug
            'slug'        => Str::slug($request->title_fr) . '-' . uniqid(),

            // Content
            'content_fr'  => $request->content_fr,
            'content_en'  => $request->content_en,

            // Meta
            'reading_time' => $request->reading_time ?? 3,
            'featured'     => $request->boolean('featured'),
            'status'       => $request->status,
        ]);

        // Replace image
        if ($request->hasFile('image')) {
            $post->clearMediaCollection('blog_images');

            $post->addMediaFromRequest('image')
                 ->toMediaCollection('blog_images');
        }

        return redirect()
            ->route('backoffice.blog.posts.index')
            ->with('success', 'Article mis à jour avec succès.');
    }

    public function destroy(BlogPost $post)
    {
        $post->clearMediaCollection('blog_images');
        $post->delete();

        return redirect()
            ->back()
            ->with('success', 'Article supprimé avec succès.');
    }
}
