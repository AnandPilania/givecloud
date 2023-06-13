<?php

namespace Ds\Http\Resources\PeerToPeer;

use Ds\Http\Resources\MediaResource;
use Ds\Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use SocialLinks\Page as SocialLinksPage;

/** @mixin \Ds\Models\FundraisingPage */
class FundraisingPageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $teamFundraisingPage = $this->is_team ? $this : $this->teamFundraisingPage;

        return [
            'id' => $this->hashid,
            'title' => $this->getTitle(),
            'fundraiser_type' => $this->is_team ? 'team' : 'personal',
            'team_name' => $teamFundraisingPage->team_name ?? null,
            'team_goal_amount' => $teamFundraisingPage->goal_amount ?? null,
            'team_currency_code' => $teamFundraisingPage->currency_code ?? null,
            'team_members' => static::collection($this->teamMembers),
            'team_join_code' => $this->when($teamFundraisingPage && $teamFundraisingPage->member_organizer_id === member('id'), $teamFundraisingPage->team_join_code ?? null),
            'team_join_shortlink_url' => $this->is_team ? URL::routeAsShortlink('peer-to-peer-campaign.join-team', [$this->hashid]) : null,
            'goal_amount' => $this->goal_amount,
            'currency_code' => $this->currency_code,
            'avatar_name' => $this->avatar_name ?? 'custom',
            'social_avatar' => $this->memberOrganizer->avatar ?? null,
            'supporter_initials' => $this->memberOrganizer->initials,
            'amount_raised' => $this->amount_raised,
            'share_links' => $this->getShareLinks(),
        ];
    }

    public function getTitle(): ?string
    {
        if ($this->is_team) {
            return Str::possessive($this->team_name) . ' ' . trans('general.team');
        }

        if ($this->memberOrganizer->first_name) {
            return Str::possessive($this->memberOrganizer->first_name) . ' ' . trans('general.challenge');
        }

        return $this->title;
    }

    public function getSocialPreviewImage(): ?MediaResource
    {
        $media = rescueQuietly(fn () => $this->product->metadata['donation_forms_social_preview_image']);

        return $media
            ? MediaResource::make($media)->setCustom(['social_preview' => ['1200x1200', 'crop' => 'entropy']])
            : null;
    }

    public function getShareLinks(): array
    {
        $page = new SocialLinksPage([
            'url' => $this->absolute_url,
            'title' => strip_tags($this->product->seo_pagetitle),
            'text' => strip_tags($this->product->seo_pagedescription),
            'image' => optional($this->getSocialPreviewImage())->toObject()->custom->social_preview ?? null,
        ]);

        return [
            'facebook' => $page->facebook->shareUrl,
            'twitter' => $page->twitter->shareUrl,
            'linkedin' => $page->linkedin->shareUrl,
            'sms' => $page->sms->shareUrl,
            'email' => $page->email->shareUrl,
        ];
    }
}
