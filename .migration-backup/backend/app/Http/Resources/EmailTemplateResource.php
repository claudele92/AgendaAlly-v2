<?php
declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\EmailTemplate;
use App\Support\SystemEmailTemplates;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmailTemplateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var EmailTemplate|JsonResource $this */
        return [
            'id'                => $this->id,
            'email_setting_id'  => $this->email_setting_id,
            'subject'           => $this->subject,
            'body'              => $this->body,
            'alt_body'          => $this->alt_body,
            'status'            => $this->status,
            'type'              => $this->type,
            'system_template'   => SystemEmailTemplates::isSystemRecord($this->resource),
            'template_class'    => \App\Support\FinancialEmailTemplates::isFinancial($this->type) ? 'financial_library' : (SystemEmailTemplates::isSystemRecord($this->resource) ? 'system'
                : ($this->type === EmailTemplate::TYPE_SUBSCRIBE ? 'custom_subscription' : 'application_controlled')),
            'editable'          => $this->type !== EmailTemplate::TYPE_ORDER,
            'placeholders'      => SystemEmailTemplates::placeholders($this->type),
            'previewable'       => in_array($this->type, ['verify', 'reset', 'subscribe'], true) || \App\Support\FinancialEmailTemplates::isFinancial($this->type),
            'sender_class'      => \App\Support\FinancialEmailTemplates::isFinancial($this->type) ? 'committed_manual_event_library_smtp_disabled' : null,
            'campaign_scheduled' => $this->type === EmailTemplate::TYPE_SUBSCRIBE && (int) $this->status === 0,
            'send_to'           => $this->getRawOriginal('send_to'),
            'created_at'        => $this->when($this->created_at, $this->created_at?->format('Y-m-d H:i:s') . 'Z'),
            'updated_at'        => $this->when($this->updated_at, $this->updated_at?->format('Y-m-d H:i:s') . 'Z'),

            'email_setting'     => EmailSettingResource::make($this->whenLoaded('emailSetting')),
        ];
    }
}
