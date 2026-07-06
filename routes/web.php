<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/mis-tickets', [TicketController::class, 'myTickets'])->name('tickets.mine');
    Route::get('/tickets/{ticket}', [TicketController::class, 'show'])->name('tickets.show');
    Route::post('/tickets', [TicketController::class, 'store'])->name('tickets.store');
    Route::post('/tickets/{ticket}/comments', [TicketController::class, 'addComment'])->name('tickets.comments.store');
});

Route::middleware(['auth', 'verified', 'can:viewTicketBoard'])->group(function () {
    Route::get('/dashboard', [TicketController::class, 'index'])->name('dashboard');
    Route::get('/admin/tickets', [TicketController::class, 'index'])->name('tickets.board');
    Route::post('/admin/tickets/{ticket}/assign', [TicketController::class, 'assign'])->name('tickets.assign');
    Route::post('/admin/tickets/{ticket}/status', [TicketController::class, 'updateStatus'])->name('tickets.updateStatus');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';