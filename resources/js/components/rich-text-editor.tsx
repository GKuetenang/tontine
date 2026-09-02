import FileHandler from '@tiptap/extension-file-handler';
import Image from '@tiptap/extension-image';
import Placeholder from '@tiptap/extension-placeholder';

import { EditorContent, useEditor, useEditorState } from '@tiptap/react';

import StarterKit from '@tiptap/starter-kit';

import {
    BoldIcon,
    Heading1Icon,
    Heading2Icon,
    Heading3Icon,
    ItalicIcon,
    Link2OffIcon,
    LinkIcon,
    ListIcon,
    ListOrderedIcon,
    Redo2Icon,
    UnderlineIcon,
    Undo2Icon,
} from 'lucide-react';

import { useState } from 'react';
import { toast } from 'sonner';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import {
    ALLOWED_IMAGE_TYPES,
    fileToBase64,
    validateEditorImage,
} from '@/lib/files';

type Props = {
    value?: string;
    onChange: (value: string) => void;
    placeholder?: string;
};

const EMPTY_EDITOR_STATE = {
    canUndo: false,
    canRedo: false,

    isBold: false,
    isItalic: false,
    isUnderline: false,

    isHeading1: false,
    isHeading2: false,
    isHeading3: false,

    isBulletList: false,
    isOrderedList: false,

    isLink: false,
};

