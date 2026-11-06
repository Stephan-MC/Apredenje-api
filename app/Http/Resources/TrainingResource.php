<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrainingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'code' => $this->code,
            'trainer_id' => $this->trainer_id,
            'fee' => $this->fee,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
