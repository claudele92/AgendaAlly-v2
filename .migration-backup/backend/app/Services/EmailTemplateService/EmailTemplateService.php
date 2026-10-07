<?php
declare(strict_types=1);

namespace App\Services\EmailTemplateService;

use App\Helpers\ResponseError;
use App\Models\EmailTemplate;
use App\Services\CoreService;
use App\Support\EmailTemplateContent;
use App\Support\SystemEmailTemplates;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class EmailTemplateService extends CoreService
{
    /**
     * @return string
     */
    protected function getModelClass(): string
    {
        return EmailTemplate::class;
    }

    public function create(array $data): array
    {
        EmailTemplateContent::validate($data);
        if ($data['type'] === EmailTemplate::TYPE_ORDER) {
            throw ValidationException::withMessages(['type' => ['Order/Invoice is application controlled, not an executable managed template.']]);
        }
        if (SystemEmailTemplates::isSystem($data['type']) || \App\Support\FinancialEmailTemplates::isFinancial($data['type'])) {
            throw ValidationException::withMessages(['type' => ['Account templates are provisioned automatically. Edit the existing record.']]);
        }
        $data = array_intersect_key($data, array_flip(['type', 'subject', 'body', 'alt_body', 'email_setting_id', 'send_to']));
        try {
            /** @var EmailTemplate $emailTemplate */
            $data['status'] = EmailTemplate::STATUS_LIBRARY_ONLY;
            $data['send_to'] ??= '2099-01-01 00:00:00';
            $verify         = EmailTemplate::TYPE_VERIFY;

            if (
                in_array($data['type'], [$verify, EmailTemplate::TYPE_RESET], true) && (
                    !stristr($data['body'], '$verify_code') ||
                    !stristr($data['alt_body'], '$verify_code')
                )
            ) {
                $message = 'when status: ' . $verify . ' you should add text $verify_code on body and alt body';

                return [
                    'status'    => false,
                    'message'   => $message,
                    'code'      => ResponseError::ERROR_501
                ];
            }

            $emailTemplate = DB::transaction(function () use ($data) {
                $provider = DB::table('email_settings')->where('active', true);
                if (isset($data['email_setting_id'])) $provider->where('id', $data['email_setting_id']);
                $provider = $provider->orderByDesc('updated_at')->lockForUpdate()->first(['id']);
                if (!$provider) {
                    throw ValidationException::withMessages(['email_setting_id' => ['An active email provider is required to store presentation. No email is sent.']]);
                }
                $data['email_setting_id'] = $provider->id;
                if (SystemEmailTemplates::isSystem($data['type'])
                    && $this->model()->where('type', $data['type'])->exists()) {
                    throw ValidationException::withMessages(['type' => ['This system template already exists. Edit it instead.']]);
                }
                return $this->model()->create($data);
            });

            // Template management is presentation CRUD, not a send action.
            // The existing explicitly scheduled subscription workflow remains separate.

            return [
                'status'    => true,
                'code'      => ResponseError::NO_ERROR,
            ];
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {

            $this->error($e);

            return [
                'status'    => false,
                'code'      => ResponseError::ERROR_501,
            ];
        }
    }

    public function update(EmailTemplate $emailTemplate, array $data): array
    {
        EmailTemplateContent::validate($data);
        if ($emailTemplate->type === EmailTemplate::TYPE_ORDER || $data['type'] === EmailTemplate::TYPE_ORDER) {
            throw ValidationException::withMessages(['type' => ['Order/Invoice presentation belongs to its application-controlled invoice view.']]);
        }
        if ((SystemEmailTemplates::isSystemRecord($emailTemplate) || SystemEmailTemplates::isSystem($data['type']) || \App\Support\FinancialEmailTemplates::isFinancial($data['type']))
            && $emailTemplate->type !== $data['type']) {
            throw ValidationException::withMessages(['type' => ['System template identity cannot be changed.']]);
        }
        if (SystemEmailTemplates::isSystemRecord($emailTemplate)) {
            if ((int) ($data['email_setting_id'] ?? 0) !== (int) $emailTemplate->email_setting_id
                || strtotime((string) ($data['send_to'] ?? '')) !== strtotime((string) $emailTemplate->send_to)) {
                throw ValidationException::withMessages(['type' => ['System template provider and scheduling are application controlled.']]);
            }
            $data = array_intersect_key($data, array_flip(['subject', 'body', 'alt_body']));
            $data['type'] = $emailTemplate->type;
        } else {
            $data = array_intersect_key($data, array_flip(['type', 'subject', 'body', 'alt_body', 'email_setting_id', 'send_to']));
        }
        try {
            // Preserve campaign/library status. A content edit must not re-arm
            // an existing processed campaign or activate library-only content.
            $verify         = EmailTemplate::TYPE_VERIFY;

            if (
                in_array($data['type'], [$verify, EmailTemplate::TYPE_RESET], true) && (
                    !stristr($data['body'], '$verify_code') ||
                    !stristr($data['alt_body'], '$verify_code')
                )
            ) {
                $message = 'when status: ' . $verify . ' you should add text $verify_code on body and alt body';

                return [
                    'status'    => false,
                    'message'   => $message,
                    'code'      => ResponseError::ERROR_501
                ];
            }

            $emailTemplate->update($data);

            // Editing presentation must never directly dispatch delivery.

            return [
                'status'    => true,
                'code'      => ResponseError::NO_ERROR,
            ];
        } catch (Throwable $e) {

            $this->error($e);

            return [
                'status'    => false,
                'code'      => ResponseError::ERROR_501,
            ];
        }
    }

    public function delete(?array $ids = []): void
    {
        $templates = $this->model()->whereIn('id', is_array($ids) ? $ids : [])->get();
        if ($templates->contains(fn (EmailTemplate $template) => SystemEmailTemplates::isSystemRecord($template))) {
            throw ValidationException::withMessages(['ids' => ['Required system templates cannot be deleted.']]);
        }
        foreach ($templates as $emailTemplate) {
            $emailTemplate->delete();
        }
    }

    public function dropAll(?array $exclude = []): array
    {
        throw ValidationException::withMessages(['ids' => ['Delete selected non-system templates instead. Required account templates must be preserved.']]);
    }
}
