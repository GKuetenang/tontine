import { NotebookPenIcon } from 'lucide-react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { useTranslation } from '@/hooks/use-translation';

export function MeetingNotesPlaceholder() {
    const { t } = useTranslation();

    return (
        <Card>
            <CardHeader>
                <CardTitle>{t('Notes')}</CardTitle>
            </CardHeader>

            <CardContent>
                <div className="flex flex-col items-center justify-center gap-3 py-10 text-center">
                    <div className="rounded-full bg-muted p-3">
                        <NotebookPenIcon className="size-6 text-muted-foreground" />
                    </div>

                    <div className="space-y-1">
                        <p className="font-medium">
                            {t('L’assise n’est pas encore ouverte')}
                        </p>

                        <p className="max-w-md text-sm text-muted-foreground">
                            {t(
                                'Les notes pourront être ajoutées lorsque l’assise sera en cours.',
                            )}
                        </p>
                    </div>
                </div>
            </CardContent>
        </Card>
    );
}
