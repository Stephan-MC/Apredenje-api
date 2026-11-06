<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStaffRequest;
use App\Http\Requests\UpdateStaffRequest;
use App\Http\Resources\StaffResource;
use App\Models\Staff;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class StaffController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Staff::class);

        return StaffResource::collection(Staff::query()->paginate());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreStaffRequest $request): StaffResource
    {
        $validated = $request->validated();
        $staff = Staff::create($validated);

        return new StaffResource($staff);
    }

    /**
     * Display the specified resource.
     */
    public function show(Staff $staff): StaffResource
    {
        Gate::authorize('view', $staff);

        return new StaffResource($staff);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateStaffRequest $request, Staff $staff): StaffResource
    {
        $validated = $request->validated();
        if (isset($validated['password']) && empty($validated['password'])) {
            unset($validated['password']);
        }
        $staff->update($validated);

        return new StaffResource($staff);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Staff $staff): Response
    {
        Gate::authorize('delete', $staff);
        $staff->delete();

        return response()->noContent();
    }
}
