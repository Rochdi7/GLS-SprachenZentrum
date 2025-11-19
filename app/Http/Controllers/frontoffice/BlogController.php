<?php

namespace App\Http\Controllers\Frontoffice;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Category;

class BlogController extends Controller
{
    /**
     * BLOG LIST PAGE
     * /blog
     */
    public function index()
    {
        // Latest posts for the main grid
        $posts = Post::latest()->paginate(9);

        // Featured post (or first post)
        $featured = Post::latest()->first();

        // Sidebar categories
        $categories = Category::orderBy('name')->get();

        // Popular or random posts for sidebar
        $popular = Post::orderBy('views', 'desc')->take(5)->get();

        return view('frontoffice.blog.blog', [
            'posts'      => $posts,
            'featured'   => $featured,
            'categories' => $categories,
            'popular'    => $popular,
        ]);
    }



    /**
     * BLOG DETAILS PAGE
     * /blog/{slug}
     */
    public function details($slug)
    {
        // Find article
        $post = Post::where('slug', $slug)->firstOrFail();

        // Increment views (optional)
        $post->increment('views');

        // Recent posts for sidebar
        $recentPosts = Post::latest()->take(5)->get();

        // Categories for sidebar
        $categories = Category::orderBy('name')->get();

        return view('frontoffice.blog.blog-details', [
            'post'        => $post,
            'recentPosts' => $recentPosts,
            'categories'  => $categories,
        ]);
    }
}
