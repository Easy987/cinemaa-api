<?php

namespace App\Http\Resources\Movie;

use Illuminate\Http\Resources\Json\ResourceCollection;

class TitleCollection extends ResourceCollection
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return $this->collection->mapWithKeys(static function ($item) {
            return [$item->lang => $item->title];
        })->toArray();
    }
}
