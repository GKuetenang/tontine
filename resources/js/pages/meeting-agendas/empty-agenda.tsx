import { ClipboardListIcon } from 'lucide-react';
import { useTranslation } from '@/hooks/use-translation';

export function EmptyAgenda() {
    const { t } = useTranslation();

    return (
        <div className="flex flex-col items-center justify-center gap-3 py-10 text-center">
            <div className="rounded-full bg-muted p-3">
                <ClipboardListIcon className="size-6 text-muted-foreground" />
            </div>

            <div className="space-y-1">
                <p className="font-medium">
                    {t('Aucun point à l’ordre du jour')}
                </p>

                <p className="text-sm text-muted-foreground">
                    {t(
                        'Ajoutez les sujets qui seront abordés pendant l’assise.',
                    )}
                </p>
            </div>
        </div>
    );
}
