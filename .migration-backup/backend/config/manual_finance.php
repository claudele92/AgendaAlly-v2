<?php
declare(strict_types=1);
return [
    // Manual human execution, never a provider/SMTP activation switch.
    'evidence_methods'=>[
        'bank_transfer'=>['attachment_required'=>false,'terminal_reference_required'=>true,
            'reference_scope'=>'institution_method_original_executor','authority'=>'finance_checked_terminal_institution_reference'],
        'mobile_money'=>['attachment_required'=>false,'terminal_reference_required'=>true,
            'reference_scope'=>'institution_method_original_executor','authority'=>'finance_checked_terminal_institution_reference'],
    ],
    'smtp_enabled'=>false,'provider_execution_enabled'=>false,'specialist_payout_enabled'=>false,
];
