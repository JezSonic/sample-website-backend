<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Nette\NotImplementedException;

class UserController extends Controller {
    /**
     * Display a currently logged-in user.
     */
    public function index(Request $request): UserResource {
        return new UserResource($request->user());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {
        throw new NotImplementedException();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {
        throw new NotImplementedException();
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user): UserResource {
        return new UserResource($user);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user) {
        throw new NotImplementedException();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user) {
        throw new NotImplementedException();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user) {
        throw new NotImplementedException();
    }
}
