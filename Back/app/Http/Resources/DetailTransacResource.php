<?php

namespace App\Http\Resources;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DetailTransacResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'date' => $this->date_transaction,
            'montant' => $this->montant,
            'etat' => $this->etat,
            'type' => $this->type_transfert,
            "expediteur"=> Client::find($this->expediteur_id)->telephone
        ];
    }
}
