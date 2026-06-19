<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessWebhookJob;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class WebhookController extends Controller
{
    public function facebook(Request $request): Response
    {
        if ($request->isMethod('GET')) {
            $mode      = $request->query('hub_mode');
            $token     = $request->query('hub_verify_token');
            $challenge = $request->query('hub_challenge');

            abort_unless(
                $mode === 'subscribe' && $token === config('services.facebook.verify_token'),
                403
            );
            return response($challenge, 200);
        }

        $signature = $request->header('X-Hub-Signature-256', '');
        $expected  = 'sha256=' . hash_hmac('sha256', $request->getContent(), config('services.facebook.app_secret'));
        abort_unless(hash_equals($expected, $signature), 403);

        foreach ($request->input('entry', []) as $entry) {
            foreach ($entry['messaging'] ?? [] as $messaging) {
                if (!isset($messaging['message']['text']) && !isset($messaging['message']['attachments'])) {
                    continue;
                }
                dispatch(new ProcessWebhookJob(
                    payload:   $this->normalizeFacebook($messaging),
                    platform:  'facebook',
                    channelId: (string) $entry['id'],
                ));
            }
        }

        return response('OK', 200);
    }

    // Instagram dùng cùng cơ chế Facebook (Messenger API for Instagram)
    public function instagram(Request $request): Response
    {
        if ($request->isMethod('GET')) {
            $mode      = $request->query('hub_mode');
            $token     = $request->query('hub_verify_token');
            $challenge = $request->query('hub_challenge');

            abort_unless(
                $mode === 'subscribe' && $token === config('services.facebook.verify_token'),
                403
            );
            return response($challenge, 200);
        }

        $signature = $request->header('X-Hub-Signature-256', '');
        $expected  = 'sha256=' . hash_hmac('sha256', $request->getContent(), config('services.facebook.app_secret'));
        abort_unless(hash_equals($expected, $signature), 403);

        foreach ($request->input('entry', []) as $entry) {
            foreach ($entry['messaging'] ?? [] as $messaging) {
                if (!isset($messaging['message']['text']) && !isset($messaging['message']['attachments'])) {
                    continue;
                }
                dispatch(new ProcessWebhookJob(
                    payload:   $this->normalizeFacebook($messaging),
                    platform:  'instagram',
                    channelId: (string) $entry['id'],
                ));
            }
        }

        return response('OK', 200);
    }

    public function zalo(Request $request): Response
    {
        if ($request->isMethod('GET')) {
            return response($request->query('verifyToken', ''), 200);
        }

        $event = $request->input('event_name');
        if ($event !== 'user_send_text' && $event !== 'user_send_image') {
            return response('OK', 200);
        }

        dispatch(new ProcessWebhookJob(
            payload: [
                'sender_id'   => (string) $request->input('sender.id'),
                'sender_name' => $request->input('sender.display_name'),
                'type'        => $event === 'user_send_image' ? 'image' : 'text',
                'content'     => $request->input('message.text', '[media]'),
                'message_id'  => $request->input('message.msg_id'),
            ],
            platform:  'zalo',
            channelId: (string) $request->input('recipient.id'),
        ));

        return response('OK', 200);
    }

    public function webchat(Request $request): Response
    {
        $request->validate([
            'channel_id' => ['required', 'string'],
            'sender_id'  => ['required', 'string'],
            'content'    => ['required', 'string'],
        ]);

        dispatch(new ProcessWebhookJob(
            payload: [
                'sender_id'   => $request->sender_id,
                'sender_name' => $request->input('sender_name'),
                'type'        => 'text',
                'content'     => $request->content,
            ],
            platform:  'webchat',
            channelId: $request->channel_id,
        ));

        return response('OK', 200);
    }

    private function normalizeFacebook(array $messaging): array
    {
        return [
            'sender_id'   => $messaging['sender']['id'],
            'sender_name' => null,
            'type'        => isset($messaging['message']['attachments']) ? 'image' : 'text',
            'content'     => $messaging['message']['text'] ?? '[attachment]',
            'mid'         => $messaging['message']['mid'] ?? null,
        ];
    }
}
