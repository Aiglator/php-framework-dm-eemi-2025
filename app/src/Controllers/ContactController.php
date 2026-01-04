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
        } elseif ($method === 'GET') {
            return $this->getAllContacts($request);
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

    private function getAllContacts(Request $request): Response {
        $queryParams = $request->getQueryParams();

        if (isset($queryParams['filename'])) {
            return $this->getOneContact($queryParams['filename']);
        }

        $dir = __DIR__ . '/../../var/contacts';

        if (!is_dir($dir)) {
            return new Response(
                json_encode([]),
                200,
                ['Content-Type' => 'application/json']
            );
        }

        $fichiers = scandir($dir);
        $contacts = [];

        foreach ($fichiers as $f) {
            if ($f === '.' || $f === '..') {
                continue;
            }
            if (!str_ends_with($f, '.json')) {
                continue;
            }

            $path = $dir . '/' . $f;
            $content = file_get_contents($path);
            $contact = json_decode($content, true);

            if ($contact !== null) {
                $contacts[] = $contact;
            }
        }

        return new Response(
            json_encode($contacts),
            200,
            ['Content-Type' => 'application/json']
        );
    }

    private function getOneContact(string $filename): Response {
        $dir = __DIR__ . '/../../var/contacts';
        $filepath = $dir . '/' . $filename;

        if (!file_exists($filepath)) {
            return new Response(
                json_encode(['error' => 'Contact not found']),
                404,
                ['Content-Type' => 'application/json']
            );
        }

        $content = file_get_contents($filepath);
        $contact = json_decode($content, true);

        if ($contact === null) {
            return new Response(
                json_encode(['error' => 'Invalid contact file']),
                500,
                ['Content-Type' => 'application/json']
            );
        }

        return new Response(
            json_encode($contact),
            200,
            ['Content-Type' => 'application/json']
        );
    }

}
