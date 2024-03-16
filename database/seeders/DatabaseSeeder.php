<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Database\Factories\BookFactory;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        Book::factory(25)->create()->each(function ($book) {

            $numOfReviews = random_int(4, 33);
            // Save the Review for the Books
            $book->reviews()->saveMany(Review::factory()->count($numOfReviews)->make());

        });



    }
}
