<?php

namespace App\Http\Requests\Admin;

use App\Services\Ordering\TemplateRenderer;
use App\Services\Ordering\WhatsAppLinkBuilder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

// F-ASET-02/03/04 : contacts et modèles du message « Ordina » — sprint 6
class OrderSettingsRequest extends FormRequest
{
    public const TEMPLATE_FIELDS = [
        'whatsapp_message', 'whatsapp_product_message',
        'order_subject_template', 'order_body_template',
        'availability_subject_template', 'availability_body_template',
        'general_subject_template', 'general_body_template',
    ];

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'order_email' => ['nullable', 'email:rfc', 'max:191'],
            'contact_email' => ['nullable', 'email:rfc', 'max:191'],
            'whatsapp_number' => ['nullable', 'string', 'max:30'],
            'whatsapp_message' => ['nullable', 'string', 'max:500'],
            'whatsapp_product_message' => ['nullable', 'string', 'max:500'],
            'order_subject_template' => ['required', 'string', 'max:150'],
            'order_body_template' => ['required', 'string', 'max:1500'],
            'availability_subject_template' => ['required', 'string', 'max:150'],
            'availability_body_template' => ['required', 'string', 'max:1500'],
            'general_subject_template' => ['required', 'string', 'max:150'],
            'general_body_template' => ['required', 'string', 'max:1500'],
        ];
    }

    /**
     * @return list<callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                // R-WA-02 : numéro international valide
                if ($this->filled('whatsapp_number') && WhatsAppLinkBuilder::normalize($this->input('whatsapp_number')) === null) {
                    $validator->errors()->add('whatsapp_number', __('admin.settings.invalid_whatsapp'));
                }

                // R-ORDINA-07 : seulement les variables autorisées
                $renderer = app(TemplateRenderer::class);

                foreach (self::TEMPLATE_FIELDS as $field) {
                    $unknown = $renderer->unknownVariables((string) $this->input($field));

                    if ($unknown !== []) {
                        $list = implode(', ', array_map(fn ($v) => '{'.$v.'}', $unknown));
                        $validator->errors()->add($field, __('admin.settings.unknown_variable', ['variables' => $list]));
                    }
                }
            },
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'order_email' => __('admin.settings.order_email'),
            'contact_email' => __('admin.settings.contact_email'),
            'whatsapp_number' => __('admin.settings.whatsapp_number'),
            'order_subject_template' => __('admin.settings.subject'),
            'availability_subject_template' => __('admin.settings.subject'),
            'general_subject_template' => __('admin.settings.subject'),
            'order_body_template' => __('admin.settings.body'),
            'availability_body_template' => __('admin.settings.body'),
            'general_body_template' => __('admin.settings.body'),
        ];
    }
}
