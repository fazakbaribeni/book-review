<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;

class Book extends Model
{
    use HasFactory;

    protected $table = "books";
    protected $fillable = [

        "title",
        "published_date",
        "author"
    ];

    /***
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /***
     * @param Builder $query
     * @param string $title
     * @return Builder
     */
    public function scopeTitle(Builder $query, string $title): Builder
    {
        return $query->where('title', 'LIKE', '%' . $title . '%');
    }

    /***
     * @param Builder $query
     * @param $from
     * @param $to
     * @return Builder|QueryBuilder
     */
    public function scopeWithReviewsCount(Builder $query, $from = null, $to = null): Builder|QueryBuilder
    {
        return $query->withCount([
            'reviews' => fn(Builder $q) => $this->dateRangeFilter($q, $from, $to)
        ]);
    }

    /***
     * @param Builder $query
     * @param $from
     * @param $to
     * @return Builder|QueryBuilder
     */
    public function scopeWithAvgRating(Builder $query, $from = null, $to = null): Builder|QueryBuilder
    {
        return $query->withAvg([
            'reviews' => fn(Builder $q) => $this->dateRangeFilter($q, $from, $to)
        ], 'rating');
    }

    /***
     * @param Builder $query
     * @param $from
     * @param $to
     * @return Builder|QueryBuilder
     */
    public function scopePopular(Builder $query, $from = null, $to = null): Builder|QueryBuilder
    {
        return $query->withReviewsCount()
            ->orderBy('reviews_count', 'desc');
    }

    /***
     * @param Builder $query
     * @param $from
     * @param $to
     * @return Builder|QueryBuilder
     */
    public function scopeHighestRated(Builder $query, $from = null, $to = null): Builder|QueryBuilder
    {
        return $query->withAvgRating()
            ->orderBy('reviews_avg_rating', 'desc');
    }

    /**
     * @param Builder $query
     * @param int $minReviews
     * @return Builder|QueryBuilder
     */
    public function scopeMinReviews(Builder $query, int $minReviews): Builder|QueryBuilder
    {
        return $query->having('reviews_count', '>=', $minReviews);
    }

    /***
     * @param Builder $query
     * @param $from
     * @param $to
     * @return void
     */
    private function dateRangeFilter(Builder $query, $from = null, $to = null)
    {
        if ($from && !$to) {
            $query->where('created_at', '>=', $from);
        } elseif (!$from && $to) {
            $query->where('created_at', '<=', $to);
        } elseif ($from && $to) {
            $query->whereBetween('created_at', [$from, $to]);
        }
    }

    /***
     * @param Builder $query
     * @return Builder|QueryBuilder
     */
    public function scopePopularLastMonth(Builder $query): Builder|QueryBuilder
    {
        return $query->popular(now()->subMonth(), now())
            ->highestRated(now()->subMonth(), now())
            ->minReviews(2);
    }

    /**
     * @param Builder $query
     * @return Builder|QueryBuilder
     */

    public function scopePopularLast6Months(Builder $query): Builder|QueryBuilder
    {
        return $query->popular(now()->subMonths(6), now())
            ->highestRated(now()->subMonths(6), now())
            ->minReviews(5);
    }

    /**
     * @param Builder $query
     * @return Builder|QueryBuilder
     */
    public function scopeHighestRatedLastMonth(Builder $query): Builder|QueryBuilder
    {
        return $query->highestRated(now()->subMonth(), now())
            ->popular(now()->subMonth(), now())
            ->minReviews(2);
    }

    /**
     * @param Builder $query
     * @return Builder|QueryBuilder
     */
    public function scopeHighestRatedLast6Months(Builder $query): Builder|QueryBuilder
    {
        return $query->highestRated(now()->subMonths(6), now())
            ->popular(now()->subMonths(6), now())
            ->minReviews(5);
    }

    /**
     * @return void
     */
    protected static function booted()
    {
        static::updated(
            fn(Book $book) => cache()->forget('book:' . $book->id)
        );
        static::deleted(
            fn(Book $book) => cache()->forget('book:' . $book->id)
        );
    }
}
