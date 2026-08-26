import DOMPurify from 'dompurify';

type Props = {
    content: string;
    className?: string;
};

const baseClassName = [
    'text-sm leading-relaxed',

    '[&_p]:my-2',

    '[&_h1]:mt-6',
    '[&_h1]:mb-3',
    '[&_h1]:text-2xl',
    '[&_h1]:font-bold',

    '[&_h2]:mt-5',
    '[&_h2]:mb-2',
    '[&_h2]:text-xl',
    '[&_h2]:font-semibold',

    '[&_h3]:mt-4',
    '[&_h3]:mb-2',
    '[&_h3]:text-lg',
    '[&_h3]:font-semibold',

    '[&_ul]:my-3',
    '[&_ul]:list-disc',
    '[&_ul]:pl-6',

    '[&_ol]:my-3',
    '[&_ol]:list-decimal',
    '[&_ol]:pl-6',

    '[&_li]:my-1',

    '[&_blockquote]:my-4',
    '[&_blockquote]:border-l-4',
    '[&_blockquote]:pl-4',
    '[&_blockquote]:italic',
    '[&_blockquote]:text-muted-foreground',

    '[&_a]:text-primary',
    '[&_a]:font-medium',
    '[&_a]:underline',
    '[&_a]:underline-offset-4',
    '[&_a:hover]:opacity-80',

    '[&_img]:my-4',
    '[&_img]:max-w-full',
    '[&_img]:rounded-none',
    '[&_img]:shadow-md',
].join(' ');

export function RichTextContent({
    content,
    className = '',
}: Props) {
    return (
        <div
            className={`${baseClassName} ${className}`}
            dangerouslySetInnerHTML={{
                __html: DOMPurify.sanitize(content),
            }}
        />
    );
}