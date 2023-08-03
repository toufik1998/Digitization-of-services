<?php

namespace App\Http\Controllers;
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
// use Illuminate\Support\Facades\Mail;
// use App\Mail\OrderCreated;

use App\Mail\WelcomeEmail;
use Illuminate\Support\Facades\Mail;



use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class FormController extends Controller
{
        public function __construct()
        {
            $this->middleware('auth');
        }
        
       public function index()
       {
           return view('personelInfo');
       }
       
        public function storeInformation(Request $request)
        {
            if($request->has('Matricule')){
                $validator = $request->validate([
                    'Matricule' => 'required|string',
                    'Nom' => 'required|string|min:2',
                    'Prenom' => 'required|string|min:2',
                    'date_de_naissance' => 'required',
                    'email' => 'required|email',
                    'télephone' => 'required|string',
                    'adresse' => 'required|string|min:10',
                    'Numéro_du_pièce' => 'required',
                    'Fonction_Antérieur' => 'required|string|min:4',
                    'Fonction' => 'required|string|min:4',
                    'Service_Etablissement' => 'required|string|min:4',
                    'Arret' => 'required|string|min:4',
                    'Date_nomination' => 'required',
                    'Date_effet' => 'required',
                    'Date_fin' => 'required',
                    'Nom_Prénom' => 'required',
                    'Conjoint_Fonction' => 'required',
                    'Conjoint_Matricule' => 'required',
                    'Service_employeur' => 'required',
                    'Date_embauche' => 'required',
                    'Conjoint_adresse' => 'required',
                    'Conjoint_régime' => 'required',
                    'Taux_indemnité' => 'required',
                    'ville_précédant' => 'required',
                    'quartier_précédant' => 'required',
                    'lot_n°_précédant' => 'required',
                    'Date_libération' => 'required',
                    'ville_actuelle' => 'required',
                    'quartier_actuelle' => 'required',
                    'lot_n°_actuelle' => 'required',
                    'Date_occupation' => 'required'
                ]);
                $personelinfo=personelinfo::create([
                    'matricule' => strtoupper($request->Matricule),
                    'nom' => strtoupper($request->Nom ),
                    'prenom' => strtoupper($request->Prenom ),
                    'sexe' => strtoupper($request->person_sexe ),
                    'date_naissance' => strtoupper($request->date_de_naissance ),
                    'lieu_naissance' => strtoupper($request->place_birth ),
                    'email' => strtoupper($request->email ),
                    'telephone' => strtoupper($request->télephone ),
                    'adresse' => strtoupper($request->adresse ),
                    'type_piece' => strtoupper($request->document_type ),
                    'numero_piece' => strtoupper($request->Numéro_du_pièce ),
                    'region' => strtoupper($request->person_region ),
                    'localite' => strtoupper($request->person_locality ),
                    'corps_anterieur' => strtoupper($request->anterior_body ),
                    'corps' => strtoupper($request->person_body),
                    'minstere_anterieur' => strtoupper($request->previous_ministry ),
                    'minstere' => strtoupper($request->person_ministry ),
                    'fonction' => strtoupper($request->Fonction ),
                    'fonction_anterieur' => strtoupper($request->Fonction_Antérieur ),
                    'service' => strtoupper($request->Service_Etablissement ),
                    'arret' => strtoupper($request->Arret ),
                    'date_nomination' => strtoupper($request->Date_nomination ),
                    'date_effet' => strtoupper($request->Date_effet ),
                    'date_fin' => strtoupper($request->Date_fin ),
                    'situation_matrimoniale' => strtoupper($request->marital_status ),
                ]);
                Conjoint::create([
                    'personelinfos_id' => $personelinfo->id,
                    'nom_prenom' => strtoupper($request->Nom_Prénom),
                    'fonction' => strtoupper($request->Conjoint_Fonction),
                    'matricule_Conjoint' => strtoupper($request->Conjoint_Matricule),
                    'service_empolyeur' => strtoupper($request->Service_employeur),
                    'date_embauche' => strtoupper($request->Date_embauche),
                    'adress_conjoint' => strtoupper($request->Conjoint_adresse),
                    'regime' => strtoupper($request->Conjoint_régime),
                    'taux_indemnite' => strtoupper($request->Taux_indemnité)
                ]);
                Previous::create([
                    'personelinfos_id' => $personelinfo->id,
                    'ville_precedant' => strtoupper($request->ville_précédant ),
                    'quartier_precedant' => strtoupper($request->quartier_précédant ),
                    'lot_precedant' => strtoupper($request->lot_n°_précédant ),
                    'date_liberation' => strtoupper($request->Date_libération)
                ]);
                Current::create([
                    'personelinfos_id' => $personelinfo->id,
                    'ville_actuelle' => strtoupper($request->ville_actuelle),
                    'quartier_actuelle' => strtoupper($request->quartier_actuelle),
                    'lot_actuelle' => strtoupper($request->lot_n°_actuelle),
                    'date_occupation' => strtoupper($request->Date_occupation),
                    'nom_parent' => strtoupper($request->parent_name)
                ]);

                return response()->json([
                    'message' => 'les information a été bien créer',
                    'personel_id' => $personelinfo->id,
             ]);

            }
            if($request->has('Décision_de_nomination')){
                $request->validate([
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
                
                $files = [
                    'Décision_de_nomination',
                    'Décision_affectation',
                    'Certificat_de_1ère_prise_de_service',
                    'Bulletin_de_solde_avant_nomination',
                    'Bulletin_de_solde_après_nommination',
                    'Certificat_de_non_hébergement',
                    'Attestation_sur_honneur_légalisée',
                    'certificat_de_résidence',
                    'Pièce_identité',
                    'Acte_de_mariage',
                ];
                
                $uploadedFiles = [];
                
                foreach ($files as $file) {
                    $uploadedFile = $request->file($file);
                    $uploadedFileName = $uploadedFile->hashName();
                    $uploadedFile->move('uploads', $uploadedFileName);
                    $uploadedFiles[$file] = 'uploads/' . $uploadedFileName;
                }
                
                File::create([
                    'personelinfos_id' => $request->personel_id,
                    'decisionnomination_path' => $uploadedFiles['Décision_de_nomination'],
                    'decisionaffectation_path' => $uploadedFiles['Décision_affectation'],
                    'certificatpriseservice_path' => $uploadedFiles['Certificat_de_1ère_prise_de_service'],
                    'Bulletinsoldeavant_path' => $uploadedFiles['Bulletin_de_solde_avant_nomination'],
                    'Bulletinsoldeapres_path' => $uploadedFiles['Bulletin_de_solde_après_nommination'],
                    'certifcatnomhebergement_path' => $uploadedFiles['Certificat_de_non_hébergement'],
                    'attestationhonneurlegalise_path' => $uploadedFiles['Attestation_sur_honneur_légalisée'],
                    'certificatresidence_path' => $uploadedFiles['certificat_de_résidence'],
                    'pieceidentite_path' => $uploadedFiles['Pièce_identité'],
                    'actemariage_path' => $uploadedFiles['Acte_de_mariage'],
                ]);
                
                  return response()->json(['message' => 'Les fichiers ont été bien joints.']);

            }
            if($request->has('credential_paiment')){

                 $request->validate([
                     'nom_paiment' => 'required',
                     'prenom_paiment' => 'required',
                     'credential_paiment' => 'required',
                     'nature_recette' => 'required',
                     'numéro_avis_de_recette' => 'required',
                 ]);
                
                $url="https://wbservice.tresor.gouv.ci/wbpartenaires/tstrest/GenererAvisrecette";

                $data = [
                    'action' => 'GenererAvisrecette',
                    'credential_id' => $request->credential_paiment,
                    'client_nom' => $request->nom_paiment,
                    'client_prenom' => $request->prenom_paiment,
                    'identifiant' => $request->numéro_avis_de_recette,
                    'nature_recette' => $request->nature_recette,
                    'montant_total' => $request->montant_total,
                    'telephone' => $request->telephone_paiment,
                ];

                $client = new Client();
                $response = $client->post($url, [
                    'form_params' => $data,
                ]);

                $responseData = $response->getBody()->getContents();
                $responseMesssage = json_decode($responseData, true)['response_message'];
                $responseCode = json_decode($responseData, true)['response_code'];
                if ($responseCode == 1) {
                    $application = new Application();
                    $application->id = $request->personel_id;
                    $application->status ='en attente';
                    $application->user_id =auth()->user()->id;
                    $application->editable1 = 'yes';
                    $application->save();
                    Paiment::create([
                        'personelinfos_id'  => $request->personel_id,
                        'client_nom'        => $request->nom_paiment,
                        'client_prenom'     => $request->prenom_paiment,
                        'credential_id'     => $request->credential_paiment,
                        'telephone'         => $request->telephone_paiment,
                        'identifiant'       => $request->numéro_avis_de_recette,
                        'nature_recette'    => $request->nature_recette,
                        'montant_total'     => $request->montant_total,
                     ]);
                    PaimentStatus::create([
                        'personelinfos_id' => $request->personel_id,
                        'statut' => 'en cours',
                        'credential_id' => $request->credential_paiment,
                        'identifiant' => $request->nature_recette,
                        'payment_id' => '',
                    ]);

                    $user = auth()->user();
                    $admin       = User::role('Administrateur')->first();
                    $controleur1 = User::role('controleur 1')->first();
                    $controleur2 = User::role('controleur 2')->first();
                    $controleur3 = User::role('controleur 3')->first();

                    Notification::send($admin      , new documentAdded($user->id));
                    Notification::send($controleur1, new documentAdded($user->id));
                    Notification::send($controleur2, new documentAdded($user->id));
                    Notification::send($controleur3, new documentAdded($user->id));

                    Mail::to($user->email)->send(new WelcomeEmail($user,$request->personel_id));

                    return response()->json([
                    'message' => 'paiment a été créer avec succés',
                    'number'  => 1
                    ]);

                } else {
                    return response()->json([
                        'message' => $responseMesssage,
                        'number' => $responseCode
                    ]);
                }
            }
            if($request->has('print_info')){

                $personelinfo = personelinfo::where('id',$request->personel_id)->first();
                $previous =Previous::where('personelinfos_id',$request->personel_id)->first();
                $current =Current::where('personelinfos_id',$request->personel_id)->first();
                $conjoint =Conjoint::where('personelinfos_id',$request->personel_id)->first();
                // dd($data);
                $dompdf = new Dompdf();

                // Render the view as HTML
                $html = view('inscription', compact('personelinfo','previous','current','conjoint'))->render();

                // Load the HTML into dompdf
                $dompdf->loadHtml($html);

                // Set the paper size and orientation
                $dompdf->setPaper('A4', 'portrait');

                // Render the PDF
                $dompdf->render();

                $pdrf = PDF::loadView('inscription',compact('personelinfo','previous','current','conjoint'));
                return $pdrf->download('ins.pdf');
                $output = $dompdf->output();
                return response($output, 200)
                        ->header('Content-Type', 'application/pdf')
                        ->header('Content-Disposition', 'attachment; filename="inscription.pdf"');
                // return redirect()->back()->with('succès','votre demnade a été bien télecharger');
            }
            if($request->has('print_payment')){

                $paiment =Paiment::where('personelinfos_id',$request->personel_id)->first();
                // dd($data);
                $dompdf = new Dompdf();

                // Render the view as HTML
                $html = view('paiment', compact('paiment'))->render();

                // Load the HTML into dompdf
                $dompdf->loadHtml($html);

                // Set the paper size and orientation
                $dompdf->setPaper('A4', 'portrait');

                // Render the PDF
                $dompdf->render();
                $output = $dompdf->output();

                return response($output, 200)
                        ->header('Content-Type', 'application/pdf')
                        ->header('Content-Disposition', 'attachment; filename="paiment.pdf"');
                return redirect()->back()->with('succès','votre commande a été bien télecharger');
            }

        }

}
