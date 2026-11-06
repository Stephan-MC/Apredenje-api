<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAdminRequest;
use App\Http\Requests\UpdateAdminRequest;
use App\Http\Resources\AdminResource;
use App\Models\Admin;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Admin::class);

        return AdminResource::collection(Admin::query()->paginate());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAdminRequest $request): AdminResource
    {
        $validated = $request->validated();
        $admin = Admin::create($validated);

        return new AdminResource($admin);
    }

    /**
     * Display the specified resource.
     */
    public function show(Admin $admin): AdminResource
    {
        Gate::authorize('view', $admin);

        return new AdminResource($admin);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAdminRequest $request, Admin $admin): AdminResource
    {
        $validated = $request->validated();
        if (isset($validated['password']) && empty($validated['password'])) {
            unset($validated['password']);
        }
        $admin->update($validated);

        return new AdminResource($admin);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Admin $admin): Response
    {
        Gate::authorize('delete', $admin);
        $admin->delete();

        return response()->noContent();
    }
}
