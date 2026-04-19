<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserApiController;
use App\Http\Controllers\SongApiController;
use App\Http\Controllers\ArtistApiController;
use App\Http\Controllers\AlbumApiController;
use App\Http\Controllers\PlaylistApiController;
use App\Http\Controllers\SearchController;
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
//--Playlist--
Route::get('/playlists', [PlaylistApiController::class, 'index']);
Route::get('/playlist/{id}', [PlaylistApiController::class, 'single_index']);
Route::post('/playlist', [PlaylistApiController::class, 'store'])->middleware('auth:sanctum');
Route::patch('/playlist/{id}', [PlaylistApiController::class, 'update'])->middleware('auth:sanctum');
Route::delete('/playlist/{id}', [PlaylistApiController::class, 'destroy'])->middleware('auth:sanctum');
//--Playlist/Song--
Route::get('/playlist/{id}/songs', [PlaylistApiController::class, 'index_song']);
Route::post('/playlist/{id}/song', [PlaylistApiController::class, 'store_song'])->middleware('auth:sanctum');
Route::delete('/playlist/{playlist_id}/song/{id}', [PlaylistApiController::class, 'destroy_song'])->middleware('auth:sanctum');
//--User--
Route::post('/user/login', [UserApiController::class, 'login']);
Route::get('/users', [UserApiController::class, 'index'])->middleware('auth:sanctum');
//--User/Playlist--
Route::get('/user/{id}/playlists', [UserApiController::class, 'index_playlist'])->middleware('auth:sanctum');
Route::get('/user/{user_id}/playlist/{id}', [UserApiController::class, 'single_index_playlist'])->middleware('auth:sanctum');
Route::post('/user/{id}/playlist', [UserApiController::class, 'store_playlist'])->middleware('auth:sanctum');
Route::patch('/user/{user_id}/playlist/{id}', [UserApiController::class, 'update_playlist'])->middleware('auth:sanctum');
Route::delete('/user/{user_id}/playlist/{id}', [UserApiController::class, 'destroy_playlist'])->middleware('auth:sanctum');
//--User/Playlist/Song--
Route::get('/user/{user_id}/playlist/{id}/songs', [UserApiController::class, 'index_playlist_song'])->middleware('auth:sanctum');
Route::post('/user/{user_id}/playlist/{id}/song', [UserApiController::class, 'store_playlist_song'])->middleware('auth:sanctum');
Route::delete('/user/{user_id}/playlist/{playlist_id}/song/{id}', [UserApiController::class, 'destroy_playlist_song'])->middleware('auth:sanctum');
//--User/Song/Playlists--
Route::get('/user/{user_id}/song/{song_id}/playlists', [UserApiController::class, 'song_playlists'])->middleware('auth:sanctum');;
Route::post('/user/{user_id}/song/{id}/playlists', [UserApiController::class, 'store_song_playlists'])->middleware('auth:sanctum');
//--Search--
Route::get('/search', [SearchController::class, 'index']);