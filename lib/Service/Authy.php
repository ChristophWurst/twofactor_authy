<?php

/**
 * @author Christoph Wurst <christoph@winzerhof-wurst.at>
 *
 * Two-factor Authy
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */

namespace OCA\TwoFactorAuthy\Service;

use OCP\Http\Client\IClientService;
use OCP\IConfig;
use OCP\ISession;
use OCP\IUser;
use OCP\Security\ISecureRandom;

class Authy {

	/** @var IConfig */
	private $config;

	/** @var IClientService */
	private $clientService;

	/** @var ISession */
	private $session;

	/** @var ISecureRandom */
	private $random;

	public function __construct(IConfig $config, IClientService $clientService, ISession $session, ISecureRandom $random) {
		$this->clientService = $clientService;
		$this->config = $config;
		$this->session = $session;
		$this->random = $random;
	}

	private function getApiKey() {
		return $this->config->getSystemValue('authy_api_key');
	}

	private function getUserId(IUser $user) {
		return $this->config->getUserValue($user->getUID(), 'twofactor_authy', 'id');
	}

	public function startLogin(IUser $user) {
		$client = $this->clientService->newClient();

		$userId = $this->getUserId($user);
		$response = $client->post("https://api.authy.com/onetouch/json/users/$userId/approval_requests", [
			'body' => [
				'message' => 'Nextcloud login',
				'seconds_to_expire' => 60 * 60, // 1h
			],
			'headers' => [
				'X-Authy-API-Key' => $this->getApiKey(),
			]
		]);
		$data = json_decode($response->getBody(), true);
		$uuid = $data['approval_request']['uuid'];
		return $uuid;
	}

	public function hasAuthy(IUser $user) {
		return $this->getApiKey() !== '' && $this->getUserId($user) !== '';
	}

	public function getStatus($uuid) {
		$client = $this->clientService->newClient();

		$response = $client->get("http://api.authy.com/onetouch/json/approval_requests/$uuid", [
			'headers' => [
				'X-Authy-API-Key' => $this->getApiKey(),
			]
		]);
		$data = json_decode($response->getBody(), true);

		if ($data['approval_request']['status'] === 'approved') {
			$challenge = $this->setDummyChallenge();
			return [
				'authorized' => true,
				'challenge' => $challenge,
			];
		}
		return [
			'authorized' => false,
		];
	}

	private function setDummyChallenge() {
		$challenge = $this->random->generate(32);
		$this->session->set('twofactor_authy_dummy_challenge', $challenge);
		return $challenge;
	}

	public function verifyDummyChallenge($user, $challenge) {
		if (!$this->session->exists('twofactor_authy_dummy_challenge')) {
			return false;
		}
		if ($this->session->get('twofactor_authy_dummy_challenge') !== $challenge) {
			return false;
		}

		$this->session->remove('twofactor_authy_dummy_challenge');
		return true;
	}

}
