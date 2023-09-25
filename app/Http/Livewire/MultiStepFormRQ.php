<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Models\Conjoint;
use App\Models\Current;
use App\Models\File;
use App\Models\Personelinfo;
use App\Models\Previous;
use App\Models\Paiment;
use App\Models\PaimentStatus;
use App\Models\Application;
use Dompdf\Dompdf;
use App\Notifications\documentAdded;
use Illuminate\Support\Facades\Notification;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;
use PDF;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Hash;
use App\Mail\WelcomeEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;


class MultiStepFormRQ extends Component
{
    use WithFileUploads;

    public $curentStep=1;
    public $totalSteps=3;

    //user info

    public $Secteur_activite;
    public $person_ministry;
    public $Service_Etablissement;
    public $raison_sociale;
    public $Sigle;
    public $Contact;
    public $Adresse;
    public $Mail;
    public $Ville;
    public $Quartier;
    public $type_batiment;
    public $Standing;
    public $ILot;
    public $Lot;
    public $Usage;
    public $date_occupation; 
    public $Fonctionaire;
    public $Matricule;

    // files
    public $Décision_de_nomination;
    public $Décision_affectation;
    public $Certificat_de_1ère_prise_de_service;
    public $Bulletin_de_solde_avant_nomination;
    public $Bulletin_de_solde_après_nommination;
    public $Certificat_de_non_hébergement;
    public $Attestation_sur_honneur_légalisée;
    public $certificat_de_résidence;
    public $Pièce_identité;
    public $Acte_de_mariage;

    //payment
    public $nom_paiment; 
    public $prenom_paiment; 
    public $telephone_paiment; 
    public $credential_paiment;
    public $nature_recette;
    public $numéro_avis_de_recette;
    public $montant_total;
    public $statut;

    public $displayerrors = false;
    public $errormessage = '';
     
    public $isLoading = false;

    public $quartiers = null;
    public $radio ="physique";
    


    public function render()
    {
        return view('livewire.multi-step-form-r-q');
    }
    public function decreseStep()
    {
        $this->resetErrorBag();
        $this->curentStep--;
        if($this->curentStep<1){
            $this->curentStep =1;
        }
    }
    public function increseStep()
    {
        $this->resetErrorBag();
        $this->validateData();
        $this->curentStep++;
        if($this->curentStep > $this->totalSteps){
            $this->curentStep = $this->totalSteps;
        }
    }
    public function validateData()
    {
        if($this->curentStep == 1){
            if($this->radio =="physique"){
                $this->validate([
                    'Matricule' => 'required|string',
                    'Fonctionaire' => 'required|string|min:2',
                    'Lot' => 'required|string|min:4',
                    'Service_Etablissement' => 'required|string|min:4',
                    'ILot' => 'required|string',
                    'date_occupation' => 'required',
                    'Quartier' => 'required',
                    'Ville'  => 'required',
                    'Mail' => 'required',
                    'Adresse' => 'required',
                    'Contact'  => 'required',
                    'Secteur_activite' => 'required'
                ]);
            }else{
                $this->validate([
                    'Lot' => 'required|string|min:4',
                    'Service_Etablissement' => 'required|string|min:4',
                    'ILot' => 'required|string',
                    'date_occupation' => 'required',
                    'Quartier' => 'required',
                    'Ville'  => 'required',
                    'Mail' => 'required',
                    'Adresse' => 'required',
                    'Contact'  => 'required',
                    'Sigle' => 'required',
                    'raison_sociale' => 'required',
                    'Secteur_activite' => 'required'
                ]);
            }
            
        }else if($this->curentStep == 2){
            $this->validate([
                'Décision_de_nomination' => 'required|mimes:pdf,doc,docx,jpg,jpeg,png|max:2048',
                'Décision_affectation' => 'required|mimes:pdf,doc,docx,jpg,jpeg,png|max:2048',
                'Certificat_de_1ère_prise_de_service' => 'required|mimes:pdf,doc,docx,jpg,jpeg,png|max:2048',
                'Bulletin_de_solde_avant_nomination' => 'required|mimes:pdf,doc,docx,jpg,jpeg,png|max:2048',
                'Bulletin_de_solde_après_nommination' => 'required|mimes:pdf,doc,docx,jpg,jpeg,png|max:2048',
                'Certificat_de_non_hébergement' => 'required|mimes:pdf,doc,docx,jpg,jpeg,png|max:2048',
                'Attestation_sur_honneur_légalisée' => 'required|mimes:pdf,doc,docx,jpg,jpeg,png|max:2048',
                'certificat_de_résidence' => 'required|mimes:pdf,doc,docx,jpg,jpeg,png|max:2048',
                'Pièce_identité' => 'required|mimes:pdf,doc,docx,jpg,jpeg,png|max:2048',
                'Acte_de_mariage' => 'required|mimes:pdf,doc,docx,jpg,jpeg,png|max:2048',
            ]);
        }else if($this->curentStep == 3){
            $this->validate([
                'nom_paiment' => 'required',
                'prenom_paiment' => 'required',
                'credential_paiment' => 'required',
                'nature_recette' => 'required',
                'numéro_avis_de_recette' => 'required',
            ]);
        }
    }

