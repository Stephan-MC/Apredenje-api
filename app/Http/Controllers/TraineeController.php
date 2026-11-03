<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTraineeRequest;
use App\Http\Requests\UpdateTraineeRequest;
use App\Models\Trainee;

class TraineeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Trainee::query()->paginate();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTraineeRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Trainee $trainee)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTraineeRequest $request, Trainee $trainee)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Trainee $trainee)
    {
        //
    }
}
