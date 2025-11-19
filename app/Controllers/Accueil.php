<?php

namespace App\Controllers;

class Accueil extends BaseController
{
    public function __construct()
    {
        // On charge le helper URL et HTML
        helper(['url', 'html']);
    }

    /** Méthode par défaut */
    public function index()
    {

        // Vérifie si l’utilisateur est connecté
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/');
        }

        $data['titre'] = "Bienvenue sur l'intranet GSB";
        
        $articles = $this->getFluxRss();
        $data['articles'] = $articles;

        return view('structures/page_entete')
            . view('structures/messages')
            . view('sommaire')
            . view('structures/contenu_entete', $data)
            . view('actualites', $data)
            . view('structures/page_pied');
    }


    public function getFluxRss()
    {
        // URL du flux RSS
        $url = 'https://www.santemagazine.fr/rss';

        // Chargement du flux XML
        $rss = @simplexml_load_file($url);

        // Code Mme Piton pour débloquer le codage SSL en local
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
        $fluxXml = curl_exec($ch);
        curl_close($ch);
        $rss = @simplexml_load_string($fluxXml); 


        // Vérifie si le chargement du flux a réussi
        if ($rss === false) {
            return array("error" => "Impossible de charger le flux RSS.");
        }

        if ($rss) {
            foreach ($rss->channel->item as $item) {
                $articles[] = [
                    'titre'       => $item->title,
                    'lien'        => $item->link,
                    'description' => $item->description,
                    'date'        => $item->pubDate,
                    'image'       => $item->enclosure['url'] // ['url] attribut de la balise xml
                ];
            }
        }
        return $articles;
    }
    
}
