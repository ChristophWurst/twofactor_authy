<?php

/**
 * @author Christoph Wurst <christoph@winzerhof-wurst.at>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\TwoFactorAuthy\Controller;

use OCA\TwoFactorAuthy\Service\Authy;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;

class ChallengeController extends Controller {

	/** @var Authy */
	private $authy;

	public function __construct(IRequest $request, Authy $authy) {
		parent::__construct('twofactor_authy', $request);
		$this->authy = $authy;
	}

	/**
	 * @NoAdminRequired
	 * @NoTwoFactorRequired
	 * @UseSession
	 *
	 * @param string $id
	 * @return JSONResponse
	 */
	public function show($id) {
		$status = $this->authy->getStatus($id);
		return $status;
	}

}