    public function storeInfo()
    {
        $this->isLoading = true;
        $this->resetErrorBag();
        $this->validateData();
        
        
                $url="https://wbservice.tresor.gouv.ci/wbpartenaires/tstrest/GenererAvisrecette";

                $data = [
                    'action' => 'GenererAvisrecette',
                    'credential_id' => $this->credential_paiment,
                    'client_nom' => $this->nom_paiment,
                    'client_prenom' => $this->prenom_paiment,
                    'identifiant' => $this->numéro_avis_de_recette,
                    'nature_recette' => $this->nature_recette,
                    'montant_total' => $this->montant_total,
                    'telephone' => $this->telephone_paiment,
                ];

                $client = new Client();
                $response = $client->post($url, [
                    'form_params' => $data,
                ]);

                $responseData = $response->getBody()->getContents();
                $responseMesssage = json_decode($responseData, true)['response_message'];
                $responseCode = json_decode($responseData, true)['response_code'];
                if ($responseCode == 1) {    

                    if($this->radio =="physique"){
                    $personelinfo=Personelinfo::create([
                                'Matricule' => strtoupper($this->Matricule),
                                'Fonctionaire' => strtoupper($this->Fonctionaire),
                                'Lot' => strtoupper($this->Lot),
                                'service' => strtoupper($this->Service_Etablissement),
                                'ILot' => strtoupper($this->ILot),
                                'date_occupation' => strtoupper($this->date_occupation),
                                'Quartier' => strtoupper($this->Quartier),
                                'Ville'  => strtoupper($this->Ville),
                                'email' => strtoupper($this->Mail),
                                'Adresse' => strtoupper($this->Adresse),
                                'telephone'  => strtoupper($this->Contact),
                                'secteur' => strtoupper($this->Secteur_activite),
                                'minstere' => strtoupper($this->person_ministry),
                                'Standing' => strtoupper($this->Standing),
                                'type_batiment' => strtoupper($this->type_batiment),
                                'Usage' => strtoupper($this->Usage),
                                'person' => strtoupper($this->radio),
                    ]);
                }else{
                    $personelinfo=Personelinfo::create([
                            'Lot' => strtoupper($this->Lot),
                            'service' => strtoupper($this->Service_Etablissement),
                            'ILot' => strtoupper($this->ILot),
                            'date_occupation' => strtoupper($this->date_occupation),
                            'Quartier' => strtoupper($this->Quartier),
                            'Ville'  => strtoupper($this->Ville),
                            'Mail' => strtoupper($this->Mail),
                            'Adresse' => strtoupper($this->Adresse),
                            'telephone'  => strtoupper($this->Contact),
                            'Sigle' => strtoupper($this->Sigle),
                            'raisonsociale' => strtoupper($this->raison_sociale),
                            'secteur' => strtoupper($this->Secteur_activite),
                            'minstere' => strtoupper($this->person_ministry),
                            'Standing' => strtoupper($this->Standing),
                            'type_batiment' => strtoupper($this->type_batiment),
                            'Usage' => strtoupper($this->Usage),
                            'person' => strtoupper($this->radio),

                ]);         
                }
                               
                        $nomination = Str::random(10).$this->Décision_de_nomination->getClientOriginalName();
                        $this->Décision_de_nomination->storeAs('files',$nomination);
            
                        $affectation = Str::random(10).$this->Décision_affectation->getClientOriginalName();
                        $this->Décision_affectation->storeAs('files',$affectation);
            
                        $prise_de_service = Str::random(10).$this->Certificat_de_1ère_prise_de_service->getClientOriginalName();
                        $this->Certificat_de_1ère_prise_de_service->storeAs('files',$prise_de_service);
            
                        $solde_avant_nomination = Str::random(10).$this->Bulletin_de_solde_avant_nomination->getClientOriginalName();
                        $this->Bulletin_de_solde_avant_nomination->storeAs('files',$solde_avant_nomination);
                        
                        $Bulletin_de_solde_après = Str::random(10).$this->Bulletin_de_solde_après_nommination->getClientOriginalName();
                        $this->Bulletin_de_solde_après_nommination->storeAs('files',$Bulletin_de_solde_après);
                       
                        $non_hébergement = Str::random(10).$this->Certificat_de_non_hébergement->getClientOriginalName();
                        $this->Certificat_de_non_hébergement->storeAs('files',$non_hébergement);
                       
                        $Attestation_honneur = Str::random(10).$this->Attestation_sur_honneur_légalisée->getClientOriginalName();
                        $this->Attestation_sur_honneur_légalisée->storeAs('files',$Attestation_honneur);
                       
                        $certificat_résidence = Str::random(10).$this->certificat_de_résidence->getClientOriginalName();
                        $this->certificat_de_résidence->storeAs('files',$certificat_résidence);
            
                        $piecei_dentite = Str::random(10).$this->Pièce_identité->getClientOriginalName();
                        $this->Pièce_identité->storeAs('files',$piecei_dentite);
            
                        $acte_mariage = Str::random(10).$this->Acte_de_mariage->getClientOriginalName();
                        $this->Acte_de_mariage->storeAs('files',$acte_mariage);
            
                        File::create([
                            'personelinfos_id' => $personelinfo->id,
                            'decisionnomination_path' => $nomination,
                            'decisionaffectation_path' => $affectation,
                            'certificatpriseservice_path' => $prise_de_service,
                            'Bulletinsoldeavant_path' => $solde_avant_nomination,
                            'Bulletinsoldeapres_path' => $Bulletin_de_solde_après,
                            'certifcatnomhebergement_path' => $non_hébergement,
                            'attestationhonneurlegalise_path' => $Attestation_honneur,
                            'certificatresidence_path' => $certificat_résidence,
                            'pieceidentite_path' => $piecei_dentite,
                            'actemariage_path' => $acte_mariage,
                        ]);
                        Paiment::create([
                            'personelinfos_id'  => $personelinfo->id,
                            'client_nom'        => $this->nom_paiment,
                            'client_prenom'     => $this->prenom_paiment,
                            'credential_id'     => $this->credential_paiment,
                            'telephone'         => $this->telephone_paiment,
                            'identifiant'       => $this->numéro_avis_de_recette,
                            'nature_recette'    => $this->nature_recette,
                            'montant_total'     => $this->montant_total,
                         ]);
                        PaimentStatus::create([
                            'personelinfos_id' => $personelinfo->id,
                            'statut' => 'en cours',
                            'credential_id' => $this->credential_paiment,
                            'identifiant' => $this->numéro_avis_de_recette,
                            'payment_id' => '',
                        ]);
                        $application = new Application();
                        $application->id = $personelinfo->id;
                        $application->type = "R";
                        $application->status ='en attente';
                        $application->user_id =auth()->user()->id;
                        $application->editable1 = 'yes';
                        $application->save();
                        $this->reset();

                    $user = auth()->user();
                    $admin       = User::role('Administrateur')->first();
                    $controleur1 = User::role('controleur 1')->first();
                    $controleur2 = User::role('controleur 2')->first();
                    $controleur3 = User::role('controleur 3')->first();

                    Notification::send($admin      , new documentAdded($user->id));
                    Notification::send($controleur1, new documentAdded($user->id));
                    Notification::send($controleur2, new documentAdded($user->id));
                    Notification::send($controleur3, new documentAdded($user->id));

                    Mail::to($user->email)->send(new WelcomeEmail($user,$personelinfo->id));
                    $this->isLoading = false;
                    return redirect()->route('demande')->with('success','votre demande a été créer avec succés');
                }
                else {
                     $this->displayerrors = true;
                     $this->errormessage = $responseMesssage;
                     $this->isLoading = false;
                }
    }
    public function mount()
    {
        $this->curentStep =1;
    }

}
