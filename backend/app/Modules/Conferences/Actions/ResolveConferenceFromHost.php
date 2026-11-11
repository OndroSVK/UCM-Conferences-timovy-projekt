<?php

namespace App\Modules\Conferences\Actions;

use App\Modules\Conferences\Models\Conference;
use App\Shared\Actions\Action;
use App\Shared\Conference\ConferenceContext;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Public part: the first label of the host selects the conference
 * (ans2027.ucm.sk, locally ans2027.localhost) and sets ConferenceContext.
 * Unknown subdomain → 404.
 */
final class ResolveConferenceFromHost extends Action
{
    public function __construct(private readonly ConferenceContext $context)
    {
    }

    public function handle(string $host): Conference
    {
        $label = strtolower(explode('.', $host)[0] ?? '');

        $conference = $label === '' ? null : Conference::query()->where('subdomain', $label)->first();

        if ($conference === null) {
            throw new NotFoundHttpException(__('errors.conference_not_found'));
        }

        $this->context->setConference($conference->id);

        return $conference;
    }
}
