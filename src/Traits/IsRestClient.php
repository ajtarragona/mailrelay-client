<?php

namespace Ajtarragona\MailRelay\Traits;

use Ajtarragona\MailRelay\Exceptions\MailRelayAuthException;
use Ajtarragona\MailRelay\Exceptions\MailRelayConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

trait IsRestClient
{
	protected $options;
	protected $api_url;
	protected $api_key;
	protected $debug;

	public function __construct($options = [])
	{
		$opts = config('mailrelay');
		if ($options) $opts = array_merge($opts, $options);

		$this->options = json_decode(json_encode($opts), FALSE);
		$this->debug = $this->options->debug ?? false;
		$this->api_url = rtrim($this->options->api_url, "/") . "/";
		$this->api_key = $this->options->api_key;
	}

	/**
	 * Prepara la petición base con headers
	 */
	protected function client()
	{
		return Http::withHeaders([
			'X-AUTH-TOKEN' => $this->api_key,
			'Accept'       => 'application/json',
		])->baseUrl($this->api_url);
	}

	public function restGet($url, $params = [])
	{
		return $this->call('GET', $url, $params);
	}

	public function restPost($url, $body = [])
	{
		return $this->call('POST', $url, $body);
	}

	public function restPut($url, $body = [])
	{
		return $this->call('PUT', $url, $body);
	}

	public function restDelete($url, $body = [])
	{
		return $this->call('DELETE', $url, $body);
	}

	protected function call($method, $url, $args = [])
	{
		$url = ltrim($url, "/");
		if (!$url) return false;
		$method = strtolower($method);
		try {
			if ($this->debug) {
				Log::debug("MailRelay: Calling $method to: " . $this->api_url . $url, $args);
			}

			// Ejecución dinámica del método (get, post, put...)
			$response = $this->client()->$method($url, $args);

			if ($this->debug) {
				Log::debug("STATUS: " . $response->status());
			}

			if ($response->successful()) {
				return $response->object(); // Devuelve objeto stdClass (equivalente a json_decode)
			}

			return $this->handleError($response);
		} catch (\Exception $e) {
			throw new MailRelayConnectionException("Error conectando con Mailrelay: " . $e->getMessage());
		}
	}

	protected function handleError($response)
	{
		$status = $response->status();

		if ($this->debug) {
			Log::error("MailRelay API error: " . $status, $response->json() ?? []);
		}

		return match ($status) {
			404 => null,
			401 => throw new MailRelayAuthException("API Key inválida o no enviada."),
			422 => $response->object(), // Errores de validación
			default => false,
		};
	}
}
