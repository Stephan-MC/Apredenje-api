<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTrainerRequest;
use App\Http\Requests\UpdateTrainerRequest;
use App\Models\Trainer;

class TrainerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTrainerRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Trainer $trainer)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTrainerRequest $request, Trainer $trainer)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Trainer $trainer)
    {
        //
    }
}
