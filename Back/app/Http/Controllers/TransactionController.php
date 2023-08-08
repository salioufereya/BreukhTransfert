<?php

namespace App\Http\Controllers;

use App\Models\Compte;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Http\Requests\TransactionRequest;
use App\Http\Resources\DetailTransacResource;
use App\Http\Resources\TransactionResource;
use App\Models\Client;
use Carbon\Carbon;
use Dotenv\Validator;
use Illuminate\Contracts\Validation\Validator as ValidationValidator;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;


class TransactionController extends Controller
{


    public function randomCode($length)
    {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < $length; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass); //turn the array into a string
    }

    public function depot(Request $request)
    {

        $type = $request->type;
        $montant = $request->montant;
        $frais = $request->frais;
        $expediteur = $request->expediteur_id;
        $destinateur = $request->destinateur_id;
        $frais = $montant * 0.02;
        $code = $this->randomCode(25);
        $montants = $montant - $frais;
        if ($request->fournisseur === "WR") {
            Transaction::insert([
                'type_transfert' => $type,
                'montant' => $montants,
                'date_transaction' => now(),
                'expediteur_id' => $expediteur,
                'destinataire_id' => $destinateur,
                'immediat' => false,
                'code' => $code,
                'compte_id' => null
            ]);

            return Response(["message" => " Vous avec deposer avec succes la somme de ($montants) ! voici le code :$code"]);
        } else {
            DB::beginTransaction();

            try {
                $compte_id = Compte::where('client_id', $request->destinateur_id)
                    ->where('fournisseur', $request->fournisseur)->first();

                if (!$compte_id) {
                    return Response(["message" => "Ce client ne dispose pas de compte pour ce fournisseur!"]);
                }

                if ($compte_id->estFerme == true) {
                    return Response(["message" => "Ce Compte ne peut plus recevoir de transaction car est fermé!"]);
                }
                $newSolde =  $compte_id->solde += $request->montant;
                $compte_id->update(['solde' => $newSolde]);
                Transaction::insert([
                    'type_transfert' => $request->type,
                    'montant' => $request->montant,
                    'date_transaction' => now(),
                    'expediteur_id' => $request->expediteur_id,
                    'compte_id' => $compte_id->id,
                    'immediat' => false,
                    'destinataire_id' => null
                ]);
                DB::commit();
                return Response(["message" => "Depot reussi avec succés!!!!!"]);
            } catch (\Exception $e) {
                DB::rollback();
                return Response(["message" => "error $e"]);
            }
        }
    }
    public function transfert(Request $request)
    {


        $type = $request->type;
        $montant = $request->montant;
        $frais = $request->frais;
        $expediteur = $request->expediteur_id;
        $destinateur = $request->destinateur_id;
        $fournisseur = $request->fournisseur;

        if ($fournisseur == "OM") {
            $frais = $montant * 0.01;
        } elseif ($fournisseur == "CB") {
            $frais = $montant * 0.05;
        } elseif ($fournisseur == "WV") {
            $frais = $montant * 0.01;
        } elseif ($fournisseur == "WR") {
            $frais = $montant * 0.02;
        }

        if ($montant < 500) {
            return Response(["message" => "montant ne doit pas etre infereiru a 500"]);
        }
        if ($fournisseur === "Wari" && $montant < 1000) {
            return Response(["message" => "montant ne doit pas etre infereiru a 1000"]);
        }
        if ($fournisseur === "CB" && $montant < 10000) {
            return Response(["message" => "montant ne doit pas etre infereiru a 10000"]);
        }
        if ($fournisseur != "CB" && $montant >= 10000000) {
            return Response(["message" => "montant ne doit pas etre superieurur a 1000000"]);
        }



        if ($fournisseur != "WR") {
            $exp =  Compte::where('client_id', $expediteur)->where('fournisseur', $fournisseur)->first();
            $des =  Compte::where('client_id', $destinateur)->where('fournisseur', $fournisseur)->first();
            if (!($exp)) {
                return Response(["message" => "compte expediteur inexistant!!!!"]);
            }
            if (!($des)) {
                return Response(["message" => "compte destinateur inexistant!!!!"]);
            }

            if ($montant + $frais > $exp->solde) {
                return Response(["message" => "Vous ne disposez pas ce montant "]);
            }
            if ($exp->estFerme == true || $des->estFerme == true) {
                return Response(["message" => "Compte ferme ne peut plus avoir de transactions!!!!"]);
            }
            if ($exp->estBloque == true) {
                return Response(["message" => "Compte Bloque ne peut plus faire de transfert sortants!!!!"]);
            }
            $newm = $exp->solde -= $montant + $frais;
            $newp = $des->solde += $montant;

            DB::beginTransaction();

            try {
                $exp->update(['solde' => $newm]);
                $des->update(['solde' => $newp]);
                Transaction::insert([
                    'type_transfert' => $type,
                    'montant' => $montant,
                    'date_transaction' => now(),
                    'expediteur_id' => $expediteur,
                    'compte_id' => $destinateur,
                    'immediat' => false,
                    'destinataire_id' => null
                ]);
                DB::commit();
                return Response(["message" => "transfert reussi avec succés!!!!!"]);
            } catch (\Exception $e) {
                DB::rollback();
                return Response(["message" => "error .$e"]);
            }
        } else {
            $code = $this->randomCode(25);
            DB::beginTransaction();

            try {
                Transaction::insert([
                    'type_transfert' => $type,
                    'montant' => $montant - $frais,
                    'date_transaction' => now(),
                    'expediteur_id' => $expediteur,
                    'destinataire_id' => $destinateur,
                    'immediat' => false,
                    'code' => $code,
                    'compte_id' => null
                ]);
                DB::commit();
                return Response(["message" => "transfert wari  avec succés le code .$code!!!!!"]);
            } catch (\Exception $e) {
                DB::rollback();
                return Response(["message" => "error"]);
            }
        }
    }



    public function getMontant(Request $request)
    {

        $transaction = Transaction::where("code", $request->code)->get();

        return   DetailTransacResource::collection($transaction)->all() ;
    }


    public function retrait(Request $request)
    {
        $type = $request->type;
        $montant = $request->montant;
        $expediteur = $request->expediteur_id;
        $destinateur = $request->destinateur_id;
        $fournisseur = $request->fournisseur;

        if ($fournisseur != "WR") {
            $CompteRetrait =  Compte::where('client_id', $expediteur)->where('fournisseur', $fournisseur)->first();

            if (!$CompteRetrait) {
                return Response(["message" => "Compte Inexistant"]);
            }
            if ($montant  > $CompteRetrait->solde) {
                return Response(["message" => "Vous ne pouvez pas retirer cette somme"]);
            }
            $new =  $CompteRetrait->solde -= $montant;
            DB::beginTransaction();

            try {
                $CompteRetrait->update(['solde' => $new]);
                Transaction::insert([
                    'type_transfert' => $type,
                    'montant' => $montant,
                    'date_transaction' => now(),
                    'expediteur_id' => $expediteur,
                    'destinataire_id' => null,
                    'immediat' => false,
                    'code' => null,
                    'compte_id' => $expediteur
                ]);
                DB::commit();
                return Response(["message" => "Retrait avec succés!!!!!"]);
            } catch (\Exception $e) {
                DB::rollback();
                return Response(["message" => "Error!!!!! $e"]);
            }
        }
    }

    public function retraitAvecCode(Request $request)
    {
        $type = $request->type;
        $montant = $request->montant;
        $expediteur = $request->expediteur_id;
        $destinateur = $request->destinateur_id;
        $fournisseur = $request->fournisseur;
        $code = $request->code;
        $transaction = Transaction::where('destinataire_id', $expediteur)->where('code', $code)->first();

        if (!$transaction) {
            return Response(["message" => "Transaction inexistant"]);
        }
        if ($transaction->etat == "retire") {
            return Response(["message" => "Deja retiré"]);
        }
        if ($transaction->code == "invalid") {
            return Response(["message" => "Le code n'est plus valide"]);
        }
        if ($transaction->etat == "annulee") {
            return Response(["message" => "La transaction n'est plus valide"]);
        }

        DB::beginTransaction();

        try {
            $transaction->update(['etat' => "retire"]);
            Transaction::insert([
                'type_transfert' => $type,
                'montant' => $transaction->montant,
                'date_transaction' => now(),
                'expediteur_id' => $expediteur,
                'destinataire_id' => null,
                'immediat' => false,
                'code' => null,
                'compte_id' => null
            ]);
            DB::commit();
            return Response(["message" => "Retrait de $transaction->montant reussi avec succés!!!!!"]);
        } catch (\Exception $e) {
            DB::rollback();
            return Response(["message" => "Error!!!!! $e"]);
        }
        return $transaction;
    }

    public function historique(Request $request)
    {
        $expediteur = $request->expediteur_id;
        $transa = Transaction::where('expediteur_id', $expediteur)->get();
        return  TransactionResource::collection($transa)->all();
    }

    public function annulerTransaction(Request $request)
    {

        $transac = Transaction::where('id', $request->id)->where('expediteur_id', $request->expediteur)->first();
        $dateActuel = Carbon::now();
        $dateActuel = Carbon::now();
        $dateTransaction = Carbon::parse($transac->date_transaction);
        $dureeTransaction = $dateActuel->diffInDays($dateTransaction);

        if ($dureeTransaction > 1) {
            return ["message" => "La transaction a dépassé un jour et ne peut plus être annulee"];
        }

        if ($transac->etat == "annulee") {

            return ["message" => "La transaction a été  deja annulée "];
        }
        if ($transac->type_transfert == "Depot" || $transac->type_transfert == "Transfert") {

            $transa = Transaction::where('type_transfert', $transac->type_transfert)->where('expediteur_id', $request->expediteur)->latest('date_transaction')->first();
            if ($transa->id != $request->id) {
                return ["message" => "La transaction ne peut plus etre annulée "];
            }
        }

        if ($transac->type_transfert == "Depot") {
            $transaction = Transaction::find($request->id);
            $transaction->etat = "annulee";
            $transaction->save();
            $expediteur = Compte::find($transaction->expediteur_id);
            $expediteur->solde += $transaction->montant;
            $expediteur->save();

            return ["message" => "La transaction a été annulée avec succès"];
        }
        if ($transac->type_transfert == "Transfert" || $transac->compte_id == null) {
            $transaction = Transaction::find($request->id);
            $transaction->etat = "annulee";
            $transaction->save();
            return ["message" => "La transaction a été annulée avec succès"];
        }

        if ($transac->type_transfert == "Transfert") {
            $transaction = Transaction::find($request->id);
            $transaction->etat = "annulee";
            $transaction->save();
            $expediteur = Compte::find($transaction->expediteur_id);
            $expediteur->solde += $transaction->montant;
            $expediteur->save();
            return ["message" => "La transaction a été annulée avec succès"];
        }


        // $compteExiste = Compte::where('client_id', $request->client_id)
        //     ->where('fournisseur', $request->fournisseur)
        //     ->first();

        // $dernierCompteTransaction = Transaction::where('compte_id', $compteExiste->id)
        //     ->latest('date_transaction')
        //     ->first();

        // $dernierTransactionCode = Transaction::where('expediteur_id', $request->client_id)
        //     ->latest('date_transaction')
        //     ->first();

        // if ($dernierCompteTransaction && $dernierTransactionCode) {
        //     $derniereTransaction = $dernierCompteTransaction->date_transaction > $dernierTransactionCode->date_transaction
        //         ? $dernierCompteTransaction
        //         : $dernierTransactionCode;
        // } elseif ($dernierCompteTransaction) {
        //     $derniereTransaction = $dernierCompteTransaction;
        // } elseif ($dernierTransactionCode) {
        //     $derniereTransaction = $dernierTransactionCode;
        // } else {
        //     return ["message" => "Aucune transaction trouvée pour cet utilisateur et fournisseur"];
        // }

        // $dateActuel = Carbon::now();
        // $dateTransaction = Carbon::parse($derniereTransaction->date_transaction);
        // $dureeTransaction = $dateActuel->diffInDays($dateTransaction);

        // if ($dureeTransaction > 1) {
        //     return ["message" => "La transaction a dépassé un jour et ne peut plus être annulée"];
        // }

        // if ($derniereTransaction->type_transfert == "Depot") {
        //     $compteAssocie = Compte::where('id', $derniereTransaction->compte_id)
        //         ->first();
        //     $compteAssocie->solde -= $derniereTransaction->montant;
        //     $compteAssocie->save();

        //     return [
        //         "message" => "Transaction Annuler votre nouveau solde est de " . $compteAssocie->solde,
        //         "transaction" => $compteAssocie
        //     ];
        // } elseif ($derniereTransaction->type_transfert == "Transfert") {
        //     $compteAssocie = Client::where('id', $derniereTransaction->expediteur_id)
        //         ->first();

        //     return [
        //         "message" => "Transaction Via Annuler votre nouveau solde est de ",
        //         "transaction" => $compteAssocie
        //     ];
        // }
    }
}
