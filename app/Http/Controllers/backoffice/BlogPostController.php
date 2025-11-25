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
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $posts = BlogPost::latest()->paginate(15);
        return view('backoffice.blog.posts.index', compact('posts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = BlogCategory::orderBy('name')->get();

        return view('backoffice.blog.posts.create', [
            'categories' => $categories
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBlogPostRequest $request)
    {
        // Create Post
        $post = BlogPost::create([
            'title'        => $request->title,
            'slug'         => Str::slug($request->title),
            'category_id'  => $request->category_id,
            'content'      => $request->content,
            'reading_time' => $request->reading_time ?? 3,
            'featured'     => $request->featured ? 1 : 0,
            'status'       => $request->status,
        ]);

        // Upload image via Spatie Media Library
        if ($request->hasFile('image')) {
            $post->addMediaFromRequest('image')
                 ->toMediaCollection('blog_images');
        }

        return redirect()
            ->route('backoffice.blog.posts.index')
            ->with('success', 'L’article a été ajouté avec succès.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $post = BlogPost::findOrFail($id);
        $categories = BlogCategory::orderBy('name')->get();

        return view('backoffice.blog.posts.edit', [
            'post'        => $post,
            'categories'  => $categories
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBlogPostRequest $request, string $id)
    {
        $post = BlogPost::findOrFail($id);

        $post->update([
            'title'        => $request->title,
            'slug'         => Str::slug($request->title),
            'category_id'  => $request->category_id,
            'content'      => $request->content,
            'reading_time' => $request->reading_time ?? 3,
            'featured'     => $request->featured ? 1 : 0,
            'status'       => $request->status,
        ]);

        // Replace image if uploaded
        if ($request->hasFile('image')) {
            $post->clearMediaCollection('blog_images');
            $post->addMediaFromRequest('image')
                 ->toMediaCollection('blog_images');
        }

        return redirect()
            ->route('backoffice.blog.posts.index')
            ->with('success', 'L’article a été mis à jour.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $post = BlogPost::findOrFail($id);

        $post->clearMediaCollection('blog_images');
        $post->delete();

        return redirect()
            ->back()
            ->with('success', 'L’article a été supprimé.');
    }
}
