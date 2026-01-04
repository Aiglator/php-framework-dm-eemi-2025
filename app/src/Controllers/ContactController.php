<?php

namespace App\Controllers;

use App\Lib\Controllers\AbstractController;
use App\Lib\Http\Request;
use App\Lib\Http\Response;

class ContactController extends AbstractController {

    public function process(Request $request): Response {
        $method = $request->getMethod();

        if ($method === 'POST') {
            return $this->creerContact($request);
        }

        return new Response(
            json_encode(['error' => 'Method not allowed']),
            405,
            ['Content-Type' => 'application/json']
        );
    }

    private function creerContact(Request $request): Response {
        $headers = $request->getHeaders();

        if (!isset($headers['Content-Type']) || $headers['Content-Type'] !== 'application/json') {
            return new Response(
                json_encode(['error' => 'Content-Type must be application/json']),
                400,
                ['Content-Type' => 'application/json']
            );
        }

        $body = $request->getBody();
        $data = json_decode($body, true);

        if ($data === null) {
            return new Response(
                json_encode(['error' => 'Invalid JSON']),
                400,
                ['Content-Type' => 'application/json']
            );
        }

        // verif que les champs sont bons
        $keys = array_keys($data);
        sort($keys);
        $required = ['email', 'subject', 'message'];
        sort($required);

        if ($keys !== $required) {
            return new Response(
                json_encode(['error' => 'Only email, subject and message are allowed']),
                400,
                ['Content-Type' => 'application/json']
            );
        }

        $dir = __DIR__ . '/../../var/contacts';
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $ts = time();
        $nom_fichier = $ts . '_' . $data['email'] . '.json';
        $chemin = $dir . '/' . $nom_fichier;

        $contenu = [
            'email' => $data['email'],
            'subject' => $data['subject'],
            'message' => $data['message'],
            'dateOfCreation' => $ts,
            'dateOfLastUpdate' => $ts
        ];

        file_put_contents($chemin, json_encode($contenu, JSON_PRETTY_PRINT));

        return new Response(
            json_encode(['file' => $nom_fichier]),
            201,
            ['Content-Type' => 'application/json']
        );
    }

}
