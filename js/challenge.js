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

(function($, OC) {
	'use strict';

	$(function() {
		var uuid = $('#uuid').val();
		var pollUrl = OC.generateUrl('/apps/twofactor_authy/challenge/{uuid}', {
			uuid: uuid
		});

		var polling = setInterval(function() {
			$.ajax(pollUrl, {
				'method': 'GET'
			}).then(function(data) {
				if (data.authorized && data.authorized === true) {
					clearInterval(polling);
					$('#challenge').val(data.challenge);
					$('#authy-form').submit();
				}
			}).fail(function() {
				console.error('poll failed');
			});
		}, 3 * 1000);
	});

})($, OC);