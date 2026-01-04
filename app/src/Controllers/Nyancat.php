<?php
// Bonjour c'est Rayan a la base je voulais mettre badapple mais c'etait trop lourd donc voila nyancat c'est un heastereggs puisque j'avais dis
// que j'allais le mettre 

namespace App\Controllers;

use App\Lib\Controllers\AbstractController;
use App\Lib\Http\Request;
use App\Lib\Http\Response;

class Nyancat extends AbstractController {

    public function process(Request $request): Response {
        // clean le buffer sinon ca marche pas
        if (ob_get_level()) ob_end_clean();

        header('Content-Type: text/plain; charset=utf-8');
        header('X-Accel-Buffering: no');
        header('Cache-Control: no-cache');
        // explication vue que j'utilise le terminal windows je dois faire croire a ascii.live que je suis sur un vrai terminal
        $url = 'https://ascii.live/nyan';

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_USERAGENT, 'curl/7.68.0');
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Accept: */*']);

        // envoyer direct au fur et a mesure
        curl_setopt($curl, CURLOPT_WRITEFUNCTION, function($c, $datanyancat) {
            echo $datanyancat;
            flush();
            return strlen($datanyancat);
        });

        curl_exec($curl);
        // apparemment curl close et déprecié sur php 8.4 donc j'ai remplacé par unset qui fait pareil 
        unset($curl);

        exit;
    }

}
