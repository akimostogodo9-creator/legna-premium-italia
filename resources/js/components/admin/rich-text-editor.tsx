import { EditorContent, useEditor } from '@tiptap/react';
import type { Editor } from '@tiptap/react';
import StarterKit from '@tiptap/starter-kit';
import {
    Bold,
    Heading2,
    Heading3,
    Italic,
    Link2,
    List,
    ListOrdered,
} from 'lucide-react';
import type { ReactNode } from 'react';
import InputError from '@/components/input-error';
import { Label } from '@/components/ui/label';
import { useTranslations } from '@/lib/i18n';

type Props = {
    id: string;
    label: string;
    value: string;
    onChange: (html: string) => void;
    error?: string;
    hint?: string;
};

/**
 * Éditeur de texte riche (étape 5) : uniquement les balises acceptées par HtmlSanitizer
 * (paragraphes, gras, italique, titres H2/H3, listes, liens). translate="no" (F-ADM-00b).
 * Sprint 4 — Tiptap v3 : le lien fait déjà partie du StarterKit (configuré ici, pas ajouté en double).
 */
export function RichTextEditor({
    id,
    label,
    value,
    onChange,
    error,
    hint,
}: Props) {
    const editor = useEditor({
        immediatelyRender: false,
        extensions: [
            StarterKit.configure({
                heading: { levels: [2, 3] },
                blockquote: false,
                code: false,
                codeBlock: false,
                horizontalRule: false,
                strike: false,
                underline: false,
                link: { openOnClick: false, autolink: true },
            }),
        ],
        content: value || '',
        editorProps: {
            attributes: {
                id,
                translate: 'no',
                'aria-label': label,
                class: 'prose-admin min-h-40 px-3 py-2 text-sm focus:outline-none',
            },
        },
        onUpdate: ({ editor: e }) => onChange(e.isEmpty ? '' : e.getHTML()),
    });

    return (
        <div className="grid gap-2">
            <Label htmlFor={id}>{label}</Label>
            <div className="rounded-md border border-input shadow-xs focus-within:ring-2 focus-within:ring-ring">
                {editor && <Toolbar editor={editor} />}
                <EditorContent editor={editor} />
            </div>
            {hint && !error && (
                <p className="text-sm text-muted-foreground">{hint}</p>
            )}
            <InputError message={error} />
        </div>
    );
}

function Toolbar({ editor }: { editor: Editor }) {
    const t = useTranslations();

    const button = (
        active: boolean,
        onClick: () => void,
        icon: ReactNode,
        title: string,
    ) => (
        <button
            type="button"
            title={title}
            aria-label={title}
            aria-pressed={active}
            onClick={onClick}
            className={`rounded p-1.5 hover:bg-muted ${active ? 'bg-muted text-foreground' : 'text-muted-foreground'}`}
        >
            {icon}
        </button>
    );

    const setLink = () => {
        const previous = editor.getAttributes('link').href as
            | string
            | undefined;
        const url = window.prompt(
            t('editor.link_prompt'),
            previous ?? 'https://',
        );

        if (url === null) {
            return;
        }

        if (url === '') {
            editor.chain().focus().unsetLink().run();

            return;
        }

        editor
            .chain()
            .focus()
            .extendMarkRange('link')
            .setLink({ href: url })
            .run();
    };

    return (
        <div className="flex flex-wrap gap-1 border-b border-input p-1">
            {button(
                editor.isActive('bold'),
                () => editor.chain().focus().toggleBold().run(),
                <Bold className="size-4" />,
                t('editor.bold'),
            )}
            {button(
                editor.isActive('italic'),
                () => editor.chain().focus().toggleItalic().run(),
                <Italic className="size-4" />,
                t('editor.italic'),
            )}
            {button(
                editor.isActive('heading', { level: 2 }),
                () => editor.chain().focus().toggleHeading({ level: 2 }).run(),
                <Heading2 className="size-4" />,
                t('editor.heading'),
            )}
            {button(
                editor.isActive('heading', { level: 3 }),
                () => editor.chain().focus().toggleHeading({ level: 3 }).run(),
                <Heading3 className="size-4" />,
                t('editor.subheading'),
            )}
            {button(
                editor.isActive('bulletList'),
                () => editor.chain().focus().toggleBulletList().run(),
                <List className="size-4" />,
                t('editor.bullet_list'),
            )}
            {button(
                editor.isActive('orderedList'),
                () => editor.chain().focus().toggleOrderedList().run(),
                <ListOrdered className="size-4" />,
                t('editor.ordered_list'),
            )}
            {button(
                editor.isActive('link'),
                setLink,
                <Link2 className="size-4" />,
                t('editor.link'),
            )}
        </div>
    );
}
