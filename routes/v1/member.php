<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\version1\Controller\Member\MemberController;

 Route::post('storeMember', [MemberController::class,'storeMember'])->name('storeMember');
 Route::post('setPasswordForMember', [MemberController::class,'setPasswordForMember'])->name('setPasswordForMember');
 Route::post('passwordUpdateForMember', [MemberController::class,'passwordUpdateForMember'])->name('passwordUpdateForMember');
 Route::post('memberLogin', [MemberController::class,'memberLogin'])->name('memberLogin');

