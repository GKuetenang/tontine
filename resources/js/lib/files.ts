export function fileToBase64(
    file: File,
): Promise<string> {
    return new Promise(
        (resolve, reject) => {
            const reader =
                new FileReader();

            reader.onload = () => {
                if (
                    typeof reader.result !==
                    'string'
                ) {
                    reject(
                        new Error(
                            'Impossible de convertir le fichier.',
                        ),
                    );

                    return;
                }

                resolve(reader.result);
            };

            reader.onerror = () => {
                reject(
                    reader.error ??
                    new Error(
                        'Impossible de lire le fichier.',
                    ),
                );
            };

            reader.readAsDataURL(file);
        },
    );
}

export const ALLOWED_IMAGE_TYPES = [
    'image/jpeg',
    'image/png',
    'image/webp',
];

const MAX_IMAGE_SIZE =
    2 * 1024 * 1024;

export function validateEditorImage(
    file: File,
): string | null {
    if (
        !ALLOWED_IMAGE_TYPES.includes(
            file.type,
        )
    ) {
        return 'Seules les images JPG, PNG et WEBP sont autorisées.';
    }

    if (
        file.size > MAX_IMAGE_SIZE
    ) {
        return 'L’image ne doit pas dépasser 2 Mo.';
    }

    return null;
}