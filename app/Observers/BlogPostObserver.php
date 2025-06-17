<?php

namespace App\Observers;

use App\Models\BlogPost;
use Carbon\Carbon;

class BlogPostObserver
{
    public function created(BlogPost $blogPost): void
    {
        //
    }

    public function updated(BlogPost $blogPost): void
    {
        //
    }

    public function deleted(BlogPost $blogPost): void
    {
        //
    }

    public function restored(BlogPost $blogPost): void
    {
        //
    }

    public function forceDeleted(BlogPost $blogPost): void
    {
        //
    }

    /**
     * Обробка перед оновленням запису.
     *
     * @param  BlogPost  $blogPost
     */
    public function updating(BlogPost $blogPost)
    {
        $this->setPublishedAt($blogPost);
        $this->setSlug($blogPost);
    }

    /**
     * якщо поле published_at порожнє і нам прийшло 1 в ключі is_published,
     * то генеруємо поточну дату
     */
    protected function setPublishedAt(BlogPost $blogPost)
    {
        if (empty($blogPost->published_at) && $blogPost->is_published) {
            $blogPost->published_at = Carbon::now();
        }
    }

    /**
     * якщо псевдонім порожній
     * то генеруємо псевдонім
     */
    protected function setSlug(BlogPost $blogPost)
    {
        if (empty($blogPost->slug)) {
            $blogPost->slug = \Str::slug($blogPost->title);
        }
    }
}
