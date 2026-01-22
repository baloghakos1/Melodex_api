<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserApiController;
use App\Http\Controllers\SongApiController;
use App\Http\Controllers\ArtistApiController;
use App\Http\Controllers\MemberApiController;
use App\Http\Controllers\AlbumApiController;
//--Artist--
Route::get('/artists', [ArtistApiController::class, 'index']);
Route::get('/artist/{id}', [ArtistApiController::class, 'single_index']);
Route::post('/artist', [ArtistApiController::class, 'store'])->middleware('auth:sanctum');
Route::patch('/artist/{id}', [ArtistApiController::class, 'update'])->middleware('auth:sanctum');
Route::delete('/artist/{id}', [ArtistApiController::class, 'destroy'])->middleware('auth:sanctum');
//--Artist/Album--
Route::get('/artist/{id}/albums', [ArtistApiController::class, 'index_album']);
Route::get('/artist/{artist_id}/album/{id}', [ArtistApiController::class, 'single_index_album']);
Route::post('/artist/{id}/album', [ArtistApiController::class, 'store_album'])->middleware('auth:sanctum');
Route::patch('/artist/{artist_id}/album/{id}', [ArtistApiController::class, 'update_album'])->middleware('auth:sanctum');
Route::delete('/artist/{artist_id}/album/{id}', [ArtistApiController::class, 'destroy_album'])->middleware('auth:sanctum');
//--Artist/Album/Song--
Route::get('/artist/{artist_id}/album/{id}/songs', [ArtistApiController::class, 'index_song']);
Route::post('/artist/{artist_id}/album/{id}/song', [ArtistApiController::class, 'store_song'])->middleware('auth:sanctum');
Route::patch('/artist/{artist_id}/album/{album_id}/song/{id}', [ArtistApiController::class, 'update_song'])->middleware('auth:sanctum');
Route::delete('/artist/{artist_id}/album/{album_id}/song/{id}', [ArtistApiController::class, 'destroy_song'])->middleware('auth:sanctum');
//--Album--
Route::get('/albums', [AlbumApiController::class, 'index']);
Route::get('/album/{id}', [AlbumApiController::class, 'single_index']);
Route::post('/album', [AlbumApiController::class, 'store'])->middleware('auth:sanctum');
Route::patch('/album/{id}', [AlbumApiController::class, 'update'])->middleware('auth:sanctum');
Route::delete('/album/{id}', [AlbumApiController::class, 'destroy'])->middleware('auth:sanctum');
//--Album/Song--
Route::get('/album/{id}/songs', [AlbumApiController::class, 'index_song']);
Route::post('/album/{id}/song', [AlbumApiController::class, 'store_song'])->middleware('auth:sanctum');
Route::patch('/album/{album_id}/song/{id}', [AlbumApiController::class, 'update_song'])->middleware('auth:sanctum');
Route::delete('/album/{album_id}/song/{id}', [AlbumApiController::class, 'destroy_song'])->middleware('auth:sanctum');
//--Song--
Route::get('/songs', [SongApiController::class, 'index']);
Route::get('/song/{id}', [SongApiController::class, 'single_index'])->middleware('auth:sanctum');
Route::post('/song', [SongApiController::class, 'store'])->middleware('auth:sanctum');
Route::patch('/song/{id}', [SongApiController::class, 'update'])->middleware('auth:sanctum');
Route::delete('/song/{id}', [SongApiController::class, 'destroy'])->middleware('auth:sanctum');
//--User--
Route::post('/user/login', [UserApiController::class, 'login']);
Route::get('/users', [UserApiController::class, 'index'])->middleware('auth:sanctum');