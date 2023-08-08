<?php

namespace App\Http\Controllers;

use App\Http\Requests\CompteRequest;
use App\Http\Resources\CompteResource;
use App\Models\Compte;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CompteController extends Controller
{

    public function bloque(Request $request)
    {
        $compte =  Compte::where('client_id', $request->id)->where('fournisseur', $request->fournisseur)->first();
        if ($compte) {
            if ($compte->estBloque == false) {
                $compte->update(["estBloque" => true]);
                return Response(["message" => "Compte Bloque avec succes"]);
            } else {
                return Response(["message" => "Compte deja bloque "]);
            }
        } else {
            return Response(["message" => "Compte inexistant"]);
        }
    }




    public function debloque(Request $request)
    {
        $compte =  Compte::where('client_id', $request->id)->where('fournisseur', $request->fournisseur)->first();
        if ($compte) {
            if ($compte->estBloque == true) {
                $compte->update(["estBloque" => false]);
                return Response(["message" => "Compte Debloque avec succes"]);
            } else {
                return Response(["message" => "Compte deja debloque "]);
            }
        } else {
            return Response(["message" => "Compte inexistant"]);
        }
    }




    public function ferme(Request $request)
    {
        $compte =  Compte::where('client_id', $request->id)->where('fournisseur', $request->fournisseur)->first();
        if ($compte) {
            if ($compte->estFerme == false) {
                $compte->update(["estFerme" => true]);
                return Response(["message" => "Compte Ferme avec succes"]);
            } else {
                return Response(["message" => "Compte deja Ferme "]);
            }
        } else {
            return Response(["message" => "Compte inexistant"]);
        }
    }



    public function addCompte(CompteRequest $request)
    {

        $isExist = Compte::where('client_id', $request->id)->where("fournisseur", $request->fournisseur)->first();
        if ($isExist) {
            return Response(["message" => "Compte existe deja!!!!!"]);
        }
        Compte::create([
            "client_id" => $request->id,
            "fournisseur" => $request->fournisseur,
            "solde" => 0
        ]);
        return Response(["message" => "Compte creé avec succes"]);
    }

    public function compte(Request $request)
    {
        $expediteur = $request->expediteur_id;
        $compte = Compte::where('client_id', $expediteur)->get();
        return  CompteResource::collection($compte)->all();
    }
}
