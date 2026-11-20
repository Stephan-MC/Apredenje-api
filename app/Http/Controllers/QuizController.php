<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreQuizRequest;
use App\Http\Requests\UpdateQuizRequest;
use App\Http\Resources\QuizResource;
use App\Models\Quiz;
use App\Models\Training;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class QuizController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Training $training): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Quiz::class);

        return QuizResource::collection($training->quizzes()->paginate());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreQuizRequest $request, Training $training): QuizResource
    {
        $quiz = $training->quizzes()->create($request->validated());

        return new QuizResource($quiz);
    }

    /**
     * Display the specified resource.
     */
    public function show(Training $training, Quiz $quiz): QuizResource
    {
        Gate::authorize('view', $quiz);

        return new QuizResource($quiz);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateQuizRequest $request, Training $training, Quiz $quiz): QuizResource
    {
        $quiz->update($request->validated());

        return new QuizResource($quiz);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Training $training, Quiz $quiz): Response
    {
        Gate::authorize('delete', $quiz);
        $quiz->delete();

        return response()->noContent();
    }
}
