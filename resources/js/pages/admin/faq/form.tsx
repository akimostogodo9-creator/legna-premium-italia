import { Head, Link, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { CheckboxField, TextField } from '@/components/admin/fields';
import { RichTextEditor } from '@/components/admin/rich-text-editor';
import { Button } from '@/components/ui/button';
import { useUnsavedChangesWarning } from '@/hooks/use-unsaved-changes-warning';
import { useTranslations } from '@/lib/i18n';
import faqRoutes from '@/routes/admin/faq';

type Faq = {
    id: number;
    question: string;
    answer: string;
    topic: string | null;
    show_on_home: boolean;
    is_active: boolean;
};

// F-AFAQ-01 (sprint 8) : création / modification d'une question
export default function FaqForm({
    faq,
    topics,
}: {
    faq: Faq | null;
    topics: string[];
}) {
    const t = useTranslations();
    const form = useForm({
        question: faq?.question ?? '',
        answer: faq?.answer ?? '',
        topic: faq?.topic ?? '',
        show_on_home: faq?.show_on_home ?? false,
        is_active: faq?.is_active ?? true,
    });

    useUnsavedChangesWarning(form.isDirty && !form.processing);

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.submit(faq ? faqRoutes.update(faq.id) : faqRoutes.store());
    };

    const title = faq ? t('faq.edit') : t('faq.new');

    return (
        <>
            <Head title={title} />
            <form
                onSubmit={submit}
                className="flex max-w-3xl flex-col gap-6 p-4 md:p-8"
            >
                <h1 className="text-2xl font-semibold">{title}</h1>
                <TextField
                    id="question"
                    label={t('faq.question')}
                    value={form.data.question}
                    onChange={(e) => form.setData('question', e.target.value)}
                    error={form.errors.question}
                    required
                />
                <RichTextEditor
                    id="answer"
                    label={t('faq.answer')}
                    value={form.data.answer}
                    onChange={(html) => form.setData('answer', html)}
                    error={form.errors.answer}
                />
                <TextField
                    id="topic"
                    label={t('faq.topic')}
                    hint={t('faq.topic_hint')}
                    list="faq-topics"
                    value={form.data.topic}
                    onChange={(e) => form.setData('topic', e.target.value)}
                    error={form.errors.topic}
                />
                <datalist id="faq-topics">
                    {topics.map((topic) => (
                        <option key={topic} value={topic} />
                    ))}
                </datalist>
                <CheckboxField
                    id="show_on_home"
                    label={t('faq.show_on_home')}
                    checked={form.data.show_on_home}
                    onChange={(v) => form.setData('show_on_home', v)}
                />
                <CheckboxField
                    id="is_active"
                    label={t('faq.active')}
                    checked={form.data.is_active}
                    onChange={(v) => form.setData('is_active', v)}
                />
                <div className="flex gap-3">
                    <Button type="submit" disabled={form.processing}>
                        {t('actions.save')}
                    </Button>
                    <Button variant="secondary" asChild>
                        <Link href={faqRoutes.index()}>
                            {t('actions.cancel')}
                        </Link>
                    </Button>
                </div>
            </form>
        </>
    );
}
