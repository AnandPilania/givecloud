<?php

namespace Ds\Domain\Theming\Liquid\Drops;

use Ds\Domain\Theming\Liquid\Drop;
use Ds\Services\SocialLinkService;

class FundraisingPageDrop extends Drop
{
    protected function initialize($source)
    {
        $content = $source->description;

        if (volt_setting('p2p_allow_custom_description') !== '1') {
            $content = volt_setting('p2p_description_template_' . $source->description_template);
        }

        $this->liquid = [
            'id' => $source->id,
            'handle' => $source->url,
            'share_url' => $source->share_url,
            'social_urls' => SocialLinkService::generate($source->share_url, $source->title, ($source->photo ? $source->photo->thumbnail_url : null), $source->description),
            'status' => $source->status,
            'privacy' => $source->privacy,
            'page_type_id' => $source->product_id,
            'title' => $source->title,
            'category' => $source->category,
            'content' => $content,
            'description_template' => $source->description_template,
            'permalink' => $source->absolute_url,
            'goal_deadline' => $source->goal_deadline,
            'goal_amount' => $source->goal_amount,
            'currency' => new CurrencyDrop(currency($source->currency_code)),
            'video_url' => $source->video_url,
            'is_team' => $source->is_team,
            'team_name' => $source->team_name,
            'days_left' => $source->days_left,
            'has_ended' => $source->has_ended,
            'total_days' => $source->total_days,
            'days_elapsed' => $source->days_elapsed,
            'days_elapsed_percent' => $source->days_elapsed_percent,
            'amount_raised' => $source->amount_raised,
            'donation_count' => $source->donation_count,
            'progress_percent' => $source->progress_percent,
            'can_edit' => member('id') == $source->member_organizer_id,
            'report_reasons' => explode(',', sys_get('fundraising_pages_report_reasons')),
            'pending_message' => sys_get('fundraising_page_pending_message'),
            'denied_message' => sys_get('fundraising_page_denied_message'),
        ];
    }

    public function author()
    {
        return new AuthorDrop($this->source->memberOrganizer);
    }

    public function organizer()
    {
        return [
            'id' => $this->source->memberOrganizer->id,
            'display_name' => $this->source->memberOrganizer->display_name,
            'first_name' => $this->source->memberOrganizer->first_name,
            'last_name' => $this->source->memberOrganizer->last_name,
            'is_denied' => $this->source->memberOrganizer->is_denied,
            'is_pending' => $this->source->memberOrganizer->is_pending,
            'is_unverified' => $this->source->memberOrganizer->is_unverified,
            'is_verified' => $this->source->memberOrganizer->is_verified,
        ];
    }

    public function photo()
    {
        return $this->source->photo;
    }

    public function product()
    {
        return $this->source->product ?? null;
    }

    public function social_proof()
    {
        return $this->source->orderItems()
            ->whereHas('order', function ($q) {
                $q->paid()->whereNull('productorder.refunded_at')->whereNull('productorder.deleted_at');
            })->orderBy('id', 'desc')
            ->with('order')
            ->take(80)
            ->get()
            ->map(function ($orderItem) {
                return new SocialProofDrop($orderItem);
            })->all();
    }

    public function team_photo()
    {
        return $this->source->teamPhoto;
    }

    public function video()
    {
        if ($this->source->video) {
            return (array) $this->source->video;
        }
    }
}
