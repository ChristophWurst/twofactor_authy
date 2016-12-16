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

namespace OCA\TwoFactorAuthy\Provider;

use OCA\TwoFactorAuthy\Service\Authy;
use OCP\Authentication\TwoFactorAuth\IProvider;
use OCP\IL10N;
use OCP\IUser;
use OCP\Template;

class AuthyProvider implements IProvider {

    /** @var Authy */
    private $authy;

    /** @var IL10N */
    private $l10n;

    /**
     * @param Authy $authy
     * @param IL10N $l10n
     */
    public function __construct(Authy $authy, IL10N $l10n) {
        $this->authy = $authy;
        $this->l10n = $l10n;
    }

    /**
     * Get unique identifier of this 2FA provider
     *
     * @return string
     */
    public function getId() {
        return 'authy';
    }

    /**
     * Get the display name for selecting the 2FA provider
     *
     * @return string
     */
    public function getDisplayName() {
        return 'Authy';
    }

    /**
     * Get the description for selecting the 2FA provider
     *
     * @return string
     */
    public function getDescription() {
        return $this->l10n->t('Authenticate with Authy');
    }

    /**
     * Get the template for rending the 2FA provider view
     *
     * @param IUser $user
     * @return Template
     */
    public function getTemplate(IUser $user) {
		$uuid = $this->authy->startLogin($user);
        $tmpl = new Template('twofactor_authy', 'challenge');
		$tmpl->assign('uuid', $uuid);
		return $tmpl;
    }

    /**
     * Verify the given challenge
     *
     * @param IUser $user
     * @param string $challenge
	 * @return bool
     */
    public function verifyChallenge(IUser $user, $challenge) {
        return $this->authy->verifyDummyChallenge($user, $challenge);
    }

    /**
     * Decides whether 2FA is enabled for the given user
     *
     * @param IUser $user
     * @return boolean
     */
    public function isTwoFactorAuthEnabledForUser(IUser $user) {
        return $this->authy->hasAuthy($user);
    }

}
