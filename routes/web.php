<?php

use App\Http\Controllers\CommentGoodController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\RecipeCommentController;
use App\Http\Controllers\RecipeController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// recipes/create が recipes/{recipe} に飲まれないよう、認証ルートを先に登録する
Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('recipes', RecipeController::class)->except(['index', 'show']);

    Route::get('favorites', [FavoriteController::class, 'index'])->name('favorites.index');
    Route::post('recipes/{recipe}/favorite', [FavoriteController::class, 'store'])->name('favorites.store');
    Route::delete('recipes/{recipe}/favorite', [FavoriteController::class, 'destroy'])->name('favorites.destroy');

    Route::post('users/{user}/follow', [FollowController::class, 'store'])->name('follows.store');
    Route::delete('users/{user}/follow', [FollowController::class, 'destroy'])->name('follows.destroy');

    Route::post('recipes/{recipe}/comments', [RecipeCommentController::class, 'store'])->name('comments.store');
    Route::delete('comments/{comment}', [RecipeCommentController::class, 'destroy'])->name('comments.destroy');

    Route::post('comments/{comment}/good', [CommentGoodController::class, 'store'])->name('comment-goods.store');
    Route::delete('comments/{comment}/good', [CommentGoodController::class, 'destroy'])->name('comment-goods.destroy');
});

Route::resource('recipes', RecipeController::class)->only(['index', 'show']);

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
