<?php

namespace App\Http\Controllers\Frontoffice;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\BlogCategory;

class BlogController extends Controller
{
    /**
     * BLOG INDEX PAGE
     */
    public function index()
    {
        // Featured post
        $featured = BlogPost::with('category')
            ->where('status', 'published')
            ->where('featured', true)
            ->latest()
            ->first();

        // Posts list
        $posts = BlogPost::with('category')
            ->where('status', 'published')
            ->latest()
            ->paginate(9);

        // Categories
        $categories = BlogCategory::where('is_active', 1)
            ->orderBy('name_fr')
            ->get();

        // Popular posts (most viewed)
        $popular = BlogPost::with('category')
            ->where('status', 'published')
            ->orderBy('views', 'desc')
            ->take(5)
            ->get();

        return view('frontoffice.blog.blog', compact(
            'posts',
            'featured',
            'categories',
            'popular'
        ));
    }


    /**
     * BLOG DETAILS PAGE
     */
    public function details($slug)
    {
        // Get requested article
        $post = BlogPost::where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        // Increment view counter
        $post->increment('views');

        // Recent posts sidebar
        $recentPosts = BlogPost::where('status', 'published')
            ->latest()
            ->take(5)
            ->get();

        // Categories sidebar
        $categories = BlogCategory::where('is_active', 1)
            ->orderBy('name_fr')
            ->get();

        // Related posts (same category)
        $relatedPosts = BlogPost::where('category_id', $post->category_id)
            ->where('id', '!=', $post->id)
            ->where('status', 'published')
            ->latest()
            ->take(3)
            ->get();

        return view('frontoffice.blog.blog-details', [
            'post'         => $post,
            'recentPosts'  => $recentPosts,
            'categories'   => $categories,
            'relatedPosts' => $relatedPosts,
        ]);
    }
}
