<?php

/*
 * This file is part of fof/filter.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\Filter\Listener;

use Flarum\Flags\Event\Created;
use Flarum\Flags\Flag;
use Flarum\Post\Event\Saving;
use Flarum\Post\Post;
use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\User\Guest;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Mail\Mailer;
use Illuminate\Mail\Message;
use Symfony\Contracts\Translation\TranslatorInterface;

class CheckPost
{
    /**
     * @var SettingsRepositoryInterface
     */
    protected $settings;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var Mailer
     */
    protected $mailer;

    /**
     * @var Dispatcher
     */
    protected $bus;

    /**
     * @var array 命中的审核内容
     */
    protected $hitKeys = [];

    public function __construct(SettingsRepositoryInterface $settings, TranslatorInterface $translator, Mailer $mailer, Dispatcher $bus)
    {
        $this->settings = $settings;
        $this->translator = $translator;
        $this->mailer = $mailer;
        $this->bus = $bus;
    }

    public function handle(Saving $event)
    {
        $post = $event->post;

        if ($event->actor->can('bypassFoFFilter', $post->discussion)) {
            return;
        }

        if ($this->checkContent($post->content, 'global') || $this->checkContent($post->discussion->title, 'global')) {
            $this->flagPost($post);

            if ((bool)$this->settings->get('fof-filter.emailWhenFlagged') && $post->emailed == 0) {
                $this->sendEmail($post);
            }
        } elseif (!$post->is_approved) {
            $this->unsetFlagPost($post);
        }

        /* using recipientUsers to detect if the discussion is private
        "$post->discussion->is_private" return false when the user just creates a private discussion, so is not used */
        if ($post->discussion->recipientUsers?->isNotEmpty()) {
            return;
        }


        if ($this->checkContent($post->content, 'excludePrivate') || $this->checkContent($post->discussion->title, 'excludePrivate')) {
            $this->flagPost($post);

            if ((bool)$this->settings->get('fof-filter.emailWhenFlagged') && $post->emailed == 0) {
                $this->sendEmail($post);
            }
        } elseif (!$post->is_approved) {
            $this->unsetFlagPost($post);
        }
    }

    public function checkContent($postContent, $censorType): bool
    {
        $censors = json_decode($this->settings->get('fof-filter.censors-' . $censorType), true);

        // 如果是非私聊，并且开启了忽略仅楼主可见
        if ($censorType == "excludePrivate" && (bool)$this->settings->get('fof-filter.only-op-see-not-check')) {
            // 替换掉部分内容后再处理
            $postContent = preg_replace('#\[OP].*?\[/OP]#is', '', $postContent);
        }

        $isExplicit = false;

        preg_replace_callback(
            $censors,
            function ($matches) use (&$isExplicit) {
                if ($matches) {
                    $isExplicit = true;
                    $this->hitKeys[] = $matches[0];
                }
            },
            str_replace(' ', '', $postContent)
        );

        return $isExplicit;
    }

    public function flagPost(Post $post): void
    {
        $post->is_approved = false;
        $post->auto_mod = true;
        $post->afterSave(function ($post) {
            if ($post->number == 1) {
                $post->discussion->is_approved = false;
                $post->discussion->save();
            }

            $detail = join(',', $this->hitKeys);

            $flag = new Flag();
            $flag->post_id = $post->id;
            $flag->type = 'autoMod';
            $flag->reason_detail = $this->translator->trans('fof-filter.forum.flag_message') . ':' . $detail;
            $flag->created_at = time();
            $flag->save();

            $this->bus->dispatch(new Created($flag, new Guest()));
        });
    }

    public function unsetFlagPost(Post $post): void
    {
        // 如果未打开就算了
        if (!(bool)$this->settings->get('fof-filter.edit-check-passed-unset-approved')) {
            return;
        }

        $post->is_approved = true;
        $post->afterSave(function ($post) {
            if ($post->number == 1) {
                $post->discussion->is_approved = true;
                $post->discussion->save();
            }

            // 删除小黑屋记录
            if ((bool)$this->settings->get('fof-filter.edit-check-passed-unset-approved')) {
                Flag::query()
                    ->where('post_id', $post->id)
                    ->where('type', 'autoMod')
                    ->delete();
            }
        });
    }

    public function sendEmail($post): void
    {
        // Admin hasn't saved an email template to the database
        if (empty($this->settings->get('fof-filter.flaggedSubject'))) {
            $this->settings->set(
                'fof-filter.flaggedSubject',
                $this->translator->trans('fof-filter.admin.email.default_subject')
            );
        }

        if (empty($this->settings->get('fof-filter.flaggedEmail'))) {
            $this->settings->set(
                'fof-filter.flaggedEmail',
                $this->translator->trans('fof-filter.admin.email.default_text')
            );
        }

        $email = $post->user->email;
        $linebreaks = ["\n", "\r\n"];
        $subject = $this->settings->get('fof-filter.flaggedSubject');
        $text = str_replace($linebreaks, $post->user->username, $this->settings->get('fof-filter.flaggedEmail'));
        $this->mailer->send(
            'fof-filter::default',
            ['text' => $text],
            function (Message $message) use ($subject, $email) {
                $message->to($email);
                $message->subject($subject);
            }
        );
        $post->emailed = true;
    }
}
