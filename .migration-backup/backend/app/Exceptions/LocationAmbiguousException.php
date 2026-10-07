<?php
declare(strict_types=1);

namespace App\Exceptions;

use Exception;

/**
 * Thrown by BookingService::autoResolveBookingLocation() when a booking's
 * shop_location_id was omitted and neither the shop (more than one SERVICE
 * location) nor the selected master (unassigned, or assigned to more than
 * one of them) narrows it to a single valid answer.
 *
 * A distinct class - rather than a plain Exception, like the other
 * location-validation failures in BookingService::resolveBookingLocation()
 * - so BookingService::create()'s catch block can single it out and
 * surface ResponseError::LOCATION_AMBIGUOUS as the response's own `code`,
 * not just its message. Every other exception in that method collapses to
 * the generic ERROR_501, which the frontend has no reliable way to tell
 * apart from this one - and telling them apart is the whole point: this
 * is the one case where the customer needs a "choose a branch" prompt
 * instead of a generic error message.
 */
class LocationAmbiguousException extends Exception
{
}
