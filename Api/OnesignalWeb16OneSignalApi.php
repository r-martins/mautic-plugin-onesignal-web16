<?php

declare(strict_types=1);

namespace MauticPlugin\OnesignalWeb16Bundle\Api;

use GuzzleHttp\Psr7\Response;
use Mautic\NotificationBundle\Api\OneSignalApi;
use Mautic\NotificationBundle\Entity\Notification;
use Psr\Http\Message\ResponseInterface;

/**
 * Replaces the stock {@see OneSignalApi} when this plugin is enabled.
 *
 * - Web (non-mobile) uses <code>include_subscription_ids</code> (OneSignal REST + Web SDK v16 subscription IDs in Mautic <code>push_ids</code>).
 * - Mobile uses <code>include_player_ids</code>.
 * - HTTP 200 with JSON <code>errors</code> or an empty <code>id</code> is coerced to status 422 so the stock
 *   {@see \Mautic\NotificationBundle\EventListener\CampaignSubscriber} records a failure.
 */
final class OnesignalWeb16OneSignalApi extends OneSignalApi
{
    public function send(string $endpoint, array $data): ResponseInterface
    {
        return $this->normalizeOnesignalCampaignResponse(parent::send($endpoint, $data));
    }

    public function sendNotification($playerId, Notification $notification): ResponseInterface
    {
        $data = [];

        $buttonId = $notification->getHeading();
        $title    = $notification->getHeading();
        $url      = $notification->getUrl();
        $button   = $notification->getButton();
        $message  = $notification->getMessage();

        if (!\is_array($playerId)) {
            $playerId = [$playerId];
        }

        if ($notification->isMobile()) {
            $data['include_player_ids'] = $playerId;
        } else {
            $data['include_subscription_ids'] = $playerId;
        }

        if (!\is_array($message)) {
            $message = ['en' => $message];
        }
        $data['contents'] = $message;
        if (!empty($title)) {
            if (!\is_array($title)) {
                $title = ['en' => $title];
            }
        }
        $data['headings'] = $title;
        if ($url) {
            $data['url'] = $url;
        }
        if ($notification->isMobile()) {
            $this->addMobileData($data, $notification->getMobileSettings());
            if ($button) {
                $data['buttons'][] = ['id' => $buttonId, 'text' => $button];
            }
        } else {
            if ($button && $url) {
                $data['web_buttons'][] = ['id' => $buttonId, 'text' => $button, 'url' => $url];
            }
        }

        return $this->send('/notifications', $data);
    }

    private function normalizeOnesignalCampaignResponse(ResponseInterface $response): ResponseInterface
    {
        $stream = $response->getBody();
        $body   = (string) $stream;
        if ($stream->isSeekable()) {
            $stream->rewind();
        }
        $decoded = json_decode($body, true);
        if (!\is_array($decoded)) {
            return $response;
        }
        if (\array_key_exists('errors', $decoded) && !empty($decoded['errors'])) {
            return $this->responseWithFailingStatus($response, $body);
        }
        if (\array_key_exists('id', $decoded) && ($decoded['id'] === '' || $decoded['id'] === null)) {
            return $this->responseWithFailingStatus($response, $body);
        }

        return $response;
    }

    private function responseWithFailingStatus(ResponseInterface $response, string $body): ResponseInterface
    {
        return new Response(422, $response->getHeaders(), $body);
    }
}