export function RichTextEditor({
    value = '',
    onChange,
    placeholder = 'Saisir les notes de l’assise...',
}: Props) {
    const [linkOpen, setLinkOpen] = useState(false);

    const [linkUrl, setLinkUrl] = useState('');

    const editor = useEditor({
        immediatelyRender: false,

        extensions: [
            StarterKit.configure({
                heading: {
                    levels: [1, 2, 3],
                },

                bulletList: {
                    keepMarks: true,
                },

                orderedList: {
                    keepMarks: true,
                },

                link: {
                    openOnClick: false,
                    autolink: true,
                },

                undoRedo: {
                    depth: 100,
                },
            }),

            Placeholder.configure({
                placeholder,
            }),

            Image.configure({
                allowBase64: true,

                resize: {
                    enabled: true,

                    directions: [
                        'top-left',
                        'top-right',
                        'bottom-left',
                        'bottom-right',
                    ],

                    minWidth: 120,
                    minHeight: 80,

                    alwaysPreserveAspectRatio: true,
                },

                HTMLAttributes: {
                    class: 'my-4 max-w-full rounded-lg',
                },
            }),

            FileHandler.configure({
                allowedMimeTypes: ALLOWED_IMAGE_TYPES,

                consumePasteEvent: true,

                onDrop: async (editor, files, pos) => {
                    for (const file of files) {
                        const error = validateEditorImage(file);

                        if (error) {
                            toast.error(error);

                            continue;
                        }

                        try {
                            const src = await fileToBase64(file);

                            editor
                                .chain()
                                .focus()
                                .insertContentAt(pos, {
                                    type: 'image',
                                    attrs: {
                                        src,
                                        alt: file.name,
                                    },
                                })
                                .run();
                        } catch {
                            toast.error('Impossible d’insérer l’image.');
                        }
                    }
                },

                onPaste: async (editor, files) => {
                    for (const file of files) {
                        const error = validateEditorImage(file);

                        if (error) {
                            toast.error(error);

                            continue;
                        }

                        try {
                            const src = await fileToBase64(file);

                            editor
                                .chain()
                                .focus()
                                .setImage({
                                    src,
                                    alt: file.name,
                                })
                                .run();
                        } catch {
                            toast.error('Impossible d’insérer l’image.');
                        }
                    }
                },
            }),
        ],

        content: value,

        editorProps: {
            attributes: {
                class: [
                    'rich-text-editor',
                    'min-h-48 px-4 py-3 text-sm outline-none',

                    '[&_p]:my-2',
                    '[&_p]:leading-relaxed',

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
                    '[&_blockquote]:border-primary/40',
                    '[&_blockquote]:pl-4',
                    '[&_blockquote]:italic',
                    '[&_blockquote]:text-muted-foreground',

                    '[&_a]:font-medium',
                    '[&_a]:text-primary',
                    '[&_a]:underline',
                    '[&_a]:underline-offset-4',
                    '[&_a:hover]:opacity-80',

                    '[&_img]:my-4',
                    '[&_img]:max-w-full',
                    '[&_img]:rounded-none',

                    '[&_.is-editor-empty:first-child::before]:pointer-events-none',
                    '[&_.is-editor-empty:first-child::before]:float-left',
                    '[&_.is-editor-empty:first-child::before]:h-0',
                    '[&_.is-editor-empty:first-child::before]:text-muted-foreground',
                    '[&_.is-editor-empty:first-child::before]:content-[attr(data-placeholder)]',
                ].join(' '),
            },
        },

        onUpdate({ editor }) {
            onChange(editor.getHTML());
        },
    });

    const editorStateResult = useEditorState({
        editor,

        selector: ({ editor }) => {
            if (!editor) {
                return EMPTY_EDITOR_STATE;
            }

            return {
                canUndo: editor.can().undo(),

                canRedo: editor.can().redo(),

                isBold: editor.isActive('bold'),

                isItalic: editor.isActive('italic'),

                isUnderline: editor.isActive('underline'),

                isHeading1: editor.isActive('heading', {
                    level: 1,
                }),

                isHeading2: editor.isActive('heading', {
                    level: 2,
                }),

                isHeading3: editor.isActive('heading', {
                    level: 3,
                }),

                isBulletList: editor.isActive('bulletList'),

                isOrderedList: editor.isActive('orderedList'),

                isLink: editor.isActive('link'),
            };
        },
    });

    const editorState = editorStateResult ?? EMPTY_EDITOR_STATE;

    if (!editor) {
        return null;
    }

    const toolbarButtonClass = 'size-8';

    const openLinkEditor = () => {
        const previousUrl = editor.getAttributes('link').href as
            string | undefined;

        setLinkUrl(previousUrl ?? '');

        setLinkOpen(true);
    };

    const applyLink = () => {
        const url = linkUrl.trim();

        if (!url) {
            editor.chain().focus().extendMarkRange('link').unsetLink().run();

            setLinkOpen(false);

            return;
        }

        editor
            .chain()
            .focus()
            .extendMarkRange('link')
            .setLink({
                href: url,
            })
            .run();

        setLinkOpen(false);
    };

    const removeLink = () => {
        editor.chain().focus().extendMarkRange('link').unsetLink().run();

        setLinkUrl('');

        setLinkOpen(false);
    };

    const undo = () => {
        if (!editorState.canUndo) {
            return;
        }

        editor.chain().focus().undo().run();
    };

    const redo = () => {
        if (!editorState.canRedo) {
            return;
        }

        editor.chain().focus().redo().run();
    };

    return (
        <div
            className="overflow-hidden rounded-md border bg-background"
            // onPointerDown={(event) => {
            //     event.stopPropagation();
            // }}
            // onMouseDown={(event) => {
            //     event.stopPropagation();
            // }}
        >
            <div className="flex flex-wrap items-center gap-1 border-b bg-muted/30 p-2">
                <Button
                    type="button"
                    variant={editorState.isBold ? 'secondary' : 'ghost'}
                    size="icon"
                    className={toolbarButtonClass}
                    title="Gras"
                    onClick={() => editor.chain().focus().toggleBold().run()}
                >
                    <BoldIcon className="size-4" />
                </Button>

                <Button
                    type="button"
                    variant={editorState.isItalic ? 'secondary' : 'ghost'}
                    size="icon"
                    className={toolbarButtonClass}
                    title="Italique"
                    onClick={() => editor.chain().focus().toggleItalic().run()}
                >
                    <ItalicIcon className="size-4" />
                </Button>

                <Button
                    type="button"
                    variant={editorState.isUnderline ? 'secondary' : 'ghost'}
                    size="icon"
                    className={toolbarButtonClass}
                    title="Souligné"
                    onClick={() =>
                        editor.chain().focus().toggleUnderline().run()
                    }
                >
                    <UnderlineIcon className="size-4" />
                </Button>

                <ToolbarSeparator />

                <Button
                    type="button"
                    variant={editorState.isHeading1 ? 'secondary' : 'ghost'}
                    size="icon"
                    className={toolbarButtonClass}
                    title="Titre 1"
                    onClick={() =>
                        editor
                            .chain()
                            .focus()
                            .toggleHeading({
                                level: 1,
                            })
                            .run()
                    }
                >
                    <Heading1Icon className="size-4" />
                </Button>

                <Button
                    type="button"
                    variant={editorState.isHeading2 ? 'secondary' : 'ghost'}
                    size="icon"
                    className={toolbarButtonClass}
                    title="Titre 2"
                    onClick={() =>
                        editor
                            .chain()
                            .focus()
                            .toggleHeading({
                                level: 2,
                            })
                            .run()
                    }
                >
                    <Heading2Icon className="size-4" />
                </Button>

                <Button
                    type="button"
                    variant={editorState.isHeading3 ? 'secondary' : 'ghost'}
                    size="icon"
                    className={toolbarButtonClass}
                    title="Titre 3"
                    onClick={() =>
                        editor
                            .chain()
                            .focus()
                            .toggleHeading({
                                level: 3,
                            })
                            .run()
                    }
                >
                    <Heading3Icon className="size-4" />
                </Button>

                <ToolbarSeparator />

                <Button
                    type="button"
                    variant={editorState.isBulletList ? 'secondary' : 'ghost'}
                    size="icon"
                    className={toolbarButtonClass}
                    title="Liste à puces"
                    onClick={() =>
                        editor.chain().focus().toggleBulletList().run()
                    }
                >
                    <ListIcon className="size-4" />
                </Button>

                <Button
                    type="button"
                    variant={editorState.isOrderedList ? 'secondary' : 'ghost'}
                    size="icon"
                    className={toolbarButtonClass}
                    title="Liste numérotée"
                    onClick={() =>
                        editor.chain().focus().toggleOrderedList().run()
                    }
                >
                    <ListOrderedIcon className="size-4" />
                </Button>

                <ToolbarSeparator />

                <Popover open={linkOpen} onOpenChange={setLinkOpen}>
                    <PopoverTrigger asChild>
                        <Button
                            type="button"
                            variant={editorState.isLink ? 'secondary' : 'ghost'}
                            size="icon"
                            className={toolbarButtonClass}
                            title="Lien"
                            onClick={openLinkEditor}
                        >
                            <LinkIcon className="size-4" />
                        </Button>
                    </PopoverTrigger>

                    <PopoverContent className="w-80" align="start">
                        <div className="space-y-3">
                            <div className="space-y-1">
                                <p className="text-sm font-medium">
                                    Ajouter un lien
                                </p>

                                <p className="text-xs text-muted-foreground">
                                    Saisissez l’adresse du lien.
                                </p>
                            </div>

                            <Input
                                type="url"
                                value={linkUrl}
                                placeholder="https://example.com"
                                autoFocus
                                onChange={(event) =>
                                    setLinkUrl(event.target.value)
                                }
                                onKeyDown={(event) => {
                                    if (event.key === 'Enter') {
                                        event.preventDefault();

                                        applyLink();
                                    }
                                }}
                            />

                            <div className="flex items-center justify-between">
                                {editorState.isLink ? (
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="sm"
                                        onClick={removeLink}
                                    >
                                        <Link2OffIcon className="size-4" />
                                        Retirer
                                    </Button>
                                ) : (
                                    <div />
                                )}

                                <Button
                                    type="button"
                                    size="sm"
                                    onClick={applyLink}
                                >
                                    Appliquer
                                </Button>
                            </div>
                        </div>
                    </PopoverContent>
                </Popover>

                <ToolbarSeparator />

                <Button
                    type="button"
                    variant="ghost"
                    size="icon"
                    className={toolbarButtonClass}
                    title="Annuler"
                    disabled={!editorState.canUndo}
                    onClick={undo}
                >
                    <Undo2Icon className="size-4" />
                </Button>

                <Button
                    type="button"
                    variant="ghost"
                    size="icon"
                    className={toolbarButtonClass}
                    title="Rétablir"
                    disabled={!editorState.canRedo}
                    onClick={redo}
                >
                    <Redo2Icon className="size-4" />
                </Button>
            </div>

            <EditorContent editor={editor} />

            <div className="border-t bg-muted/20 px-4 py-2">
                <p className="text-xs text-muted-foreground">
                    Vous pouvez glisser-déposer ou coller une image JPG, PNG ou
                    WEBP. Cliquez sur une image pour la redimensionner.
                </p>
            </div>
        </div>
    );
}

function ToolbarSeparator() {
    return <div className="mx-1 h-5 w-px bg-border" aria-hidden="true" />;
}
