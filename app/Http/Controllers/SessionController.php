<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSessionRequest;
use App\Http\Requests\UpdateSessionRequest;
use App\Http\Resources\SessionResource;
use App\Models\Session;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class SessionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Session::class);

        return SessionResource::collection(Session::query()->paginate());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSessionRequest $request): SessionResource
    {
        $session = Session::create($request->validated());

        return new SessionResource($session);
    }

    /**
     * Display the specified resource.
     */
    public function show(Session $session): SessionResource
    {
        Gate::authorize('view', $session);

        return new SessionResource($session);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSessionRequest $request, Session $session): SessionResource
    {
        $session->update($request->validated());

        return new SessionResource($session);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Session $session): Response
    {
        Gate::authorize('delete', $session);
        $session->delete();

        return response()->noContent();
    }
}
