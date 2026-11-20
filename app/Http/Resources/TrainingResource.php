<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrainingResource extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var \App\Models\Training
     */
    public $resource;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'title' => $this->resource->title,
            'code' => $this->resource->code,
            'trainer_id' => $this->resource->trainer_id,
            'fee' => $this->resource->fee,
            'trainer' => new TrainerResource($this->whenLoaded('trainer')),
            'quizzes' => QuizResource::collection($this->whenLoaded('quizzes')),
            'created_at' => $this->resource->created_at,
            'updated_at' => $this->resource->updated_at,
        ];
    }
}
