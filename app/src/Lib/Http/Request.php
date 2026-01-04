<?php

namespace App\Lib\Http;

class Request {
    private string $uri;
    private string $methode;
    private array $headers;
    private string $corps;
    private array $paramsUri = [];

    public function __construct() {
        $this->uri = $_SERVER['REQUEST_URI'];
        $this->methode = $_SERVER['REQUEST_METHOD'];
        $this->headers = getallheaders();
        $this->corps = file_get_contents('php://input');
    }

    public function getUri(): string {
        return $this->uri;
    }

    public function getMethod(): string {
        return $this->methode;
    }

    public function getHeaders(): array {
        return $this->headers;
    }

    public function getBody(): string {
        return $this->corps;
    }

    public function getQueryParams(): array {
        parse_str($_SERVER['QUERY_STRING'] ?? '', $params);
        return $params;
    }

    public function setUriParams(array $params): void {
        $this->paramsUri = $params;
    }

    public function getUriParams(): array {
        return $this->paramsUri;
    }

    public function getUriParam(string $key): ?string {
        return $this->paramsUri[$key] ?? null;
    }
}
