type Props = {
    title: string;
    subtitle?: string | null;
    as?: 'h1' | 'h2';
    className?: string;
};

// Titre de section : grand, aligné à gauche, sans étiquette décorative au-dessus
export function SectionHeading({
    title,
    subtitle,
    as: Tag = 'h2',
    className = '',
}: Props) {
    return (
        <div className={`max-w-2xl ${className}`}>
            <Tag className="text-[2rem] text-legno-900 md:text-[2.75rem]">
                {title}
            </Tag>
            {subtitle && (
                <p className="mt-4 text-lg text-legno-700">{subtitle}</p>
            )}
        </div>
    );
}
