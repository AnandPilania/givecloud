<?php

namespace Ds\Http\Resources;

use Ds\Domain\Theming\Liquid\Drops\SocialProofDrop;
use Ds\Enums\SocialProofType;
use Ds\Illuminate\Http\Resources\Json\JsonResource;

class SocialProofResource extends JsonResource
{
    /** @var \Ds\Domain\Theming\Liquid\Drops\SocialProofDrop */
    protected $socialProofDrop;

    /** @var string */
    protected $socialProofType;

    /**
     * @param \Ds\Models\OrderItem|\Ds\Models\Pledge $resource
     * @param string $socialProofType
     */
    public function __construct($resource, string $socialProofType = SocialProofType::RECENT)
    {
        parent::__construct($resource);

        $this->socialProofDrop = new SocialProofDrop($resource);
        $this->socialProofType = $socialProofType;
    }

    public function toArray($request): array
    {
        $data = $this->socialProofDrop->toArray();

        return array_merge($data, [
            'type' => $this->socialProofType,
        ]);
    }
}
