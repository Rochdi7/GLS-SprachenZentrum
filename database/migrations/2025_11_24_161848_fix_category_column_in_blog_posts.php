<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('blog_posts', function (Blueprint $table) {
        if (Schema::hasColumn('blog_posts', 'blog_category_id')) {
            $table->renameColumn('blog_category_id', 'category_id');
        }
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            //
        });
    }
};
