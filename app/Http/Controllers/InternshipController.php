<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInternshipRequest;
use App\Http\Requests\UpdateInternshipRequest;
use App\Http\Resources\InternshipResource;
use App\Models\Internship;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class InternshipController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Internship::class);

        return InternshipResource::collection(Internship::query()->paginate());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreInternshipRequest $request): InternshipResource
    {
        $internship = Internship::create($request->validated());

        return new InternshipResource($internship);
    }

    /**
     * Display the specified resource.
     */
    public function show(Internship $internship): InternshipResource
    {
        Gate::authorize('view', $internship);

        return new InternshipResource($internship);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateInternshipRequest $request, Internship $internship): InternshipResource
    {
        $internship->update($request->validated());

        return new InternshipResource($internship);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Internship $internship): Response
    {
        Gate::authorize('delete', $internship);
        $internship->delete();

        return response()->noContent();
    }
}
