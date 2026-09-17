import { UsersIcon } from 'lucide-react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { useTranslation } from '@/hooks/use-translation';

export function AttendancePlaceholder() {
    const { t } = useTranslation();

    return (
        <Card>
            <CardHeader>
                <CardTitle>{t('Présences')}</CardTitle>
            </CardHeader>

            <CardContent>
                <div className="flex flex-col items-center justify-center gap-3 py-10 text-center">
                    <UsersIcon className="size-8 text-muted-foreground" />

                    <div className="space-y-1">
                        <p className="font-medium">
                            {t('L’assise n’est pas encore ouverte')}
                        </p>

                        <p className="max-w-md text-sm text-muted-foreground">
                            {t(
                                'Les présences seront initialisées automatiquement lors de l’ouverture de l’assise.',
                            )}
                        </p>
                    </div>
                </div>
            </CardContent>
        </Card>
    );
}
